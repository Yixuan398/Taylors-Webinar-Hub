<?php
/**
 * api/event_delete.php — Delete an event (admin only).
 * Registrations are removed automatically via ON DELETE CASCADE.
 */

require_once __DIR__ . '/../includes/auth.php';

require_admin();

$eventId = (int) ($_POST['event_id'] ?? 0);

if ($eventId > 0) {
    $stmt = db()->prepare('DELETE FROM events WHERE id = ?');
    $stmt->execute([$eventId]);

    flash_set('dashboard_notice', 'Event deleted.');
}

redirect('../Admin-Management/Dashboard.php');
