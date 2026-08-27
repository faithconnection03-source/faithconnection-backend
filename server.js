/* ==========================================================================
   FAITHCONNECTION - MYSQL BACKEND SERVER (NODE.JS + EXPRESS + MYSQL2)
   ========================================================================== */

const express = require('express');
const mysql = require('mysql2');
const cors = require('cors');

const app = express();
const PORT = process.env.PORT || 5000;

app.use(cors());
app.use(express.json({ limit: '50mb' }));

// --- MySQL Connection Pool ---
const db = mysql.createPool({
    host: 'localhost',
    user: 'root',
    password: 'FAITHjejus@9839', 
    database: 'faithconnection_db',
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
});

// Test Connection
db.getConnection((err, connection) => {
    if (err) {
        console.error('❌ MySQL Connection Failed:', err.message);
    } else {
        console.log('✅ Connected to MySQL Database: faithconnection_db');
        connection.release();
    }
});

// ==========================================================================
// API ROUTES
// ==========================================================================

// 1. GET ALL POSTS
app.get('/api/posts', (req, res) => {
    const sql = `
        SELECT p.*, u.name as author_name, u.avatar as author_avatar, u.role as author_role
        FROM posts p
        JOIN users u ON p.user_id = u.id
        ORDER BY p.created_at DESC
    `;
    db.query(sql, (err, results) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json(results);
    });
});

// 2. CREATE NEW POST (Prayer or Testimony)
app.post('/api/posts', (req, res) => {
    const { id, user_id, type, category, text, image } = req.body;
    const sql = `INSERT INTO posts (id, user_id, type, category, text, image) VALUES (?, ?, ?, ?, ?, ?)`;
    db.query(sql, [id, user_id, type || 'prayer', category || 'General', text, image || null], (err, result) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json({ success: true, message: 'Post published successfully!' });
    });
});

// 3. EDIT POST
app.put('/api/posts/:id', (req, res) => {
    const { id } = req.params;
    const { type, category, text } = req.body;
    const sql = `UPDATE posts SET type = ?, category = ?, text = ? WHERE id = ?`;
    db.query(sql, [type, category, text, id], (err, result) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json({ success: true, message: 'Post updated successfully!' });
    });
});

// 4. DELETE POST
app.delete('/api/posts/:id', (req, res) => {
    const { id } = req.params;
    const sql = `DELETE FROM posts WHERE id = ?`;
    db.query(sql, [id], (err, result) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json({ success: true, message: 'Post deleted successfully!' });
    });
});

// 5. GET ALL USERS / BELIEVERS
app.get('/api/users', (req, res) => {
    db.query('SELECT id, username, name, email, role, church, avatar, bio FROM users', (err, results) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json(results);
    });
});

// 6. GET ALL MINISTRIES / GROUPS
app.get('/api/ministries', (req, res) => {
    const sql = `
        SELECT m.*, u.name as leader_name,
        (SELECT COUNT(*) FROM group_members WHERE group_id = m.id) as members_count
        FROM ministry_groups m
        JOIN users u ON m.leader_id = u.id
    `;
    db.query(sql, (err, results) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json(results);
    });
});

// 7. GET GROUP CHAT MESSAGES
app.get('/api/ministries/:id/messages', (req, res) => {
    const { id } = req.params;
    const sql = `
        SELECT gm.*, u.name as sender_name, u.avatar as sender_avatar, u.role as sender_role
        FROM group_messages gm
        JOIN users u ON gm.user_id = u.id
        WHERE gm.group_id = ?
        ORDER BY gm.created_at ASC
    `;
    db.query(sql, [id], (err, results) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json(results);
    });
});

// Start Server
app.listen(PORT, () => {
    console.log(`🚀 FaithConnection Backend running at http://localhost:${PORT}`);
});
