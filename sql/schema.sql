-- ============================================================
-- ServiceConnect Database Schema
-- Emergency Roadside Assistance Platform
-- Run this in phpMyAdmin or via: mysql -u root -p < schema.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS serviceconnect;
USE serviceconnect;

-- ----------------------------------------------------------------
-- Users table — holds drivers, mechanics, and admins (role column)
-- ----------------------------------------------------------------
CREATE TABLE users (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(100)        NOT NULL,
    email           VARCHAR(150)        NOT NULL UNIQUE,
    password        VARCHAR(255)        NOT NULL,
    phone           VARCHAR(20)         NOT NULL,
    role            ENUM('user','mechanic','admin') NOT NULL DEFAULT 'user',
    -- Mechanic-only fields
    garage_name     VARCHAR(150)        DEFAULT NULL,
    specialization  VARCHAR(150)        DEFAULT NULL,
    is_verified     TINYINT(1)          NOT NULL DEFAULT 0,
    avg_rating      DECIMAL(2,1)        DEFAULT NULL,
    rating_count    INT                 NOT NULL DEFAULT 0,
    -- Location (last known, updated on login / dashboard load)
    latitude        DECIMAL(10,7)       DEFAULT NULL,
    longitude       DECIMAL(10,7)       DEFAULT NULL,
    created_at      TIMESTAMP           DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ----------------------------------------------------------------
-- Service requests — the core booking/dispatch entity
-- ----------------------------------------------------------------
CREATE TABLE service_requests (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT             NOT NULL,
    mechanic_id     INT             DEFAULT NULL,
    issue_type      VARCHAR(100)    NOT NULL,
    description     TEXT            DEFAULT NULL,
    vehicle_info    VARCHAR(150)    DEFAULT NULL,
    latitude        DECIMAL(10,7)   NOT NULL,
    longitude       DECIMAL(10,7)   NOT NULL,
    address_text    VARCHAR(255)    DEFAULT NULL,
    status          ENUM('pending','accepted','completed','cancelled') NOT NULL DEFAULT 'pending',
    estimated_cost  DECIMAL(8,2)    DEFAULT NULL,
    created_at      TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    accepted_at     TIMESTAMP       NULL DEFAULT NULL,
    completed_at    TIMESTAMP       NULL DEFAULT NULL,
    FOREIGN KEY (user_id)     REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (mechanic_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ----------------------------------------------------------------
-- Reviews — rating feedback loop (trust & verification, per slide)
-- ----------------------------------------------------------------
CREATE TABLE reviews (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    request_id      INT             NOT NULL,
    user_id         INT             NOT NULL,
    mechanic_id     INT             NOT NULL,
    rating          TINYINT         NOT NULL,   -- 1 to 5
    comment         VARCHAR(500)    DEFAULT NULL,
    created_at      TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id)  REFERENCES service_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)     REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (mechanic_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------------------------------------------
-- Seed data: one admin + a couple of demo mechanics so the app
-- is testable immediately after import.
-- Default password for ALL seeded accounts is: password123
-- (hash below is bcrypt for "password123")
-- ----------------------------------------------------------------
INSERT INTO users (name, email, password, phone, role, is_verified, garage_name, specialization, latitude, longitude)
VALUES
('Admin User', 'admin@serviceconnect.com', '$2y$10$9v0hVpF1Z1s0m2b1Kx1aWOa1s3o1f5p1J8m3q4e5r6t7y8u9i0o1a', '9999999999', 'admin', 1, NULL, NULL, NULL, NULL),
('Ravi Kumar', 'ravi.mechanic@example.com', '$2y$10$9v0hVpF1Z1s0m2b1Kx1aWOa1s3o1f5p1J8m3q4e5r6t7y8u9i0o1a', '9876543210', 'mechanic', 1, 'Kumar Auto Works', 'Engine & Electrical', 28.6139, 77.2090),
('Sanjay Singh', 'sanjay.mechanic@example.com', '$2y$10$9v0hVpF1Z1s0m2b1Kx1aWOa1s3o1f5p1J8m3q4e5r6t7y8u9i0o1a', '9876500000', 'mechanic', 1, 'Singh Garage', 'Tyres & Towing', 28.5355, 77.3910);

-- NOTE: The seeded password hash above is a placeholder. Generate a real
-- one after import by running (in a PHP file, once):
--   echo password_hash('password123', PASSWORD_DEFAULT);
-- and UPDATE users SET password = '<generated hash>' WHERE ...
-- register.php in this project always hashes correctly for NEW signups,
-- so the seed accounts are only a convenience — you can also just
-- register fresh accounts through the UI.
