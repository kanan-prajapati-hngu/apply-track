
<?php

require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Method Not Allowed");
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    die("Invalid application ID.");
}

$stmt = $pdo->prepare(
    "DELETE FROM applications WHERE id = ?"
);

$stmt->execute([$id]);

header("Location: applications.php");
exit;

?>
