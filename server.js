const express = require('express');
const mysql = require('mysql2');
const cors = require('cors');
const bcrypt = require('bcryptjs');
const crypto = require('crypto');

const app = express();
const PORT = process.env.PORT || 5000;

app.use(cors());
app.use(express.json({ limit: '50mb' }));

// --- MySQL Connection Pool (Aiven Cloud & SSL Support Included) ---
// NOTE: env var names below match EXACTLY what you set in Render's Environment tab.
// If you rename anything in Render, update it here too (names are case-sensitive).
const dbConfig = {
    host: process.env.Host || process.env.DB_HOST,
    user: process.env.DB_USER,
    password: process.env.Password || process.env.DB_PASSWORD,
    database: process.env.Databasename || process.env.DB_NAME,
    port: process.env.DB_PORT ? Number(process.env.DB_PORT) : 14165,
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0,
    ssl: { rejectUnauthorized: false } // Aiven MySQL SSL Fix
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
                email VARCHAR(255) UNIQUE,
                password VARCHAR(255),
                role VARCHAR(50) DEFAULT 'Church Member',
                church VARCHAR(255) DEFAULT 'Fellowship Community Church',
                avatar TEXT,
                bio TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
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
// AUTH ROUTES (login / register / google_login)
// Frontend (app.js) calls these as:
//   POST /api.php?action=register
//   POST /api.php?action=login
//   POST /api.php?action=google_login
// So we mount matching routes at /api.php with an ?action= query param.
// ==========================================================================

function genId(prefix) {
    return `${prefix}_${crypto.randomBytes(8).toString('hex')}`;
}

app.post('/api.php', (req, res) => {
    const action = req.query.action;
    const data = req.body || {};

    if (action === 'register') {
        const { name, email, password } = data;
        if (!name || !email || !password) {
            return res.json({ success: false, message: 'Incomplete data' });
        }
        db.query('SELECT id FROM users WHERE email = ?', [email], (err, rows) => {
            if (err) return res.json({ success: false, message: 'Database error' });
            if (rows.length > 0) {
                return res.json({ success: false, message: 'Email already registered' });
            }
            const hash = bcrypt.hashSync(password, 10);
            const id = genId('u');
            db.query(
                'INSERT INTO users (id, username, name, email, password) VALUES (?, ?, ?, ?, ?)',
                [id, email.split('@')[0], name, email, hash],
                (insErr) => {
                    if (insErr) return res.json({ success: false, message: 'Error registering user' });
                    res.json({ success: true, message: 'Registration successful! Please login.' });
                }
            );
        });
        return;
    }

    if (action === 'login') {
        const { email, password } = data;
        if (!email || !password) {
            return res.json({ success: false, message: 'Incomplete data' });
        }
        db.query('SELECT * FROM users WHERE email = ?', [email], (err, rows) => {
            if (err) return res.json({ success: false, message: 'Database error' });
            if (rows.length === 0) {
                return res.json({ success: false, message: 'User not found' });
            }
            const user = rows[0];
            if (!user.password || !bcrypt.compareSync(password, user.password)) {
                return res.json({ success: false, message: 'Invalid credentials' });
            }
            res.json({
                success: true,
                message: 'Login successful',
                user: { id: user.id, name: user.name, email: user.email }
            });
        });
        return;
    }

    if (action === 'google_login') {
        const { email, name } = data;
        if (!email || !name) {
            return res.json({ success: false, message: 'Incomplete Google user data' });
        }
        db.query('SELECT * FROM users WHERE email = ?', [email], (err, rows) => {
            if (err) return res.json({ success: false, message: 'Database error' });
            if (rows.length > 0) {
                const user = rows[0];
                return res.json({
                    success: true,
                    message: 'Google Login successful',
                    user: { id: user.id, name: user.name, email: user.email }
                });
            }
            const id = genId('u');
            const dummyHash = bcrypt.hashSync(crypto.randomBytes(16).toString('hex'), 10);
            db.query(
                'INSERT INTO users (id, username, name, email, password) VALUES (?, ?, ?, ?, ?)',
                [id, email.split('@')[0], name, email, dummyHash],
                (insErr) => {
                    if (insErr) return res.json({ success: false, message: 'Error registering Google user in database' });
                    res.json({
                        success: true,
                        message: 'Google Account registered & logged in successfully',
                        user: { id, name, email }
                    });
                }
            );
        });
        return;
    }

    if (action === 'create_post') {
        const { user_id, content, image } = data;
        if (!user_id || !content) {
            return res.json({ success: false, message: 'Invalid post data' });
        }
        const id = genId('p');
        db.query(
            'INSERT INTO posts (id, user_id, type, category, text, image) VALUES (?, ?, ?, ?, ?, ?)',
            [id, user_id, 'prayer', 'General', content, image || null],
            (err) => {
                if (err) return res.json({ success: false, message: 'Error creating post' });
                res.json({ success: true, message: 'Post published successfully!' });
            }
        );
        return;
    }

    res.json({ success: false, message: 'Invalid action' });
});

app.get('/api.php', (req, res) => {
    const action = req.query.action;

    if (action === 'get_posts') {
        db.query(
            `SELECT posts.id, posts.text AS content, posts.created_at, users.name AS user_name
             FROM posts JOIN users ON posts.user_id = users.id
             ORDER BY posts.created_at DESC`,
            (err, rows) => {
                if (err) return res.json({ success: false, message: 'Database error' });
                res.json({ success: true, posts: rows });
            }
        );
        return;
    }

    if (action === 'get_user') {
        const { id } = req.query;
        db.query('SELECT id, name, email, role, church, avatar, bio FROM users WHERE id = ?', [id], (err, rows) => {
            if (err) return res.json({ success: false, message: 'Database error' });
            if (rows.length === 0) return res.json({ success: false, message: 'User not found' });
            res.json({ success: true, user: rows[0] });
        });
        return;
    }

    if (action === 'get_ministries') {
        db.query(
            `SELECT m.*, u.name as leader_name,
             (SELECT COUNT(*) FROM group_members WHERE group_id = m.id) as members_count
             FROM ministry_groups m JOIN users u ON m.leader_id = u.id`,
            (err, rows) => {
                if (err) return res.json({ success: false, message: 'Database error' });
                res.json({ success: true, ministries: rows });
            }
        );
        return;
    }

    res.json({ success: false, message: 'Invalid action' });
});

// ==========================================================================
// EXISTING REST API ROUTES (unchanged)
// ==========================================================================

app.get('/', (req, res) => {
    res.send('🚀 FaithConnection Backend Server Live Hai!');
});

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

app.post('/api/posts', (req, res) => {
    const { id, user_id, type, category, text, image } = req.body;
    const sql = `INSERT INTO posts (id, user_id, type, category, text, image) VALUES (?, ?, ?, ?, ?, ?)`;
    db.query(sql, [id, user_id, type || 'prayer', category || 'General', text, image || null], (err, result) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json({ success: true, message: 'Post published successfully!' });
    });
});

app.put('/api/posts/:id', (req, res) => {
    const { id } = req.params;
    const { type, category, text } = req.body;
    const sql = `UPDATE posts SET type = ?, category = ?, text = ? WHERE id = ?`;
    db.query(sql, [type, category, text, id], (err, result) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json({ success: true, message: 'Post updated successfully!' });
    });
});

app.delete('/api/posts/:id', (req, res) => {
    const { id } = req.params;
    const sql = `DELETE FROM posts WHERE id = ?`;
    db.query(sql, [id], (err, result) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json({ success: true, message: 'Post deleted successfully!' });
    });
});

app.get('/api/users', (req, res) => {
    db.query('SELECT id, username, name, email, role, church, avatar, bio FROM users', (err, results) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json(results);
    });
});

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

app.listen(PORT, () => {
    console.log(`🚀 Server running on port ${PORT}`);
});