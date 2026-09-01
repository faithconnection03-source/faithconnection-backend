/* ==========================================================================
   FAITHCONNECTION - FIREBASE & MYSQL INTEGRATED CORE (CLEANED)
   ========================================================================= */

// Import the functions you need from the SDKs you need
import { initializeApp } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-app.js";
import { getAnalytics } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-analytics.js";
import {
    getAuth, GoogleAuthProvider, signInWithPopup,
    createUserWithEmailAndPassword, signInWithEmailAndPassword,
    signOut
} from "https://www.gstatic.com/firebasejs/10.8.0/firebase-auth.js";
import {
    getFirestore, doc, setDoc, getDoc,
    collection, addDoc, getDocs, query, orderBy
} from "https://www.gstatic.com/firebasejs/10.8.0/firebase-firestore.js";

// Your web app's Firebase configuration (from your Firebase Console setup)
const firebaseConfig = {
    apiKey: "AIzaSyB8VePl5WBSilDyZwq8hSLdYCrmBiuLIPI",
    authDomain: "faithconnection-af4f2.firebaseapp.com",
    projectId: "faithconnection-af4f2",
    storageBucket: "faithconnection-af4f2.firebasestorage.app",
    messagingSenderId: "135734412322",
    appId: "1:135734412322:web:11b16fe5f8130b98a4367b",
    measurementId: "G-1T1KQPCN4R"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const analytics = getAnalytics(app);
const auth = getAuth(app);
const db = getFirestore(app);
const googleProvider = new GoogleAuthProvider();

// --- Live Backend API Base URL (kept for reference, no longer used) ---
const API_BASE_URL = "https://faithconnection.free.je/api.php"; // Render backend URL

// --- Dynamic State Variables (Fully driven by database responses) ---
let currentUserProfile = null;
let postsDB = [];
let ministriesDB = [];
let chatsDB = {};
let storiesDB = [];

let loggedInUserId = localStorage.getItem('fc_logged_user_v6') || null;
let currentFeedFilter = 'all'; 
let activeChatUserId = null;
let activeGroupId = null;
let currentActiveProfileId = null;
let selectedPostImageBase64 = '';
let selectedAvatarBase64 = '';
let currentVerseIndex = 0;
let storyTimer = null;

// Sync Storage Helper
function syncStorage() {
    if (loggedInUserId) {
        localStorage.setItem('fc_logged_user_v6', loggedInUserId);
    } else {
        localStorage.removeItem('fc_logged_user_v6');
    }
}

// --- Toast Notifications ---
function showToast(message) {
    const toast = document.getElementById('toast-notification');
    if (!toast) return;
    toast.innerHTML = `<i class="fa-solid fa-circle-check" style="color:var(--accent-cyan); margin-right:0.5rem;"></i> ${message}`;
    toast.style.display = 'block';
    setTimeout(() => {
        toast.style.display = 'none';
    }, 3200);
}

// ==========================================================================
// AUTHENTICATION & API WORKFLOW (MYSQL + FIREBASE)
// ==========================================================================
function switchAuthTab(tab) {
    clearAuthAlert();
    const loginForm = document.getElementById('login-form');
    const regForm = document.getElementById('register-form');
    const tabLoginBtn = document.getElementById('tab-login-btn');
    const tabRegBtn = document.getElementById('tab-register-btn');

    if (tab === 'login') {
        loginForm.style.display = 'flex';
        regForm.style.display = 'none';
        tabLoginBtn.classList.add('active');
        tabRegBtn.classList.remove('active');
    } else {
        loginForm.style.display = 'none';
        regForm.style.display = 'flex';
        tabRegBtn.classList.add('active');
        tabLoginBtn.classList.remove('active');
    }
}

function showAuthAlert(msg, isError = true) {
    const alertBox = document.getElementById('auth-alert');
    if(!alertBox) return;
    alertBox.className = isError ? 'auth-alert error' : 'auth-alert success';
    alertBox.innerText = msg;
    alertBox.style.display = 'block';
}

function clearAuthAlert() {
    const alertBox = document.getElementById('auth-alert');
    if(alertBox) alertBox.style.display = 'none';
}

// --- Google Sign In Handler ---
window.switchAuthTab = switchAuthTab;
window.handleLogin = handleLogin;
window.handleRegister = handleRegister;
window.handleLogout = handleLogout;
window.handleCreatePost = handleCreatePost;

window.handleGoogleSignIn = function() {
    clearAuthAlert();

    signInWithPopup(auth, googleProvider)
    .then(async (result) => {
        const user = result.user;
        const userRef = doc(db, "users", user.uid);
        const existing = await getDoc(userRef);

        if (!existing.exists()) {
            await setDoc(userRef, {
                name: user.displayName || '',
                email: user.email || '',
                avatar: user.photoURL || '',
                createdAt: new Date().toISOString()
            });
        }

        loggedInUserId = user.uid;
        currentUserProfile = { id: user.uid, name: user.displayName, email: user.email, avatar: user.photoURL };
        syncStorage();
        initAppSession();
        showToast(`Welcome, ${user.displayName}!`);
    }).catch((error) => {
        console.error('Firebase Google Auth Error:', error);
        showAuthAlert(error.message || 'Google sign-in was cancelled or failed.');
    });
};

function handleLogin(e) {
    e.preventDefault();
    clearAuthAlert();
    const email = document.getElementById('login-identifier').value.trim();
    const password = document.getElementById('login-password').value;

    signInWithEmailAndPassword(auth, email, password)
    .then((cred) => {
        loggedInUserId = cred.user.uid;
        syncStorage();
        initAppSession();
        showToast('Login successful! Welcome to FaithConnection.');
    })
    .catch(err => {
        console.error('Login Error:', err);
        showAuthAlert(err.message || 'Invalid credentials.');
    });
}

function handleRegister(e) {
    e.preventDefault();
    clearAuthAlert();

    const name = document.getElementById('reg-name').value.trim();
    const email = document.getElementById('reg-email').value.trim().toLowerCase();
    const password = document.getElementById('reg-password').value;

    createUserWithEmailAndPassword(auth, email, password)
    .then(async (cred) => {
        await setDoc(doc(db, "users", cred.user.uid), {
            name,
            email,
            avatar: '',
            createdAt: new Date().toISOString()
        });
        showToast('Registration successful! Please login.');
        switchAuthTab('login');
    })
    .catch(err => {
        console.error('Registration Error:', err);
        showAuthAlert(err.message || 'Registration failed.');
    });
}

function handleLogout() {
    signOut(auth).catch(err => console.error('Sign out error:', err));
    loggedInUserId = null;
    currentUserProfile = null;
    localStorage.removeItem('fc_logged_user_v6');
    document.getElementById('app-wrapper').classList.remove('logged-in');
    document.getElementById('auth-screen').style.display = 'flex';
    switchAuthTab('login');
    showToast('Logged out successfully.');
}

function initAppSession() {
    clearAuthAlert();
    document.getElementById('auth-screen').style.display = 'none';
    document.getElementById('app-wrapper').classList.add('logged-in');

    fetchUserData();
    fetchPostsFromDatabase();
    fetchMinistriesFromDatabase();
}

function fetchUserData() {
    if (!loggedInUserId) return;
    getDoc(doc(db, "users", loggedInUserId))
    .then(snap => {
        if (snap.exists()) {
            currentUserProfile = { id: loggedInUserId, ...snap.data() };
        }
    })
    .catch(err => console.error('Error fetching user profile:', err));
}

function fetchPostsFromDatabase() {
    const postsQuery = query(collection(db, "posts"), orderBy("created_at", "desc"));
    getDocs(postsQuery)
    .then(snap => {
        postsDB = snap.docs.map(d => ({ id: d.id, ...d.data() }));
        renderFeed(currentFeedFilter);
    })
    .catch(err => console.error('Error fetching posts:', err));
}

function fetchMinistriesFromDatabase() {
    getDocs(collection(db, "ministries"))
    .then(snap => {
        ministriesDB = snap.docs.map(d => ({ id: d.id, ...d.data() }));
    })
    .catch(err => console.error('Error fetching ministries:', err));
}

function handleCreatePost() {
    const textElem = document.getElementById('post-input-text');
    const text = textElem ? textElem.value.trim() : '';

    if (!text && !selectedPostImageBase64) {
        alert('Please type a message before posting.');
        return;
    }

    addDoc(collection(db, "posts"), {
        user_id: loggedInUserId,
        user_name: currentUserProfile ? currentUserProfile.name : 'Believer',
        content: text,
        image: selectedPostImageBase64,
        created_at: new Date().toISOString()
    })
    .then(() => {
        if(textElem) textElem.value = '';
        selectedPostImageBase64 = '';
        fetchPostsFromDatabase();
        showToast('Post published and saved to database!');
    })
    .catch(err => {
        console.error('Post creation error:', err);
        alert('Error creating post.');
    });
}

function renderFeed(filterType = 'all') {
    const container = document.getElementById('feed-posts-container');
    if (!container) return;
    container.innerHTML = '';

    if (postsDB.length === 0) {
        container.innerHTML = `
            <div style="text-align:center; color:var(--text-muted); padding:3rem 1rem;">
                <i class="fa-solid fa-hands-praying" style="font-size:2.5rem; color:var(--accent-cyan); margin-bottom:0.8rem; display:block;"></i>
                <h3>No posts found in database</h3>
            </div>
        `;
        return;
    }

    postsDB.forEach(post => {
        const card = document.createElement('div');
        card.className = 'feed-card';
        card.innerHTML = `
            <div class="post-header">
                <div class="author-meta">
                    <h4>${escapeHtml(post.user_name)}</h4>
                    <span>${escapeHtml(post.created_at)}</span>
                </div>
            </div>
            <div class="post-body">
                <p class="post-text">${escapeHtml(post.content)}</p>
            </div>
        `;
        container.appendChild(card);
    });
}

// Utility to prevent injection when rendering text dynamically from DB
function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}

// Navigation & Theme Utilities
window.navigateTo = function(viewId) {
    document.querySelectorAll('.app-view').forEach(view => view.classList.remove('active'));
    const targetView = document.getElementById(`view-${viewId}`);
    if (targetView) targetView.classList.add('active');
};

window.toggleTheme = function() {
    const current = document.documentElement.getAttribute('data-theme');
    const next = current === 'light' ? 'dark' : 'light';
    document.documentElement.setAttribute('data-theme', next);
    localStorage.setItem('fc_theme_v6', next);
};

document.addEventListener('DOMContentLoaded', () => {
    const savedTheme = localStorage.getItem('fc_theme_v6') || 'dark';
    document.documentElement.setAttribute('data-theme', savedTheme);

    if (loggedInUserId) {
        initAppSession();
    } else {
        document.getElementById('auth-screen').style.display = 'flex';
        document.getElementById('app-wrapper').classList.remove('logged-in');
    }
});
