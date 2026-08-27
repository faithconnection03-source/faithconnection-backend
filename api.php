/* ==========================================================================
   FAITHCONNECTION - COMPLETE JAVASCRIPT APPLICATION CORE
   Fixed 3-Dots Dropdown with Context Search (Feed + Profile + Testimonies)
   ========================================================================= */

// --- Initial Mock Database ---
const defaultUsers = {
    'u_samuel': {
        id: 'u_samuel',
        username: 'samuel_k',
        name: 'Samuel Kumar',
        email: 'samuel@faithconnection.com',
        phone: '9876543210',
        password: '12345',
        role: 'Youth Leader / Member',
        church: 'Grace Fellowship Assembly',
        avatar: 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=200&q=80',
        bio: 'Walking by faith, praying for global youth revival and kingdom leadership! 🙏✨',
        friends: ['u_sarah', 'u_timothy', 'u_hannah'],
        ministries: ['m_1', 'm_2'],
        savedPosts: ['p_1']
    },
    'u_sarah': {
        id: 'u_sarah',
        username: 'sarah_worship',
        name: 'Sister Sarah Jenkins',
        email: 'sarah@faithconnection.com',
        phone: '9876543211',
        password: '12345',
        role: 'Worship Leader / Singer',
        church: 'City Praise Tabernacle',
        avatar: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=200&q=80',
        bio: 'Singing praises to the Almighty God. Ministering through music and worship arts. 🎶🕊️',
        friends: ['u_samuel', 'u_timothy', 'u_david'],
        ministries: ['m_1'],
        savedPosts: []
    },
    'u_timothy': {
        id: 'u_timothy',
        username: 'timothy_prayer',
        name: 'Brother Timothy Vance',
        email: 'timothy@faithconnection.com',
        phone: '9876543212',
        password: '12345',
        role: 'Prayer Warrior / Intercessor',
        church: 'Hope & Life Baptist Church',
        avatar: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=200&q=80',
        bio: 'Standing in the spiritual gap through unceasing prayer, intercession and scripture meditation.',
        friends: ['u_samuel', 'u_sarah'],
        ministries: ['m_1', 'm_3'],
        savedPosts: []
    },
    'u_hannah': {
        id: 'u_hannah',
        username: 'hannah_grace',
        name: 'Hannah Grace Miller',
        email: 'hannah@faithconnection.com',
        phone: '9876543213',
        password: '12345',
        role: 'Sunday School Teacher',
        church: 'Calvary Community Chapel',
        avatar: 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=200&q=80',
        bio: 'Nurturing young hearts in biblical truth and God’s everlasting love. 📖🌱',
        friends: ['u_samuel'],
        ministries: ['m_1', 'm_2'],
        savedPosts: []
    },
    'u_david': {
        id: 'u_david',
        username: 'pastor_david',
        name: 'Pastor David Thompson',
        email: 'david@faithconnection.com',
        phone: '9876543214',
        password: '12345',
        role: 'Pastor / Church Leader',
        church: 'New Life International Church',
        avatar: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=200&q=80',
        bio: 'Serving God’s flock with pastoral care, biblical preaching, and community outreach. ✝️',
        friends: ['u_sarah'],
        ministries: ['m_3'],
        savedPosts: []
    }
};

const defaultPosts = [
    {
        id: 'p_samuel_1',
        type: 'prayer',
        userId: 'u_samuel',
        category: 'Youth & Education',
        text: 'Praying for all the college students and youth preparing for exams and spiritual growth this season. May God grant divine wisdom, sharp focus, and unshakeable peace! 🙏✨',
        image: 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=800&q=80',
        likes: ['u_sarah', 'u_timothy', 'u_samuel'],
        prayers: ['u_sarah', 'u_timothy'],
        comments: [
            { id: 'c_s1', userId: 'u_sarah', user: 'Sister Sarah Jenkins', text: 'Amen Samuel! Standing with our youth in prayer.', time: '2h ago' }
        ],
        createdAt: Date.now() - 3600000 * 1
    },
    {
        id: 'p_1',
        type: 'prayer',
        userId: 'u_sarah',
        category: 'Healing & Health',
        text: 'Dear brothers and sisters, please lift up my mother in prayer. She was admitted for surgery tomorrow morning. We are standing firmly on God\'s promises of total healing and peace! 🙏🕊️',
        image: 'https://images.unsplash.com/photo-1507692049790-de58290a4334?auto=format&fit=crop&w=800&q=80',
        likes: ['u_samuel', 'u_timothy', 'u_hannah'],
        prayers: ['u_samuel', 'u_timothy'],
        comments: [
            { id: 'c_1', userId: 'u_timothy', user: 'Brother Timothy Vance', text: 'Amen Sister Sarah! Praying Psalm 103:3 over her right now.', time: '1 hr ago' },
            { id: 'c_2', userId: 'u_samuel', user: 'Samuel Kumar', text: 'Standing in agreement with you in faith!', time: '30 mins ago' }
        ],
        createdAt: Date.now() - 3600000 * 2
    },
    {
        id: 'p_samuel_2',
        type: 'testimony',
        userId: 'u_samuel',
        category: 'Answered Prayer',
        text: 'Praise the Lord! Our youth fellowship outreach yesterday reached over 50 souls with the Gospel message. Thank you everyone for your unceasing prayers and support! ⭐🙌',
        image: '',
        likes: ['u_sarah', 'u_david', 'u_samuel'],
        prayers: ['u_sarah'],
        comments: [],
        createdAt: Date.now() - 3600000 * 5
    },
    {
        id: 'p_2',
        type: 'testimony',
        userId: 'u_timothy',
        category: 'Answered Prayer',
        text: 'Hallelujah! Praise the Lord! After 6 months of prayer and searching, my younger brother was blessed with a job as a senior engineer today! Thank you to all who prayed with us. God is faithful! ⭐🙌',
        image: 'https://images.unsplash.com/photo-1499209974431-9dddcece7f88?auto=format&fit=crop&w=800&q=80',
        likes: ['u_samuel', 'u_sarah', 'u_david'],
        prayers: ['u_sarah'],
        comments: [
            { id: 'c_3', userId: 'u_david', user: 'Pastor David Thompson', text: 'Praise God for His wondrous provision! Rejoicing with your family.', time: '2 hrs ago' }
        ],
        createdAt: Date.now() - 3600000 * 6
    },
    {
        id: 'p_3',
        type: 'prayer',
        userId: 'u_hannah',
        category: 'Youth & Education',
        text: 'Asking for special prayers for our upcoming Sunday School VBS camp next week. Praying for open hearts, joyful spirits, and safety for all the children attending! 🎨📖',
        image: '',
        likes: ['u_samuel'],
        prayers: ['u_samuel'],
        comments: [],
        createdAt: Date.now() - 3600000 * 12
    }
];

const defaultMinistries = [
    {
        id: 'm_1',
        name: 'Youth Praise & Worship Network',
        category: 'Worship & Creative Arts',
        leaderId: 'u_sarah',
        leaderName: 'Sister Sarah Jenkins',
        bio: 'Connecting young worshipers, musicians, and audio engineers for unified praise across congregations.',
        members: ['u_samuel', 'u_sarah', 'u_timothy', 'u_hannah'],
        messages: [
            { id: 'gm_1', userId: 'u_sarah', text: 'Welcome everyone to the Youth Praise & Worship Network WhatsApp group! Let us share our worship setlists, audio clips, and prayer requests here. 🎶🕊️', time: '09:00 AM' },
            { id: 'gm_2', userId: 'u_samuel', text: 'Praise the Lord Sister Sarah! Ready for Friday worship practice. 🙏', time: '09:15 AM' },
            { id: 'gm_3', userId: 'u_timothy', text: 'Amen! Standing in prayer for the entire worship team.', time: '10:02 AM' }
        ]
    },
    {
        id: 'm_2',
        name: 'NextGen Bible & Mentorship Circle',
        category: 'Youth & Young Adults',
        leaderId: 'u_samuel',
        leaderName: 'Samuel Kumar',
        bio: 'Equipping teenagers and young adults with solid biblical foundations and weekly virtual devotionals.',
        members: ['u_samuel', 'u_hannah', 'u_sarah'],
        messages: [
            { id: 'gm_4', userId: 'u_samuel', text: 'Welcome to NextGen Mentorship group! Weekly devotional topic: "Building unshakeable faith in college/school". 📖', time: 'Yesterday' },
            { id: 'gm_5', userId: 'u_hannah', text: 'Amen! Looking forward to discussing with our students.', time: 'Yesterday' }
        ]
    },
    {
        id: 'm_3',
        name: 'Global Intercessory Watchmen',
        category: 'Prayer & Intercession',
        leaderId: 'u_timothy',
        leaderName: 'Brother Timothy Vance',
        bio: '24/7 prayer chain standing in faith for community revival, missionaries, and global needs.',
        members: ['u_timothy', 'u_david', 'u_samuel'],
        messages: [
            { id: 'gm_6', userId: 'u_timothy', text: 'Praise God Watchmen! Tonight at 9 PM we have our global intercession call. Please post urgent prayer requests below. 🕯️🙏', time: '11:30 AM' }
        ]
    }
];

const defaultChats = {
    'u_sarah': [
        { from: 'them', text: 'Grace and peace to you, brother! How may we pray together today? 🙏', time: '10:15 AM' }
    ],
    'u_timothy': [
        { from: 'them', text: 'Praise the Lord! Joining our evening fellowship prayer call tonight?', time: '02:30 PM' }
    ],
    'u_hannah': [
        { from: 'them', text: 'Hello! Thank you for supporting our children\'s ministry!', time: 'Yesterday' }
    ]
};

const defaultStories = [
    { id: 's_1', userId: 'u_sarah', text: 'Morning devotional: "The joy of the Lord is your strength!" 🎶✨', time: '1h ago' },
    { id: 's_2', userId: 'u_timothy', text: 'Morning prayer walk complete. Praying peace over your homes! 🌅🙏', time: '3h ago' },
    { id: 's_3', userId: 'u_samuel', text: 'Youth choir practice tonight at 6 PM! See you all there. 🎤', time: '4h ago' },
    { id: 's_4', userId: 'u_david', text: 'Preparing Sunday sermon: "Walking by Faith, Not by Sight". 📖✝️', time: '6h ago' }
];

