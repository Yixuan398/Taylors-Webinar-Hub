CREATE DATABASE IF NOT EXISTS taylors_webinar_hub
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE taylors_webinar_hub;

CREATE TABLE IF NOT EXISTS users (
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
    student_id    VARCHAR(30)   DEFAULT NULL,
    faculty       VARCHAR(100)  DEFAULT NULL,
    intake        VARCHAR(50)   DEFAULT NULL,
    programme     VARCHAR(150)  DEFAULT NULL,
    year_of_study VARCHAR(30)   DEFAULT NULL,
    created_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Add academic profile fields to earlier versions of the users table.
DELIMITER $$
DROP PROCEDURE IF EXISTS ensure_academic_profile_columns$$
CREATE PROCEDURE ensure_academic_profile_columns()
BEGIN
    IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'student_id') THEN
        ALTER TABLE users ADD COLUMN student_id VARCHAR(30) DEFAULT NULL;
    END IF;
    IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'faculty') THEN
        ALTER TABLE users ADD COLUMN faculty VARCHAR(100) DEFAULT NULL;
    END IF;
    IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'intake') THEN
        ALTER TABLE users ADD COLUMN intake VARCHAR(50) DEFAULT NULL;
    END IF;
    IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'programme') THEN
        ALTER TABLE users ADD COLUMN programme VARCHAR(150) DEFAULT NULL;
    END IF;
    IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'year_of_study') THEN
        ALTER TABLE users ADD COLUMN year_of_study VARCHAR(30) DEFAULT NULL;
    END IF;
END$$
CALL ensure_academic_profile_columns()$$
DROP PROCEDURE ensure_academic_profile_columns$$
DELIMITER ;

