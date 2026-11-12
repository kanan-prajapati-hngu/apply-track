
<?php
require_once "../config/database.php";

// Get search input
$search = $_GET['search'] ?? '';

// Get selected status
$status = $_GET['status'] ?? '';

// Build SQL query
$sql = "
    SELECT
        a.*,
        s.status_name
    FROM applications a
    INNER JOIN application_statuses s
        ON a.status_id = s.id
    WHERE 1=1
";

$params = [];

// Search by company or job title
if (!empty($search)) {
    $sql .= " AND (a.company_name LIKE :search
              OR a.job_title LIKE :search)";
    $params['search'] = "%$search%";
}

// Filter by status
if (!empty($status)) {
    $sql .= " AND a.status_id = :status";
    $params['status'] = $status;
}

$sql .= " ORDER BY a.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch available statuses
$statusQuery = $pdo->query(
    "SELECT * FROM application_statuses"
);

$statuses = $statusQuery->fetchAll(PDO::FETCH_ASSOC);

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Applications | ApplyTrack</title>

    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

<div class="main-content">

    <header class="topbar">
        <div>
            <h2>My Applications</h2>
            <p>Manage and track all your job applications.</p>
        </div>

        <a href="add-application.php" class="primary-btn">
            + Add Application
        </a>
    </header>

    <!-- Search and Filter -->

    <section class="content-card">

        <form method="GET" class="filter-form">

            <input
                type="text"
                name="search"
                placeholder="Search company or job title..."
                value="<?= e($search) ?>"
            >

            <select name="status">
                <option value="">All Statuses</option>

                <?php foreach ($statuses as $s): ?>
                    <option
                        value="<?= e($s['id']) ?>"
                        <?= $status == $s['id'] ? 'selected' : '' ?>
                    >
                        <?= e($s['status_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="primary-btn">
                Search
            </button>

            <a href="applications.php">Reset</a>

        </form>

        <div class="table-wrapper">

            <table>

                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Job Position</th>
                        <th>Job Type</th>
                        <th>Location</th>
                        <th>Applied Date</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>

                <?php if (count($applications) > 0): ?>

                    <?php foreach ($applications as $app): ?>

                        <tr>
                            <td><strong><?= e($app['company_name']) ?></strong></td>
                            <td><?= e($app['job_title']) ?></td>
                            <td><?= e($app['job_type']) ?></td>
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
                        <td colspan="6">No applications found.</td>
                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </section>

    <p style="margin-top:20px;">
        <a href="../index.php">← Back to Dashboard</a>
    </p>

</div>

</body>
</html>
