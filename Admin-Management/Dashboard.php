<?php
// Admin Dashboard — list all events with publish/delete actions.
require_once __DIR__ . '/../includes/auth.php';
$admin = require_admin();

$events = db()->query(
    "SELECT e.*,
            (SELECT COUNT(*) FROM event_registrations r WHERE r.event_id = e.id) AS joined_count
     FROM events e
     ORDER BY e.event_date DESC, e.start_time DESC"
)->fetchAll();

$publishedCount = count(array_filter($events, fn ($ev) => $ev['status'] === 'published'));
$draftCount     = count($events) - $publishedCount;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events | Taylor's Webinar & Guest Speaker Hub</title>

    <link rel="stylesheet" href="../assets/css/Dashboard.css">
    <link rel="stylesheet" href="../assets/css/backend.css">
    <script src="../assets/js/Dashboard.js" defer></script>
</head>

<body>

    <!-- Header -->
    <header class="site-header">
        <div class="header-content">
            <div class="brand">
                <img
                    src="../assets/images/logo.png"
                    alt="Taylor's University Logo"
                    class="brand-logo"
                >

                <div class="brand-text">
                    <span class="brand-name">Taylor’s</span>
                    <span class="brand-subtitle">
                        WEBINAR &amp; GUEST SPEAKER HUB
                    </span>
                </div>
            </div>

            <div class="header-actions">
                <div class="admin-profile">
                    <div class="admin-avatar">ST</div>

                    <div class="admin-info">
                        <span class="admin-name">Sarah Tan</span>
                        <span class="admin-role">Administrator</span>
                    </div>

                    <button
                        type="button"
                        class="profile-dropdown"
                        aria-label="Profile menu"
                        aria-expanded="false"
                    >
                        <svg
                            width="16"
                            height="16"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <path d="m6 9 6 6 6-6"/>
                        </svg>
                    </button>
                </div>

                <a href="../api/logout.php" class="logout-link">
                    Log out
                </a>
            </div>
        </div>
    </header>

    <!-- Main content -->
    <main class="main-content">

        <div class="page-heading">
            <div>
                <div class="breadcrumb">
                    <span>Admin dashboard</span>
                    <span class="breadcrumb-divider">/</span>
                    <span>Events</span>
                </div>

                <h1>Manage Events</h1>
            </div>

            <?php if ($notice = flash_get('dashboard_notice')): ?>
                <p class="form-message success"><?= e($notice) ?></p>
            <?php endif; ?>

            <a href="Add_Edit_Events.php?mode=create" class="create-event-button">
                + Create New Event
            </a>
        </div>

        <!-- Events table -->
        <section class="events-panel">

            <div class="panel-toolbar">
                <div class="event-filters" role="group" aria-label="Filter events">
                    <button
                        type="button"
                        class="filter-button active"
                        data-filter="all"
                        aria-pressed="true"
                    >
                        All events <span id="all-count"><?= count($events) ?></span>
                    </button>

                    <button
                        type="button"
                        class="filter-button"
                        data-filter="published"
                        aria-pressed="false"
                    >
                        Published <span id="published-count"><?= $publishedCount ?></span>
                    </button>

                    <button
                        type="button"
                        class="filter-button"
                        data-filter="draft"
                        aria-pressed="false"
                    >
                        Drafts <span id="draft-count"><?= $draftCount ?></span>
                    </button>
                </div>

                <div class="search-wrapper">
                    <svg
                        width="18"
                        height="18"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.7"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
                        <circle cx="11" cy="11" r="7"/>
                        <path d="m20 20-4-4"/>
                    </svg>

                    <input
                        type="search"
                        id="search-events"
                        placeholder="Search events..."
                        aria-label="Search events"
                    >
                </div>
            </div>

            <div class="table-scroll">
                <table class="events-table">
                    <thead>
                        <tr>
                            <th class="title-column">Title</th>
                            <th class="date-column">Date &amp; Venue</th>
                            <th class="category-column">Category</th>
                            <th class="status-column">Status</th>
                            <th class="actions-column">Actions</th>
                        </tr>
                    </thead>

                    <tbody id="events-body">
                    <?php foreach ($events as $event): ?>
                        <tr data-status="<?= e($event['status']) ?>">
                            <td>
                                <div class="event-title">
                                    <?= e($event['title']) ?>
                                </div>
                                <div class="event-subtitle">
                                    <?= e($event['organiser']) ?> &middot;
                                    <?= (int) $event['joined_count'] ?>/<?= (int) $event['capacity'] ?> joined
                                </div>
                            </td>

                            <td>
                                <div class="event-date">
                                    <?= e(date('d M Y', strtotime($event['event_date']))) ?>
                                    &middot; <?= e(format_event_time($event['start_time'])) ?>
                                </div>
                                <div class="event-venue">
                                    <?= e($event['venue']) ?>
                                </div>
                            </td>

                            <td class="category-text"><?= e($event['category']) ?></td>

                            <td>
                                <span class="status-badge <?= e($event['status']) ?>">
                                    <span class="status-dot"></span>
                                    <?= ucfirst($event['status']) ?>
                                </span>
                            </td>

                            <td>
                                <div class="row-actions">
                                    <a href="Add_Edit_Events.php?id=<?= (int) $event['id'] ?>"
                                       class="action-button">
                                        Edit
                                    </a>

                                    <!-- Publish / unpublish toggles the event status -->
                                    <form method="post" action="../api/event_status.php">
                                        <input type="hidden" name="event_id"
                                               value="<?= (int) $event['id'] ?>">
                                        <input type="hidden" name="status"
                                               value="<?= $event['status'] === 'published' ? 'draft' : 'published' ?>">
                                        <button type="submit" class="action-button status-action">
                                            <?= $event['status'] === 'published' ? 'Unpublish' : 'Publish' ?>
                                        </button>
                                    </form>

                                    <!-- Delete removes the event and its registrations -->
                                    <form method="post" action="../api/event_delete.php"
                                          onsubmit="return confirm('Delete event: <?= e($event['title']) ?>?');">
                                        <input type="hidden" name="event_id"
                                               value="<?= (int) $event['id'] ?>">
                                        <button type="submit"
                                                class="action-button delete-button"
                                                data-action="delete">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$events): ?>
                        <tr>
                            <td colspan="5">No events yet. Create your first event.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                </table>
            </div>

            <div class="panel-footer">
                <span id="events-count">Showing 4 of 4 events</span>
            </div>

        </section>

    </main>

    <!-- Footer -->
    <footer class="site-footer">
        <p>© 2026 Taylor's Webinar &amp; Guest Speaker Hub</p>

        <div class="footer-links">
            <a href="#">Privacy policy</a>
            <a href="#">Terms of use</a>
        </div>
    </footer>

</body>
</html>