const bibleVersesList = [
    { text: 'For I know the plans I have for you,” declares the LORD, “plans to prosper you and not to harm you, plans to give you hope and a future.', ref: 'Jeremiah 29:11 (NIV)' },
    { text: 'I can do all things through Christ who strengthens me.', ref: 'Philippians 4:13 (NKJV)' },
    { text: 'Trust in the LORD with all your heart and lean not on your own understanding; in all your ways submit to him, and he will make your paths straight.', ref: 'Proverbs 3:5-6 (NIV)' },
    { text: 'The LORD is my shepherd; I shall not want. He makes me lie down in green pastures, he leads me beside quiet waters.', ref: 'Psalm 23:1-2 (ESV)' },
    { text: 'Do not be anxious about anything, but in every situation, by prayer and petition, with thanksgiving, present your requests to God.', ref: 'Philippians 4:6 (NIV)' },
    { text: 'The Lord is my light and my salvation—whom shall I fear? The Lord is the stronghold of my life—of whom shall I be afraid?', ref: 'Psalm 27:1 (NIV)' }
];

const scriptureCollections = [
    { title: '🕊️ Peace in Difficult Times', text: 'Peace I leave with you; my peace I give you. I do not give to you as the world gives. Do not let your hearts be troubled.', cite: 'John 14:27' },
    { title: '💪 Strength & Courage', text: 'Be strong and courageous. Do not be afraid; do not be discouraged, for the LORD your God will be with you wherever you go.', cite: 'Joshua 1:9' },
    { title: '💊 Healing & Restoration', text: 'He heals the brokenhearted and binds up their wounds.', cite: 'Psalm 147:3' },
    { title: '🌱 God\'s Unfailing Provision', text: 'And my God will meet all your needs according to the riches of his glory in Christ Jesus.', cite: 'Philippians 4:19' }
];

// --- Persistent State Variables ---
let usersDB = JSON.parse(localStorage.getItem('fc_users_v6')) || defaultUsers;
let postsDB = JSON.parse(localStorage.getItem('fc_posts_v6')) || defaultPosts;
let ministriesDB = JSON.parse(localStorage.getItem('fc_ministries_v6')) || defaultMinistries;
let chatsDB = JSON.parse(localStorage.getItem('fc_chats_v6')) || defaultChats;
let storiesDB = JSON.parse(localStorage.getItem('fc_stories_v6')) || defaultStories;

let loggedInUserId = localStorage.getItem('fc_logged_user_v6') || null;
let currentFeedFilter = 'all'; // 'all', 'prayer', 'testimony'
let activeChatUserId = 'u_sarah';
let activeGroupId = 'm_1';
let currentActiveProfileId = null;
let selectedPostImageBase64 = '';
let selectedAvatarBase64 = '';
let currentVerseIndex = 0;
let storyTimer = null;

// Synchronize all data to LocalStorage
function syncStorage() {
    if (!Array.isArray(postsDB)) postsDB = defaultPosts;
    localStorage.setItem('fc_users_v6', JSON.stringify(usersDB));
    localStorage.setItem('fc_posts_v6', JSON.stringify(postsDB));
    localStorage.setItem('fc_ministries_v6', JSON.stringify(ministriesDB));
    localStorage.setItem('fc_chats_v6', JSON.stringify(chatsDB));
    localStorage.setItem('fc_stories_v6', JSON.stringify(storiesDB));
    if (loggedInUserId) {
        localStorage.setItem('fc_logged_user_v6', loggedInUserId);
    } else {
        localStorage.removeItem('fc_logged_user_v6');
    }
}

// --- Time Helper ---
function timeAgo(date) {
    const seconds = Math.floor((Date.now() - new Date(date)) / 1000);
    if (seconds < 60) return 'Just now';
    const minutes = Math.floor(seconds / 60);
    if (minutes < 60) return `${minutes}m ago`;
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `${hours}h ago`;
    const days = Math.floor(hours / 24);
    return `${days}d ago`;
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
// AUTHENTICATION LOGIC
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
    alertBox.className = isError ? 'auth-alert error' : 'auth-alert success';
    alertBox.innerText = msg;
    alertBox.style.display = 'block';
}

function clearAuthAlert() {
    const alertBox = document.getElementById('auth-alert');
    alertBox.style.display = 'none';
}

// ==========================================================================
// GOOGLE / GMAIL AUTHENTICATION WORKFLOW
// ==========================================================================
function handleGoogleSignIn() {
    renderGoogleAccounts();
    openModal('google-auth-modal');
}

function renderGoogleAccounts() {
    const list = document.getElementById('google-accounts-list');
    if (!list) return;
    list.innerHTML = '';

    const googleProfiles = [
        { 
            id: 'u_samuel', 
            name: 'Samuel Kumar', 
            email: 'samuel.kumar@gmail.com', 
            avatar: 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=200&q=80' 
        },
        { 
            id: 'u_sarah', 
            name: 'Sister Sarah Jenkins', 
            email: 'sarah.jenkins@gmail.com', 
            avatar: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=200&q=80' 
        }
    ];

    googleProfiles.forEach(acc => {
        const item = document.createElement('div');
        item.style.cssText = 'display:flex; align-items:center; gap:0.9rem; padding:0.75rem 1rem; border:1px solid #e0e0e0; border-radius:12px; cursor:pointer; transition:0.2s; background:#ffffff; box-shadow:0 2px 5px rgba(0,0,0,0.03);';
        item.onmouseenter = () => item.style.background = '#f1f3f4';
        item.onmouseleave = () => item.style.background = '#ffffff';
        item.onclick = () => selectGoogleAccount(acc.id, acc.email);
        item.innerHTML = `
            <img src="${acc.avatar}" style="width:40px; height:40px; border-radius:50%; object-fit:cover; border:1.5px solid #0072ff;" alt="${acc.name}">
            <div style="text-align:left;">
                <div style="font-weight:700; font-size:0.94rem; color:#202124;">${acc.name}</div>
                <div style="font-size:0.8rem; color:#5f6368;">${acc.email}</div>
            </div>
            <i class="fa-solid fa-chevron-right" style="margin-left:auto; color:#9aa0a6; font-size:0.8rem;"></i>
        `;
        list.appendChild(item);
    });
}

function selectGoogleAccount(userId, email) {
    loggedInUserId = userId;
    syncStorage();
    closeModal('google-auth-modal');
    initAppSession();
    showToast(`Signed in with Google (${email})! 🙏✨`);
}

function toggleCustomGoogleInput() {
    const sec = document.getElementById('google-custom-email-section');
    const btn = document.getElementById('btn-toggle-custom-google');
    if (sec.style.display === 'none') {
        sec.style.display = 'block';
        btn.style.display = 'none';
        document.getElementById('google-custom-email').focus();
    } else {
        sec.style.display = 'none';
        btn.style.display = 'flex';
    }
}

function handleContinueWithCustomGoogleAccount() {
    const email = document.getElementById('google-custom-email').value.trim();
    const name = document.getElementById('google-custom-name').value.trim() || email.split('@')[0];

    if (!email || !email.includes('@')) {
        alert('Please enter a valid Gmail address (e.g. name@gmail.com).');
        return;
    }

    // Check if user exists or create new Google user
    let existingUserId = Object.keys(usersDB).find(id => usersDB[id].email.toLowerCase() === email.toLowerCase());
    if (!existingUserId) {
        existingUserId = 'u_google_' + Date.now();
        usersDB[existingUserId] = {
            id: existingUserId,
            username: email.split('@')[0].toLowerCase().replace(/[^a-z0-9]/g, '_'),
            name: name,
            email: email,
            phone: '',
            password: 'google_oauth',
            role: 'Believer / Member',
            church: 'Fellowship Community',
            avatar: 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=200&q=80',
            bio: 'Walking by faith in Christ. Signed in with Google Account.',
            friends: ['u_sarah', 'u_timothy'],
            ministries: ['m_1'],
            savedPosts: []
        };
    }

    loggedInUserId = existingUserId;
    syncStorage();
    closeModal('google-auth-modal');
    initAppSession();
    showToast(`Signed in with Google as ${name}! 🙏✨`);
}

function handleLogin(e) {
    e.preventDefault();
    clearAuthAlert();
    const identifier = document.getElementById('login-identifier').value.trim().toLowerCase();
    const password = document.getElementById('login-password').value;

    const matchedUserId = Object.keys(usersDB).find(id => {
        const u = usersDB[id];
        return (
            u.username.toLowerCase() === identifier ||
            u.email.toLowerCase() === identifier ||
            u.phone === identifier
        );
    });

    if (!matchedUserId || usersDB[matchedUserId].password !== password) {
        showAuthAlert('Invalid credentials! Please try again or use the 1-Click Demo.');
        return;
    }

    loggedInUserId = matchedUserId;
    syncStorage();
    initAppSession();
    showToast(`Signed in successfully! Grace and peace to you.`);
}

function handleRegister(e) {
    e.preventDefault();
    clearAuthAlert();

    const name = document.getElementById('reg-name').value.trim();
    const username = document.getElementById('reg-username').value.trim().toLowerCase().replace(/\s+/g, '_');
    const email = document.getElementById('reg-email').value.trim().toLowerCase();
    const phone = document.getElementById('reg-phone').value.trim();
    const role = document.getElementById('reg-role').value;
    const church = document.getElementById('reg-church').value.trim();
    const password = document.getElementById('reg-password').value;

    if (!role) {
        showAuthAlert('Please select your church role or ministry area.');
        return;
    }

    const duplicate = Object.values(usersDB).some(u => u.username === username || u.email === email);
    if (duplicate) {
        showAuthAlert('A user with that Username or Email is already registered.');
        return;
    }

    const newId = 'u_' + Date.now();
    usersDB[newId] = {
        id: newId,
        username: username,
        name: name,
        email: email,
        phone: phone,
        password: password,
        role: role,
        church: church || 'Fellowship Church',
        avatar: `https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=200&q=80`,
        bio: `Walking by faith in Jesus Christ as a ${role}.`,
        friends: ['u_sarah', 'u_timothy'],
        ministries: ['m_1'],
        savedPosts: []
    };

    // Reciprocal friendship
    if (usersDB['u_sarah']) usersDB['u_sarah'].friends.push(newId);
    if (usersDB['u_timothy']) usersDB['u_timothy'].friends.push(newId);

    loggedInUserId = newId;
    syncStorage();
    initAppSession();
    showToast(`Praise God! Account created. Welcome, ${name}!`);
}

