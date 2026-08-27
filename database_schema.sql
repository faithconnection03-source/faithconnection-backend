-- =====================================================================
-- FAITHCONNECTION - COMPLETE MYSQL DATABASE SCHEMA & INITIAL DATA
-- Database Name: faithconnection_db
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `faithconnection_db`
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `faithconnection_db`;

-- ---------------------------------------------------------------------
-- 1. USERS TABLE
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` VARCHAR(50) NOT NULL PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `phone` VARCHAR(20) DEFAULT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` VARCHAR(100) DEFAULT 'Church Member',
    `church` VARCHAR(150) DEFAULT 'Fellowship Community Church',
    `avatar` TEXT DEFAULT NULL,
    `bio` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 2. POSTS TABLE (Prayer Requests & Praise Testimonies)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `posts` (
    `id` VARCHAR(50) NOT NULL PRIMARY KEY,
    `user_id` VARCHAR(50) NOT NULL,
    `type` ENUM('prayer', 'testimony') NOT NULL DEFAULT 'prayer',
    `category` VARCHAR(100) NOT NULL DEFAULT 'General',
    `text` TEXT NOT NULL,
    `image` LONGTEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 3. POST LIKES (Amen Reactions)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `post_likes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `post_id` VARCHAR(50) NOT NULL,
    `user_id` VARCHAR(50) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_like` (`post_id`, `user_id`),
    FOREIGN KEY (`post_id`) REFERENCES `posts`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 4. PRAYERS SUPPORTED (I Prayed Counter)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `post_prayers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `post_id` VARCHAR(50) NOT NULL,
    `user_id` VARCHAR(50) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_prayer` (`post_id`, `user_id`),
    FOREIGN KEY (`post_id`) REFERENCES `posts`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 5. COMMENTS TABLE
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `comments` (
    `id` VARCHAR(50) NOT NULL PRIMARY KEY,
    `post_id` VARCHAR(50) NOT NULL,
    `user_id` VARCHAR(50) NOT NULL,
    `text` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`post_id`) REFERENCES `posts`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 6. SAVED POSTS (Bookmarks)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `saved_posts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` VARCHAR(50) NOT NULL,
    `post_id` VARCHAR(50) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_saved` (`user_id`, `post_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`post_id`) REFERENCES `posts`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 7. USER CONNECTIONS / FRIENDSHIPS
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `user_connections` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` VARCHAR(50) NOT NULL,
    `friend_id` VARCHAR(50) NOT NULL,
    `status` ENUM('pending', 'connected') DEFAULT 'connected',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_connection` (`user_id`, `friend_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`friend_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 8. MINISTRIES & WHATSAPP-STYLE GROUPS
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ministry_groups` (
    `id` VARCHAR(50) NOT NULL PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `category` VARCHAR(100) NOT NULL,
    `leader_id` VARCHAR(50) NOT NULL,
    `bio` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`leader_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 9. GROUP MEMBERS (With Admin Authority Flag)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `group_members` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `group_id` VARCHAR(50) NOT NULL,
    `user_id` VARCHAR(50) NOT NULL,
    `is_admin` BOOLEAN DEFAULT FALSE,
    `joined_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_group_member` (`group_id`, `user_id`),
    FOREIGN KEY (`group_id`) REFERENCES `ministry_groups`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 10. GROUP MESSAGES (WhatsApp Group Chat)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `group_messages` (
    `id` VARCHAR(50) NOT NULL PRIMARY KEY,
    `group_id` VARCHAR(50) NOT NULL,
    `user_id` VARCHAR(50) NOT NULL,
    `text` TEXT DEFAULT NULL,
    `image` LONGTEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`group_id`) REFERENCES `ministry_groups`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 11. DIRECT 1-ON-1 MESSAGES
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `direct_messages` (
    `id` VARCHAR(50) NOT NULL PRIMARY KEY,
    `sender_id` VARCHAR(50) NOT NULL,
    `receiver_id` VARCHAR(50) NOT NULL,
    `text` TEXT DEFAULT NULL,
    `image` LONGTEXT DEFAULT NULL,
    `is_read` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`sender_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`receiver_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 12. 24-HOUR STATUS STORIES
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `stories` (
    `id` VARCHAR(50) NOT NULL PRIMARY KEY,
    `user_id` VARCHAR(50) NOT NULL,
    `text` TEXT NOT NULL,
    `media` LONGTEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================================
-- INITIAL SEED DATA (Demo Users, Posts, Ministries & Group Chats)
-- =====================================================================

-- Insert Default Users
INSERT INTO `users` (`id`, `username`, `name`, `email`, `phone`, `password`, `role`, `church`, `avatar`, `bio`) VALUES
('u_samuel', 'samuel_k', 'Samuel Kumar', 'samuel@faithconnection.com', '9876543210', '12345', 'Youth Leader / Member', 'Grace Fellowship Assembly', 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=200&q=80', 'Walking by faith, praying for global youth revival and kingdom leadership! 🙏✨'),
('u_sarah', 'sarah_worship', 'Sister Sarah Jenkins', 'sarah@faithconnection.com', '9876543211', '12345', 'Worship Leader / Singer', 'City Praise Tabernacle', 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=200&q=80', 'Singing praises to the Almighty God. Ministering through music and worship arts. 🎶🕊️'),
('u_timothy', 'timothy_prayer', 'Brother Timothy Vance', 'timothy@faithconnection.com', '9876543212', '12345', 'Prayer Warrior / Intercessor', 'Hope & Life Baptist Church', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=200&q=80', 'Standing in the spiritual gap through unceasing prayer, intercession and scripture meditation.'),
('u_hannah', 'hannah_grace', 'Hannah Grace Miller', 'hannah@faithconnection.com', '9876543213', '12345', 'Sunday School Teacher', 'Calvary Community Chapel', 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=200&q=80', 'Nurturing young hearts in biblical truth and God’s everlasting love. 📖🌱'),
('u_david', 'pastor_david', 'Pastor David Thompson', 'david@faithconnection.com', '9876543214', '12345', 'Pastor / Church Leader', 'New Life International Church', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=200&q=80', 'Serving God’s flock with pastoral care, biblical preaching, and community outreach. ✝️')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- Insert Sample Posts (Prayer Requests & Testimonies)
INSERT INTO `posts` (`id`, `user_id`, `type`, `category`, `text`, `image`) VALUES
('p_samuel_1', 'u_samuel', 'prayer', 'Youth & Education', 'Praying for all the college students and youth preparing for exams and spiritual growth this season. May God grant divine wisdom, sharp focus, and unshakeable peace! 🙏✨', 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=800&q=80'),
('p_1', 'u_sarah', 'prayer', 'Healing & Health', 'Dear brothers and sisters, please lift up my mother in prayer. She was admitted for surgery tomorrow morning. We are standing firmly on God\'s promises of total healing and peace! 🙏🕊️', 'https://images.unsplash.com/photo-1507692049790-de58290a4334?auto=format&fit=crop&w=800&q=80'),
('p_samuel_2', 'u_samuel', 'testimony', 'Answered Prayer', 'Praise the Lord! Our youth fellowship outreach yesterday reached over 50 souls with the Gospel message. Thank you everyone for your unceasing prayers and support! ⭐🙌', NULL),
('p_2', 'u_timothy', 'testimony', 'Answered Prayer', 'Hallelujah! Praise the Lord! After 6 months of prayer and searching, my younger brother was blessed with a job as a senior engineer today! Thank you to all who prayed with us. God is faithful! ⭐🙌', 'https://images.unsplash.com/photo-1499209974431-9dddcece7f88?auto=format&fit=crop&w=800&q=80'),
('p_3', 'u_hannah', 'prayer', 'Youth & Education', 'Asking for special prayers for our upcoming Sunday School VBS camp next week. Praying for open hearts, joyful spirits, and safety for all the children attending! 🎨📖', NULL)
ON DUPLICATE KEY UPDATE `text`=VALUES(`text`);

-- Insert Sample Post Likes
INSERT IGNORE INTO `post_likes` (`post_id`, `user_id`) VALUES
('p_samuel_1', 'u_sarah'),
('p_samuel_1', 'u_timothy'),
('p_samuel_1', 'u_samuel'),
('p_1', 'u_samuel'),
('p_1', 'u_timothy'),
('p_samuel_2', 'u_sarah'),
('p_samuel_2', 'u_david'),
('p_2', 'u_samuel');

-- Insert Sample Prayers Supported
INSERT IGNORE INTO `post_prayers` (`post_id`, `user_id`) VALUES
('p_samuel_1', 'u_sarah'),
('p_samuel_1', 'u_timothy'),
('p_1', 'u_samuel'),
('p_1', 'u_timothy'),
('p_3', 'u_samuel');

-- Insert Sample Comments
INSERT INTO `comments` (`id`, `post_id`, `user_id`, `text`) VALUES
('c_1', 'p_1', 'u_timothy', 'Amen Sister Sarah! Praying Psalm 103:3 over her right now.'),
('c_2', 'p_1', 'u_samuel', 'Standing in agreement with you in faith!'),
('c_3', 'p_2', 'u_david', 'Praise God for His wondrous provision! Rejoicing with your family.')
ON DUPLICATE KEY UPDATE `text`=VALUES(`text`);

-- Insert Ministry Groups
INSERT INTO `ministry_groups` (`id`, `name`, `category`, `leader_id`, `bio`) VALUES
('m_1', 'Youth Praise & Worship Network', 'Worship & Creative Arts', 'u_sarah', 'Connecting young worshipers, musicians, and audio engineers for unified praise across congregations.'),
('m_2', 'NextGen Bible & Mentorship Circle', 'Youth & Young Adults', 'u_samuel', 'Equipping teenagers and young adults with solid biblical foundations and weekly virtual devotionals.'),
('m_3', 'Global Intercessory Watchmen', 'Prayer & Intercession', 'u_timothy', '24/7 prayer chain standing in faith for community revival, missionaries, and global needs.')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- Insert Group Members
INSERT IGNORE INTO `group_members` (`group_id`, `user_id`, `is_admin`) VALUES
('m_1', 'u_sarah', TRUE),
('m_1', 'u_samuel', FALSE),
('m_1', 'u_timothy', FALSE),
('m_1', 'u_hannah', FALSE),
('m_2', 'u_samuel', TRUE),
('m_2', 'u_hannah', FALSE),
('m_2', 'u_sarah', FALSE),
('m_3', 'u_timothy', TRUE),
('m_3', 'u_david', FALSE),
('m_3', 'u_samuel', FALSE);

-- Insert Group Messages (WhatsApp Group Chat)
INSERT INTO `group_messages` (`id`, `group_id`, `user_id`, `text`) VALUES
('gm_1', 'm_1', 'u_sarah', 'Welcome everyone to the Youth Praise & Worship Network WhatsApp group! Let us share our worship setlists, audio clips, and prayer requests here. 🎶🕊️'),
('gm_2', 'm_1', 'u_samuel', 'Praise the Lord Sister Sarah! Ready for Friday worship practice. 🙏'),
('gm_3', 'm_1', 'u_timothy', 'Amen! Standing in prayer for the entire worship team.'),
('gm_4', 'm_2', 'u_samuel', 'Welcome to NextGen Mentorship group! Weekly devotional topic: "Building unshakeable faith in college/school". 📖')
ON DUPLICATE KEY UPDATE `text`=VALUES(`text`);
