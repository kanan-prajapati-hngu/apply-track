
<?php
require_once "../config/database.php";

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $company = trim($_POST['company_name'] ?? '');
    $jobTitle = trim($_POST['job_title'] ?? '');
    $jobType = $_POST['job_type'] ?? '';
    $location = trim($_POST['location'] ?? '');
    $appliedDate = $_POST['applied_date'] ?? '';
    $statusId = $_POST['status_id'] ?? '';
    $jobUrl = trim($_POST['job_url'] ?? '');
    $interviewDate = $_POST['interview_date'] ?? '';
    $notes = trim($_POST['notes'] ?? '');

    // Validation
    if ($company === '') {
        $errors[] = "Company name is required.";
    }

    if ($jobTitle === '') {
        $errors[] = "Job title is required.";
    }

    if ($appliedDate === '') {
        $errors[] = "Applied date is required.";
    }

    if (!filter_var($jobUrl, FILTER_VALIDATE_URL) && $jobUrl !== '') {
        $errors[] = "Enter a valid job URL.";
    }

    // Insert record
    if (empty($errors)) {

        $sql = "
            INSERT INTO applications
            (
                company_name,
                job_title,
                job_type,
                location,
                applied_date,
                status_id,
                job_url,
                interview_date,
                notes
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            $company,
            $jobTitle,
            $jobType,
            $location,
            $appliedDate,
            $statusId,
            $jobUrl ?: null,
            $interviewDate ?: null,
            $notes
        ]);

        header("Location: applications.php");
        exit;
    }
}

// Fetch statuses
$statusQuery = $pdo->query(
    "SELECT * FROM application_statuses ORDER BY id"
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
    <title>Add Application | ApplyTrack</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

<main class="main-content">

    <header class="topbar">
        <div>
            <h2>Add Application</h2>
            <p>Keep track of a new job opportunity.</p>
        </div>

        <a href="applications.php">← Back</a>
    </header>

    <section class="content-card">

        <?php if (!empty($errors)): ?>
            <div class="form-errors">
                <?php foreach ($errors as $error): ?>
                    <p><?= e($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="application-form">

            <div class="form-group">
                <label>Company Name *</label>
                <input type="text" name="company_name"
                    value="<?= e($_POST['company_name'] ?? '') ?>"
                    required>
            </div>

            <div class="form-group">
                <label>Job Title *</label>
                <input type="text" name="job_title"
                    value="<?= e($_POST['job_title'] ?? '') ?>"
                    required>
            </div>

            <div class="form-group">
                <label>Job Type</label>
                <select name="job_type">
                    <option value="">Select Job Type</option>
                    <option>Full-time</option>
                    <option>Part-time</option>
                    <option>Internship</option>
                    <option>Contract</option>
                </select>
            </div>

            <div class="form-group">
                <label>Location</label>
                <input type="text" name="location">
            </div>

            <div class="form-group">
                <label>Applied Date *</label>
                <input type="date" name="applied_date"
                    value="<?= date('Y-m-d') ?>" required>
            </div>

            <div class="form-group">
                <label>Application Status</label>
                <select name="status_id" required>
                    <?php foreach ($statuses as $s): ?>
                        <option value="<?= e($s['id']) ?>"
                            <?= $s['id'] == 2 ? 'selected' : '' ?>>
                            <?= e($s['status_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Job URL</label>
                <input type="url" name="job_url">
            </div>

            <div class="form-group">
                <label>Interview Date</label>
                <input type="date" name="interview_date">
            </div>

            <div class="form-group full-width">
                <label>Notes</label>
                <textarea name="notes" rows="4"></textarea>
            </div>

            <div class="form-actions full-width">
                <button type="submit" class="primary-btn">
                    Save Application
                </button>

                <a href="applications.php">Cancel</a>
            </div>

        </form>

    </section>

</main>

</body>
</html>