function handleLogout() {
    loggedInUserId = null;
    localStorage.removeItem('fc_logged_user_v6');
    document.getElementById('app-wrapper').classList.remove('logged-in');
    document.getElementById('auth-screen').style.display = 'flex';
    switchAuthTab('login');
    showToast('Logged out of fellowship.');
}

function initAppSession() {
    clearAuthAlert();
    document.getElementById('auth-screen').style.display = 'none';
    document.getElementById('app-wrapper').classList.add('logged-in');

    const me = usersDB[loggedInUserId] || usersDB['u_samuel'];
    if (!me.savedPosts) me.savedPosts = [];

    // Header & Sidebar Avatar / Info
    document.getElementById('header-avatar-img').src = me.avatar;
    document.getElementById('sidebar-user-avatar').src = me.avatar;
    document.getElementById('sidebar-user-name').innerText = me.name;
    document.getElementById('sidebar-user-role').innerText = me.role;
    document.getElementById('create-post-avatar').src = me.avatar;

    // Render components
    renderStories();
    renderFeed('all');
    renderTestimonies();
    renderDailyVerse();
    renderScriptureCollections();
    renderBelieversList();
    renderMinistriesList();
    renderChatView();

    navigateTo('feed');
}

// ==========================================================================
// NAVIGATION & ROUTING
// ==========================================================================
function navigateTo(viewId, param = null) {
    document.querySelectorAll('.app-view').forEach(view => view.classList.remove('active'));
    document.querySelectorAll('.nav-item').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.mob-nav-btn').forEach(btn => btn.classList.remove('active'));

    const targetView = document.getElementById(`view-${viewId}`);
    if (targetView) targetView.classList.add('active');

    const sidebarBtn = document.getElementById(`nav-${viewId}`);
    if (sidebarBtn) sidebarBtn.classList.add('active');

    const mobBtn = document.getElementById(`mob-${viewId}`);
    if (mobBtn) mobBtn.classList.add('active');

    if (viewId === 'profile') {
        currentActiveProfileId = param || loggedInUserId;
        renderProfileView(currentActiveProfileId);
    } else if (viewId === 'chat') {
        if (param) activeChatUserId = param;
        renderChatView();
    } else if (viewId === 'group-chat') {
        if (param) activeGroupId = param;
        renderGroupChat();
    } else if (viewId === 'feed') {
        renderFeed(currentFeedFilter);
    } else if (viewId === 'testimonies') {
        renderTestimonies();
    }

    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function scrollToCreatePost() {
    navigateTo('feed');
    const textarea = document.getElementById('post-input-text');
    if (textarea) {
        textarea.focus();
        textarea.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

function openCreateTestimonyQuick() {
    navigateTo('feed');
    document.getElementById('post-input-type').value = 'testimony';
    document.getElementById('post-input-text').placeholder = 'Share how God answered your prayers or performed a miracle...';
    scrollToCreatePost();
}

// ==========================================================================
// STORIES FEATURE
// ==========================================================================
function renderStories() {
    const container = document.getElementById('stories-container');
    if (!container) return;
    container.innerHTML = '';

    const me = usersDB[loggedInUserId];
    
    // Add Your Story Item
    const addStoryItem = document.createElement('div');
    addStoryItem.className = 'story-item';
    addStoryItem.onclick = handleAddStoryPrompt;
    addStoryItem.innerHTML = `
        <div class="story-ring" style="background: var(--bg-surface-hover); border: 2px dashed var(--accent-cyan);">
            <img src="${me ? me.avatar : 'https://i.pravatar.cc/150'}" alt="">
        </div>
        <span class="story-username">+ Share Story</span>
    `;
    container.appendChild(addStoryItem);

    storiesDB.forEach(story => {
        const user = usersDB[story.userId] || { name: 'Believer', avatar: 'https://i.pravatar.cc/150' };
        const item = document.createElement('div');
        item.className = 'story-item';
        item.onclick = () => openStory(story.id);
        item.innerHTML = `
            <div class="story-ring">
                <img src="${user.avatar}" alt="${user.name}">
            </div>
            <span class="story-username">${user.name.split(' ')[0]}</span>
        `;
        container.appendChild(item);
    });
}

function handleAddStoryPrompt() {
    const text = prompt("Share a quick faith status or prayer moment for today's story:", "Praising God for His everlasting mercy and love! ✨");
    if (text && text.trim()) {
        const newStory = {
            id: 's_' + Date.now(),
            userId: loggedInUserId,
            text: text.trim(),
            time: 'Just now'
        };
        storiesDB.unshift(newStory);
        syncStorage();
        renderStories();
        showToast('Story shared with believers!');
    }
}

function openStory(storyId) {
    const story = storiesDB.find(s => s.id === storyId);
    if (!story) return;
    const user = usersDB[story.userId] || { name: 'Believer', avatar: 'https://i.pravatar.cc/150' };

    document.getElementById('story-user-avatar').src = user.avatar;
    document.getElementById('story-user-name').innerText = user.name;
    document.getElementById('story-time-stamp').innerText = story.time;
    document.getElementById('story-text').innerText = `"${story.text}"`;

    const progressFill = document.getElementById('story-progress-fill');
    progressFill.style.width = '0%';
    openModal('story-modal');

    let current = 0;
    if (storyTimer) clearInterval(storyTimer);
    storyTimer = setInterval(() => {
        current += 2;
        progressFill.style.width = `${current}%`;
        if (current >= 100) {
            clearInterval(storyTimer);
            closeModal('story-modal');
        }
    }, 100);
}

// ==========================================================================
// FEED & POSTS SYSTEM (ROBUST CONTEXT-BASED DROPDOWNS & ACTIONS)
// ==========================================================================
function handlePostImageSelect(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(evt) {
            selectedPostImageBase64 = evt.target.result;
            document.getElementById('post-image-preview').src = selectedPostImageBase64;
            document.getElementById('post-image-preview-container').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
}

function clearPostImage() {
    selectedPostImageBase64 = '';
    document.getElementById('post-image-preview').src = '';
    document.getElementById('post-image-preview-container').style.display = 'none';
    document.getElementById('post-file-input').value = '';
}

// CREATE POST
function handleCreatePost() {
    const textElem = document.getElementById('post-input-text');
    const categoryElem = document.getElementById('post-input-category');
    const typeElem = document.getElementById('post-input-type');

    const text = textElem ? textElem.value.trim() : '';
    const category = categoryElem ? categoryElem.value : 'General';
    const type = typeElem ? typeElem.value : 'prayer';

    if (!text && !selectedPostImageBase64) {
        alert('Please type a message, prayer request, or attach a photo before posting.');
        return;
    }

    if (!loggedInUserId || !usersDB[loggedInUserId]) {
        loggedInUserId = 'u_samuel';
    }

    const newPostId = 'p_' + Date.now();
    const newPost = {
        id: newPostId,
        type: type,
        userId: loggedInUserId,
        category: category,
        text: text,
        image: selectedPostImageBase64 || '',
        likes: [loggedInUserId],
        prayers: type === 'prayer' ? [loggedInUserId] : [],
        comments: [],
        createdAt: Date.now()
    };

    if (!Array.isArray(postsDB)) postsDB = [];
    postsDB.unshift(newPost);
    syncStorage();

    // Reset Form Fields
    if (textElem) textElem.value = '';
    clearPostImage();

    // Render Feeds Immediately
    renderFeed(currentFeedFilter);
    renderTestimonies();

    if (currentActiveProfileId === loggedInUserId) {
        renderProfileView(loggedInUserId);
    }

    const isTestimony = type === 'testimony';
    showToast(isTestimony ? '⭐ Praise Testimony published to the community!' : '🙏 Prayer Request posted to the community!');

    // Highlight newly posted card
    setTimeout(() => {
        const newCard = document.getElementById(`postcard-${newPostId}`);
        if (newCard) {
            newCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
            newCard.style.border = '2px solid var(--accent-cyan)';
            newCard.style.boxShadow = '0 0 25px rgba(0, 242, 254, 0.4)';
            setTimeout(() => {
                newCard.style.border = '1px solid var(--border-color)';
                newCard.style.boxShadow = 'var(--shadow-sm)';
            }, 2500);
        }
    }, 100);
}

// FEED FILTERING (All Posts / Prayer Requests / Praise Testimonies)
function filterFeedPosts(filterType) {
    currentFeedFilter = filterType;
    document.querySelectorAll('.feed-filter-tab').forEach(btn => btn.classList.remove('active'));
    const activeTab = document.getElementById(`ff-${filterType}`);
    if (activeTab) activeTab.classList.add('active');
    renderFeed(filterType);
}

function renderFeed(filterType = 'all') {
    const container = document.getElementById('feed-posts-container');
    if (!container) return;
    container.innerHTML = '';

    if (!Array.isArray(postsDB)) postsDB = defaultPosts;

    let filteredPosts = postsDB;
    if (filterType === 'prayer') {
        filteredPosts = postsDB.filter(p => p.type === 'prayer');
    } else if (filterType === 'testimony') {
        filteredPosts = postsDB.filter(p => p.type === 'testimony');
    }

    if (filteredPosts.length === 0) {
        container.innerHTML = `
            <div style="text-align:center; color:var(--text-muted); padding:3rem 1rem; background:var(--bg-surface); border-radius:var(--radius-xl); border:1px solid var(--border-color);">
                <i class="fa-solid fa-hands-praying" style="font-size:2.5rem; color:var(--accent-cyan); margin-bottom:0.8rem; display:block;"></i>
                <h3 style="font-size:1.1rem; color:var(--text-primary); margin-bottom:0.3rem;">No posts found in this category</h3>
                <p style="font-size:0.88rem;">Share your prayer request or praise testimony above!</p>
            </div>
        `;
        return;
    }

    filteredPosts.forEach(post => {
        container.appendChild(createPostCardElement(post));
    });
}

function renderTestimonies() {
    const container = document.getElementById('testimonies-feed-container');
    if (!container) return;
    container.innerHTML = '';

    if (!Array.isArray(postsDB)) postsDB = defaultPosts;
    const testimonies = postsDB.filter(p => p.type === 'testimony');

    if (testimonies.length === 0) {
        container.innerHTML = `
            <div style="text-align:center; color:var(--text-muted); padding:3rem 1rem; background:var(--bg-surface); border-radius:var(--radius-xl); border:1px solid var(--border-color);">
                <i class="fa-solid fa-star" style="font-size:2.5rem; color:var(--accent-gold); margin-bottom:0.8rem; display:block;"></i>
                <h3 style="font-size:1.1rem; color:var(--text-primary); margin-bottom:0.3rem;">No praise reports yet</h3>
                <p style="font-size:0.88rem;">Click "Share Testimony" to celebrate what God has done in your life!</p>
            </div>
        `;
        return;
    }

    testimonies.forEach(post => {
        container.appendChild(createPostCardElement(post));
    });
}

// GENERATE POST CARD (WITH GUARANTEED WORKING 3-DOTS EDIT, DELETE, SAVE & SHARE EVERYWHERE)
function createPostCardElement(post) {
    const author = usersDB[post.userId] || { name: 'Faithful Believer', avatar: 'https://i.pravatar.cc/150', role: 'Church Member' };
    
    // Check if the current user owns this post
    const isOwner = (post.userId === loggedInUserId) || (currentActiveProfileId === loggedInUserId && post.userId === loggedInUserId);
    
    const hasLiked = post.likes && post.likes.includes(loggedInUserId);
    const hasPrayed = post.prayers && post.prayers.includes(loggedInUserId);
    const me = usersDB[loggedInUserId] || {};
    const isSaved = (me.savedPosts || []).includes(post.id);
    const isTestimony = post.type === 'testimony';

    const card = document.createElement('div');
    card.className = 'feed-card';
    card.id = `postcard-${post.id}`;

    card.innerHTML = `
        <div class="post-header">
            <div class="post-author" onclick="navigateTo('profile', '${post.userId}')">
                <img src="${author.avatar}" class="avatar-sm" alt="${author.name}">
                <div class="author-meta">
                    <h4>${author.name}</h4>
                    <span>${author.role || 'Believer'} • ${timeAgo(post.createdAt || Date.now())}</span>
                </div>
            </div>
            
            <!-- 3-DOTS OPTIONS MENU (Using direct button context) -->
            <div class="post-menu-wrap" onclick="event.stopPropagation()">
                <button class="post-menu-btn" type="button" onclick="togglePostDropdown(event, this)" title="Options">
                    <i class="fa-solid fa-ellipsis-vertical"></i>
                </button>
                <div class="post-dropdown">
                    ${isOwner ? `
                        <div class="post-dropdown-item" onclick="openEditPostModal('${post.id}')">
                            <i class="fa-solid fa-pen" style="color:var(--accent-cyan);"></i> Edit Post
                        </div>
                        <div class="post-dropdown-item danger" onclick="handleDeletePost('${post.id}')">
                            <i class="fa-solid fa-trash" style="color:var(--accent-red);"></i> Delete Post
                        </div>
                        <div class="post-dropdown-item" onclick="toggleBookmark('${post.id}')">
                            <i class="fa-solid fa-bookmark" style="color:var(--accent-gold);"></i> ${isSaved ? 'Unsave Post' : 'Save Post'}
                        </div>
                        <div class="post-dropdown-item" onclick="sharePost('${post.id}')">
                            <i class="fa-solid fa-share-nodes" style="color:var(--accent-blue);"></i> Share Post
                        </div>
                    ` : `
                        <div class="post-dropdown-item" onclick="toggleBookmark('${post.id}')">
                            <i class="fa-solid fa-bookmark" style="color:var(--accent-gold);"></i> ${isSaved ? 'Unsave Post' : 'Save Post'}
                        </div>
                        <div class="post-dropdown-item" onclick="sharePost('${post.id}')">
                            <i class="fa-solid fa-share-nodes" style="color:var(--accent-blue);"></i> Share Post
                        </div>
                    `}
                </div>
            </div>
        </div>

        <div class="post-body">
            <div style="display:flex; gap:0.5rem; flex-wrap:wrap; margin-bottom:0.8rem;">
                <span class="post-category-tag ${isTestimony ? 'testimony' : ''}">
                    <i class="fa-solid ${isTestimony ? 'fa-star' : 'fa-hands-praying'}"></i> ${isTestimony ? 'Praise Testimony' : 'Prayer Request'}
                </span>
                <span class="post-category-tag" style="background:rgba(255,255,255,0.06); color:var(--text-secondary);">
                    <i class="fa-solid fa-tag"></i> ${post.category}
                </span>
            </div>
            <p class="post-text">${post.text}</p>
            ${post.image ? `<img src="${post.image}" class="post-image-attachment" alt="Attached photo">` : ''}
        </div>

        <div class="post-stats-bar">
            <span><i class="fa-solid fa-heart" style="color:var(--accent-magenta);"></i> ${post.likes ? post.likes.length : 0} Amen</span>
            <span>${post.prayers ? post.prayers.length : 0} Believers Prayed • ${(post.comments || []).length} Comments</span>
        </div>

        <div class="post-actions-bar">
            <button class="action-btn ${hasLiked ? 'liked' : ''}" onclick="toggleLike('${post.id}')">
                <i class="${hasLiked ? 'fa-solid' : 'fa-regular'} fa-heart"></i> Amen
            </button>
            <button class="action-btn ${hasPrayed ? 'prayed' : ''}" onclick="togglePrayed('${post.id}')">
                <i class="fa-solid fa-hands-praying"></i> I Prayed
            </button>
            <button class="action-btn" onclick="toggleComments(this, '${post.id}')">
                <i class="fa-regular fa-comment-dots"></i> Comment
            </button>
            <button class="action-btn" onclick="sharePost('${post.id}')">
                <i class="fa-solid fa-share-nodes"></i> Share
            </button>
            <button class="action-btn ${isSaved ? 'prayed' : ''}" onclick="toggleBookmark('${post.id}')">
                <i class="${isSaved ? 'fa-solid' : 'fa-regular'} fa-bookmark"></i>
            </button>
        </div>

        <div class="comments-section">
            <div class="comment-list">
                ${(post.comments || []).map(c => `
                    <div class="comment-item">
                        <div class="comment-content">
                            <strong onclick="navigateTo('profile', '${c.userId}')">${c.user}:</strong>
                            <span>${c.text}</span>
                        </div>
                        ${c.userId === loggedInUserId ? `
                            <div class="comment-actions">
                                <button class="comment-btn" onclick="handleEditComment('${post.id}', '${c.id}')"><i class="fa-solid fa-pen"></i></button>
                                <button class="comment-btn delete" onclick="handleDeleteComment('${post.id}', '${c.id}')"><i class="fa-solid fa-trash"></i></button>
                            </div>
                        ` : ''}
                    </div>
                `).join('')}
            </div>

            <form class="comment-form" onsubmit="handleAddComment(event, '${post.id}')">
                <input type="text" placeholder="Write a word of prayer or encouragement..." required>
                <button type="submit" class="btn-primary" style="padding:0.4rem 1rem;"><i class="fa-solid fa-paper-plane"></i></button>
            </form>
        </div>
    `;

    return card;
}

// --- 3-DOTS DROPDOWN CONTROLLER (Direct Parent Context Lookup) ---
function togglePostDropdown(e, btn) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    const menuWrap = btn.closest('.post-menu-wrap');
    if (!menuWrap) return;

    const targetDropdown = menuWrap.querySelector('.post-dropdown');
    const isCurrentlyActive = targetDropdown ? targetDropdown.classList.contains('active') : false;

    // Close all open dropdowns across the whole page first
    document.querySelectorAll('.post-dropdown').forEach(d => d.classList.remove('active'));

    // Toggle current dropdown directly
    if (targetDropdown && !isCurrentlyActive) {
        targetDropdown.classList.add('active');
    }
}

// Close dropdowns on background click
document.addEventListener('click', (e) => {
    if (!e.target.closest('.post-menu-wrap')) {
        document.querySelectorAll('.post-dropdown').forEach(d => d.classList.remove('active'));
    }
});

// --- Post Interactions ---
function toggleLike(postId) {
    const post = postsDB.find(p => p.id === postId);
    if (!post) return;
    if (!post.likes) post.likes = [];

    const index = post.likes.indexOf(loggedInUserId);
    if (index > -1) {
        post.likes.splice(index, 1);
    } else {
        post.likes.push(loggedInUserId);
    }

    syncStorage();
    renderFeed(currentFeedFilter);
    renderTestimonies();
    if (currentActiveProfileId) renderProfileView(currentActiveProfileId);
}

function togglePrayed(postId) {
    const post = postsDB.find(p => p.id === postId);
    if (!post) return;
    if (!post.prayers) post.prayers = [];

    const index = post.prayers.indexOf(loggedInUserId);
    if (index > -1) {
        post.prayers.splice(index, 1);
    } else {
        post.prayers.push(loggedInUserId);
        showToast('You stood in prayer for this request! 🙏');
    }

    syncStorage();
    renderFeed(currentFeedFilter);
    renderTestimonies();
    if (currentActiveProfileId) renderProfileView(currentActiveProfileId);
}

function toggleBookmark(postId) {
    const me = usersDB[loggedInUserId];
    if (!me) return;
    if (!me.savedPosts) me.savedPosts = [];

    const index = me.savedPosts.indexOf(postId);
    if (index > -1) {
        me.savedPosts.splice(index, 1);
        showToast('Post removed from saved list.');
    } else {
        me.savedPosts.push(postId);
        showToast('Post saved to your profile bookmark!');
    }

    syncStorage();
    renderFeed(currentFeedFilter);
    renderTestimonies();
    if (currentActiveProfileId === loggedInUserId) renderProfileView(loggedInUserId);
}

function toggleComments(btn, postId) {
    const card = btn.closest('.feed-card');
    if (card) {
        const section = card.querySelector('.comments-section');
        if (section) section.classList.toggle('active');
    }
}

function handleAddComment(e, postId) {
    e.preventDefault();
    const input = e.target.querySelector('input');
    const text = input.value.trim();
    if (!text) return;

    const post = postsDB.find(p => p.id === postId);
    const me = usersDB[loggedInUserId];
    if (!post || !me) return;

    if (!post.comments) post.comments = [];
    post.comments.push({
        id: 'c_' + Date.now(),
        userId: loggedInUserId,
        user: me.name,
        text: text,
        time: 'Just now'
    });

    syncStorage();
    input.value = '';
    renderFeed(currentFeedFilter);
    renderTestimonies();
    if (currentActiveProfileId) renderProfileView(currentActiveProfileId);
}

function handleEditComment(postId, commentId) {
    const post = postsDB.find(p => p.id === postId);
    if (!post) return;
    const comment = (post.comments || []).find(c => c.id === commentId);
    if (!comment) return;

    const updated = prompt('Edit your comment:', comment.text);
    if (updated !== null && updated.trim()) {
        comment.text = updated.trim();
        syncStorage();
        renderFeed(currentFeedFilter);
        renderTestimonies();
        if (currentActiveProfileId) renderProfileView(currentActiveProfileId);
    }
}

function handleDeleteComment(postId, commentId) {
    if (confirm('Delete this comment?')) {
        const post = postsDB.find(p => p.id === postId);
        if (post && post.comments) {
            post.comments = post.comments.filter(c => c.id !== commentId);
            syncStorage();
            renderFeed(currentFeedFilter);
            renderTestimonies();
            if (currentActiveProfileId) renderProfileView(currentActiveProfileId);
        }
    }
}

// OPEN EDIT POST MODAL
function openEditPostModal(postId) {
    const post = postsDB.find(p => p.id === postId);
    if (!post) return;

    document.getElementById('edit-post-id').value = post.id;
    if (document.getElementById('edit-post-type')) {
        document.getElementById('edit-post-type').value = post.type || 'prayer';
    }
    document.getElementById('edit-post-category').value = post.category;
    document.getElementById('edit-post-text').value = post.text;
    openModal('edit-post-modal');
}

function handleSaveEditedPost(e) {
    e.preventDefault();
    const id = document.getElementById('edit-post-id').value;
    const type = document.getElementById('edit-post-type') ? document.getElementById('edit-post-type').value : 'prayer';
    const category = document.getElementById('edit-post-category').value;
    const text = document.getElementById('edit-post-text').value.trim();

    const post = postsDB.find(p => p.id === id);
    if (post) {
        post.type = type;
        post.category = category;
        post.text = text;
        syncStorage();
        closeModal('edit-post-modal');
        renderFeed(currentFeedFilter);
        renderTestimonies();
        if (currentActiveProfileId) renderProfileView(currentActiveProfileId);
        showToast('Post updated successfully!');
    }
}

// DELETE POST
function handleDeletePost(postId) {
    if (confirm('Are you sure you want to delete this post?')) {
        postsDB = postsDB.filter(p => p.id !== postId);
        syncStorage();
        renderFeed(currentFeedFilter);
        renderTestimonies();
        if (currentActiveProfileId) renderProfileView(currentActiveProfileId);
        showToast('Post deleted successfully.');
    }
}

function sharePost(postId) {
    const post = postsDB.find(p => p.id === postId);
    if (!post) return;
    const shareData = {
        title: 'FaithConnection Prayer Need',
        text: `"${post.text}" - shared via FaithConnection`,
        url: window.location.href
    };

    if (navigator.share) {
        navigator.share(shareData);
    } else {
        navigator.clipboard.writeText(`${post.text} - via FaithConnection`);
        showToast('Post message copied to clipboard!');
    }
}

// ==========================================================================
// DAILY BIBLE VERSE
// ==========================================================================
function renderDailyVerse() {
    const item = bibleVersesList[currentVerseIndex];
    document.getElementById('daily-verse-quote').innerText = `"${item.text}"`;
    document.getElementById('daily-verse-source').innerText = item.ref;
}

function cycleNextVerse() {
    currentVerseIndex = (currentVerseIndex + 1) % bibleVersesList.length;
    renderDailyVerse();
}

function shareVerseText() {
    const item = bibleVersesList[currentVerseIndex];
    const text = `"${item.text}" — ${item.ref}`;
    if (navigator.share) {
        navigator.share({ title: 'Daily Bible Bread', text: text });
    } else {
        navigator.clipboard.writeText(text);
        showToast('Daily verse copied to clipboard!');
    }
}

function copyVerseText() {
    const item = bibleVersesList[currentVerseIndex];
    navigator.clipboard.writeText(`"${item.text}" — ${item.ref}`);
    showToast('Verse copied to clipboard!');
}

function renderScriptureCollections() {
    const grid = document.getElementById('scripture-collection-grid');
    if (!grid) return;
    grid.innerHTML = '';
    scriptureCollections.forEach(col => {
        const div = document.createElement('div');
        div.className = 'verse-collection-card';
        div.innerHTML = `
            <h4>${col.title}</h4>
            <p>"${col.text}"</p>
            <cite>${col.cite}</cite>
        `;
        grid.appendChild(div);
    });
}

// ==========================================================================
// ACTIVE BELIEVERS & FELLOWSHIP DIRECTORY
// ==========================================================================
function renderBelieversList(query = '') {
    const container = document.getElementById('believers-list-container');
    if (!container) return;
    container.innerHTML = '';

    const filter = query.toLowerCase().trim();
    const me = usersDB[loggedInUserId] || {};
    const myFriends = me.friends || [];

    const believers = Object.values(usersDB).filter(u => {
        if (u.id === loggedInUserId) return false;
        if (!filter) return true;
        return (
            u.name.toLowerCase().includes(filter) ||
            u.role.toLowerCase().includes(filter) ||
            u.church.toLowerCase().includes(filter)
        );
    });

    if (believers.length === 0) {
        container.innerHTML = '<p style="text-align:center; color:var(--text-muted); grid-column: 1/-1; padding:2rem 0;">No believers found matching your search.</p>';
        return;
    }

    believers.forEach(u => {
        const isFriend = myFriends.includes(u.id);
        const card = document.createElement('div');
        card.className = 'believer-card';
        card.innerHTML = `
            <div class="online-dot"></div>
            <img src="${u.avatar}" class="avatar-lg" alt="${u.name}" onclick="navigateTo('profile', '${u.id}')" style="cursor:pointer;">
            <h4 onclick="navigateTo('profile', '${u.id}')">${u.name}</h4>
            <span class="role-pill">${u.role}</span>
            <span class="church-name"><i class="fa-solid fa-church"></i> ${u.church}</span>
            
            <div class="believer-actions">
                <button class="${isFriend ? 'btn-secondary' : 'btn-primary'}" style="font-size:0.78rem; padding:0.4rem 0.8rem;" onclick="toggleFriendConnection('${u.id}')">
                    <i class="fa-solid ${isFriend ? 'fa-user-check' : 'fa-user-plus'}"></i> ${isFriend ? 'Connected' : 'Connect'}
                </button>
                <button class="btn-secondary" style="font-size:0.78rem; padding:0.4rem 0.8rem;" onclick="navigateTo('chat', '${u.id}')">
                    <i class="fa-solid fa-comments"></i> Chat
                </button>
            </div>
        `;
        container.appendChild(card);
    });
}

function toggleFriendConnection(targetId) {
    const me = usersDB[loggedInUserId];
    const friend = usersDB[targetId];
    if (!me || !friend) return;

    if (!me.friends) me.friends = [];
    if (!friend.friends) friend.friends = [];

    const index = me.friends.indexOf(targetId);
    if (index > -1) {
        me.friends.splice(index, 1);
        const fIdx = friend.friends.indexOf(loggedInUserId);
        if (fIdx > -1) friend.friends.splice(fIdx, 1);
        showToast(`Disconnected fellowship connection.`);
    } else {
        me.friends.push(targetId);
        if (!friend.friends.includes(loggedInUserId)) friend.friends.push(loggedInUserId);
        showToast(`Connected with ${friend.name}! 🎉`);
    }

    syncStorage();
    renderBelieversList(document.getElementById('believers-filter')?.value || '');
    if (currentActiveProfileId) renderProfileView(currentActiveProfileId);
}

// ==========================================================================
// MINISTRIES & WHATSAPP STYLE GROUP CHAT + ADMIN SYSTEM
// ==========================================================================
function renderMinistriesList() {
    const container = document.getElementById('ministries-list-container');
    if (!container) return;
    container.innerHTML = '';

    const me = usersDB[loggedInUserId] || {};
    const myMinistries = me.ministries || [];

    ministriesDB.forEach(m => {
        const isJoined = myMinistries.includes(m.id);
        const isAdmin = m.leaderId === loggedInUserId;
        const leader = usersDB[m.leaderId] || { name: m.leaderName || 'Admin' };

        const card = document.createElement('div');
        card.className = 'ministry-card';
        card.innerHTML = `
            <div>
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.4rem;">
                    <h3>${m.name}</h3>
                    ${isAdmin ? `<span class="admin-tag"><i class="fa-solid fa-crown"></i> You are Admin</span>` : ''}
                </div>
                <div class="ministry-meta">
                    <span><strong>Category:</strong> ${m.category}</span> • 
                    <span><strong>Admin:</strong> ${leader.name}</span> • 
                    <span><strong>Members:</strong> ${(m.members || []).length}</span>
                </div>
                <p class="ministry-desc">${m.bio}</p>
            </div>
            
            <div class="ministry-footer-actions">
                <button class="btn-primary" onclick="openGroupChat('${m.id}')" style="flex:1;">
                    <i class="fa-solid fa-comments"></i> Group Chat
                </button>
                <button class="btn-secondary" onclick="openGroupInfoModal('${m.id}')">
                    <i class="fa-solid fa-users-gear"></i> ${isAdmin ? 'Admin Manage' : 'Members'}
                </button>
                <button class="${isJoined ? 'btn-danger' : 'btn-secondary'}" onclick="toggleJoinMinistry('${m.id}')" title="${isJoined ? 'Leave' : 'Join'}">
                    <i class="fa-solid ${isJoined ? 'fa-right-from-bracket' : 'fa-plus'}"></i>
                </button>
            </div>
        `;
        container.appendChild(card);
    });
}

function toggleJoinMinistry(mId) {
    const me = usersDB[loggedInUserId];
    const ministry = ministriesDB.find(m => m.id === mId);
    if (!me || !ministry) return;

    if (!me.ministries) me.ministries = [];
    if (!ministry.members) ministry.members = [];

    const index = me.ministries.indexOf(mId);
    if (index > -1) {
        if (ministry.leaderId === loggedInUserId) {
            alert("As the Group Admin, you cannot leave your own group. You can manage members or delete the group.");
            return;
        }
        me.ministries.splice(index, 1);
        ministry.members = ministry.members.filter(id => id !== loggedInUserId);
        showToast(`Left ${ministry.name}.`);
    } else {
        me.ministries.push(mId);
        if (!ministry.members.includes(loggedInUserId)) ministry.members.push(loggedInUserId);
        showToast(`Joined ${ministry.name}! Welcome to the group.`);
    }

    syncStorage();
    renderMinistriesList();
    if (currentActiveProfileId === loggedInUserId) renderProfileView(loggedInUserId);
}

function handleCreateMinistry(e) {
    e.preventDefault();
    const name = document.getElementById('ministry-name-input').value.trim();
    const category = document.getElementById('ministry-cat-input').value;
    const bio = document.getElementById('ministry-bio-input').value.trim();
    const me = usersDB[loggedInUserId];

    const newMinistry = {
        id: 'm_' + Date.now(),
        name: name,
        category: category,
        leaderId: loggedInUserId,
        leaderName: me.name,
        bio: bio,
        members: [loggedInUserId],
        messages: [
            { id: 'gm_' + Date.now(), userId: loggedInUserId, text: `Welcome to the ${name} group channel! Post your prayers and fellowship here. 🙏`, time: 'Just now' }
        ]
    };

    ministriesDB.push(newMinistry);
    if (!me.ministries) me.ministries = [];
    me.ministries.push(newMinistry.id);

    syncStorage();
    closeModal('create-ministry-modal');
    document.getElementById('ministry-name-input').value = '';
    document.getElementById('ministry-bio-input').value = '';

    renderMinistriesList();
    openGroupChat(newMinistry.id);
    showToast(`Ministry Group created! You are the Admin with full management authority.`);
}

// --- WHATSAPP STYLE GROUP CHAT CONTROLLER ---
function openGroupChat(groupId) {
    activeGroupId = groupId;
    const group = ministriesDB.find(m => m.id === groupId);
    const me = usersDB[loggedInUserId];

    // Ensure member is added to group if not already
    if (group && !group.members.includes(loggedInUserId)) {
        group.members.push(loggedInUserId);
        if (me && !me.ministries.includes(groupId)) me.ministries.push(groupId);
        syncStorage();
    }

    navigateTo('group-chat', groupId);
}

function renderGroupChat() {
    const group = ministriesDB.find(m => m.id === activeGroupId);
    if (!group) return;

    document.getElementById('group-chat-title').innerText = group.name;
    
    // Member names preview
    const memberNames = (group.members || []).map(mId => {
        if (mId === loggedInUserId) return 'You';
        const u = usersDB[mId];
        return u ? u.name.split(' ')[0] : 'Believer';
    });
    document.getElementById('group-chat-members-preview').innerText = memberNames.join(', ');

    const messagesContainer = document.getElementById('group-messages-container');
    messagesContainer.innerHTML = '';

    if (!group.messages || group.messages.length === 0) {
        group.messages = [
            { id: 'gm_init', userId: group.leaderId, text: `Welcome to ${group.name}! Share prayers, worship moments, and encouraging words. 🙏`, time: 'Today' }
        ];
    }

    group.messages.forEach(msg => {
        const isMe = msg.userId === loggedInUserId;
        const sender = usersDB[msg.userId] || { name: 'Believer', avatar: 'https://i.pravatar.cc/150', role: 'Member' };
        const isGroupAdmin = msg.userId === group.leaderId;

        const row = document.createElement('div');
        row.className = `group-msg-row ${isMe ? 'outgoing' : 'incoming'}`;
        
        row.innerHTML = `
            ${!isMe ? `<img src="${sender.avatar}" class="avatar-sm" alt="${sender.name}" onclick="navigateTo('profile', '${sender.id}')" style="cursor:pointer;">` : ''}
            <div class="group-msg-bubble">
                ${!isMe ? `
                    <div class="group-sender-header">
                        <span class="group-sender-name" onclick="navigateTo('profile', '${sender.id}')">${sender.name}</span>
                        ${isGroupAdmin ? `<span class="admin-badge-tiny"><i class="fa-solid fa-crown"></i> Admin</span>` : `<span class="group-sender-role">${sender.role || 'Member'}</span>`}
                    </div>
                ` : ''}
                ${msg.image ? `<img src="${msg.image}" style="max-width:100%; border-radius:10px; margin-bottom:0.4rem;" alt="Attached image">` : ''}
                <div class="group-msg-text">${msg.text || ''}</div>
                <div class="group-msg-meta">${msg.time || 'Just now'}</div>
            </div>
        `;
        messagesContainer.appendChild(row);
    });

    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function handleSendGroupChat(e) {
    e.preventDefault();
    const input = document.getElementById('group-chat-input-text');
    const text = input.value.trim();
    if (!text || !activeGroupId) return;

    const group = ministriesDB.find(m => m.id === activeGroupId);
    if (!group) return;

    const newMsg = {
        id: 'gm_' + Date.now(),
        userId: loggedInUserId,
        text: text,
        time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
    };

    if (!group.messages) group.messages = [];
    group.messages.push(newMsg);
    syncStorage();
    input.value = '';
    renderGroupChat();

    // Simulated Active Group Members Response (WhatsApp Group feel)
    setTimeout(() => {
        const otherMembers = (group.members || []).filter(mId => mId !== loggedInUserId);
        if (otherMembers.length > 0) {
            const randomMemberId = otherMembers[Math.floor(Math.random() * otherMembers.length)];
            const member = usersDB[randomMemberId];
            if (member) {
                const groupReplies = [
                    "Amen! Agreeing with you in prayer right now! 🙏✨",
                    "Glory to God! Standing in faith with this group.",
                    "Praise the Lord! God is moving powerfully among us.",
                    "Thank you for sharing this prayer point! Lifting it up.",
                    "Hallelujah! May God's peace and strength be with everyone."
                ];
                const replyText = groupReplies[Math.floor(Math.random() * groupReplies.length)];
                group.messages.push({
                    id: 'gm_' + Date.now(),
                    userId: member.id,
                    text: replyText,
                    time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
                });
                syncStorage();
                renderGroupChat();
            }
        }
    }, 1100);
}

function handleSendGroupPhoto(e) {
    const file = e.target.files[0];
    if (file && activeGroupId) {
        const reader = new FileReader();
        reader.onload = function(evt) {
            const group = ministriesDB.find(m => m.id === activeGroupId);
            if (group) {
                if (!group.messages) group.messages = [];
                group.messages.push({
                    id: 'gm_' + Date.now(),
                    userId: loggedInUserId,
                    text: '📷 Shared a photo with the fellowship group',
                    image: evt.target.result,
                    time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
                });
                syncStorage();
                renderGroupChat();
            }
        };
        reader.readAsDataURL(file);
    }
}

function sendGroupPrayerPrompt() {
    const prayer = prompt("Post an urgent prayer request to this group channel:", "Please pray for our family and tonight's revival meeting! 🙏");
    if (prayer && prayer.trim()) {
        const group = ministriesDB.find(m => m.id === activeGroupId);
        if (group) {
            if (!group.messages) group.messages = [];
            group.messages.push({
                id: 'gm_' + Date.now(),
                userId: loggedInUserId,
                text: `🙏 [URGENT PRAYER REQUEST]:\n${prayer.trim()}`,
                time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
            });
            syncStorage();
            renderGroupChat();
            showToast('Prayer request shared with group!');
        }
    }
}

// --- GROUP INFO & ADMIN MANAGEMENT MODAL ---
function openCurrentGroupInfoModal() {
    if (activeGroupId) {
        openGroupInfoModal(activeGroupId);
    }
}

function openGroupInfoModal(groupId) {
    const group = ministriesDB.find(m => m.id === groupId);
    if (!group) return;

    activeGroupId = groupId;
    const isAdmin = group.leaderId === loggedInUserId;

    document.getElementById('modal-group-name').innerText = group.name;
    document.getElementById('modal-group-category').innerText = group.category;
    document.getElementById('modal-group-bio').innerText = group.bio;
    document.getElementById('modal-group-members-count').innerText = (group.members || []).length;

    // ADMIN AUTHORITY PANEL: Populate Add Believer dropdown if logged in user is Admin
    const adminAddSection = document.getElementById('admin-add-member-section');
    const memberSelect = document.getElementById('admin-member-select');

    if (isAdmin) {
        adminAddSection.style.display = 'block';
        memberSelect.innerHTML = '';
        
        // Find believers not in this group
        const eligibleBelievers = Object.values(usersDB).filter(u => !(group.members || []).includes(u.id));
        if (eligibleBelievers.length === 0) {
            memberSelect.innerHTML = '<option disabled selected>All registered believers are in this group</option>';
        } else {
            memberSelect.innerHTML = '<option value="" disabled selected>Select a believer to add...</option>';
            eligibleBelievers.forEach(u => {
                const opt = document.createElement('option');
                opt.value = u.id;
                opt.innerText = `${u.name} (${u.role})`;
                memberSelect.appendChild(opt);
            });
        }
    } else {
        adminAddSection.style.display = 'none';
    }

    // Render Members List with Remove Buttons for Admin
    const membersListContainer = document.getElementById('modal-group-members-list');
    membersListContainer.innerHTML = '';

    (group.members || []).forEach(mId => {
        const u = usersDB[mId] || { id: mId, name: 'Believer', avatar: 'https://i.pravatar.cc/150', role: 'Member' };
        const isThisMemberAdmin = mId === group.leaderId;

        const item = document.createElement('div');
        item.className = 'group-member-item';
        item.innerHTML = `
            <div class="member-left">
                <img src="${u.avatar}" class="avatar-sm" alt="">
                <div class="member-details">
                    <h5>${u.name} ${isThisMemberAdmin ? `<span class="admin-tag"><i class="fa-solid fa-crown"></i> Admin</span>` : ''}</h5>
                    <span>${u.role || 'Member'} • ${u.church || 'Fellowship'}</span>
                </div>
            </div>
            <div>
                ${isAdmin && !isThisMemberAdmin ? `
                    <button class="btn-danger" onclick="handleRemoveGroupMember('${group.id}', '${u.id}')" title="Remove Member">
                        <i class="fa-solid fa-user-minus"></i> Remove
                    </button>
                ` : ''}
            </div>
        `;
        membersListContainer.appendChild(item);
    });

    openModal('group-info-modal');
}

// ADMIN ACTION: Add Member to Group
function handleAddGroupMemberFromModal() {
    const select = document.getElementById('admin-member-select');
    const newMemberId = select.value;
    if (!newMemberId || !activeGroupId) return;

    const group = ministriesDB.find(m => m.id === activeGroupId);
    const newMember = usersDB[newMemberId];
    if (!group || !newMember) return;

    if (!group.members) group.members = [];
    if (!group.members.includes(newMemberId)) {
        group.members.push(newMemberId);
        if (!newMember.ministries) newMember.ministries = [];
        if (!newMember.ministries.includes(group.id)) newMember.ministries.push(group.id);

        // Add system message to group chat
        if (!group.messages) group.messages = [];
        group.messages.push({
            id: 'gm_' + Date.now(),
            userId: loggedInUserId,
            text: `📢 [ADMIN NOTICE]: Welcome ${newMember.name} to the ${group.name} fellowship group! 🎉`,
            time: 'Just now'
        });

        syncStorage();
        openGroupInfoModal(activeGroupId);
        renderGroupChat();
        renderMinistriesList();
        showToast(`${newMember.name} added to the group!`);
    }
}

// ADMIN ACTION: Remove Member from Group
function handleRemoveGroupMember(groupId, memberId) {
    const group = ministriesDB.find(m => m.id === groupId);
    const member = usersDB[memberId];
    if (!group || !member) return;

    if (confirm(`As Group Admin, do you want to remove ${member.name} from this group?`)) {
        group.members = group.members.filter(id => id !== memberId);
        if (member.ministries) {
            member.ministries = member.ministries.filter(id => id !== groupId);
        }

        // Add system message to group chat
        if (!group.messages) group.messages = [];
        group.messages.push({
            id: 'gm_' + Date.now(),
            userId: loggedInUserId,
            text: `ℹ️ [ADMIN UPDATE]: ${member.name} was removed from the group by the Admin.`,
            time: 'Just now'
        });

        syncStorage();
        openGroupInfoModal(groupId);
        renderGroupChat();
        renderMinistriesList();
        showToast(`${member.name} removed from group.`);
    }
}

// ==========================================================================
// DIRECT CHAT (1-on-1 MESSAGING)
// ==========================================================================
function renderChatView() {
    const contactsContainer = document.getElementById('chat-contacts-container');
    if (!contactsContainer) return;
    contactsContainer.innerHTML = '';

    const me = usersDB[loggedInUserId] || {};
    const friends = me.friends || [];

    // Ensure we have an active contact
    if (!activeChatUserId || !usersDB[activeChatUserId] || activeChatUserId === loggedInUserId) {
        activeChatUserId = friends[0] || 'u_sarah';
    }

    friends.forEach(fId => {
        const friend = usersDB[fId];
        if (!friend) return;
        const isActive = fId === activeChatUserId;
        const conv = chatsDB[fId] || [];
        const lastMsg = conv[conv.length - 1] ? (conv[conv.length - 1].text || '📷 Photo') : 'Start fellowship';

        const item = document.createElement('div');
        item.className = `chat-contact-item ${isActive ? 'active' : ''}`;
        item.onclick = () => {
            activeChatUserId = fId;
            renderChatView();
        };
        item.innerHTML = `
            <img src="${friend.avatar}" class="avatar-sm" alt="${friend.name}">
            <div class="contact-meta">
                <h4>${friend.name}</h4>
                <p>${lastMsg.substring(0, 30)}${lastMsg.length > 30 ? '...' : ''}</p>
            </div>
        `;
        contactsContainer.appendChild(item);
    });

    const activeFriend = usersDB[activeChatUserId] || usersDB['u_sarah'];
    document.getElementById('chat-header-avatar').src = activeFriend.avatar;
    document.getElementById('chat-header-name').innerText = activeFriend.name;

    const messagesArea = document.getElementById('chat-messages-body');
    messagesArea.innerHTML = '';

    if (!chatsDB[activeChatUserId]) {
        chatsDB[activeChatUserId] = [{ from: 'them', text: 'Grace and peace to you in Christ Jesus! Let us pray together in faith.', time: 'Today' }];
    }

    chatsDB[activeChatUserId].forEach(msg => {
        const bubble = document.createElement('div');
        bubble.className = `chat-msg ${msg.from === 'me' ? 'outgoing' : 'incoming'}`;
        bubble.innerHTML = `
            ${msg.image ? `<img src="${msg.image}" alt="Chat attachment">` : ''}
            ${msg.text ? `<p>${msg.text}</p>` : ''}
        `;
        messagesArea.appendChild(bubble);
    });

    messagesArea.scrollTop = messagesArea.scrollHeight;
}

function handleSendChat(e) {
    e.preventDefault();
    const input = document.getElementById('chat-input-text');
    const text = input.value.trim();
    if (!text || !activeChatUserId) return;

    if (!chatsDB[activeChatUserId]) chatsDB[activeChatUserId] = [];
    chatsDB[activeChatUserId].push({ from: 'me', text: text });
    syncStorage();
    input.value = '';
    renderChatView();

    // Simulated Fellowship Response
    setTimeout(() => {
        const responses = [
            "Amen! Standing in faith with you! 🙏✨",
            "Praise the Lord! God's timing is always perfect.",
            "May the peace of Christ guard your heart and mind today.",
            "Thank you for sharing, lifting this up in our prayer time! 🕊️",
            "Glory to God! Have a blessed day in His presence."
        ];
        const randomResp = responses[Math.floor(Math.random() * responses.length)];
        chatsDB[activeChatUserId].push({ from: 'them', text: randomResp });
        syncStorage();
        renderChatView();
    }, 900);
}

function handleCreatePost() {
    const textElem = document.getElementById('post-input-text');
    const categoryElem = document.getElementById('post-input-category');
    const typeElem = document.getElementById('post-input-type');

    const text = textElem ? textElem.value.trim() : '';
    const category = categoryElem ? categoryElem.value : 'General';
    const type = typeElem ? typeElem.value : 'prayer';

    if (!text && !selectedPostImageBase64) {
        alert('Please type a message before posting.');
        return;
    }

    // Send post data to backend MySQL database
    fetch('api.php?action=create_post', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ userId: loggedInUserId, type, category, text, image: selectedPostImageBase64 })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            renderFeed(currentFeedFilter);
            showToast('Post published and saved to MySQL!');
        }
    });
}

