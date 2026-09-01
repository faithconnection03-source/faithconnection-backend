/* ==========================================================================
   FAITHCONNECTION - FUNCTIONAL SOCIAL / FELLOWSHIP CORE
   UI/theme/config are kept intact. Data features use the existing API endpoint.
   ========================================================================== */

import { initializeApp } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-app.js";
import {
    getAuth, GoogleAuthProvider, signInWithPopup, signOut,
    createUserWithEmailAndPassword, signInWithEmailAndPassword
} from "https://www.gstatic.com/firebasejs/10.8.0/firebase-auth.js";
import {
    getFirestore, doc, setDoc, getDoc, collection, query, where, getDocs
} from "https://www.gstatic.com/firebasejs/10.8.0/firebase-firestore.js";

/* --- Existing Firebase configuration: unchanged --- */
const firebaseConfig = {
    apiKey: "AIzaSyB8VePl5WBSilDyZwq8hSLdYCrmBiuLIPI",
    authDomain: "faithconnection-af4f2.firebaseapp.com",
    projectId: "faithconnection-af4f2",
    storageBucket: "faithconnection-af4f2.firebasestorage.app",
    messagingSenderId: "135734412322",
    appId: "1:135734412322:web:11b16fe5f8130b98a4367b",
    measurementId: "G-1T1KQPCN4R"
};

const app = initializeApp(firebaseConfig);
const auth = getAuth(app);
const db = getFirestore(app);
const googleProvider = new GoogleAuthProvider();

/* --- Existing API configuration: unchanged --- */
const API_BASE_URL = "https://faithconnection.free.je/api.php";

/* --- App state --- */
let currentUserProfile = null;
let postsDB = [];
let usersDB = [];
let ministriesDB = [];
let chatsDB = [];
let storiesDB = [];
let currentFeedFilter = "all";
let activeChatUserId = null;
let activeGroupId = null;
let currentActiveProfileId = null;
let selectedPostImageBase64 = "";
let selectedAvatarBase64 = "";
let storyTimer = null;
let currentVerseIndex = 0;
let pendingCommentPostId = null;

let loggedInUserId = localStorage.getItem("fc_logged_user_v6") || null;
Object.defineProperty(window, "loggedInUserId", { get: () => loggedInUserId, configurable: true });

/* --------------------------------------------------------------------------
   Helpers
   -------------------------------------------------------------------------- */
const $ = (id) => document.getElementById(id);

