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