function sendPrayerAmenDirect() {
    if (!activeChatUserId) return;
    if (!chatsDB[activeChatUserId]) chatsDB[activeChatUserId] = [];
    chatsDB[activeChatUserId].push({ from: 'me', text: '🙏 Sending you blessings, prayers, and fellowship love today!' });
    syncStorage();
    renderChatView();
    showToast('Prayer sent!');
}

function filterChatList(query) {
    const filter = query.toLowerCase();
    document.querySelectorAll('.chat-contact-item').forEach(item => {
        const name = item.querySelector('h4').innerText.toLowerCase();
        item.style.display = name.includes(filter) ? 'flex' : 'none';
    });
}

function viewChatUserProfile() {
    if (activeChatUserId) {
        navigateTo('profile', activeChatUserId);
    }
}

// ==========================================================================
// USER PROFILE VIEW & TABS
// ==========================================================================
function renderProfileView(userId) {
    currentActiveProfileId = userId || loggedInUserId;
    const user = usersDB[currentActiveProfileId] || usersDB[loggedInUserId];
    if (!user) return;

    document.getElementById('profile-avatar').src = user.avatar;
    document.getElementById('profile-role-pill').innerText = user.role || 'Member';
    document.getElementById('profile-name').innerText = user.name;
    document.getElementById('profile-username').innerText = `@${user.username}`;
    document.getElementById('profile-church-name').innerText = user.church || 'Community Fellowship';
    document.getElementById('profile-bio-text').innerText = user.bio || 'Faithful believer walking in Christ.';

    const userPosts = postsDB.filter(p => p.userId === currentActiveProfileId);
    const userFriends = user.friends || [];
    const userMinistries = user.ministries || [];

    document.getElementById('stat-posts-count').innerText = userPosts.length;
    document.getElementById('stat-friends-count').innerText = userFriends.length;
    document.getElementById('stat-ministries-count').innerText = userMinistries.length;

    // Action buttons in profile header
    const actionsArea = document.getElementById('profile-actions-area');
    actionsArea.innerHTML = '';
    if (currentActiveProfileId === loggedInUserId) {
        actionsArea.innerHTML = `<button class="btn-secondary" onclick="openEditProfileModal()"><i class="fa-solid fa-pen"></i> Edit Profile</button>`;
    } else {
        const me = usersDB[loggedInUserId] || {};
        const isFriend = (me.friends || []).includes(currentActiveProfileId);
        actionsArea.innerHTML = `
            <button class="${isFriend ? 'btn-secondary' : 'btn-primary'}" onclick="toggleFriendConnection('${currentActiveProfileId}')">
                <i class="fa-solid ${isFriend ? 'fa-user-check' : 'fa-user-plus'}"></i> ${isFriend ? 'Connected' : 'Connect'}
            </button>
            <button class="btn-primary" onclick="navigateTo('chat', '${currentActiveProfileId}')">
                <i class="fa-solid fa-comments"></i> Message
            </button>
        `;
    }

    // Render User's Posts in Profile
    const postsContainer = document.getElementById('profile-posts-container');
    postsContainer.innerHTML = '';
    if (userPosts.length === 0) {
        postsContainer.innerHTML = `
            <div style="text-align:center; color:var(--text-muted); padding:3rem 1rem; background:var(--bg-surface); border-radius:var(--radius-xl); border:1px solid var(--border-color);">
                <i class="fa-solid fa-signs-post" style="font-size:2.5rem; color:var(--accent-cyan); margin-bottom:0.8rem; display:block;"></i>
                <h3 style="font-size:1.1rem; color:var(--text-primary); margin-bottom:0.3rem;">No prayers or testimonies published yet</h3>
                <p style="font-size:0.88rem;">Share your faith journey with the community!</p>
            </div>
        `;
    } else {
        userPosts.forEach(post => postsContainer.appendChild(createPostCardElement(post)));
    }

    // Render User's Friends in Profile
    const friendsContainer = document.getElementById('profile-friends-container');
    friendsContainer.innerHTML = '';
    if (userFriends.length === 0) {
        friendsContainer.innerHTML = '<p style="text-align:center; color:var(--text-muted); grid-column: 1/-1; padding:2rem 0;">No connections yet.</p>';
    } else {
        userFriends.forEach(fId => {
            const friend = usersDB[fId];
            if (friend) {
                const card = document.createElement('div');
                card.className = 'believer-card';
                card.innerHTML = `
                    <img src="${friend.avatar}" class="avatar-lg" alt="${friend.name}">
                    <h4 onclick="navigateTo('profile', '${friend.id}')">${friend.name}</h4>
                    <span class="role-pill">${friend.role}</span>
                    <span class="church-name">${friend.church}</span>
                    <div class="believer-actions">
                        <button class="btn-primary" style="font-size:0.75rem; padding:0.35rem 0.7rem;" onclick="navigateTo('chat', '${friend.id}')">Chat</button>
                        <button class="btn-secondary" style="font-size:0.75rem; padding:0.35rem 0.7rem;" onclick="navigateTo('profile', '${friend.id}')">Profile</button>
                    </div>
                `;
                friendsContainer.appendChild(card);
            }
        });
    }

    // Render Saved Posts
    const savedContainer = document.getElementById('profile-saved-container');
    savedContainer.innerHTML = '';
    const savedIds = user.savedPosts || [];
    const savedPosts = postsDB.filter(p => savedIds.includes(p.id));

    if (savedPosts.length === 0) {
        savedContainer.innerHTML = '<p style="text-align:center; color:var(--text-muted); padding:2rem 0;">No bookmarked posts yet.</p>';
    } else {
        savedPosts.forEach(post => savedContainer.appendChild(createPostCardElement(post)));
    }

    showProfileTab('posts');
}

