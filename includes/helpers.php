<?php
/**
 * helpers.php — Small shared utilities.
 */

/** HTML-escape output to prevent XSS. */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/** Redirect helper (relative to the page that calls it). */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/** Store a one-time flash message in the session. */
function flash_set(string $key, string $message): void
{
    $_SESSION['flash'][$key] = $message;
}

/** Read and clear a flash message. */
function flash_get(string $key): ?string
{
    if (empty($_SESSION['flash'][$key])) {
        return null;
    }

    $message = $_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);

    return $message;
}

/** Create a per-session CSRF token for state-changing forms. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/** Stop a state-changing request when its CSRF token is missing or invalid. */
function require_valid_csrf(): void
{
    $submitted = $_POST['csrf_token'] ?? '';
    $known = $_SESSION['csrf_token'] ?? '';

    if (!is_string($submitted) || !is_string($known)
        || $known === '' || !hash_equals($known, $submitted)) {
        http_response_code(403);
        exit('Invalid request token. Please return to the form and try again.');
    }
}

/**
 * Format an event date like "Fri, 6 Nov 2026".
 */
function format_event_date(string $date): string
{
    return date('D, j M Y', strtotime($date));
}

/** Format a TIME value like "9:00 AM". */
function format_event_time(string $time): string
{
    return date('g:i A', strtotime($time));
}
