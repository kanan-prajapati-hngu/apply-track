<?php
session_start();

require_once "../config/database.php";

// Allow only POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Method Not Allowed");
}

// Validate CSRF token
$submittedToken = $_POST['csrf_token'] ?? '';

if (
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $submittedToken)
) {
    http_response_code(403);
    exit("Invalid form submission. Please try again.");
}

// Validate application ID
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    http_response_code(400);
    exit("Invalid application ID.");
}

// Delete application
$stmt = $pdo->prepare(
    "DELETE FROM applications WHERE id = ?"
);

$stmt->execute([$id]);

header("Location: applications.php");
exit;


?>