function showProfileTab(tabName) {
    document.querySelectorAll('.profile-tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.profile-tab-content').forEach(c => c.classList.remove('active'));

    document.getElementById(`ptab-${tabName}`)?.classList.add('active');
    document.getElementById(`profile-tab-content-${tabName}`)?.classList.add('active');
}

// Edit Profile Modal
function openEditProfileModal() {
    const me = usersDB[loggedInUserId];
    if (!me) return;

    document.getElementById('edit-preview-avatar').src = me.avatar;
    selectedAvatarBase64 = me.avatar;
    document.getElementById('edit-name').value = me.name;
    document.getElementById('edit-role').value = me.role;
    document.getElementById('edit-church').value = me.church;
    document.getElementById('edit-bio').value = me.bio;

    openModal('edit-profile-modal');
}

function handleAvatarFileSelect(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(evt) {
            selectedAvatarBase64 = evt.target.result;
            document.getElementById('edit-preview-avatar').src = selectedAvatarBase64;
        };
        reader.readAsDataURL(file);
    }
// Example: Register function mein fetch API add karna
function handleRegister(e) {
    e.preventDefault();
    clearAuthAlert();

    const name = document.getElementById('reg-name').value.trim();
    const username = document.getElementById('reg-username').value.trim().toLowerCase().replace(/\s+/g, '_');
    const email = document.غeg-email').value.trim().toLowerCase();
    const phone = document.getElementById('reg-phone').value.trim();
    const role = document.getElementById('reg-role').value;
    const church = document.getElementById('reg-church').value.trim();
    const password = document.getElementById('reg-password').value;

    // Backend API call to save user in MySQL via api.php
    fetch('api.php?action=register', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name, username, email, phone, role, church, password })
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            showToast(`Praise God! Account created in MySQL. Welcome, ${name}!`);
            // Session initialization
        } else {
            showAuthAlert(data.message || 'Registration failed.');
        }
    })
    .catch(err => console.error('Database Error:', err));
}

