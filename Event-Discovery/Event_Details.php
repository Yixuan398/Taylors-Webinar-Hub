<?php
// Event Details — load one published event from the database.
require_once __DIR__ . '/../includes/auth.php';
$user = require_login();

$eventId = (int) ($_GET['id'] ?? 0);

$stmt = db()->prepare(
    "SELECT e.*,
            (SELECT COUNT(*) FROM event_registrations r WHERE r.event_id = e.id) AS joined_count
     FROM events e
     WHERE e.id = ? AND e.status = 'published'"
);
$stmt->execute([$eventId]);
$event = $stmt->fetch();

// Unknown or draft event — send the student back to the list
if (!$event) {
    flash_set('event_notice', 'Event not found.');
    redirect('Browse_Events.php');
}

// Has the current student already joined this event?
$joined = db()->prepare(
    'SELECT id FROM event_registrations WHERE user_id = ? AND event_id = ?'
);
$joined->execute([$user['id'], $eventId]);
$hasJoined = (bool) $joined->fetch();

$joinedCount  = (int) $event['joined_count'];
$capacity     = (int) $event['capacity'];
$placesLeft   = max(0, $capacity - $joinedCount);
$percentFull  = $capacity > 0 ? min(100, round($joinedCount / $capacity * 100)) : 0;
$isFull       = $placesLeft === 0;

// Split the stored description into paragraphs for display
$paragraphs = array_filter(array_map('trim', explode("\n", $event['description'])));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Details | Taylor's Webinar & Guest Speaker Hub</title>
    <link rel="stylesheet" href="../assets/css/Event_Details.css">
    <link rel="stylesheet" href="../assets/css/backend.css">
