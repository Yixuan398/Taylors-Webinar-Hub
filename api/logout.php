<?php
/**
 * api/logout.php — End the session and return to the login page.
 */

require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../Authentication/Login.php');
}
require_valid_csrf();

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    unset($params['lifetime']);
    $params['expires'] = time() - 42000;
    setcookie(session_name(), '', $params);
}

session_destroy();

// Start a fresh session just for the flash message
session_start();
flash_set('login_notice', 'You have been logged out.');

redirect('../Authentication/Login.php');