// ==========================================================================
// GLOBAL SEARCH
// ==========================================================================
function handleGlobalSearch(query) {
    const dropdown = document.getElementById('search-results-dropdown');
    const q = query.toLowerCase().trim();
    if (!q) {
        dropdown.style.display = 'none';
        return;
    }

    dropdown.innerHTML = '';

    // Search Believers
    const matchingUsers = Object.values(usersDB).filter(u => u.name.toLowerCase().includes(q) || u.role.toLowerCase().includes(q));
    // Search Posts
    const matchingPosts = postsDB.filter(p => p.text.toLowerCase().includes(q) || p.category.toLowerCase().includes(q));
    // Search Ministries
    const matchingMinistries = ministriesDB.filter(m => m.name.toLowerCase().includes(q));

    if (matchingUsers.length === 0 && matchingPosts.length === 0 && matchingMinistries.length === 0) {
        dropdown.innerHTML = '<div style="padding:0.8rem; text-align:center; color:var(--text-muted); font-size:0.85rem;">No results found.</div>';
        dropdown.style.display = 'block';
        return;
    }

    matchingUsers.forEach(u => {
        const item = document.createElement('div');
        item.className = 'search-item';
        item.onclick = () => {
            dropdown.style.display = 'none';
            document.getElementById('global-search-input').value = '';
            navigateTo('profile', u.id);
        };
        item.innerHTML = `
            <img src="${u.avatar}" class="avatar-sm" alt="">
            <div class="search-item-info">
                <h5>${u.name}</h5>
                <p>Believer • ${u.role}</p>
            </div>
        `;
        dropdown.appendChild(item);
    });

    matchingMinistries.forEach(m => {
        const item = document.createElement('div');
        item.className = 'search-item';
        item.onclick = () => {
            dropdown.style.display = 'none';
            document.getElementById('global-search-input').value = '';
            openGroupChat(m.id);
        };
        item.innerHTML = `
            <i class="fa-solid fa-church" style="font-size:1.2rem; color:var(--accent-cyan);"></i>
            <div class="search-item-info">
                <h5>${m.name}</h5>
                <p>Ministry Group • ${m.category}</p>
            </div>
        `;
        dropdown.appendChild(item);
    });

    matchingPosts.slice(0, 3).forEach(p => {
        const item = document.createElement('div');
        item.className = 'search-item';
        item.onclick = () => {
            dropdown.style.display = 'none';
            document.getElementById('global-search-input').value = '';
            navigateTo('feed');
        };
        item.innerHTML = `
            <i class="fa-solid fa-hands-praying" style="font-size:1.2rem; color:var(--accent-gold);"></i>
            <div class="search-item-info">
                <h5>${p.category}</h5>
                <p>${p.text.substring(0, 40)}...</p>
            </div>
        `;
        dropdown.appendChild(item);
    });

    dropdown.style.display = 'block';
}

