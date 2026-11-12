<?php
session_start();

// Only authenticated lecturers can update booking status
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['user_role']) ||
    $_SESSION['user_role'] !== 'lecturer'
) {
    header('Location: ../auth/login.php');
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

// Validate CSRF token
if (
    !isset($_POST['csrf_token'], $_SESSION['booking_csrf_token']) ||
    !is_string($_POST['csrf_token']) ||
    !hash_equals($_SESSION['booking_csrf_token'], $_POST['csrf_token'])
) {
    http_response_code(403);
    exit('Invalid request token.');
}

require_once '../config/db.php';

$lecturer_id = (int) $_SESSION['user_id'];
$booking_id = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
$action = $_POST['action'] ?? '';

// Validate booking ID and requested action
if (
    !$booking_id ||
    $booking_id <= 0 ||
    !in_array($action, ['confirm', 'decline'], true)
) {
    http_response_code(400);
    exit('Invalid booking request.');
}

$new_status = $action === 'confirm' ? 'Confirmed' : 'Declined';

// Update only Pending bookings belonging to this lecturer's slots
$stmt = $conn->prepare(
    "UPDATE bookings b
     INNER JOIN slots s ON b.slot_id = s.id
     SET b.status = ?
     WHERE b.id = ?
       AND s.lecturer_id = ?
       AND b.status = 'Pending'"
);

$stmt->bind_param(
    "sii",
    $new_status,
    $booking_id,
    $lecturer_id
);

$stmt->execute();
$updated = $stmt->affected_rows;
$stmt->close();

// Save a one-time message for the Booking Requests page
if ($updated === 1) {
    $_SESSION['booking_message'] =
        'Booking successfully ' .
        ($action === 'confirm' ? 'confirmed.' : 'declined.');
} else {
    $_SESSION['booking_error'] =
        'Booking could not be updated. It may already be processed or does not belong to you.';
}

// Redirect back to Booking Requests
header('Location: booking_requests.php');
exit;
