<?php
/**
 * api/rsvp.php — Join or leave a published event.
 * POST: event_id, action ('join' | 'leave'), csrf_token
 */

require_once __DIR__ . '/../includes/auth.php';

$user = require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../Event-Discovery/Browse_Events.php');
}
require_valid_csrf();

$eventId = filter_var($_POST['event_id'] ?? null, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);
$action = $_POST['action'] ?? '';

if ($eventId === false || !in_array($action, ['join', 'leave'], true)) {
    flash_set('event_notice', 'Invalid registration request.');
    redirect('../Event-Discovery/Browse_Events.php');
}

$pdo = db();
$sqliteTransaction = DB_DRIVER === 'sqlite';

try {
    // SQLite needs an immediate write lock; MySQL locks the event row below.
    if ($sqliteTransaction) {
        $pdo->exec('BEGIN IMMEDIATE TRANSACTION');
    } else {
        $pdo->beginTransaction();
    }

    $eventQuery = 'SELECT * FROM events WHERE id = ? AND status = \'published\'';
    if (DB_DRIVER === 'mysql') {
        $eventQuery .= ' FOR UPDATE';
    }
    $stmt = $pdo->prepare($eventQuery);
    $stmt->execute([$eventId]);
    $event = $stmt->fetch();

    if (!$event) {
        $sqliteTransaction ? $pdo->exec('COMMIT') : $pdo->commit();
        flash_set('event_notice', 'This event is not available.');
        redirect('../Event-Discovery/Browse_Events.php');
    }

    if ($action === 'join') {
        $check = $pdo->prepare(
            'SELECT id FROM event_registrations WHERE user_id = ? AND event_id = ?'
        );
        $check->execute([$user['id'], $eventId]);

        if (!$check->fetch()) {
            $count = $pdo->prepare(
                'SELECT COUNT(*) FROM event_registrations WHERE event_id = ?'
            );
            $count->execute([$eventId]);

            if ((int) $count->fetchColumn() >= (int) $event['capacity']) {
                $sqliteTransaction ? $pdo->exec('COMMIT') : $pdo->commit();
                flash_set('event_notice', 'Sorry, this event is full.');
                redirect('../Event-Discovery/Event_Details.php?id=' . $eventId);
            }

            $insert = $pdo->prepare(
                'INSERT INTO event_registrations (user_id, event_id) VALUES (?, ?)'
            );
            $insert->execute([$user['id'], $eventId]);
        }
        $message = 'You have joined "' . $event['title'] . '".';
    } else {
        $delete = $pdo->prepare(
            'DELETE FROM event_registrations WHERE user_id = ? AND event_id = ?'
        );
        $delete->execute([$user['id'], $eventId]);
        $message = 'You have left "' . $event['title'] . '".';
    }

    $sqliteTransaction ? $pdo->exec('COMMIT') : $pdo->commit();
} catch (Throwable $exception) {
    try {
        if ($sqliteTransaction) {
            $pdo->exec('ROLLBACK');
        } elseif ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
    } catch (Throwable $rollbackException) {
        // Preserve the original database exception for normal error handling.
    }
    throw $exception;
}

flash_set('event_notice', $message);
redirect('../Event-Discovery/Event_Details.php?id=' . $eventId);
