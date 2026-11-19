<?php
// My Events — events the logged-in student has joined.
require_once __DIR__ . '/../includes/auth.php';
$user = require_login();

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$stmt = db()->prepare(
    "SELECT e.*, r.created_at AS joined_at
     FROM event_registrations r
     JOIN events e ON e.id = r.event_id
     WHERE r.user_id = ?
     ORDER BY e.event_date ASC, e.start_time ASC"
);
$stmt->execute([$user['id']]);
$registrations = $stmt->fetchAll();

// Split into upcoming and past relative to today
$today    = date('Y-m-d');
$upcoming = array_values(array_filter($registrations, fn ($ev) => $ev['event_date'] >= $today));
$past     = array_values(array_filter($registrations, fn ($ev) => $ev['event_date'] < $today));

// Calendar data for the page's JS (rendered as JSON below)
$calendarEvents = array_map(fn ($ev) => [
    'date'     => $ev['event_date'],
    'title'    => $ev['title'],
    'time'     => format_event_time($ev['start_time']),
    'category' => category_class($ev['category']),
], $registrations);

function category_class(string $category): string
{
    return match ($category) {
        'Technology'        => 'technology',
        'Arts & Culture'    => 'arts',
        'Community'         => 'community',
        'Sports & Wellness' => 'sports',
        default             => 'community',
    };
}

