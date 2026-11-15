<?php
/**
 * api/rsvp.php — Join or leave an event (student side).
 * POST: event_id, action ('join' | 'leave')
 */

require_once __DIR__ . '/../includes/auth.php';

$user    = require_login();
$eventId = (int) ($_POST['event_id'] ?? 0);
$action  = $_POST['action'] ?? 'join';

// Make sure the event exists and is published
$stmt = db()->prepare("SELECT * FROM events WHERE id = ? AND status = 'published'");
$stmt->execute([$eventId]);
$event = $stmt->fetch();

if (!$event) {
    flash_set('event_notice', 'This event is not available.');
    redirect('../Event-Discovery/Browse_Events.php');
}

if ($action === 'join') {
    // Capacity check: do not allow over-booking
    $count = db()->prepare('SELECT COUNT(*) FROM event_registrations WHERE event_id = ?');
    $count->execute([$eventId]);

    if ((int) $count->fetchColumn() >= (int) $event['capacity']) {
        flash_set('event_notice', 'Sorry, this event is full.');
        redirect('../Event-Discovery/Event_Details.php?id=' . $eventId);
    }

    // UNIQUE(user_id, event_id) prevents double registration
    $check = db()->prepare(
        'SELECT id FROM event_registrations WHERE user_id = ? AND event_id = ?'
    );
    $check->execute([$user['id'], $eventId]);

    if (!$check->fetch()) {
        $stmt = db()->prepare(
            'INSERT INTO event_registrations (user_id, event_id) VALUES (?, ?)'
        );
        $stmt->execute([$user['id'], $eventId]);
    }

    flash_set('event_notice', 'You have joined "' . $event['title'] . '".');
} else {
    $stmt = db()->prepare(
        'DELETE FROM event_registrations WHERE user_id = ? AND event_id = ?'
    );
    $stmt->execute([$user['id'], $eventId]);

    flash_set('event_notice', 'You have left "' . $event['title'] . '".');
}

redirect('../Event-Discovery/Event_Details.php?id=' . $eventId);
