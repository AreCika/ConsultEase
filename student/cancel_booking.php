
<?php
session_start();

// Only authenticated students can cancel bookings
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['user_role']) ||
    $_SESSION['user_role'] !== 'student'
) {
    header('Location: ../auth/login.php');
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

// Verify CSRF token
if (
    !isset($_POST['csrf_token'], $_SESSION['cancel_csrf_token']) ||
    !is_string($_POST['csrf_token']) ||
    !hash_equals($_SESSION['cancel_csrf_token'], $_POST['csrf_token'])
) {
    http_response_code(403);
    exit('Invalid request token.');
}

require_once '../config/db.php';

$student_id = (int) $_SESSION['user_id'];
$booking_id = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);

if (!$booking_id || $booking_id <= 0) {
    http_response_code(400);
    exit('Invalid booking ID.');
}

// Cancel only bookings belonging to this student
// and only if the current status is Pending or Confirmed
$stmt = $conn->prepare(
    "UPDATE bookings
     SET status = 'Cancelled'
     WHERE id = ?
       AND student_id = ?
       AND status IN ('Pending', 'Confirmed')"
);

$stmt->bind_param("ii", $booking_id, $student_id);
$stmt->execute();

$updated = $stmt->affected_rows;
$stmt->close();

// Store a message for the My Bookings page
if ($updated === 1) {
    $_SESSION['cancel_message'] = 'Booking cancelled successfully.';
} else {
    $_SESSION['cancel_error'] =
        'Booking could not be cancelled. It may already be processed or does not belong to you.';
}

// Redirect back to My Bookings
header('Location: my_bookings.php');
exit;
