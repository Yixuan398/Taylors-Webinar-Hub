<?php
/**
 * db.php — Database connection (PDO)
 *
 * Returns a single shared PDO instance. Supports MySQL (XAMPP) and
 * SQLite depending on DB_DRIVER in config.php. All queries elsewhere
 * in the app use prepared statements to prevent SQL injection.
 */

require_once __DIR__ . '/../config/config.php';

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    if (DB_DRIVER === 'sqlite') {
        // Zero-setup file database: create schema automatically on first run.
        $pdo = new PDO('sqlite:' . SQLITE_PATH, null, null, $options);
        $pdo->exec('PRAGMA foreign_keys = ON');
        initialise_sqlite($pdo);
    } else {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT
             . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }

    return $pdo;
}

/**
 * Create the SQLite schema and seed it with demo data if empty.
 * Mirrors database/schema.sql so the app also runs without MySQL.
 */
function initialise_sqlite(PDO $pdo): void
{
    $exists = $pdo->query(
        "SELECT name FROM sqlite_master WHERE type='table' AND name='users'"
    )->fetchColumn();

    if ($exists) {
        $columns = $pdo->query('PRAGMA table_info(users)')->fetchAll();
        $columnNames = array_column($columns, 'name');
        $academicColumns = [
            'student_id' => 'TEXT',
            'faculty' => 'TEXT',
            'intake' => 'TEXT',
            'programme' => 'TEXT',
            'year_of_study' => 'TEXT',
        ];
        foreach ($academicColumns as $name => $type) {
            if (!in_array($name, $columnNames, true)) {
                $pdo->exec("ALTER TABLE users ADD COLUMN {$name} {$type}");
            }
        }
        return; // already initialised
    }

    $pdo->exec("
        CREATE TABLE users (
            id            INTEGER PRIMARY KEY AUTOINCREMENT,
            full_name     TEXT NOT NULL,
            email         TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            role          TEXT NOT NULL DEFAULT 'student',
            first_name    TEXT,
            last_name     TEXT,
            date_of_birth TEXT,
            mobile_number TEXT,
            photo         TEXT,
            student_id    TEXT,
            faculty       TEXT,
            intake        TEXT,
            programme     TEXT,
            year_of_study TEXT,
            created_at    TEXT DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE events (
            id          INTEGER PRIMARY KEY AUTOINCREMENT,
            title       TEXT NOT NULL,
            category    TEXT NOT NULL,
            description TEXT NOT NULL,
            event_date  TEXT NOT NULL,
            start_time  TEXT NOT NULL,
            end_time    TEXT,
            venue       TEXT NOT NULL,
            organiser   TEXT NOT NULL,
            capacity    INTEGER NOT NULL DEFAULT 50,
            status      TEXT NOT NULL DEFAULT 'draft',
            image       TEXT,
            created_by  INTEGER REFERENCES users(id) ON DELETE SET NULL,
            created_at  TEXT DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE event_registrations (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id    INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
            event_id   INTEGER NOT NULL REFERENCES events(id) ON DELETE CASCADE,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            UNIQUE (user_id, event_id)
        );
    ");

    // Seed demo accounts; the seeded admin password must be rotated before deployment.
    $pdo->exec("
        INSERT INTO users (full_name, email, password_hash, role, first_name, last_name, date_of_birth, mobile_number, student_id, faculty, intake, programme, year_of_study) VALUES
        ('Hub Administrator', 'admin@taylors.edu.my', '\$2b\$10\$O4a9HH8b3aQ3RkQ04C4YDOeFR9GmBcUT52HUeFA3W8jLzPSYErKVO', 'admin', 'Hub', 'Administrator', NULL, '+60 12-000 0000', NULL, NULL, NULL, NULL, NULL),
        ('Alex Morgan', 'alex.morgan@sd.taylors.edu.my', '\$2b\$10\$8vRQnvzxHL19OUyPOIWTO.GtBS.IKJ5E.RcKYqtNnVUsZtJntRS2a', 'student', 'Alex', 'Morgan', '2003-05-14', '+60 12-345 6789', '123456', 'School of Computing', 'September 2025', 'Bachelor of Computer Science (Hons)', 'Year 2');
        INSERT INTO events (title, category, description, event_date, start_time, end_time, venue, organiser, capacity, status, created_by) VALUES
        ('Build Together: Campus Hackathon', 'Technology', 'A full-day hackathon where student teams prototype solutions for campus life. Mentors from industry will guide you from idea to pitch.', '2026-11-06', '09:00:00', '18:00:00', 'Innovation Lab, Block E', 'Taylor''s Computing Society', 120, 'published', 1),
        ('Lakeside Sessions: Open Mic Night', 'Arts & Culture', 'An evening of live music, poetry and stand-up by Taylor''s students, right by the lakeside. Sign up on arrival to perform.', '2026-11-07', '19:00:00', '22:00:00', 'Lakeside Amphitheatre', 'Taylor''s Arts Society', 200, 'published', 1),
        ('Grow a Greener Campus', 'Community', 'Join the sustainability club to plant trees and learn how small habits make the campus greener. Tools and gloves provided.', '2026-11-08', '08:30:00', '11:30:00', 'Community Garden, Block B', 'Taylor''s Sustainability Club', 60, 'published', 1),
        ('Sunset Run: Lakeside 5K', 'Sports & Wellness', 'A friendly 5K run around the lake at sunset. All paces welcome — run, jog or walk. Finisher refreshments provided.', '2026-11-11', '17:30:00', '19:00:00', 'Lakeside Track', 'Taylor''s Sports Club', 150, 'published', 1),
        ('A Taste of Home: Campus Food Fair', 'Arts & Culture', 'Student clubs serve dishes from their home countries. Come hungry and vote for your favourite stall!', '2026-11-13', '11:00:00', '15:00:00', 'Student Life Centre', 'International Student Council', 300, 'published', 1),
        ('AI in Your Career: Guest Talk', 'Technology', 'Industry guest speakers share how AI is changing graduate careers and which skills matter most in 2027 and beyond.', '2026-11-20', '14:00:00', '16:00:00', 'LT 12, Block C', 'Career Services Centre', 180, 'published', 1),
        ('Marketing Week: Brand Storytelling', 'Community', 'A hands-on workshop on crafting brand stories, run by alumni working in top agencies.', '2026-11-25', '10:00:00', '13:00:00', 'The Hangout, Block A', 'Taylor''s Business School', 80, 'draft', 1);
        INSERT INTO event_registrations (user_id, event_id) VALUES (2, 1), (2, 3);
    ");
}
