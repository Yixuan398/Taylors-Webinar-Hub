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

if ($title === '' || $category === '' || $description === ''
    || $venue === '' || $organiser === '') {
    $errors[] = 'Please fill in all required fields.';
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    $errors[] = 'Please choose a valid event date.';
}

if ($startTime === '') {
    $errors[] = 'Please choose a start time.';
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