</head>
<body>

    
        <!-- Header -->
        <header class="site-header">
            <div class="header-content">
                <a href="Home.php" class="brand">
                    <img src="../assets/images/logo.png"
                        alt="Taylor's University Logo"
                        class="brand-logo">

                    <div class="brand-text">
                        <span class="brand-name">Taylor’s</span>
                        <span class="brand-subtitle">
                            WEBINAR &amp; GUEST SPEAKER HUB
                        </span>
                    </div>
                </a>

                <nav class="header-nav">
                    <a href="../Student-Portal/My_Events.php" class="my-events-link">
                        My Events
                    </a>

                    <a href="../Student-Portal/User_Profile.php" class="user-profile">
                        <span class="user-avatar">AL</span>
                        <span class="user-name">Alex Lee</span>

                        <svg class="chevron-icon" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor"
                            stroke-width="1.7" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="m6 9 6 6 6-6"/>
                        </svg>
                    </a>

                    <a href="../api/logout.php" class="logout-link">
                        Log out
                    </a>
                </nav>
            </div>
        </header>

        <main class="main-content">

        <!-- Breadcrumb -->
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="Browse_Events.php" class="back-link">
                <svg viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.7"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 12H5"/>
                    <path d="m12 19-7-7 7-7"/>
                </svg>
                <span>All events</span>
            </a>

            <span class="breadcrumb-divider">/</span>
            <span class="breadcrumb-current">Event details</span>
        </nav>

        <!-- Event Introduction -->
        <section class="event-intro">
            <div class="event-status">
                <span class="event-type"><?= e($event['category']) ?></span>
                <span class="registration-status">
                    <?= $isFull ? 'Event full' : 'Registration open' ?>
                </span>
            </div>

            <?php if ($notice = flash_get('event_notice')): ?>
                <p class="form-message success"><?= e($notice) ?></p>
            <?php endif; ?>

            <h1><?= e($event['title']) ?></h1>

            <div class="organiser">
                <div class="organiser-icon">
                    <svg viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.8"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="m8 7-5 5 5 5"/>
                        <path d="m16 7 5 5-5 5"/>
                        <path d="m14 4-4 16"/>
                    </svg>
                </div>

                <div class="organiser-text">
                    <span>Organised by</span>
                    <strong><?= e($event['organiser']) ?></strong>
                </div>
            </div>
        </section>

        <!-- Event Information -->
        <section class="event-information">
            <div class="information-grid">

                <div class="information-item">
                    <svg viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.7"
                         stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="5" width="18" height="16" rx="2"/>
                        <path d="M7 3v4M17 3v4M3 10h18"/>
                    </svg>

                    <div>
                        <span class="info-label">Date</span>
                        <span class="info-value"><?= e(date('j M Y', strtotime($event['event_date']))) ?></span>
                    </div>
                </div>

                <div class="information-item">
                    <svg viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.7"
                         stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9"/>
                        <path d="M12 7v5l4 2"/>
                    </svg>

                    <div>
                        <span class="info-label">Time</span>
                        <span class="info-value">
                            <?= e(format_event_time($event['start_time'])) ?>
                            <?php if ($event['end_time']): ?>
                                &ndash; <?= e(format_event_time($event['end_time'])) ?>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>

                <div class="information-item">
                    <svg viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.7"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/>
                        <circle cx="12" cy="10" r="2.5"/>
                    </svg>

                    <div>
                        <span class="info-label">Venue</span>
                        <span class="info-value"><?= e($event['venue']) ?></span>
                    </div>
                </div>

                <div class="information-item">
                    <svg viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.7"
                         stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="5" width="18" height="16" rx="2"/>
                        <path d="M7 3v4M17 3v4M3 10h18"/>
                        <circle cx="17" cy="17" r="5" fill="#faf8f5"/>
                        <path d="M17 14v3l2 1"/>
                    </svg>

                    <div>
                        <span class="info-label">Registration deadline</span>
                        <span class="info-value"><?= e(date('j M Y', strtotime($event['event_date'] . ' -4 days'))) ?></span>
                    </div>
                </div>

            </div>

            <!-- Capacity -->
            <div class="capacity-card">
                <div class="capacity-heading">
                    <div class="capacity-left">
                        <svg viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="1.8"
                             stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="9" cy="8" r="3"/>
                            <path d="M3 20v-2a6 6 0 0 1 12 0v2"/>
                            <path d="M17 5a3 3 0 0 1 0 6M18 14a5 5 0 0 1 3 5v1"/>
                        </svg>
                        <strong><?= $placesLeft ?> / <?= $capacity ?> places left</strong>
                    </div>

                    <span class="registered-count"><?= $joinedCount ?> registered</span>
                </div>

                <!-- Join or leave the event (handled by api/rsvp.php) -->
                <form method="post" action="../api/rsvp.php" class="rsvp-form">
                    <input type="hidden" name="event_id" value="<?= (int) $event['id'] ?>">
                    <?php if ($hasJoined): ?>
                        <input type="hidden" name="action" value="leave">
                        <button type="submit" class="details-button rsvp-button leave">
                            Leave this event
                        </button>
                    <?php elseif ($isFull): ?>
                        <button type="button" class="details-button rsvp-button" disabled>
                            Event full
                        </button>
                    <?php else: ?>
                        <input type="hidden" name="action" value="join">
                        <button type="submit" class="details-button rsvp-button">
                            Join this event
                        </button>
                    <?php endif; ?>
                </form>

                <div class="progress-track"
                     role="progressbar"
                     aria-label="Registered places"
                     aria-valuenow="<?= $joinedCount ?>"
                     aria-valuemin="0"
                     aria-valuemax="<?= $capacity ?>">
                    <div class="progress-fill" style="width: <?= $percentFull ?>%"></div>
                </div>
            </div>
        </section>

        <!-- About This Event -->
        <section class="about-section">
            <div class="about-heading">
                <h2>About this event</h2>
                <span>Open to all Taylor’s students</span>
            </div>

            <?php foreach ($paragraphs as $paragraph): ?>
                <p><?= e($paragraph) ?></p>
            <?php endforeach; ?>
        </section>

    </main>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="footer-content">
            <span>© 2026 Taylor’s Webinar &amp; Guest Speaker Hub</span>

            <div class="footer-links">
                <a href="#">Privacy policy</a>
                <a href="#">Terms of use</a>
            </div>
        </div>
    </footer>

</body>
</html>