document.addEventListener('click', (e) => {
    const searchWrap = document.querySelector('.header-search');
    if (searchWrap && !searchWrap.contains(e.target)) {
        const dropdown = document.getElementById('search-results-dropdown');
        if (dropdown) dropdown.style.display = 'none';
    }
});

// ==========================================================================
// THEME & MODAL UTILITIES
// ==========================================================================
function toggleTheme() {
    const current = document.documentElement.getAttribute('data-theme');
    const next = current === 'light' ? 'dark' : 'light';
    document.documentElement.setAttribute('data-theme', next);
    localStorage.setItem('fc_theme_v6', next);
    const icon = document.getElementById('theme-toggle-btn')?.querySelector('i');
    if (icon) icon.className = next === 'light' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
    showToast(`Switched to ${next} mode.`);
}

function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) modal.classList.add('active');
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) modal.classList.remove('active');
    if (id === 'story-modal' && storyTimer) clearInterval(storyTimer);
}

function resetAppData() {
    if (confirm('Are you sure you want to reset all mock data back to factory defaults?')) {
        localStorage.removeItem('fc_users_v6');
        localStorage.removeItem('fc_posts_v6');
        localStorage.removeItem('fc_ministries_v6');
        localStorage.removeItem('fc_chats_v6');
        localStorage.removeItem('fc_stories_v6');
        localStorage.removeItem('fc_logged_user_v6');
        location.reload();
    }
}

// ==========================================================================
// INITIALIZATION ON DOM READY
// ==========================================================================
document.addEventListener('DOMContentLoaded', () => {
    // Restore Theme
    const savedTheme = localStorage.getItem('fc_theme_v6') || 'dark';
    document.documentElement.setAttribute('data-theme', savedTheme);
    const icon = document.getElementById('theme-toggle-btn')?.querySelector('i');
    if (icon) icon.className = savedTheme === 'light' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';

    // Restore Session or Show Auth Screen
    if (loggedInUserId && usersDB[loggedInUserId]) {
        initAppSession();
    } else {
        document.getElementById('auth-screen').style.display = 'flex';
        document.getElementById('app-wrapper').classList.remove('logged-in');
    }
});
