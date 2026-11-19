<?php
// Browse Events — list all published events from the database.
require_once __DIR__ . '/../includes/auth.php';
$user = require_login();

$events = db()->query(
    "SELECT e.*,
            (SELECT COUNT(*) FROM event_registrations r WHERE r.event_id = e.id) AS joined_count
     FROM events e
     WHERE e.status = 'published'
     ORDER BY e.event_date ASC, e.start_time ASC"
)->fetchAll();

// Map category names to the CSS modifier used by the design
function category_class(string $category): string
{
    return match ($category) {
        'Technology'         => 'technology',
        'Arts & Culture'     => 'arts',
        'Community'          => 'community',
        'Sports & Wellness'  => 'sports',
        default              => 'community',
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Events | Taylor's Webinar & Guest Speaker Hub</title>
    <link rel="stylesheet" href="../assets/css/Browse_Events.css">
    <link rel="stylesheet" href="../assets/css/backend.css">
    <script src="../assets/js/Browse_Events.js" defer></script>
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
                        <span class="brand-subtitle">WEBINAR &amp; GUEST SPEAKER HUB</span>
                    </div>
                </a>

                <nav class="header-nav">
                    <a href="../Student-Portal/My_Events.php" class="my-events-link">
                        My Events
                    </a>

                    <a href="../Student-Portal/User_Profile.php" class="user-profile">
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

        <!-- Page Introduction -->
        <section class="page-intro">
            <p class="eyebrow">MAKE MORE OF CAMPUS LIFE</p>

            <h1>Browse Events</h1>

            <div class="intro-bottom">
                <p>Find your people. Try something new. There’s a place for you here.</p>

                <div class="campus-location">
                    <svg viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.8"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/>
                        <circle cx="12" cy="10" r="2.5"/>
                    </svg>
                    <span>Taylor’s Lakeside Campus</span>
                </div>
            </div>
        </section>

        <!-- Search and Filters -->
        <section class="search-section">
            <form id="searchForm" class="search-form">

                <div class="search-input-wrapper">
                    <svg viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.7"
                         stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="7"/>
                        <path d="m20 20-4-4"/>
                    </svg>

                    <input
                        type="search"
                        id="searchInput"
                        placeholder="Search events by name or description"
                        aria-label="Search events"
                    >
                </div>

                <select id="categoryFilter" aria-label="Filter by category">
                    <option value="all">All categories</option>
                    <option value="Technology">Technology</option>
                    <option value="Arts & Culture">Arts &amp; Culture</option>
                    <option value="Community">Community</option>
                    <option value="Sports & Wellness">Sports &amp; Wellness</option>
                </select>

                <button type="submit" class="search-button">Search</button>
            </form>
        </section>

        <!-- Events Listing -->
        <section class="events-section">

            <div class="events-heading">
                <div class="heading-left">
                    <h2>Coming up on campus</h2>
                    <span class="events-count" id="eventsCount">5 events</span>
                </div>

                <div class="sort-wrapper">
                    <label for="sortSelect">Sort by:</label>
                    <select id="sortSelect">
                        <option value="soonest">Soonest first</option>
                        <option value="latest">Latest first</option>
                        <option value="az">Name A–Z</option>
                    </select>
                </div>
            </div>

            <div class="events-grid" id="eventsGrid">
                <?php foreach ($events as $event): ?>
                <article class="event-card"
                         data-title="<?= e($event['title']) ?>"
                         data-category="<?= e($event['category']) ?>"
                         data-date="<?= e($event['event_date']) ?>"
                         data-description="<?= e(strtolower($event['description'])) ?>">

                    <span class="category-tag <?= category_class($event['category']) ?>">
                        <?= e($event['category']) ?>
                    </span>

                    <h3><?= e($event['title']) ?></h3>

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
                                &middot;
                                <?= e(format_event_time($event['start_time'])) ?>
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
                            <span><?= e($event['organiser']) ?> &middot; <?= (int) $event['joined_count'] ?>/<?= (int) $event['capacity'] ?> joined</span>
                        </div>
                    </div>

                    <a href="Event_Details.php?id=<?= (int) $event['id'] ?>" class="details-button">
                        <span>View Details</span>
                        <svg viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="1.8"
                             stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 19 19 5"/>
                            <path d="M9 5h10v10"/>
                        </svg>
                    </a>
                </article>
                <?php endforeach; ?>

            </div>

            <p id="noResults" class="no-results" hidden>
                No events found. Try another search or category.
            </p>

        </section>
    </main>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="footer-content">
            <span>© 2026 Taylor’s Webinar &amp; Guest Speaker Hub</span>

        </div>
    </footer>

</body>
</html>
