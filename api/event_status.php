<?php
/**
 * api/event_status.php — Publish or unpublish an event (admin only).
 * POST: event_id, status ('published' | 'draft')
 */

require_once __DIR__ . '/../includes/auth.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../Admin-Management/Dashboard.php');
}
require_valid_csrf();

$eventId = (int) ($_POST['event_id'] ?? 0);
$status  = ($_POST['status'] ?? '') === 'published' ? 'published' : 'draft';

if ($eventId > 0) {
    $stmt = db()->prepare('UPDATE events SET status = ? WHERE id = ?');
    $stmt->execute([$status, $eventId]);

    flash_set(
        'dashboard_notice',
        $status === 'published' ? 'Event published.' : 'Event moved to drafts.'
    );
}

redirect('../Admin-Management/Dashboard.php');