CREATE TABLE IF NOT EXISTS events (
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

CREATE TABLE IF NOT EXISTS event_registrations (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    event_id   INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_event (user_id, event_id),
    FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Seed demo accounts if their email addresses are not already present.
INSERT IGNORE INTO users
    (full_name, email, password_hash, role, first_name, last_name,
     date_of_birth, mobile_number, student_id, faculty, intake, programme, year_of_study)
VALUES
('Hub Administrator', 'admin@taylors.edu.my', '$2b$10$O4a9HH8b3aQ3RkQ04C4YDOeFR9GmBcUT52HUeFA3W8jLzPSYErKVO', 'admin', 'Hub', 'Administrator', NULL, '+60 12-000 0000', NULL, NULL, NULL, NULL, NULL),
('Alex Morgan', 'alex.morgan@sd.taylors.edu.my', '$2b$10$8vRQnvzxHL19OUyPOIWTO.GtBS.IKJ5E.RcKYqtNnVUsZtJntRS2a', 'student', 'Alex', 'Morgan', '2003-05-14', '+60 12-345 6789', '123456', 'School of Computing', 'September 2025', 'Bachelor of Computer Science (Hons)', 'Year 2');

-- Seed each demo event only when the same title/date is not already present.
INSERT INTO events (title, category, description, event_date, start_time, end_time, venue, organiser, capacity, status, created_by)
SELECT 'Build Together: Campus Hackathon', 'Technology', 'A full-day hackathon where student teams prototype solutions for campus life. Mentors from industry will guide you from idea to pitch.', '2026-11-06', '09:00:00', '18:00:00', 'Innovation Lab, Block E', 'Taylor''s Computing Society', 120, 'published', (SELECT id FROM users WHERE email = 'admin@taylors.edu.my' LIMIT 1)
WHERE NOT EXISTS (SELECT 1 FROM events WHERE title = 'Build Together: Campus Hackathon' AND event_date = '2026-11-06');

INSERT INTO events (title, category, description, event_date, start_time, end_time, venue, organiser, capacity, status, created_by)
SELECT 'Lakeside Sessions: Open Mic Night', 'Arts & Culture', 'An evening of live music, poetry and stand-up by Taylor''s students, right by the lakeside. Sign up on arrival to perform.', '2026-11-07', '19:00:00', '22:00:00', 'Lakeside Amphitheatre', 'Taylor''s Arts Society', 200, 'published', (SELECT id FROM users WHERE email = 'admin@taylors.edu.my' LIMIT 1)
WHERE NOT EXISTS (SELECT 1 FROM events WHERE title = 'Lakeside Sessions: Open Mic Night' AND event_date = '2026-11-07');

INSERT INTO events (title, category, description, event_date, start_time, end_time, venue, organiser, capacity, status, created_by)
SELECT 'Grow a Greener Campus', 'Community', 'Join the sustainability club to plant trees and learn how small habits make the campus greener. Tools and gloves provided.', '2026-11-08', '08:30:00', '11:30:00', 'Community Garden, Block B', 'Taylor''s Sustainability Club', 60, 'published', (SELECT id FROM users WHERE email = 'admin@taylors.edu.my' LIMIT 1)
WHERE NOT EXISTS (SELECT 1 FROM events WHERE title = 'Grow a Greener Campus' AND event_date = '2026-11-08');

INSERT INTO events (title, category, description, event_date, start_time, end_time, venue, organiser, capacity, status, created_by)
SELECT 'Sunset Run: Lakeside 5K', 'Sports & Wellness', 'A friendly 5K run around the lake at sunset. All paces welcome — run, jog or walk. Finisher refreshments provided.', '2026-11-11', '17:30:00', '19:00:00', 'Lakeside Track', 'Taylor''s Sports Club', 150, 'published', (SELECT id FROM users WHERE email = 'admin@taylors.edu.my' LIMIT 1)
WHERE NOT EXISTS (SELECT 1 FROM events WHERE title = 'Sunset Run: Lakeside 5K' AND event_date = '2026-11-11');

INSERT INTO events (title, category, description, event_date, start_time, end_time, venue, organiser, capacity, status, created_by)
SELECT 'A Taste of Home: Campus Food Fair', 'Arts & Culture', 'Student clubs serve dishes from their home countries. Come hungry and vote for your favourite stall!', '2026-11-13', '11:00:00', '15:00:00', 'Student Life Centre', 'International Student Council', 300, 'published', (SELECT id FROM users WHERE email = 'admin@taylors.edu.my' LIMIT 1)
WHERE NOT EXISTS (SELECT 1 FROM events WHERE title = 'A Taste of Home: Campus Food Fair' AND event_date = '2026-11-13');

INSERT INTO events (title, category, description, event_date, start_time, end_time, venue, organiser, capacity, status, created_by)
SELECT 'AI in Your Career: Guest Talk', 'Technology', 'Industry guest speakers share how AI is changing graduate careers and which skills matter most in 2027 and beyond.', '2026-11-20', '14:00:00', '16:00:00', 'LT 12, Block C', 'Career Services Centre', 180, 'published', (SELECT id FROM users WHERE email = 'admin@taylors.edu.my' LIMIT 1)
WHERE NOT EXISTS (SELECT 1 FROM events WHERE title = 'AI in Your Career: Guest Talk' AND event_date = '2026-11-20');

INSERT INTO events (title, category, description, event_date, start_time, end_time, venue, organiser, capacity, status, created_by)
SELECT 'Marketing Week: Brand Storytelling', 'Community', 'A hands-on workshop on crafting brand stories, run by alumni working in top agencies.', '2026-11-25', '10:00:00', '13:00:00', 'The Hangout, Block A', 'Taylor''s Business School', 80, 'draft', (SELECT id FROM users WHERE email = 'admin@taylors.edu.my' LIMIT 1)
WHERE NOT EXISTS (SELECT 1 FROM events WHERE title = 'Marketing Week: Brand Storytelling' AND event_date = '2026-11-25');

-- Demo registrations are inserted only if missing and resolved by email/title.
INSERT IGNORE INTO event_registrations (user_id, event_id)
SELECT u.id, e.id
FROM users u JOIN events e ON e.title = 'Build Together: Campus Hackathon' AND e.event_date = '2026-11-06'
WHERE u.email = 'alex.morgan@sd.taylors.edu.my'
UNION ALL
SELECT u.id, e.id
FROM users u JOIN events e ON e.title = 'Grow a Greener Campus' AND e.event_date = '2026-11-08'
WHERE u.email = 'alex.morgan@sd.taylors.edu.my';
