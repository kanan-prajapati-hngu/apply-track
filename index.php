
<?php
require_once "config/database.php";

// Fetch dashboard statistics
$statsQuery = "
    SELECT
        COUNT(*) AS total,
        SUM(status_id = 2) AS applied,
        SUM(status_id = 3) AS assessment,
        SUM(status_id = 4) AS interview,
        SUM(status_id = 5) AS offers,
        SUM(status_id = 6) AS rejected
    FROM applications
";

$stats = $pdo->query($statsQuery)->fetch(PDO::FETCH_ASSOC);

// Fetch recent applications
$recentQuery = "
    SELECT
        a.*,
        s.status_name
    FROM applications a
    JOIN application_statuses s
        ON a.status_id = s.id
    ORDER BY a.created_at DESC
    LIMIT 5
";

$recentApplications = $pdo->query($recentQuery)->fetchAll(PDO::FETCH_ASSOC);

// Prevent HTML injection
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ApplyTrack | Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<div class="app-container">

    <aside class="sidebar">
        <div class="brand">
            <span class="brand-icon">A</span>
            <span>ApplyTrack</span>
        </div>

        <p class="menu-label">MENU</p>

        <a href="index.php" class="nav-link active">▦ Dashboard</a>
        <a href="pages/applications.php" class="nav-link">▤ Applications</a>

        <div class="sidebar-bottom">
            <p>Stay organized. Get hired.</p>
        </div>
    </aside>

    <main class="main-content">

        <header class="topbar">
            <div>
                <h2>Dashboard</h2>
                <p>Welcome back! Here's your application overview.</p>
            </div>

            <a href="pages/add-application.php" class="primary-btn">
                + Add Application
            </a>
        </header>

        <section class="stats-grid">

            <div class="stat-card">
                <span>Total Applications</span>
                <h2><?= (int)$stats['total'] ?></h2>
                <small>All tracked applications</small>
            </div>

            <div class="stat-card">
                <span>Applied</span>
                <h2><?= (int)$stats['applied'] ?></h2>
                <small>Awaiting response</small>
            </div>

            <div class="stat-card">
                <span>Interviews</span>
                <h2><?= (int)$stats['interview'] ?></h2>
                <small>Interview stage</small>
            </div>

            <div class="stat-card">
                <span>Offers</span>
                <h2><?= (int)$stats['offers'] ?></h2>
                <small>Offers received</small>
            </div>

        </section>

        <section class="content-card">

            <div class="section-heading">
                <div>
                    <h3>Recent Applications</h3>
                    <p>Your latest job application activity</p>
                </div>

                <a href="pages/applications.php">View all →</a>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Company</th>
                            <th>Job Position</th>
                            <th>Location</th>
                            <th>Applied Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if (count($recentApplications) > 0): ?>

                        <?php foreach ($recentApplications as $app): ?>
                            <tr>
                                <td><strong><?= e($app['company_name']) ?></strong></td>
                                <td><?= e($app['job_title']) ?></td>
                                <td><?= e($app['location']) ?></td>
                                <td><?= e($app['applied_date']) ?></td>
                                <td>
                                    <span class="status">
                                        <?= e($app['status_name']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                    <?php else: ?>
                        <tr>
                            <td colspan="5">No applications found.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>

                </table>
            </div>

        </section>

    </main>

</div>

</body>
</html>
