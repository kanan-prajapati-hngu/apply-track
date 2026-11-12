
<?php
session_start();

require_once "../config/database.php";

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Get application ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    http_response_code(400);
    exit("Invalid application ID.");
}

// Fetch existing application
$stmt = $pdo->prepare(
    "SELECT * FROM applications WHERE id = ?"
);
$stmt->execute([$id]);

$application = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$application) {
    http_response_code(404);
    exit("Application not found.");
}

// Fetch statuses
$statuses = $pdo->query(
    "SELECT * FROM application_statuses ORDER BY id"
)->fetchAll(PDO::FETCH_ASSOC);

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // CSRF validation
    $submittedToken = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $submittedToken)) {
        http_response_code(403);
        exit("Invalid form submission. Please try again.");
    }

    // Get form values
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

    if ($jobUrl !== '' && !filter_var($jobUrl, FILTER_VALIDATE_URL)) {
        $errors[] = "Enter a valid URL.";
    }

    // Validate status ID
    $validStatusIds = array_column($statuses, 'id');

    if (!in_array((int)$statusId, array_map('intval', $validStatusIds), true)) {
        $errors[] = "Please select a valid application status.";
    }

    // Update application
    if (empty($errors)) {

        $sql = "
            UPDATE applications SET
                company_name = ?,
                job_title = ?,
                job_type = ?,
                location = ?,
                applied_date = ?,
                status_id = ?,
                job_url = ?,
                interview_date = ?,
                notes = ?
            WHERE id = ?
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            $company,
            $jobTitle,
            $jobType,
            $location,
            $appliedDate,
            (int)$statusId,
            $jobUrl ?: null,
            $interviewDate ?: null,
            $notes,
            $id
        ]);

        header("Location: applications.php");
        exit;
    }

    // Preserve submitted values if validation fails
    $application = array_merge($application, [
        'company_name' => $company,
        'job_title' => $jobTitle,
        'job_type' => $jobType,
        'location' => $location,
        'applied_date' => $appliedDate,
        'status_id' => $statusId,
        'job_url' => $jobUrl,
        'interview_date' => $interviewDate,
        'notes' => $notes
    ]);
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Application | ApplyTrack</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

<main class="main-content">

    <header class="topbar">
        <div>
            <h2>Edit Application</h2>
            <p>Update your application details.</p>
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

        <form
            method="POST"
            action="edit-application.php?id=<?= (int)$id ?>"
            class="application-form"
        >

            <!-- CSRF Token -->
            <input
                type="hidden"
                name="csrf_token"
                value="<?= e($_SESSION['csrf_token']) ?>"
            >

            <div class="form-group">
                <label>Company Name *</label>
                <input type="text" name="company_name"
                    value="<?= e($application['company_name']) ?>"
                    required>
            </div>

            <div class="form-group">
                <label>Job Title *</label>
                <input type="text" name="job_title"
                    value="<?= e($application['job_title']) ?>"
                    required>
            </div>

            <div class="form-group">
                <label>Job Type</label>
                <select name="job_type">
                    <option value="">Select Job Type</option>

                    <?php
                    $types = ['Full-time', 'Part-time', 'Internship', 'Contract'];

                    foreach ($types as $type):
                    ?>
                        <option value="<?= e($type) ?>"
                            <?= $application['job_type'] === $type ? 'selected' : '' ?>>
                            <?= e($type) ?>
                        </option>
                    <?php endforeach; ?>

                </select>
            </div>

            <div class="form-group">
                <label>Location</label>
                <input type="text" name="location"
                    value="<?= e($application['location']) ?>">
            </div>

            <div class="form-group">
                <label>Applied Date *</label>
                <input type="date" name="applied_date"
                    value="<?= e($application['applied_date']) ?>"
                    required>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status_id" required>

                    <?php foreach ($statuses as $s): ?>
                        <option value="<?= e($s['id']) ?>"
                            <?= $application['status_id'] == $s['id'] ? 'selected' : '' ?>>
                            <?= e($s['status_name']) ?>
                        </option>
                    <?php endforeach; ?>

                </select>
            </div>

            <div class="form-group">
                <label>Job URL</label>
                <input type="url" name="job_url"
                    value="<?= e($application['job_url']) ?>">
            </div>

            <div class="form-group">
                <label>Interview Date</label>
                <input type="date" name="interview_date"
                    value="<?= e($application['interview_date']) ?>">
            </div>

            <div class="form-group full-width">
                <label>Notes</label>
                <textarea name="notes" rows="4"><?= e($application['notes']) ?></textarea>
            </div>

            <div class="form-actions full-width">

                <button type="submit" class="primary-btn">
                    Update Application
                </button>

                <a href="applications.php">Cancel</a>

            </div>

        </form>

    </section>

</main>

</body>
</html>
