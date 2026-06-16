-- Wedding database schema
-- Run this once to create the required tables

CREATE TABLE IF NOT EXISTS rsvps (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(150) NOT NULL,
    email         VARCHAR(150),
    attending     ENUM('yes', 'no') NOT NULL,
    guest_count   TINYINT UNSIGNED DEFAULT 1,
    dietary_notes TEXT,
    message       TEXT,
    submitted_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS song_requests (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(150),
    song_title   VARCHAR(200) NOT NULL,
    artist       VARCHAR(200),
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
