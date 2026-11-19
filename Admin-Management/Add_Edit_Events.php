<?php
// Add / Edit Event — create a new event or edit an existing one (admin only).
require_once __DIR__ . '/../includes/auth.php';
$admin = require_admin();
$adminInitials = strtoupper(mb_substr($admin['first_name'] ?: $admin['full_name'], 0, 1)
    . mb_substr($admin['last_name'] ?? '', 0, 1));

$eventId = (int) ($_GET['id'] ?? 0);
$event   = null;

// Editing: load the event so the form can be prefilled
if ($eventId > 0) {
    $stmt = db()->prepare('SELECT * FROM events WHERE id = ?');
    $stmt->execute([$eventId]);
    $event = $stmt->fetch();

    if (!$event) {
        flash_set('dashboard_notice', 'Event not found.');
        redirect('Dashboard.php');
    }
}

$categories = ['Technology', 'Arts & Culture', 'Community', 'Sports & Wellness'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add / Edit Events | Taylor's Webinar & Guest Speaker Hub</title>

    <link rel="stylesheet" href="../assets/css/Add_Edit_Events.css">
    <link rel="stylesheet" href="../assets/css/backend.css">
    <script src="../assets/js/Add_Edit_Events.js" defer></script>
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
                    <div class="admin-avatar"><?= e($adminInitials) ?></div>

                    <div class="admin-info">
                        <span class="admin-name"><?= e($admin['full_name']) ?></span>
                        <span class="admin-role">Administrator</span>
                    </div>

                    <span class="profile-chevron" aria-hidden="true">
                        <svg width="16" height="16" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor"
                             stroke-width="1.7" stroke-linecap="round"
                             stroke-linejoin="round">
                            <path d="m6 9 6 6 6-6"/>
                        </svg>
                    </span>
                </div>

                <form method="post" action="../api/logout.php" class="logout-form">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <button type="submit" class="logout-link">Log out</button>
                </form>
            </div>

        </div>
    </header>

    <!-- Main content -->
    <main class="main-content">

        <div class="page-heading">
            <div class="breadcrumb">
                <a href="Dashboard.php">Admin dashboard</a>
                <span>/</span>
                <a href="Dashboard.php">Events</a>
            </div>

            <h1>Add / Edit Events</h1>
        </div>

        <!-- Event form -->
        <section class="event-card">

            <div class="card-header">
                <div>
                    <h2 id="form-heading">Edit event</h2>
                    <p id="form-description">
                        Update the details students will see on your event page.
                    </p>
                </div>

                <span id="status-badge" class="status-badge draft">
                    <span class="status-dot"></span>
                    <span id="status-text">Draft</span>
                </span>
            </div>

            <form id="eventForm" method="post" action="../api/event_save.php">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="event_id" value="<?= $event ? (int) $event['id'] : 0 ?>">

                <?php if ($error = flash_get('event_form_error')): ?>
                    <p class="form-message error"><?= e($error) ?></p>
                <?php endif; ?>

                <div class="form-content">

                    <div class="form-group">
                        <label for="event-title">Event title *</label>
                        <input
                            type="text"
                            id="event-title"
                            name="title"
                            value="<?= e($event['title'] ?? '') ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="event-description">Description *</label>
                        <textarea
                            id="event-description"
                            name="description"
                            rows="3"
                            required
                        ><?= e($event['description'] ?? '') ?></textarea>
                    </div>

                    <div class="form-grid">

                        <div class="form-group">
                            <label for="event-category">Category *</label>
                            <div class="select-wrapper">
                                <select id="event-category" name="category" required>
                                    <option value="">Select category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= e($category) ?>"
                                            <?= ($event['category'] ?? '') === $category ? 'selected' : '' ?>>
                                            <?= e($category) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="event-organiser">Organiser *</label>
                            <input
                                type="text"
                                id="event-organiser"
                                name="organiser"
                                value="<?= e($event['organiser'] ?? '') ?>"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="event-date">Date *</label>
                            <div class="icon-input">
                                <input
                                    type="date"
                                    id="event-date"
                                    name="date"
                                    value="<?= e($event['event_date'] ?? '') ?>"
                                    required
                                >
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="event-time">Time *</label>
                            <div class="icon-input">
                                <input
                                    type="time"
                                    id="event-time"
                                    name="start_time"
                                    value="<?= e(isset($event) ? substr($event['start_time'], 0, 5) : '') ?>"
                                    required
                                >
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="event-end-time">End time</label>
                            <div class="icon-input">
                                <input type="time" id="event-end-time" name="end_time"
                                       value="<?= e(isset($event['end_time']) && $event['end_time'] ? substr($event['end_time'], 0, 5) : '') ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="event-venue">Venue *</label>
                            <input
                                type="text"
                                id="event-venue"
                                name="venue"
                                value="<?= e($event['venue'] ?? '') ?>"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="event-capacity">Capacity *</label>
                            <input
                                type="number"
                                id="event-capacity"
                                name="capacity"
                                value="<?= e($event['capacity'] ?? '') ?>"
                                min="1"
                                required
                            >
                        </div>

                    </div>

                    <!-- Publish toggle -->
                    <div class="publish-section">
                        <label class="publish-switch">
                            <input
                                type="checkbox"
                                id="publish-event"
                                name="publish"
                                value="1"
                                <?= ($event['status'] ?? '') === 'published' ? 'checked' : '' ?>
                            >
                            <span class="switch-slider"></span>
                        </label>

                        <div class="publish-text">
                            <label for="publish-event">Publish event</label>
                            <p id="publish-description">
                                Off — saved as a draft and hidden from students.
                            </p>
                        </div>
                    </div>

                </div>

                <!-- Form footer -->
                <div class="form-footer">
                    <p>* Required fields</p>

                    <div class="form-buttons">
                        <a href="Dashboard.php" class="cancel-button">
                            Cancel
                        </a>

                        <button type="submit" class="save-button" id="save-button">
                            Save changes
                        </button>
                    </div>
                </div>

            </form>

        </section>

    </main>

    <!-- Footer -->
    <footer class="site-footer">
        <p>© 2026 Taylor's Webinar &amp; Guest Speaker Hub</p>

    </footer>

</body>
</html>
