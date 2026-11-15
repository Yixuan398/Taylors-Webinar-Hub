<?php
/**
 * auth.php — Session helpers used by every protected page and API script.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Currently logged-in user row, or null. */
function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    static $user = null;

    if ($user === null) {
        $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch() ?: null;
    }

    return $user;
}

function is_admin(): bool
{
    $user = current_user();
    return $user !== null && $user['role'] === 'admin';
}

/** Redirect to the login page when no session exists. */
function require_login(): array
{
    $user = current_user();

    if ($user === null) {
        redirect('../Authentication/Login.php');
    }

    return $user;
}

/** Only admins may access Admin-Management pages. */
function require_admin(): array
{
    $user = require_login();

    if ($user['role'] !== 'admin') {
        redirect('../Event-Discovery/Home.php');
    }

    return $user;
}
