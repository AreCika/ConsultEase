<?php
session_start();

// Only authenticated lecturers can delete slots
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['user_role']) ||
    $_SESSION['user_role'] !== 'lecturer'
) {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../config/db.php';

// Only allow deletion through POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

// Verify CSRF token
if (
    !isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    http_response_code(403);
    exit('Invalid request token.');
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$lecturer_id = (int) $_SESSION['user_id'];

if (!$id || $id <= 0) {
    http_response_code(400);
    exit('Invalid slot ID.');
}

// Delete only a slot belonging to the logged-in lecturer
$stmt = $conn->prepare(
    "DELETE FROM slots
     WHERE id = ? AND lecturer_id = ?"
);

$stmt->bind_param("ii", $id, $lecturer_id);
$stmt->execute();
$stmt->close();

header('Location: list_slots.php');
exit;
