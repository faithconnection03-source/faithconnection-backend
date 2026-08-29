/* ==========================================================================
   FAITHCONNECTION - FIREBASE & MYSQL INTEGRATED CORE (CLEANED)
   ========================================================================= */

// Import the functions you need from the SDKs you need
import { initializeApp } from "https://www.gstatic.com/firebasejs/10.8.0/firebase/app.js";
import { getAnalytics } from "https://www.gstatic.com/firebasejs/10.8.0/firebase/analytics.js";
import { getAuth, GoogleAuthProvider, signInWithPopup } from "https://www.gstatic.com/firebasejs/10.8.0/firebase/auth.js";

// Your web app's Firebase configuration (from your Firebase Console setup)
const firebaseConfig = {
    apiKey: "AIzaSyB8VeP5WBSilDyZwq8hSLdYCrmBiuLIPI",
    authDomain: "faithconnection-af4f2.firebaseapp.com",
    projectId: "faithconnection-af4f2",
    storageBucket: "faithconnection-af4f2.appspot.com",
    messagingSenderId: "135734412322",
    appId: "1:135734412322:web:11b16e5f8130b98a4367b",
    measurementId: "G-1T1KQPCN4R"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const analytics = getAnalytics(app);
const auth = getAuth(app);
const googleProvider = new GoogleAuthProvider();

// --- Live Backend API Base URL ---
const API_BASE_URL = "https://faithconnection.great-site.net/api.php";

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
window.handleGoogleSignIn = function() {
    clearAuthAlert();
    
    signInWithPopup(auth, googleProvider)
    .then((result) => {
        const user = result.user;
        const googleUserData = {
            name: user.displayName,
            email: user.email,
            firebase_uid: user.uid,
            avatar: user.photoURL || ''
        };

        fetch(`${API_BASE_URL}?action=google_login`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(googleUserData)
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                loggedInUserId = data.user.id;
                currentUserProfile = data.user;
                syncStorage();
                initAppSession();
                showToast(`Welcome, ${user.displayName}!`);
            } else {
                showAuthAlert(data.message || 'Google authentication failed with backend.');
            }
        })
        .catch(err => {
            console.error('Backend Sync Error:', err);
            showAuthAlert('Server connection failed during Google Login.');
        });

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

    fetch(`${API_BASE_URL}?action=login`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            loggedInUserId = data.user.id;
            currentUserProfile = data.user;
            syncStorage();
            initAppSession();
            showToast('Login successful! Welcome to FaithConnection.');
        } else {
            showAuthAlert(data.message || 'Invalid credentials.');
        }
    })
    .catch(err => {
        console.error('Login Error:', err);
        showAuthAlert('Server connection failed.');
    });
}

function handleRegister(e) {
    e.preventDefault();
    clearAuthAlert();

    const name = document.getElementById('reg-name').value.trim();
    const email = document.getElementById('reg-email').value.trim().toLowerCase();
    const password = document.getElementById('reg-password').value;

    fetch(`${API_BASE_URL}?action=register`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name, email, password })
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            showToast('Registration successful! Please login.');
            switchAuthTab('login');
        } else {
            showAuthAlert(data.message || 'Registration failed.');
        }
    })
    .catch(err => {
        console.error('Database Error:', err);
        showAuthAlert('Server connection error.');
    });
}

function handleLogout() {
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
    fetch(`${API_BASE_URL}?action=get_user&id=${loggedInUserId}`)
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            currentUserProfile = data.user;
        }
    })
    .catch(err => console.error('Error fetching user profile:', err));
}

function fetchPostsFromDatabase() {
    fetch(`${API_BASE_URL}?action=get_posts`)
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            postsDB = data.posts || [];
            renderFeed(currentFeedFilter);
        }
    })
    .catch(err => console.error('Error fetching posts:', err));
}

function fetchMinistriesFromDatabase() {
    fetch(`${API_BASE_URL}?action=get_ministries`)
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            ministriesDB = data.ministries || [];
        }
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

    fetch(`${API_BASE_URL}?action=create_post`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ user_id: loggedInUserId, content: text, image: selectedPostImageBase64 })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            if(textElem) textElem.value = '';
            selectedPostImageBase64 = '';
            fetchPostsFromDatabase();
            showToast('Post published and saved to MySQL database!');
        } else {
            alert(data.message || 'Error creating post.');
        }
    })
    .catch(err => console.error('Post creation error:', err));
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
