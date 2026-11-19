<?php
/**
 * api/event_save.php — Create or update an event (admin only).
 * POST fields match the Add_Edit_Events form.
 */

require_once __DIR__ . '/../includes/auth.php';

$admin = require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../Admin-Management/Dashboard.php');
}
require_valid_csrf();

$eventId     = (int) ($_POST['event_id'] ?? 0);
$title       = trim($_POST['title'] ?? '');
$category    = trim($_POST['category'] ?? '');
$description = trim($_POST['description'] ?? '');
$date        = trim($_POST['date'] ?? '');
$startTime   = trim($_POST['start_time'] ?? '');
$endTime     = trim($_POST['end_time'] ?? '');
$venue       = trim($_POST['venue'] ?? '');
$organiser   = trim($_POST['organiser'] ?? '');
$capacity    = (int) ($_POST['capacity'] ?? 0);
$status      = isset($_POST['publish']) ? 'published' : 'draft';

// ---- Server-side validation ----
$errors = [];
$categories = ['Technology', 'Arts & Culture', 'Community', 'Sports & Wellness'];

if ($title === '' || $category === '' || $description === ''
    || $venue === '' || $organiser === '') {
    $errors[] = 'Please fill in all required fields.';
}

if (!in_array($category, $categories, true)) {
    $errors[] = 'Please choose a valid event category.';
}

if (mb_strlen($title) > 150 || mb_strlen($category) > 50
    || mb_strlen($venue) > 150 || mb_strlen($organiser) > 150) {
    $errors[] = 'One or more event fields exceed the maximum length.';
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)
    || !checkdate((int) substr($date, 5, 2), (int) substr($date, 8, 2), (int) substr($date, 0, 4))) {
    $errors[] = 'Please choose a valid event date.';
}

if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $startTime)) {
    $errors[] = 'Please choose a start time.';
}

if ($endTime !== '' && !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $endTime)) {
    $errors[] = 'Please choose a valid end time.';
} elseif ($endTime !== '' && $startTime !== '' && $endTime <= $startTime) {
    $errors[] = 'The end time must be later than the start time.';
}

if ($capacity < 1) {
    $errors[] = 'Capacity must be at least 1.';
}

if ($errors) {
    flash_set('event_form_error', implode(' ', $errors));
    $back = $eventId > 0
        ? '../Admin-Management/Add_Edit_Events.php?id=' . $eventId
        : '../Admin-Management/Add_Edit_Events.php?mode=create';
    redirect($back);
}

if ($eventId > 0) {
    $existing = db()->prepare('SELECT id FROM events WHERE id = ?');
    $existing->execute([$eventId]);
    if (!$existing->fetchColumn()) {
        flash_set('dashboard_notice', 'Event not found; no changes were saved.');
        redirect('../Admin-Management/Dashboard.php');
    }

    $registrations = db()->prepare('SELECT COUNT(*) FROM event_registrations WHERE event_id = ?');
    $registrations->execute([$eventId]);
    if ($capacity < (int) $registrations->fetchColumn()) {
        flash_set('event_form_error', 'Capacity cannot be lower than the current number of registrations.');
        redirect('../Admin-Management/Add_Edit_Events.php?id=' . $eventId);
    }
}

$endTimeValue = $endTime !== '' ? $endTime : null;

if ($eventId > 0) {
    // ---- Update existing event ----
    $stmt = db()->prepare(
        'UPDATE events
         SET title = ?, category = ?, description = ?, event_date = ?,
             start_time = ?, end_time = ?, venue = ?, organiser = ?,
             capacity = ?, status = ?
         WHERE id = ?'
    );
    $stmt->execute([
        $title, $category, $description, $date,
        $startTime, $endTimeValue, $venue, $organiser,
        $capacity, $status, $eventId,
    ]);

    flash_set('dashboard_notice', 'Event "' . $title . '" updated.');
} else {
    // ---- Create new event ----
    $stmt = db()->prepare(
        'INSERT INTO events
            (title, category, description, event_date, start_time, end_time,
             venue, organiser, capacity, status, created_by)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $title, $category, $description, $date,
        $startTime, $endTimeValue, $venue, $organiser,
        $capacity, $status, $admin['id'],
    ]);

    flash_set('dashboard_notice', 'Event "' . $title . '" created.');
}

redirect('../Admin-Management/Dashboard.php');
