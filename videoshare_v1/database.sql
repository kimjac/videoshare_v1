CREATE DATABASE IF NOT EXISTS videoshare CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE videoshare;

DROP TABLE IF EXISTS comment_votes;
DROP TABLE IF EXISTS comments;
DROP TABLE IF EXISTS videos;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(80) NOT NULL UNIQUE,
    email VARCHAR(180) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    is_admin TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE videos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(100) NOT NULL DEFAULT 'sjov',
    video_path VARCHAR(255) NOT NULL,
    thumbnail VARCHAR(255) DEFAULT NULL,
    likes INT NOT NULL DEFAULT 0,
    dislikes INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_videos_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE comments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    video_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    parent_id INT UNSIGNED NOT NULL DEFAULT 0,
    body TEXT NOT NULL,
    upvotes INT NOT NULL DEFAULT 0,
    downvotes INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_video_parent (video_id, parent_id),
    CONSTRAINT fk_comments_video FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE,
    CONSTRAINT fk_comments_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE comment_votes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    comment_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    vote ENUM('up', 'down') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_comment_vote (comment_id, user_id),
    CONSTRAINT fk_comment_votes_comment FOREIGN KEY (comment_id) REFERENCES comments(id) ON DELETE CASCADE,
    CONSTRAINT fk_comment_votes_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(120) NOT NULL UNIQUE,
    setting_value TEXT NULL
) ENGINE=InnoDB;

INSERT INTO users (username, email, password_hash, is_admin) VALUES
('admin', 'admin@example.com', '$2y$10$Oj1oM7.dAQ4eWKhQxPGwreDNTYelMujkEw0HbnAH3au6uGJm3PLNm', 1);
-- password = password

INSERT INTO settings (setting_key, setting_value) VALUES
('site_name', 'VideoShare'),
('default_language', 'da'),
('default_theme', 'dark');

INSERT INTO videos (user_id, title, description, category, video_path, thumbnail, likes, dislikes) VALUES
(1, 'Velkommen til VideoShare', 'Demo-video post for installation test.', 'sjov', 'videos/sjov/demo.mp4', NULL, 12, 1);

INSERT INTO comments (video_id, user_id, parent_id, body, upvotes, downvotes) VALUES
(1, 1, 0, 'Første kommentar i systemet.', 3, 0),
(1, 1, 1, 'Svar på første kommentar.', 2, 0);
