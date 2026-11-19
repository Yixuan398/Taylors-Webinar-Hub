<?php
// Home — personalised landing page with upcoming highlighted events.
require_once __DIR__ . '/../includes/auth.php';
$user = require_login();

// Show the three soonest published events (works on MySQL and SQLite)
$stmt = db()->prepare(
    "SELECT * FROM events
     WHERE status = 'published' AND event_date >= ?
     ORDER BY event_date ASC, start_time ASC
     LIMIT 3"
);
$stmt->execute([date('Y-m-d')]);
$highlighted = $stmt->fetchAll();

// Rotate through the three bundled poster images
$posters = ['event_ai.png', 'event_career.png', 'event_marketing.png'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | Taylor's Webinar & Guest Speaker Hub</title>
    <link rel="stylesheet" href="../assets/css/Home.css">
    <link rel="stylesheet" href="../assets/css/backend.css">
    <script src="../assets/js/Home.js" defer></script>
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
                    <span class="user-avatar"><?= htmlspecialchars(strtoupper(implode('', array_map(fn($part) => substr($part, 0, 1), array_slice(preg_split('/\s+/', trim($user['full_name'])), 0, 2))))) ?></span>
                    <span class="user-name"><?= htmlspecialchars($user['full_name']) ?></span>

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

        <main>

        <!-- Hero Section -->
        <section class="hero-section">
            <div class="hero-content">

                <div class="hero-eyebrow">
                    <span class="eyebrow-line"></span>
                    <span>LEARN. CONNECT. GROW.</span>
                </div>

                <?php if ($notice = flash_get('home_notice')): ?>
                    <p class="form-message success"><?= e($notice) ?></p>
                <?php endif; ?>

                <?php // "Last login" is passed from a cookie via the session (see api/login.php) ?>
                <?php if (!empty($_SESSION['previous_login_at'])): ?>
                    <p class="form-message success">
                        Welcome back, <?= e($user['full_name']) ?>!
                        Your last login was <?= e($_SESSION['previous_login_at']) ?>.
                    </p>
                    <?php unset($_SESSION['previous_login_at']); // show once per login ?>
                <?php endif; ?>


                <h1>Discover Webinars &amp; Guest Talks</h1>

                <p class="hero-description">
                    Explore inspiring webinars, learn from industry experts, and
                    <br class="desktop-break">
                    connect with guest speakers at Taylor’s University.
                </p>

                <a href="Browse_Events.php" class="browse-button">
                    <span>Browse Events</span>
                    <svg viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.8"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 19 19 5"/>
                        <path d="M9 5h10v10"/>
                    </svg>
                </a>

                <div class="campus-location">
                    <svg viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.7"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/>
                        <circle cx="12" cy="10" r="2.5"/>
                    </svg>
                    <span>Taylor’s Lakeside Campus · Subang Jaya, Malaysia</span>
                </div>

            </div>
        </section>

        <!-- Highlighted Events -->
        <section class="highlighted-section">
            <div class="section-container">

                <div class="section-heading">
                    <h2>Highlighted Events</h2>

                    <a href="Browse_Events.php" class="view-all-link">
                        <span>View all events</span>
                        <svg viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="1.8"
                             stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14"/>
                            <path d="m13 6 6 6-6 6"/>
                        </svg>
                    </a>
                </div>

                <div class="events-grid">
            <?php foreach ($highlighted as $i => $event): ?>
            <article class="event-card">
                <div class="event-poster">
                    <img
                        src="../assets/images/<?= $posters[$i % count($posters)] ?>"
                        alt="<?= e($event['title']) ?>"
                        class="poster-image"
                    >

                    <div class="date-badge">
                        <strong><?= date('j', strtotime($event['event_date'])) ?></strong>
                        <span><?= strtoupper(date('M', strtotime($event['event_date']))) ?></span>
                    </div>
                </div>

                <div class="event-info">
                    <span class="category-tag"><?= e($event['category']) ?></span>

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
                                &middot; <?= e(format_event_time($event['start_time'])) ?>
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
                    </div>

                    <a href="Event_Details.php?id=<?= (int) $event['id'] ?>" class="details-button">
                        <span>View Details</span>
                        <svg viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.7"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 19 19 5"/>
                            <path d="M9 5h10v10"/>
                        </svg>
                    </a>
                </div>
            </article>
            <?php endforeach; ?>
    </div>
 </div>
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
