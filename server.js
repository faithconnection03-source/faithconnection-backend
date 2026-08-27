const express = require('express');
const mysql = require('mysql2');
const cors = require('cors');

const app = express();
const PORT = process.env.PORT || 5000;

app.use(cors());
app.use(express.json({ limit: '50mb' }));

// --- MySQL Connection Pool (Aiven Cloud & SSL Support Included) ---
const dbConfig = process.env.DATABASE_URL || {
    host: process.env.DB_HOST || 'localhost',
    user: process.env.DB_USER || 'root',
    password: process.env.DB_PASSWORD || 'FAITHjejus@9839', 
    database: process.env.DB_NAME || 'faithconnection_db',
    port: process.env.DB_PORT ? Number(process.env.DB_PORT) : 3306,
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0,
    ssl: process.env.DB_HOST ? { rejectUnauthorized: false } : false // Aiven MySQL SSL Fix
};

const db = mysql.createPool(dbConfig);

// Test Connection & Auto Create Tables
db.getConnection((err, connection) => {
    if (err) {
        console.error('❌ Database Connection Failed:', err.message);
    } else {
        console.log('✅ Connected to Database Successfully!');
        
        // Auto-create database tables
        const queries = [
            `CREATE TABLE IF NOT EXISTS users (
                id VARCHAR(255) PRIMARY KEY,
                username VARCHAR(255),
                name VARCHAR(255),
                email VARCHAR(255),
                role VARCHAR(50),
                church VARCHAR(255),
                avatar TEXT,
                bio TEXT
            );`,
            `CREATE TABLE IF NOT EXISTS posts (
                id VARCHAR(255) PRIMARY KEY,
                user_id VARCHAR(255),
                type VARCHAR(50),
                category VARCHAR(100),
                text TEXT,
                image LONGTEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            );`,
            `CREATE TABLE IF NOT EXISTS ministry_groups (
                id VARCHAR(255) PRIMARY KEY,
                name VARCHAR(255),
                leader_id VARCHAR(255),
                FOREIGN KEY (leader_id) REFERENCES users(id) ON DELETE CASCADE
            );`,
            `CREATE TABLE IF NOT EXISTS group_members (
                group_id VARCHAR(255),
                user_id VARCHAR(255),
                PRIMARY KEY (group_id, user_id)
            );`,
            `CREATE TABLE IF NOT EXISTS group_messages (
                id VARCHAR(255) PRIMARY KEY,
                group_id VARCHAR(255),
                user_id VARCHAR(255),
                message TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );`
        ];

        queries.forEach((q) => {
            connection.query(q, (qErr) => {
                if (qErr) console.error('❌ Table Creation Error:', qErr.message);
            });
        });

        console.log('✅ Database tables initialized!');
        connection.release();
    }
});

// ==========================================================================
// API ROUTES
// ==========================================================================

// HOME ROUTE (Testing Ke Liye)
app.get('/', (req, res) => {
    res.send('🚀 FaithConnection Backend Server Live Hai!');
});

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

// 2. CREATE NEW POST
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

// 5. GET ALL USERS
app.get('/api/users', (req, res) => {
    db.query('SELECT id, username, name, email, role, church, avatar, bio FROM users', (err, results) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json(results);
    });
});

// 6. GET ALL MINISTRIES
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
    console.log(`🚀 Server running on port ${PORT}`);
});
