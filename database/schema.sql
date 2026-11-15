-- =====================================================================
-- Taylor's Webinar & Guest Speaker Hub — Database schema + seed data
-- Target: MySQL / MariaDB (XAMPP). Import this file in phpMyAdmin.
-- =====================================================================

CREATE DATABASE IF NOT EXISTS taylors_webinar_hub
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE taylors_webinar_hub;

-- ---------------------------------------------------------------------
-- users: accounts for students and admins
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS event_registrations;
DROP TABLE IF EXISTS events;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name     VARCHAR(100)  NOT NULL,
    email         VARCHAR(150)  NOT NULL UNIQUE,
    password_hash VARCHAR(255)  NOT NULL,
    role          ENUM('student','admin') NOT NULL DEFAULT 'student',
    first_name    VARCHAR(50)   DEFAULT NULL,
    last_name     VARCHAR(50)   DEFAULT NULL,
    date_of_birth DATE          DEFAULT NULL,
    mobile_number VARCHAR(30)   DEFAULT NULL,
    photo         VARCHAR(255)  DEFAULT NULL,
    created_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- events: webinars / guest talks created by admins
-- ---------------------------------------------------------------------
CREATE TABLE events (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(150)  NOT NULL,
    category    VARCHAR(50)   NOT NULL,
    description TEXT          NOT NULL,
    event_date  DATE          NOT NULL,
    start_time  TIME          NOT NULL,
    end_time    TIME          DEFAULT NULL,
    venue       VARCHAR(150)  NOT NULL,
    organiser   VARCHAR(150)  NOT NULL,
    capacity    INT UNSIGNED  NOT NULL DEFAULT 50,
    status      ENUM('published','draft') NOT NULL DEFAULT 'draft',
    image       VARCHAR(255)  DEFAULT NULL,
    created_by  INT UNSIGNED  DEFAULT NULL,
    created_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- event_registrations: which student joined which event
-- ---------------------------------------------------------------------
CREATE TABLE event_registrations (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    event_id   INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_event (user_id, event_id),
    FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Seed users
--   Admin   : admin@taylors.edu.my              / Admin@123
--   Student : alex.morgan@sd.taylors.edu.my     / Student@123
-- (hashes generated with PHP password_hash(), PASSWORD_DEFAULT)
-- ---------------------------------------------------------------------
INSERT INTO users (full_name, email, password_hash, role, first_name, last_name, date_of_birth, mobile_number) VALUES
('Hub Administrator', 'admin@taylors.edu.my', '$2b$10$O4a9HH8b3aQ3RkQ04C4YDOeFR9GmBcUT52HUeFA3W8jLzPSYErKVO', 'admin', 'Hub', 'Administrator', NULL, '+60 12-000 0000'),
('Alex Morgan', 'alex.morgan@sd.taylors.edu.my', '$2b$10$8vRQnvzxHL19OUyPOIWTO.GtBS.IKJ5E.RcKYqtNnVUsZtJntRS2a', 'student', 'Alex', 'Morgan', '2003-05-14', '+60 12-345 6789');

-- ---------------------------------------------------------------------
-- Seed events (matching the front-end demo content)
-- ---------------------------------------------------------------------
INSERT INTO events (title, category, description, event_date, start_time, end_time, venue, organiser, capacity, status, created_by) VALUES
('Build Together: Campus Hackathon', 'Technology',
 'A full-day hackathon where student teams prototype solutions for campus life. Mentors from industry will guide you from idea to pitch.',
 '2026-11-06', '09:00:00', '18:00:00', 'Innovation Lab, Block E', 'Taylor''s Computing Society', 120, 'published', 1),
('Lakeside Sessions: Open Mic Night', 'Arts & Culture',
 'An evening of live music, poetry and stand-up by Taylor''s students, right by the lakeside. Sign up on arrival to perform.',
 '2026-11-07', '19:00:00', '22:00:00', 'Lakeside Amphitheatre', 'Taylor''s Arts Society', 200, 'published', 1),
('Grow a Greener Campus', 'Community',
 'Join the sustainability club to plant trees and learn how small habits make the campus greener. Tools and gloves provided.',
 '2026-11-08', '08:30:00', '11:30:00', 'Community Garden, Block B', 'Taylor''s Sustainability Club', 60, 'published', 1),
('Sunset Run: Lakeside 5K', 'Sports & Wellness',
 'A friendly 5K run around the lake at sunset. All paces welcome — run, jog or walk. Finisher refreshments provided.',
 '2026-11-11', '17:30:00', '19:00:00', 'Lakeside Track', 'Taylor''s Sports Club', 150, 'published', 1),
('A Taste of Home: Campus Food Fair', 'Arts & Culture',
 'Student clubs serve dishes from their home countries. Come hungry and vote for your favourite stall!',
 '2026-11-13', '11:00:00', '15:00:00', 'Student Life Centre', 'International Student Council', 300, 'published', 1),
('AI in Your Career: Guest Talk', 'Technology',
 'Industry guest speakers share how AI is changing graduate careers and which skills matter most in 2027 and beyond.',
 '2026-11-20', '14:00:00', '16:00:00', 'LT 12, Block C', 'Career Services Centre', 180, 'published', 1),
('Marketing Week: Brand Storytelling', 'Community',
 'A hands-on workshop on crafting brand stories, run by alumni working in top agencies.',
 '2026-11-25', '10:00:00', '13:00:00', 'The Hangout, Block A', 'Taylor''s Business School', 80, 'draft', 1);

-- Sample registration so "My Events" has content for the demo student
INSERT INTO event_registrations (user_id, event_id) VALUES (2, 1), (2, 3);
