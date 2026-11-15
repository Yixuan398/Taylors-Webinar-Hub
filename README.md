# Taylor's Webinar & Guest Speaker Hub — Full Stack (HTML/CSS/JS + PHP + MySQL)

An event management web application for Taylor's University. Students can register,
log in, browse and join events; admins can create, edit, publish/unpublish and
delete events. Built with HTML, CSS, JavaScript, PHP (PDO) and MySQL/SQLite.

## Demo accounts

| Role    | Email                          | Password    |
|---------|--------------------------------|-------------|
| Admin   | admin@taylors.edu.my           | Admin@123   |
| Student | alex.morgan@sd.taylors.edu.my  | Student@123 |

The login page also has an **Admin** one-click button that signs in the demo
admin account.

## Setup with XAMPP (MySQL — recommended for the demo)

1. Copy the `Taylors-Webinar` folder into `xampp/htdocs/`.
2. Start **Apache** and **MySQL** from the XAMPP control panel.
3. Open http://localhost/phpmyadmin → **Import** → choose
   `database/schema.sql` → Go. This creates the `taylors_webinar_hub`
   database with seed users and events.
4. Check `config/config.php` — the defaults (`root`, empty password,
   database `taylors_webinar_hub`) match a standard XAMPP install.
5. Open http://localhost/Taylors-Webinar/Authentication/Login.php

## Zero-setup alternative (SQLite)

1. In `config/config.php`, change `define('DB_DRIVER', 'mysql')` to `'sqlite'`.
2. Run with any PHP 8 server, e.g. `php -S localhost:8000` from this folder.
3. The database file and seed data are created automatically on first run
   (`database/taylors_webinar_hub.sqlite`).

## Project structure

```
Taylors-Webinar/
├── config/config.php        # DB driver & credentials
├── includes/
│   ├── db.php               # Shared PDO connection (prepared statements only)
│   ├── auth.php             # Session helpers, login/admin guards
│   └── helpers.php          # XSS escaping, flash messages, formatting
├── api/                     # Form handlers (POST → validate → DB → redirect)
│   ├── signup.php  login.php  logout.php
│   ├── rsvp.php             # join / leave an event (capacity checked)
│   ├── event_save.php       # create / update event (admin)
│   ├── event_delete.php     # delete event (admin)
│   ├── event_status.php     # publish / unpublish (admin)
│   ├── profile_update.php   # save profile details
│   └── password_change.php  # verifies current password first
├── Authentication/          # Login.php, Signup.php
├── Event-Discovery/         # Home, Browse_Events, Event_Details (DB-driven)
├── Student-Portal/          # My_Events (incl. live calendar), User_Profile
├── Admin-Management/        # Dashboard, Add_Edit_Events (admin only)
├── assets/                  # css, js, images (front-end)
└── database/schema.sql      # MySQL schema + seed data
```

## Security notes (good to mention in the report)

- Passwords are hashed with `password_hash()` and verified with
  `password_verify()` — never stored in plain text.
- All database access uses **PDO prepared statements** (SQL-injection safe).
- All output is escaped with `htmlspecialchars()` via the `e()` helper (XSS safe).
- `session_regenerate_id(true)` on login prevents session fixation.
- Role-based access control: `require_login()` / `require_admin()` guards.
- Server-side validation mirrors the client-side validation on every form.