function render_event_card(array $event): void
{
    ?>
    <article class="event-card">
        <div class="event-card-top">
            <span class="category-tag <?= category_class($event['category']) ?>">
                <?= e($event['category']) ?>
            </span>

            <span class="registered-badge">
                <svg viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"/>
                    <path d="m7 12 3 3 7-7"/>
                </svg>
                Registered
            </span>
        </div>

        <h2><?= e($event['title']) ?></h2>

        <div class="event-meta">
            <div>
                <svg viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.7"
                     stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="5" width="18" height="16" rx="2"/>
                    <path d="M7 3v4M17 3v4M3 10h18"/>
                </svg>
                <span>
                    <?= e(format_event_date($event['event_date'])) ?>
                    &middot; <?= e(format_event_time($event['start_time'])) ?>
                    <?php if ($event['end_time']): ?>
                        &ndash; <?= e(format_event_time($event['end_time'])) ?>
                    <?php endif; ?>
                </span>
            </div>

            <div>
                <svg viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.7"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/>
                    <circle cx="12" cy="10" r="2.5"/>
                </svg>
                <span><?= e($event['venue']) ?></span>
            </div>

            <div>
                <svg viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.7"
                     stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="9" cy="8" r="3"/>
                    <path d="M3 20v-2a6 6 0 0 1 12 0v2"/>
                    <path d="M17 5a3 3 0 0 1 0 6M18 14a5 5 0 0 1 3 5v1"/>
                </svg>
                <span><?= e($event['organiser']) ?></span>
            </div>
        </div>

        <a href="../Event-Discovery/Event_Details.php?id=<?= (int) $event['id'] ?>"
           class="details-button">
            <span>View Details</span>
            <svg viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="1.8"
                 stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 19 19 5"/>
                <path d="M9 5h10v10"/>
            </svg>
        </a>
    </article>
    <?php
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Events | Taylor's Webinar & Guest Speaker Hub</title>
    <link rel="stylesheet" href="../assets/css/My_Events.css">
    <link rel="stylesheet" href="../assets/css/backend.css">
    <!-- Calendar data comes from the database (joined events only) -->
    <script>
        window.CALENDAR_EVENTS = <?= json_encode($calendarEvents, JSON_HEX_TAG | JSON_HEX_APOS) ?>;
    </script>
    <script src="../assets/js/My_Events.js" defer></script>
</head>
<body>

    <!-- Header -->
    <header class="site-header">
        <div class="header-content">
            <a href="../Event-Discovery/Home.php" class="brand">
                <img src="../assets/images/logo.png"
                     alt="Taylor's University Logo"
                     class="brand-logo">

                <div class="brand-text">
                    <span class="brand-name">Taylor’s</span>
                    <span class="brand-subtitle">WEBINAR &amp; GUEST SPEAKER HUB</span>
                </div>
            </a>

            <nav class="header-nav">
                <a href="My_Events.php" class="my-events-link">My Events</a>

                <a href="User_Profile.php" class="user-profile">
                    <span class="user-avatar"><?= htmlspecialchars(strtoupper(implode('', array_map(fn($part) => substr($part, 0, 1), array_slice(preg_split('/\s+/', trim($user['full_name'])), 0, 2))))) ?></span>
                    <span class="user-name"><?= htmlspecialchars($user['full_name']) ?></span>
                    
                    <svg class="chevron-icon" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor"
                         stroke-width="1.7" stroke-linecap="round"
                         stroke-linejoin="round">
                        <path d="m6 9 6 6 6-6"/>
                    </svg>
                </a>

                <form method="post" action="../api/logout.php" class="logout-form">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <button type="submit" class="logout-link">Log out</button>
                </form>
            </nav>
        </div>
    </header>

    <main class="main-content">

        <!-- Events -->
        <section class="events-column">

            <div class="event-tabs">
                <button type="button" class="tab-button active" data-tab="upcoming">
                    Upcoming Events (<?= count($upcoming) ?>)
                </button>

                <button type="button" class="tab-button" data-tab="past">
                    Past Events (<?= count($past) ?>)
                </button>
            </div>

            <div id="upcomingEvents" class="event-list">

                <?php if ($upcoming): ?>
                    <?php foreach ($upcoming as $event) { render_event_card($event); } ?>
                <?php else: ?>
                    <p class="empty-message">No upcoming events. Browse events and join one!</p>
                <?php endif; ?>
            </div>

            <div id="pastEvents" class="event-list" hidden>
                <?php if ($past): ?>
                    <?php foreach ($past as $event) { render_event_card($event); } ?>
                <?php else: ?>
                    <p class="empty-message">No past events to display.</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- Calendar -->
        <aside class="calendar-column">
            <h2 class="calendar-title">Calendar</h2>

            <div class="calendar-card">
                <div class="calendar-controls">
                    <button type="button" id="previousMonth"
                            class="calendar-arrow" aria-label="Previous month">
                        &#8249;
                    </button>

                    <div class="calendar-selects">
                        <select id="calendarMonth" aria-label="Month">
                            <option value="0">Jan</option>
                            <option value="1">Feb</option>
                            <option value="2">Mar</option>
                            <option value="3">Apr</option>
                            <option value="4">May</option>
                            <option value="5">Jun</option>
                            <option value="6">Jul</option>
                            <option value="7">Aug</option>
                            <option value="8">Sep</option>
                            <option value="9">Oct</option>
                            <option value="10" selected>Nov</option>
                            <option value="11">Dec</option>
                        </select>

                        <select id="calendarYear" aria-label="Year">
                            <?php for ($year = (int) date('Y') - 2; $year <= (int) date('Y') + 5; $year++): ?>
                                <option value="<?= $year ?>" <?= $year === (int) date('Y') ? 'selected' : '' ?>><?= $year ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <button type="button" id="nextMonth"
                            class="calendar-arrow" aria-label="Next month">
                        &#8250;
                    </button>
                </div>

                <div class="calendar-weekdays">
                    <span>Su</span>
                    <span>Mo</span>
                    <span>Tu</span>
                    <span>We</span>
                    <span>Th</span>
                    <span>Fr</span>
                    <span>Sa</span>
                </div>

                <div id="calendarDays" class="calendar-days"></div>
            </div>

            <!-- Selected Day; populated from the signed-in user's registrations. -->
            <div class="selected-day-card">
                <h3 id="selectedDayTitle"></h3>
                <div id="selectedDayEvents" class="selected-day-events"></div>
            </div>
        </aside>

    </main>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="footer-content">
            <span>© 2026 Taylor’s Webinar &amp; Guest Speaker Hub</span>

        </div>
    </footer>

</body>
</html>