function esc(value) {
    return String(value ?? "")
        .replace(/&/g, "&amp;").replace(/</g, "&lt;")
        .replace(/>/g, "&gt;").replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function avatarUrl(user, size = 200) {
    if (user?.avatar) return user.avatar;
    const name = encodeURIComponent(user?.name || "Believer");
    return `https://ui-avatars.com/api/?name=${name}&size=${size}&background=0e1428&color=00f2fe&bold=true`;
}

function formatDate(value) {
    if (!value) return "";
    const d = new Date(String(value).replace(" ", "T"));
    if (Number.isNaN(d.getTime())) return String(value);
    const diff = Date.now() - d.getTime();
    if (diff < 60 * 1000) return "Just now";
    if (diff < 60 * 60 * 1000) return `${Math.floor(diff / 60000)}m`;
    if (diff < 24 * 60 * 60 * 1000) return `${Math.floor(diff / 3600000)}h`;
    return d.toLocaleDateString([], { day: "numeric", month: "short", year: "numeric" });
}

async function api(action, method = "GET", data = null, extra = {}) {
    const params = new URLSearchParams({ action, ...extra });
    const options = { method, headers: {} };
    if (data !== null) {
        options.headers["Content-Type"] = "application/json";
        options.body = JSON.stringify(data);
    }
    const response = await fetch(`${API_BASE_URL}?${params.toString()}`, options);
    const text = await response.text();
    let json;
    try { json = JSON.parse(text); }
    catch { throw new Error(text || `API error (${response.status})`); }
    if (!response.ok || json.success === false) {
        throw new Error(json.message || `Request failed (${response.status})`);
    }
    return json;
}

function showToast(message, isError = false) {
    const toast = $("toast-notification");
    if (!toast) return;
    toast.innerHTML = `<i class="fa-solid ${isError ? "fa-circle-exclamation" : "fa-circle-check"}" style="color:${isError ? "var(--accent-red)" : "var(--accent-cyan)"}; margin-right:.5rem;"></i>${esc(message)}`;
    toast.style.display = "block";
    clearTimeout(showToast.timer);
    showToast.timer = setTimeout(() => toast.style.display = "none", 3200);
}

function showAuthAlert(message, isError = true) {
    const box = $("auth-alert");
    if (!box) return;
    box.className = isError ? "auth-alert error" : "auth-alert success";
    box.innerText = message;
    box.style.display = "block";
}
function clearAuthAlert() {
    const box = $("auth-alert");
    if (box) box.style.display = "none";
}
function syncStorage() {
    if (loggedInUserId) localStorage.setItem("fc_logged_user_v6", String(loggedInUserId));
    else localStorage.removeItem("fc_logged_user_v6");
}

function setUser(user) {
    currentUserProfile = user;
    loggedInUserId = String(user.id);
    syncStorage();
}

function isMe(id) {
    return String(id) === String(loggedInUserId);
}

function readFileAsDataUrl(file, maxBytes = 2.5 * 1024 * 1024) {
    return new Promise((resolve, reject) => {
        if (!file) return resolve("");
        if (file.size > maxBytes) return reject(new Error("Image is too large. Please use an image under 2.5 MB."));
        const reader = new FileReader();
        reader.onload = () => resolve(String(reader.result || ""));
        reader.onerror = () => reject(new Error("Could not read image."));
        reader.readAsDataURL(file);
    });
}

/* --------------------------------------------------------------------------
   Authentication
   -------------------------------------------------------------------------- */
function switchAuthTab(tab) {
    clearAuthAlert();
    const login = $("login-form"), reg = $("register-form");
    const a = $("tab-login-btn"), b = $("tab-register-btn");
    if (tab === "login") {
        login.style.display = "flex"; reg.style.display = "none";
        a.classList.add("active"); b.classList.remove("active");
    } else {
        login.style.display = "none"; reg.style.display = "flex";
        a.classList.remove("active"); b.classList.add("active");
    }
}

async function findFirebaseUserByIdentifier(identifier) {
    const value = String(identifier || "").trim().toLowerCase();
    if (!value) throw new Error("Enter your username, email or mobile number.");

    // Email can be sent directly to Firebase Authentication.
    if (value.includes("@")) return value;

    // Username/mobile are resolved from the existing Firestore profile.
    for (const field of ["username", "phone"]) {
        const snap = await getDocs(query(collection(db, "users"), where(field, "==", value)));
        if (!snap.empty) {
            const profile = snap.docs[0].data();
            if (profile.email) return profile.email;
        }
    }
    throw new Error("User not found. Please check your username/email/mobile.");
}

async function handleLogin(e) {
    e.preventDefault();
    clearAuthAlert();
    try {
        const identifier = $("login-identifier").value.trim();
        const password = $("login-password").value;
        const email = await findFirebaseUserByIdentifier(identifier);
        const credential = await signInWithEmailAndPassword(auth, email, password);
        const uid = credential.user.uid;

        let profileSnap = await getDoc(doc(db, "users", uid));
        if (profileSnap.exists()) {
            setUser({ id: uid, ...profileSnap.data() });
        } else {
            const profile = {
                id: uid, name: credential.user.displayName || email.split("@")[0],
                email, avatar: credential.user.photoURL || "", username: email.split("@")[0]
            };
            await setDoc(doc(db, "users", uid), profile, { merge: true });
            setUser(profile);
        }

        await initAppSession();
        showToast("Login successful! Welcome to FaithConnection.");
    } catch (err) {
        console.error("Login error:", err);
        let message = err?.message || "Login failed.";
        if (err?.code === "auth/invalid-credential" || err?.code === "auth/wrong-password") message = "Invalid email/username or password.";
        if (err?.code === "auth/user-not-found") message = "Account not found.";
        if (err?.code === "auth/too-many-requests") message = "Too many attempts. Please wait a moment and try again.";
        showAuthAlert(message);
    }
}

async function handleRegister(e) {
    e.preventDefault();
    clearAuthAlert();
    try {
        const name = $("reg-name").value.trim();
        const username = $("reg-username")?.value.trim().toLowerCase() || "";
        const email = $("reg-email").value.trim().toLowerCase();
        const phone = $("reg-phone")?.value.trim() || "";
        const role = $("reg-role")?.value || "Church Member";
        const church = $("reg-church")?.value.trim() || "Fellowship Community Church";
        const password = $("reg-password").value;

        if (!name || !email || !password) throw new Error("Please fill all required fields.");
        if (password.length < 6) throw new Error("Password must be at least 6 characters.");

        // Check username/mobile before creating the Firebase account.
        if (username) {
            const existingUsername = await getDocs(query(collection(db, "users"), where("username", "==", username)));
            if (!existingUsername.empty) throw new Error("Username is already taken.");
        }
        if (phone) {
            const existingPhone = await getDocs(query(collection(db, "users"), where("phone", "==", phone)));
            if (!existingPhone.empty) throw new Error("Mobile number is already registered.");
        }

        const credential = await createUserWithEmailAndPassword(auth, email, password);
        const profile = {
            id: credential.user.uid, name, username: username || email.split("@")[0],
            email, phone, role, church, avatar: "", bio: "",
            createdAt: new Date().toISOString()
        };
        await setDoc(doc(db, "users", credential.user.uid), profile, { merge: true });

        // Keep the new account signed out so the existing login flow stays unchanged.
        await signOut(auth);
        showAuthAlert("Account created successfully. You can now sign in.", false);
        switchAuthTab("login");
        $("login-identifier").value = email;
        $("login-password").value = "";
    } catch (err) {
        console.error("Registration error:", err);
        let message = err?.message || "Registration failed.";
        if (err?.code === "auth/email-already-in-use") message = "This email is already registered. Please sign in instead.";
        if (err?.code === "auth/invalid-email") message = "Please enter a valid email address.";
        if (err?.code === "auth/weak-password") message = "Password must be at least 6 characters.";
        showAuthAlert(message);
    }
}

async function handleGoogleSignIn() {
    clearAuthAlert();
    try {
        const credential = await signInWithPopup(auth, googleProvider);
        const user = credential.user;
        const result = await api("google_login", "POST", {
            email: user.email,
            name: user.displayName || "Google User",
            avatar: user.photoURL || ""
        });
        setUser(result.user);
        await initAppSession();
        showToast(`Welcome, ${user.displayName || result.user.name}!`);
    } catch (err) {
        console.error("Google sign-in:", err);
        showAuthAlert(err.message || "Google sign-in failed.");
    }
}

async function handleContinueWithCustomGoogleAccount() {
    const email = $("google-custom-email")?.value.trim().toLowerCase();
    const name = $("google-custom-name")?.value.trim() || "Google User";
    if (!email) return showToast("Enter your Gmail address first.", true);
    try {
        const result = await api("google_login", "POST", { email, name, avatar: "" });
        setUser(result.user);
        closeModal("google-auth-modal");
        await initAppSession();
        showToast("Google account connected.");
    } catch (err) {
        showToast(err.message, true);
    }
}

function toggleCustomGoogleInput() {
    const section = $("google-custom-email-section");
    const accounts = $("google-accounts-list");
    if (!section) return;
    const show = section.style.display === "none" || !section.style.display;
    section.style.display = show ? "block" : "none";
    if (accounts) accounts.style.display = show ? "none" : "flex";
}

async function handleLogout() {
    try { await signOut(auth); } catch (_) {}
    loggedInUserId = null;
    currentUserProfile = null;
    localStorage.removeItem("fc_logged_user_v6");
    $("app-wrapper")?.classList.remove("logged-in");
    $("auth-screen").style.display = "flex";
    switchAuthTab("login");
    showToast("Logged out successfully.");
}

/* --------------------------------------------------------------------------
   Session + data loading
   -------------------------------------------------------------------------- */
async function initAppSession() {
    clearAuthAlert();
    $("auth-screen").style.display = "none";
    $("app-wrapper").classList.add("logged-in");

    // Authentication is Firebase-backed, so the app must open even if the
    // optional MySQL API is temporarily unavailable. Each data section is
    // loaded independently instead of one failed request blocking the whole UI.
    await fetchUserData();
    const jobs = [
        [fetchUsers, "believers"],
        [fetchPostsFromDatabase, "posts"],
        [fetchMinistriesFromDatabase, "groups"],
        [fetchStories, "stories"]
    ];
    for (const [job, label] of jobs) {
        try { await job(); }
        catch (err) { console.warn(`FaithConnection ${label} API unavailable:`, err); }
    }
    renderAll();
}

async function fetchUserData() {
    if (!loggedInUserId) return;
    // First read the Firebase profile; this works on Netlify without PHP.
    try {
        const snap = await getDoc(doc(db, "users", String(loggedInUserId)));
        if (snap.exists()) {
            currentUserProfile = { id: loggedInUserId, ...snap.data() };
            updateCurrentUserChrome();
            return;
        }
    } catch (firebaseErr) {
        console.warn("Firebase profile read failed:", firebaseErr);
    }
    // If the MySQL backend is available, keep compatibility with it.
    try {
        const r = await api("get_user", "GET", null, { id: loggedInUserId, viewer_id: loggedInUserId });
        currentUserProfile = r.user;
        updateCurrentUserChrome();
    } catch (apiErr) {
        console.warn("API profile read failed:", apiErr);
    }
}

async function fetchUsers() {
    const r = await api("get_users", "GET", null, { viewer_id: loggedInUserId || 0 });
    usersDB = r.users || [];
    updateCurrentUserChrome();
}

async function fetchPostsFromDatabase() {
    const r = await api("get_posts", "GET", null, { viewer_id: loggedInUserId || 0 });
    postsDB = r.posts || [];
    renderFeed(currentFeedFilter);
    renderTestimonies();
    if (currentActiveProfileId) renderProfile(currentActiveProfileId);
}

async function fetchMinistriesFromDatabase() {
    const r = await api("get_ministries", "GET", null, { viewer_id: loggedInUserId || 0 });
    ministriesDB = r.ministries || [];
    renderMinistries();
}

async function fetchStories() {
    try {
        const r = await api("get_stories");
        storiesDB = r.stories || [];
        renderStories();
    } catch (_) {
        storiesDB = [];
        renderStories();
    }
}

function renderAll() {
    updateCurrentUserChrome();
    renderStories();
    renderFeed(currentFeedFilter);
    renderTestimonies();
    renderBelieversList($("believers-filter")?.value || "");
    renderMinistries();
    renderScriptures();
    if (currentActiveProfileId) renderProfile(currentActiveProfileId);
}

/* --------------------------------------------------------------------------
   User chrome
   -------------------------------------------------------------------------- */
function updateCurrentUserChrome() {
    const u = currentUserProfile;
    if (!u) return;
    const avatar = avatarUrl(u);
    ["header-avatar-img","sidebar-user-avatar","create-post-avatar"].forEach(id => {
        const el = $(id); if (el) el.src = avatar;
    });
    if ($("sidebar-user-name")) $("sidebar-user-name").textContent = u.name || "User";
    if ($("sidebar-user-role")) $("sidebar-user-role").textContent = u.role || "Church Member";
}

/* --------------------------------------------------------------------------
   Posts
   -------------------------------------------------------------------------- */
async function handleCreatePost() {
    if (!loggedInUserId) return showToast("Please sign in first.", true);
    const textEl = $("post-input-text");
    const text = textEl?.value.trim() || "";
    if (!text && !selectedPostImageBase64) return showToast("Please type a message or attach a photo.", true);

    try {
        await api("create_post", "POST", {
            user_id: loggedInUserId,
            content: text,
            image: selectedPostImageBase64,
            type: $("post-input-type")?.value || "prayer",
            category: $("post-input-category")?.value || "General"
        });
        if (textEl) textEl.value = "";
        selectedPostImageBase64 = "";
        clearPostImage();
        await fetchPostsFromDatabase();
        showToast("Post published successfully!");
    } catch (err) {
        showToast(err.message, true);
    }
}

async function handlePostImageSelect(e) {
    try {
        selectedPostImageBase64 = await readFileAsDataUrl(e.target.files?.[0]);
        $("post-image-preview").src = selectedPostImageBase64;
        $("post-image-preview-container").style.display = "block";
    } catch (err) { showToast(err.message, true); e.target.value = ""; }
}
function clearPostImage() {
    selectedPostImageBase64 = "";
    const c = $("post-image-preview-container"), i = $("post-image-preview"), f = $("post-file-input");
    if (c) c.style.display = "none";
    if (i) i.src = "";
    if (f) f.value = "";
}
function filterFeedPosts(type) {
    currentFeedFilter = type;
    ["all","prayer","testimony"].forEach(t => $("ff-" + t)?.classList.toggle("active", t === type));
    renderFeed(type);
}

function postCard(post) {
    const own = isMe(post.user_id);
    const likeClass = post.liked ? " liked" : "";
    const prayClass = post.prayed ? " prayed" : "";
    const saveIcon = post.saved ? "fa-solid fa-bookmark" : "fa-regular fa-bookmark";
    const image = post.image ? `<img class="post-image-attachment" src="${esc(post.image)}" alt="Post image" loading="lazy">` : "";
    return `
    <article class="feed-card" id="post-card-${esc(post.id)}">
      <div class="post-header">
        <div class="post-author" onclick="navigateTo('profile', '${esc(post.user_id)}')">
          <img src="${esc(avatarUrl({avatar:post.user_avatar,name:post.user_name}))}" class="avatar-sm" alt="">
          <div class="author-meta">
            <h4>${esc(post.user_name)} ${post.user_role ? `<span class="profile-handle">${esc(post.user_role)}</span>` : ""}</h4>
            <span>${esc(formatDate(post.created_at))}</span>
          </div>
        </div>
        ${own ? `
        <div class="post-menu-wrap">
          <button class="post-menu-btn" onclick="togglePostMenu('${esc(post.id)}')"><i class="fa-solid fa-ellipsis"></i></button>
          <div class="post-dropdown" id="post-menu-${esc(post.id)}">
            <div class="post-dropdown-item" onclick="openEditPostModal('${esc(post.id)}')"><i class="fa-solid fa-pen"></i> Edit</div>
            <div class="post-dropdown-item" onclick="sharePost('${esc(post.id)}')"><i class="fa-solid fa-share-nodes"></i> Share</div>
            <div class="post-dropdown-item danger" onclick="deletePost('${esc(post.id)}')"><i class="fa-solid fa-trash"></i> Delete</div>
          </div>
        </div>` : `
        <button class="post-menu-btn" onclick="sharePost('${esc(post.id)}')" title="Share"><i class="fa-solid fa-share-nodes"></i></button>`}
      </div>
      <div class="post-body">
        <span class="post-category-tag ${post.type === "testimony" ? "testimony" : ""}">
          ${post.type === "testimony" ? "⭐" : "🙏"} ${esc(post.category || "General")}
        </span>
        <p class="post-text">${esc(post.content)}</p>
        ${image}
      </div>
      <div class="post-stats-bar">
        <span><i class="fa-solid fa-heart"></i> ${Number(post.likes_count||0)} &nbsp; ${Number(post.prayers_count||0)} prayers</span>
        <span>${Number(post.comments_count||0)} comments</span>
      </div>
      <div class="post-actions-bar">
        <button class="action-btn${likeClass}" onclick="togglePostLike('${esc(post.id)}')"><i class="fa-${post.liked ? "solid" : "regular"} fa-heart"></i> Like</button>
        <button class="action-btn${prayClass}" onclick="togglePostPrayer('${esc(post.id)}')"><i class="fa-solid fa-hands-praying"></i> I Prayed</button>
        <button class="action-btn" onclick="toggleComments('${esc(post.id)}')"><i class="fa-regular fa-comment"></i> Comment</button>
        <button class="action-btn" onclick="togglePostSave('${esc(post.id)}')"><i class="${saveIcon}"></i> Save</button>
        <button class="action-btn" onclick="sharePost('${esc(post.id)}')"><i class="fa-solid fa-share-nodes"></i> Share</button>
      </div>
      <div class="comments-section" id="comments-${esc(post.id)}">
        <div class="comment-list" id="comment-list-${esc(post.id)}"></div>
        <form class="comment-form" onsubmit="addPostComment(event,'${esc(post.id)}')">
          <input id="comment-input-${esc(post.id)}" placeholder="Write an encouraging comment..." autocomplete="off" required>
          <button class="btn-primary" type="submit">Send</button>
        </form>
      </div>
    </article>`;
}

function renderFeed(filter = "all") {
    const c = $("feed-posts-container"); if (!c) return;
    const list = postsDB.filter(p => filter === "all" || p.type === filter);
    c.innerHTML = list.length ? list.map(postCard).join("") :
        `<div style="text-align:center;color:var(--text-muted);padding:3rem"><i class="fa-solid fa-hands-praying" style="font-size:2.5rem;color:var(--accent-cyan)"></i><h3>No posts yet</h3><p>Be the first to share encouragement.</p></div>`;
}

function renderTestimonies() {
    const c = $("testimonies-feed-container"); if (!c) return;
    const list = postsDB.filter(p => p.type === "testimony");
    c.innerHTML = list.length ? list.map(postCard).join("") :
        `<div style="text-align:center;color:var(--text-muted);padding:3rem">No testimonies shared yet.</div>`;
}

function togglePostMenu(id) {
    document.querySelectorAll(".post-dropdown.active").forEach(x => x.classList.remove("active"));
    $(`post-menu-${id}`)?.classList.toggle("active");
}
async function deletePost(id) {
    if (!confirm("Delete this post permanently?")) return;
    try { await api("delete_post","POST",{user_id:loggedInUserId,post_id:id}); await fetchPostsFromDatabase(); showToast("Post deleted."); }
    catch(e){showToast(e.message,true);}
}
function openEditPostModal(id) {
    const p = postsDB.find(x => String(x.id) === String(id));
    if (!p || !isMe(p.user_id)) return;
    $("edit-post-id").value = p.id;
    $("edit-post-type").value = p.type || "prayer";
    $("edit-post-category").value = p.category || "General";
    $("edit-post-text").value = p.content || "";
    openModal("edit-post-modal");
}
async function handleSaveEditedPost(e) {
    e.preventDefault();
    try {
        await api("update_post","POST",{
            user_id: loggedInUserId,
            post_id: $("edit-post-id").value,
            type: $("edit-post-type").value,
            category: $("edit-post-category").value,
            content: $("edit-post-text").value.trim()
        });
        closeModal("edit-post-modal");
        await fetchPostsFromDatabase();
        showToast("Post updated.");
    } catch(e){showToast(e.message,true);}
}
async function togglePostLike(id) {
    try { await api("toggle_like","POST",{user_id:loggedInUserId,post_id:id}); await fetchPostsFromDatabase(); }
    catch(e){showToast(e.message,true);}
}
async function togglePostPrayer(id) {
    try { await api("toggle_prayer","POST",{user_id:loggedInUserId,post_id:id}); await fetchPostsFromDatabase(); }
    catch(e){showToast(e.message,true);}
}
async function togglePostSave(id) {
    try { const r=await api("toggle_save","POST",{user_id:loggedInUserId,post_id:id}); await fetchPostsFromDatabase(); showToast(r.active?"Post saved.":"Removed from saved posts."); }
    catch(e){showToast(e.message,true);}
}
async function sharePost(id) {
    const p=postsDB.find(x=>String(x.id)===String(id)); if(!p)return;
    const text=`${p.user_name}: ${p.content}`;
    try {
        if(navigator.share) await navigator.share({title:"FaithConnection",text,url:location.href});
        else { await navigator.clipboard.writeText(text); showToast("Post text copied."); }
    } catch(e) { if(e?.name!=="AbortError") showToast("Could not share post.",true); }
}
async function toggleComments(id) {
    const box=$(`comments-${id}`); if(!box)return;
    box.classList.toggle("active");
    if(box.classList.contains("active")) await loadComments(id);
}
async function loadComments(id) {
    try {
        const r=await api("get_comments","GET",null,{post_id:id});
        const c=$(`comment-list-${id}`);
        c.innerHTML=(r.comments||[]).map(comment => `
          <div class="comment-item">
            <div class="comment-content">
              <strong onclick="navigateTo('profile','${esc(comment.user_id)}')">${esc(comment.user_name)}</strong>
              <span>${esc(comment.text)}</span>
              <div style="font-size:.7rem;color:var(--text-muted);margin-top:.25rem">${esc(formatDate(comment.created_at))}</div>
            </div>
            ${isMe(comment.user_id) ? `<div class="comment-actions">
              <button class="comment-btn" onclick="editComment('${esc(comment.id)}','${esc(id)}')">Edit</button>
              <button class="comment-btn delete" onclick="deleteComment('${esc(comment.id)}','${esc(id)}')">Delete</button>
            </div>` : ""}
          </div>`).join("") || `<div style="color:var(--text-muted);font-size:.85rem">No comments yet.</div>`;
    } catch(e){showToast(e.message,true);}
}
async function addPostComment(e,id) {
    e.preventDefault();
    const input=$(`comment-input-${id}`); const text=input?.value.trim();
    if(!text)return;
    try { await api("add_comment","POST",{user_id:loggedInUserId,post_id:id,text}); input.value=""; await loadComments(id); await fetchPostsFromDatabase(); }
    catch(e){showToast(e.message,true);}
}
async function editComment(cid,pid) {
    const existing=prompt("Edit your comment:");
    if(existing===null)return;
    try { await api("edit_comment","POST",{user_id:loggedInUserId,comment_id:cid,text:existing.trim()}); await loadComments(pid); }
    catch(e){showToast(e.message,true);}
}
async function deleteComment(cid,pid) {
    if(!confirm("Delete this comment?"))return;
    try { await api("delete_comment","POST",{user_id:loggedInUserId,comment_id:cid}); await loadComments(pid); await fetchPostsFromDatabase(); }
    catch(e){showToast(e.message,true);}
}

/* --------------------------------------------------------------------------
   Profile / follow / followers
   -------------------------------------------------------------------------- */
async function renderProfile(id) {
    const target = id || loggedInUserId;
    currentActiveProfileId = String(target);
    try {
        const r = await api("get_user","GET",null,{id:target,viewer_id:loggedInUserId||0});
        const u=r.user;
        $("profile-avatar").src=avatarUrl(u,300);
        $("profile-name").textContent=u.name||"User";
        $("profile-username").textContent=u.username ? "@"+u.username : "";
        $("profile-role-pill").textContent=u.role||"Church Member";
        $("profile-church-name").textContent=u.church||"Fellowship Community Church";
        $("profile-bio-text").textContent=u.bio||"Walking by faith and encouraging the fellowship.";
        $("stat-posts-count").textContent=u.posts_count||0;
        $("stat-friends-count").textContent=u.friends_count||0;
        $("stat-ministries-count").textContent=u.ministries_count||0;

        const actions=$("profile-actions-area");
        if(actions){
            actions.innerHTML = isMe(u.id)
              ? `<button class="btn-primary" onclick="openEditProfileModal()"><i class="fa-solid fa-pen"></i> Edit Profile</button>`
              : `<button class="${u.is_following ? "btn-secondary" : "btn-primary"}" onclick="toggleFollow('${esc(u.id)}',${u.is_following})"><i class="fa-solid fa-user-${u.is_following ? "check" : "plus"}"></i> ${u.is_following ? "Following" : "Follow"}</button>
                 <button class="btn-secondary" onclick="openChatWith('${esc(u.id)}')"><i class="fa-solid fa-message"></i> Message</button>`;
        }
        const ownPosts=postsDB.filter(p=>String(p.user_id)===String(u.id));
        $("profile-posts-container").innerHTML=ownPosts.length?ownPosts.map(postCard).join(""):`<div style="padding:2rem;text-align:center;color:var(--text-muted)">No posts yet.</div>`;
        const saved=postsDB.filter(p=>p.saved && isMe(u.id));
        $("profile-saved-container").innerHTML=isMe(u.id)?(saved.length?saved.map(postCard).join(""):`<div style="padding:2rem;text-align:center;color:var(--text-muted)">No saved posts.</div>`):`<div style="padding:2rem;text-align:center;color:var(--text-muted)">Saved posts are private.</div>`;
        $("profile-friends-container").innerHTML = `
          <div class="feed-filter-bar" style="margin-bottom:1rem">
            <button class="feed-filter-tab active" id="profile-following-btn" onclick="renderProfileConnections('following')"><i class="fa-solid fa-user-check"></i> Following</button>
            <button class="feed-filter-tab" id="profile-followers-btn" onclick="renderProfileConnections('followers')"><i class="fa-solid fa-users"></i> Followers</button>
          </div>
          <div class="believers-grid" id="profile-connections-list"></div>`;
        renderProfileTabButtons();
        await renderProfileConnections("following");
    } catch(e){showToast(e.message,true);}
}
function renderProfileTabButtons() {
    // The HTML already contains Posts/Friends/Saved. A Groups tab is added once for the stats shortcut.
    const nav=document.querySelector(".profile-nav-tabs");
    if(nav && !document.getElementById("ptab-ministries")){
        const b=document.createElement("button");
        b.className="profile-tab-btn"; b.id="ptab-ministries";
        b.innerHTML='<i class="fa-solid fa-church"></i> Groups';
        b.onclick=()=>showProfileTab("ministries");
        nav.appendChild(b);
    }
    if(nav && !document.getElementById("profile-tab-content-ministries")){
        const wrap=document.createElement("div");
        wrap.id="profile-tab-content-ministries"; wrap.className="profile-tab-content";
        wrap.innerHTML='<div class="ministries-grid" id="profile-ministries-container"></div>';
        const hero=nav.parentElement;
        const profileSection=$("view-profile");
        profileSection.appendChild(wrap);
    }
}
async function showProfileTab(tab) {
    renderProfileTabButtons();
    ["posts","friends","saved","ministries"].forEach(t=>{
        $(`ptab-${t}`)?.classList.toggle("active",t===tab);
        $(`profile-tab-content-${t}`)?.classList.toggle("active",t===tab);
    });
    if(tab==="ministries"){
        const uid=currentActiveProfileId||loggedInUserId;
        try{
            const r=await api("get_ministries","GET",null,{viewer_id:loggedInUserId||0});
            const list=(r.ministries||[]).filter(g=>true);
            const joined=list.filter(g=>{
                // API does not expose membership for the viewed user, so use viewer only for current profile.
                return String(uid)===String(loggedInUserId) ? !!g.is_member : false;
            });
            $("profile-ministries-container").innerHTML=joined.length?joined.map(groupCard).join(""):`<div style="padding:2rem;color:var(--text-muted)">No joined groups to show.</div>`;
        }catch(e){showToast(e.message,true);}
    }
}
async function toggleFollow(friendId,following) {
    try {
        await api(following?"unfollow":"follow","POST",{user_id:loggedInUserId,friend_id:friendId});
        await fetchUsers(); await renderProfile(friendId);
        showToast(following?"Unfollowed.":"Following.");
    } catch(e){showToast(e.message,true);}
}
async function renderFollowersFollowing(type="following") {
    const action=type==="followers"?"get_followers":"get_following";
    const r=await api(action,"GET",null,{user_id:currentActiveProfileId||loggedInUserId});
    return r.users||[];
}
async function renderProfileConnections(type="following"){
    const list=$("profile-connections-list"); if(!list)return;
    try{
        const users=await renderFollowersFollowing(type);
        $("profile-following-btn")?.classList.toggle("active",type==="following");
        $("profile-followers-btn")?.classList.toggle("active",type==="followers");
        list.innerHTML=users.map(believerCard).join("") || `<div style="padding:2rem;color:var(--text-muted)">No ${type} yet.</div>`;
    }catch(e){showToast(e.message,true);}
}
function believerCard(u){
    if(isMe(u.id)) return "";
    return `<div class="believer-card">
      <span class="online-dot"></span>
      <img src="${esc(avatarUrl(u,180))}" class="avatar-lg" alt="">
      <h4 onclick="navigateTo('profile','${esc(u.id)}')">${esc(u.name)}</h4>
      <span class="role-pill">${esc(u.role||"Church Member")}</span>
      <span class="church-name"><i class="fa-solid fa-church"></i> ${esc(u.church||"")}</span>
      <div class="believer-actions">
        <button class="${u.is_following?'btn-secondary':'btn-primary'}" onclick="toggleFollow('${esc(u.id)}',${!!u.is_following})">${u.is_following?"Following":"Follow"}</button>
        <button class="btn-secondary" onclick="openChatWith('${esc(u.id)}')"><i class="fa-solid fa-message"></i></button>
      </div>
    </div>`;
}
function renderBelieversList(filter=""){
    const c=$("believers-list-container"); if(!c)return;
    const f=String(filter).toLowerCase();
    const list=usersDB.filter(u=>!isMe(u.id)&&`${u.name} ${u.username} ${u.role} ${u.church}`.toLowerCase().includes(f));
    c.innerHTML=list.map(believerCard).join("")||`<div style="padding:2rem;color:var(--text-muted)">No believers found.</div>`;
}

/* --------------------------------------------------------------------------
   Groups
   -------------------------------------------------------------------------- */
function groupCard(g){
    return `<div class="ministry-card">
      <div>
        <h3>${esc(g.name)}</h3>
        <div class="ministry-meta"><i class="fa-solid fa-tag"></i> ${esc(g.category)} &nbsp; • &nbsp; <i class="fa-solid fa-users"></i> ${Number(g.members_count||0)} members</div>
        <p class="ministry-desc">${esc(g.bio||"A fellowship group for prayer and encouragement.")}</p>
        <p style="font-size:.78rem;color:var(--text-muted)">Led by ${esc(g.leader_name||"Believer")}</p>
      </div>
      <div class="ministry-footer-actions">
        ${g.is_member
          ? `<button class="btn-primary" onclick="openGroupChat('${esc(g.id)}','${esc(g.name)}')"><i class="fa-solid fa-comments"></i> Open Chat</button>
             <button class="btn-secondary" onclick="leaveGroup('${esc(g.id)}')"><i class="fa-solid fa-right-from-bracket"></i> Leave</button>`
          : `<button class="btn-primary" onclick="joinGroup('${esc(g.id)}')"><i class="fa-solid fa-user-plus"></i> Join Group</button>`}
        <button class="btn-secondary" onclick="openGroupInfo('${esc(g.id)}')"><i class="fa-solid fa-circle-info"></i> Info</button>
      </div>
    </div>`;
}
function renderMinistries(){
    const c=$("ministries-list-container"); if(!c)return;
    c.innerHTML=ministriesDB.map(groupCard).join("")||`<div style="padding:2rem;color:var(--text-muted)">No groups yet. Create the first one.</div>`;
}
async function handleCreateMinistry(e){
    e.preventDefault();
    try{
        await api("create_ministry","POST",{
            user_id:loggedInUserId,
            name:$("ministry-name-input").value.trim(),
            category:$("ministry-cat-input").value,
            bio:$("ministry-bio-input").value.trim()
        });
        e.target.reset(); closeModal("create-ministry-modal");
        await fetchMinistriesFromDatabase(); showToast("Ministry group created.");
    }catch(err){showToast(err.message,true);}
}
async function joinGroup(id){
    try{await api("join_group","POST",{user_id:loggedInUserId,group_id:id});await fetchMinistriesFromDatabase();showToast("You joined the group.");}
    catch(e){showToast(e.message,true);}
}
async function leaveGroup(id){
    if(!confirm("Leave this fellowship group?"))return;
    try{await api("leave_group","POST",{user_id:loggedInUserId,group_id:id});if(String(activeGroupId)===String(id))navigateTo("ministries");await fetchMinistriesFromDatabase();showToast("You left the group.");}
    catch(e){showToast(e.message,true);}
}
async function openGroupInfo(id){
    activeGroupId=String(id);
    const g=ministriesDB.find(x=>String(x.id)===String(id));
    if(g){
        $("modal-group-name").textContent=g.name;
        $("modal-group-category").textContent=g.category;
        $("modal-group-bio").textContent=g.bio||"";
    }
    try{
        const r=await api("get_group_members","GET",null,{group_id:id});
        const members=r.members||[];
        $("modal-group-members-count").textContent=members.length;
        $("modal-group-members-list").innerHTML=members.map(m=>`
          <div style="display:flex;align-items:center;gap:.7rem;padding:.6rem 0;border-bottom:1px solid var(--border-color)">
            <img src="${esc(avatarUrl(m,100))}" class="avatar-sm" alt="">
            <div style="flex:1"><strong>${esc(m.name)}</strong><div style="font-size:.72rem;color:var(--text-muted)">${esc(m.role||"")} ${m.is_admin?" • Admin":""}</div></div>
            <button class="btn-secondary" onclick="navigateTo('profile','${esc(m.id)}')">Profile</button>
          </div>`).join("");
        const me=members.find(m=>isMe(m.id));
        const admin=$("admin-add-member-section");
        if(admin){
            admin.style.display=me?.is_admin?"block":"none";
            if(me?.is_admin){
                $("admin-member-select").innerHTML=usersDB.filter(u=>!members.some(m=>String(m.id)===String(u.id))).map(u=>`<option value="${esc(u.id)}">${esc(u.name)} (@${esc(u.username||"")})</option>`).join("")||`<option value="">No new believers</option>`;
            }
        }
        openModal("group-info-modal");
    }catch(e){showToast(e.message,true);}
}
function openCurrentGroupInfoModal(){ if(activeGroupId)openGroupInfo(activeGroupId); }
async function handleAddGroupMemberFromModal(){
    const id=$("admin-member-select").value;
    if(!id)return;
    try{await api("add_group_member","POST",{user_id:loggedInUserId,group_id:activeGroupId,member_id:id});await openGroupInfo(activeGroupId);await fetchMinistriesFromDatabase();showToast("Member added.");}
    catch(e){showToast(e.message,true);}
}
function openGroupChat(id,name){
    activeGroupId=String(id);
    $("group-chat-title").textContent=name||"Group Chat";
    navigateTo("group-chat");
    loadGroupMessages();
}
async function loadGroupMessages(){
    if(!activeGroupId)return;
    try{
        const r=await api("get_group_messages","GET",null,{group_id:activeGroupId});
        const c=$("group-messages-container");
        c.innerHTML=(r.messages||[]).map(m=>{
            const out=isMe(m.user_id);
            return `<div class="group-msg-row ${out?"outgoing":"incoming"}">
              <img src="${esc(avatarUrl({avatar:m.sender_avatar,name:m.sender_name},80))}" class="avatar-xs" alt="">
              <div class="group-msg-bubble">
                ${!out?`<div class="group-sender-header"><span class="group-sender-name">${esc(m.sender_name)}</span><span class="group-sender-role">${esc(m.sender_role||"")}</span></div>`:""}
                ${m.image?`<img src="${esc(m.image)}" style="max-width:260px;border-radius:12px;display:block;margin-bottom:.3rem" alt="">`:""}
                <div class="group-msg-text">${esc(m.text||"")}</div>
                <div class="group-msg-meta">${esc(formatDate(m.created_at))}</div>
              </div>
            </div>`;
        }).join("")||`<div style="text-align:center;color:var(--text-muted);padding:2rem">No messages yet. Start the conversation.</div>`;
        c.scrollTop=c.scrollHeight;
    }catch(e){showToast(e.message,true);}
}
async function handleSendGroupChat(e){
    e.preventDefault();
    const input=$("group-chat-input-text"),text=input.value.trim();
    if(!activeGroupId||!text)return;
    try{await api("send_group_message","POST",{user_id:loggedInUserId,group_id:activeGroupId,text,image:""});input.value="";await loadGroupMessages();}
    catch(e){showToast(e.message,true);}
}
async function handleSendGroupPhoto(e){
    try{
        const image=await readFileAsDataUrl(e.target.files?.[0]);
        if(!image||!activeGroupId)return;
        await api("send_group_message","POST",{user_id:loggedInUserId,group_id:activeGroupId,text:"",image});
        e.target.value="";await loadGroupMessages();
    }catch(err){showToast(err.message,true);}
}
function sendGroupPrayerPrompt(){
    const input=$("group-chat-input-text"); if(input){input.value="🙏 Prayer request: Please stand with me in prayer.";input.focus();}
}

/* --------------------------------------------------------------------------
   Direct chat
   -------------------------------------------------------------------------- */
async function fetchChatContacts(){
    const r=await api("get_chat_contacts","GET",null,{user_id:loggedInUserId});
    chatsDB=r.contacts||[];
    renderChatContacts(document.querySelector(".chat-search input")?.value||"");
}
function renderChatContacts(filter=""){
    const c=$("chat-contacts-container"); if(!c)return;
    const f=filter.toLowerCase();
    const list=chatsDB.filter(u=>`${u.name} ${u.username} ${u.role}`.toLowerCase().includes(f));
    c.innerHTML=list.map(u=>`
      <div class="chat-contact-item ${String(u.id)===String(activeChatUserId)?"active":""}" onclick="openChatWith('${esc(u.id)}')">
        <img src="${esc(avatarUrl(u,100))}" class="avatar-sm" alt="">
        <div class="contact-meta" style="flex:1;min-width:0">
          <h4>${esc(u.name)}</h4>
          <p style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${esc(u.last_message||u.role||"Start a conversation")}</p>
        </div>
        ${Number(u.unread_count||0)>0?`<span class="notification-badge" style="position:static">${Number(u.unread_count)}</span>`:""}
      </div>`).join("")||`<div style="padding:1.5rem;color:var(--text-muted)">No believers found.</div>`;
}
async function openChatWith(id){
    activeChatUserId=String(id);
    navigateTo("chat");
    if(!chatsDB.length) await fetchChatContacts();
    const u=usersDB.find(x=>String(x.id)===String(id))||chatsDB.find(x=>String(x.id)===String(id));
    if(u){
        $("chat-header-name").textContent=u.name||"Believer";
        $("chat-header-avatar").src=avatarUrl(u,100);
    }
    await loadDirectMessages();
    renderChatContacts(document.querySelector(".chat-search input")?.value||"");
}
async function loadDirectMessages(){
    if(!activeChatUserId)return;
    try{
        const r=await api("get_direct_messages","GET",null,{user_id:loggedInUserId,other_id:activeChatUserId});
        const c=$("chat-messages-body");
        c.innerHTML=(r.messages||[]).map(m=>`
          <div class="chat-msg ${isMe(m.sender_id)?"outgoing":"incoming"}">
            ${m.image?`<img src="${esc(m.image)}" alt="">`:""}
            ${m.text?`<div>${esc(m.text)}</div>`:""}
            <div style="font-size:.65rem;opacity:.65;text-align:right;margin-top:.25rem">${esc(formatDate(m.created_at))}</div>
          </div>`).join("")||`<div style="text-align:center;color:var(--text-muted);padding:2rem">Say hello and encourage one another. 🙏</div>`;
        c.scrollTop=c.scrollHeight;
        fetchChatContacts();
    }catch(e){showToast(e.message,true);}
}
async function handleSendChat(e){
    e.preventDefault();
    if(!activeChatUserId)return showToast("Select a believer to chat with.",true);
    const input=$("chat-input-text"),text=input.value.trim();
    if(!text)return;
    try{await api("send_direct_message","POST",{user_id:loggedInUserId,other_id:activeChatUserId,text,image:""});input.value="";await loadDirectMessages();}
    catch(e){showToast(e.message,true);}
}
async function handleSendChatPhoto(e){
    try{
        const image=await readFileAsDataUrl(e.target.files?.[0]);
        if(!image||!activeChatUserId)return;
        await api("send_direct_message","POST",{user_id:loggedInUserId,other_id:activeChatUserId,text:"",image});
        e.target.value="";await loadDirectMessages();
    }catch(err){showToast(err.message,true);}
}
function filterChatList(value){renderChatContacts(value);}
function sendPrayerAmenDirect(){
    if(!activeChatUserId)return showToast("Select a chat first.",true);
    $("chat-input-text").value="🙏 Amen! I am standing with you in prayer. May God give you peace and strength.";
    $("chat-input-text").focus();
}
function viewChatUserProfile(){if(activeChatUserId)navigateTo("profile",activeChatUserId);}

/* --------------------------------------------------------------------------
   Stories
   -------------------------------------------------------------------------- */
function renderStories(){
    const c=$("stories-container"); if(!c)return;
    const items=storiesDB.map(s=>`
      <div class="story-item" onclick="openStory('${esc(s.id)}')">
        <div class="story-avatar-ring"><img src="${esc(avatarUrl({avatar:s.user_avatar,name:s.user_name},100))}" alt=""></div>
        <span>${esc(s.user_name)}</span>
      </div>`).join("");
    c.innerHTML=`<div class="story-item" onclick="createQuickStory()"><div class="story-avatar-ring"><img src="${esc(avatarUrl(currentUserProfile,100))}" alt=""><b style="position:absolute">+</b></div><span>Your Story</span></div>${items}`;
}
function openStory(id){
    const s=storiesDB.find(x=>String(x.id)===String(id)); if(!s)return;
    $("story-user-avatar").src=avatarUrl({avatar:s.user_avatar,name:s.user_name},100);
    $("story-user-name").textContent=s.user_name;
    $("story-time-stamp").textContent=formatDate(s.created_at);
    $("story-text").textContent=s.text;
    $("story-body-content").style.backgroundImage=s.media?`url("${s.media}")`:"";
    openModal("story-modal");
    clearTimeout(storyTimer);storyTimer=setTimeout(()=>closeModal("story-modal"),7000);
}
async function createQuickStory(){
    const text=prompt("Share a short story/status with the fellowship:");
    if(!text?.trim())return;
    try{await api("create_story","POST",{user_id:loggedInUserId,text:text.trim(),media:""});await fetchStories();showToast("Story shared for 24 hours.");}
    catch(e){showToast(e.message,true);}
}

/* --------------------------------------------------------------------------
   Profile edit
   -------------------------------------------------------------------------- */
function openEditProfileModal(){
    const u=currentUserProfile; if(!u)return;
    $("edit-preview-avatar").src=avatarUrl(u,200);
    $("edit-name").value=u.name||"";
    $("edit-role").value=u.role||"Church Member";
    $("edit-church").value=u.church||"";
    $("edit-bio").value=u.bio||"";
    selectedAvatarBase64="";
    openModal("edit-profile-modal");
}
async function handleAvatarFileSelect(e){
    try{
        selectedAvatarBase64=await readFileAsDataUrl(e.target.files?.[0]);
        $("edit-preview-avatar").src=selectedAvatarBase64;
    }catch(err){showToast(err.message,true);}
}
async function handleSaveProfile(e){
    e.preventDefault();
    try{
        const r=await api("update_profile","POST",{
            user_id:loggedInUserId,
            username:currentUserProfile?.username||"",
            name:$("edit-name").value.trim(),
            role:$("edit-role").value,
            church:$("edit-church").value.trim(),
            bio:$("edit-bio").value.trim(),
            avatar:selectedAvatarBase64||currentUserProfile?.avatar||""
        });
        currentUserProfile=r.user;closeModal("edit-profile-modal");
        await fetchUsers();await fetchPostsFromDatabase();await renderProfile(loggedInUserId);
        updateCurrentUserChrome();showToast("Profile updated successfully.");
    }catch(err){showToast(err.message,true);}
}

/* --------------------------------------------------------------------------
   Navigation, theme, settings
   -------------------------------------------------------------------------- */
window.navigateTo=function(viewId,profileId=null){
    document.querySelectorAll(".app-view").forEach(v=>v.classList.remove("active"));
    const target=$("view-"+viewId);
    if(target)target.classList.add("active");
    document.querySelectorAll(".nav-item").forEach(v=>v.classList.remove("active"));
    $("nav-"+viewId)?.classList.add("active");
    document.querySelectorAll(".mob-nav-btn").forEach(v=>v.classList.remove("active"));
    $("mob-"+viewId)?.classList.add("active");

    if(viewId==="profile"){currentActiveProfileId=String(profileId||loggedInUserId);renderProfile(currentActiveProfileId);}
    if(viewId==="believers")renderBelieversList($("believers-filter")?.value||"");
    if(viewId==="ministries")fetchMinistriesFromDatabase();
    if(viewId==="chat")fetchChatContacts();
    if(viewId==="feed")renderFeed(currentFeedFilter);
    if(viewId==="testimonies")renderTestimonies();
};
window.toggleTheme=function(){
    const current=document.documentElement.getAttribute("data-theme")||"dark";
    const next=current==="light"?"dark":"light";
    document.documentElement.setAttribute("data-theme",next);
    localStorage.setItem("fc_theme_v6",next);
};
function openModal(id){$(id)?.classList.add("active");}
function closeModal(id){$(id)?.classList.remove("active");}
function scrollToCreatePost(){
    navigateTo("feed");
    setTimeout(()=>{$("post-input-text")?.focus();$("post-input-text")?.scrollIntoView({behavior:"smooth",block:"center"});},50);
}
function openCreateTestimonyQuick(){
    navigateTo("feed");
    setTimeout(()=>{
        if($("post-input-type"))$("post-input-type").value="testimony";
        $("post-input-text")?.focus();
        $("post-input-text")?.scrollIntoView({behavior:"smooth",block:"center"});
    },50);
}
function resetAppData(){
    if(!confirm("Reset local app session and theme? Your database posts/chats will not be deleted."))return;
    localStorage.removeItem("fc_theme_v6");
    localStorage.removeItem("fc_logged_user_v6");
    location.reload();
}

/* --------------------------------------------------------------------------
   Daily Bread
   -------------------------------------------------------------------------- */
const verses=[
    ['"For I know the plans I have for you,” declares the LORD, “plans to prosper you and not to harm you, plans to give you hope and a future."','Jeremiah 29:11 (NIV)'],
    ['"I can do all this through him who gives me strength."','Philippians 4:13 (NIV)'],
    ['"Cast all your anxiety on him because he cares for you."','1 Peter 5:7 (NIV)'],
    ['"The LORD is my shepherd, I lack nothing."','Psalm 23:1 (NIV)'],
    ['"Be strong and courageous. Do not be afraid; do not be discouraged."','Joshua 1:9 (NIV)']
];
function renderScriptures(){
    const grid=$("scripture-collection-grid"); if(!grid)return;
    grid.innerHTML=verses.map((v,i)=>`<div class="verse-card"><span class="verse-card-category">${["Hope","Strength","Peace","Provision","Courage"][i]}</span><p>${esc(v[0])}</p><cite>${esc(v[1])}</cite></div>`).join("");
    updateVerse();
}
function updateVerse(){const v=verses[currentVerseIndex];if($("daily-verse-quote"))$("daily-verse-quote").textContent=v[0];if($("daily-verse-source"))$("daily-verse-source").textContent=v[1];}
function cycleNextVerse(){currentVerseIndex=(currentVerseIndex+1)%verses.length;updateVerse();}
async function shareVerseText(){
    const text=`${$("daily-verse-quote")?.textContent||""} — ${$("daily-verse-source")?.textContent||""}`;
    try{if(navigator.share)await navigator.share({title:"Daily Bread",text});else{await navigator.clipboard.writeText(text);showToast("Verse copied.");}}
    catch(e){if(e?.name!=="AbortError")showToast("Could not share verse.",true);}
}
async function copyVerseText(){try{await navigator.clipboard.writeText(`${$("daily-verse-quote").textContent} — ${$("daily-verse-source").textContent}`);showToast("Verse copied.");}catch(e){showToast("Copy failed.",true);}}

/* --------------------------------------------------------------------------
   Global search
   -------------------------------------------------------------------------- */
function handleGlobalSearch(value){
    const drop=$("search-results-dropdown");
    const q=String(value||"").trim().toLowerCase();
    if(!drop)return;
    if(!q){drop.style.display="none";drop.innerHTML="";return;}
    const users=usersDB.filter(u=>`${u.name} ${u.username} ${u.role} ${u.church}`.toLowerCase().includes(q)).slice(0,5);
    const posts=postsDB.filter(p=>`${p.content} ${p.category} ${p.user_name}`.toLowerCase().includes(q)).slice(0,5);
    drop.innerHTML=[...users.map(u=>`<div class="search-result-item" onclick="navigateTo('profile','${esc(u.id)}')"><i class="fa-solid fa-user"></i><span>${esc(u.name)}<small> @${esc(u.username||"")}</small></span></div>`),
        ...posts.map(p=>`<div class="search-result-item" onclick="navigateTo('feed');document.getElementById('global-search-input').value=''"><i class="fa-solid fa-file-lines"></i><span>${esc(p.user_name)}: ${esc((p.content||"").slice(0,55))}</span></div>`)].join("")||`<div style="padding:.8rem;color:var(--text-muted)">No results.</div>`;
    drop.style.display="block";
}

/* --------------------------------------------------------------------------
   Expose every inline handler used by index.html
   -------------------------------------------------------------------------- */
Object.assign(window,{
    switchAuthTab,handleLogin,handleRegister,handleLogout,handleGoogleSignIn,
    handleContinueWithCustomGoogleAccount,toggleCustomGoogleInput,handleCreatePost,
    clearPostImage,handlePostImageSelect,filterFeedPosts,openCreateTestimonyQuick,
    cycleNextVerse,shareVerseText,copyVerseText,renderBelieversList,handleGlobalSearch,
    handleCreateMinistry,openCurrentGroupInfoModal,handleAddGroupMemberFromModal,
    handleSendGroupChat,handleSendGroupPhoto,sendGroupPrayerPrompt,handleSendChat,
    handleSendChatPhoto,filterChatList,sendPrayerAmenDirect,viewChatUserProfile,
    showProfileTab,openEditProfileModal,handleAvatarFileSelect,handleSaveProfile,
    openModal,closeModal,resetAppData,scrollToCreatePost,togglePostMenu,openEditPostModal,
    handleSaveEditedPost,deletePost,togglePostLike,togglePostPrayer,togglePostSave,
    toggleComments,addPostComment,editComment,deleteComment,sharePost,toggleFollow,
    renderProfileConnections,openChatWith,joinGroup,leaveGroup,openGroupInfo,openGroupChat,openStory,createQuickStory
});

/* Lightweight polling keeps direct/group chat feeling live without changing hosting/config. */
setInterval(() => {
    if (!loggedInUserId) return;
    if (activeChatUserId && $("view-chat")?.classList.contains("active")) loadDirectMessages();
    if (activeGroupId && $("view-group-chat")?.classList.contains("active")) loadGroupMessages();
}, 5000);

/* --------------------------------------------------------------------------
   Boot
   -------------------------------------------------------------------------- */
document.addEventListener("DOMContentLoaded", async ()=>{
    const savedTheme=localStorage.getItem("fc_theme_v6")||"dark";
    document.documentElement.setAttribute("data-theme",savedTheme);
    renderScriptures();

    // Firebase is the source of truth for authentication. This prevents an
    // old MySQL/localStorage id from forcing the app into a broken session.
    try {
        if (auth.currentUser) {
            const uid = auth.currentUser.uid;
            loggedInUserId = uid;
            syncStorage();
            await fetchUserData();
            await initAppSession();
        } else {
            loggedInUserId = null;
            localStorage.removeItem("fc_logged_user_v6");
            $("auth-screen").style.display="flex";
            $("app-wrapper").classList.remove("logged-in");
        }
    } catch(e) {
        console.error("Boot error:", e);
        loggedInUserId = null;
        localStorage.removeItem("fc_logged_user_v6");
        $("auth-screen").style.display="flex";
        $("app-wrapper").classList.remove("logged-in");
    }

    document.addEventListener("click",(e)=>{
        if(!e.target.closest(".post-menu-wrap"))document.querySelectorAll(".post-dropdown.active").forEach(x=>x.classList.remove("active"));
        if(!e.target.closest(".header-search"))$("search-results-dropdown")?.style.setProperty("display","none");
    });
});
