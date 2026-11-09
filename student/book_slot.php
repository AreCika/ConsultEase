<?php
session_start();

// Only authenticated students can book consultation slots
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['user_role']) ||
    $_SESSION['user_role'] !== 'student'
) {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../config/db.php';
 


$student_id = (int) $_SESSION['user_id'];

// Get and validate the selected slot ID
$slot_id = filter_input(INPUT_GET, 'slot_id', FILTER_VALIDATE_INT);

if (!$slot_id || $slot_id <= 0) {
    http_response_code(400);
    exit('Invalid consultation slot.');
}

// Retrieve the selected consultation slot
$stmt = $conn->prepare(
    "SELECT s.*, u.name AS lecturer_name
     FROM slots s
     JOIN users u ON s.lecturer_id = u.id
     WHERE s.id = ?"
);

$stmt->bind_param("i", $slot_id);
$stmt->execute();

$slot = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$slot) {
    http_response_code(404);
    exit('Consultation slot not found.');
}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $reason = trim($_POST['reason'] ?? '');

    if ($reason === '') {
        echo "<p style='color:red;'>Please enter a reason for consultation.</p>";
    } else {
        try {
            // Start transaction to prevent concurrent bookings
            $conn->begin_transaction();

            // Lock the selected slot while checking and creating a booking
            $lockStmt = $conn->prepare(
                "SELECT id FROM slots WHERE id = ? FOR UPDATE"
            );
            $lockStmt->bind_param("i", $slot_id);
            $lockStmt->execute();
            $lockedSlot = $lockStmt->get_result()->fetch_assoc();
            $lockStmt->close();

            if (!$lockedSlot) {
                $conn->rollback();
                echo "<p style='color:red;'>Consultation slot not found.</p>";
            } else {

                // Check whether the slot already has an active booking
                $checkStmt = $conn->prepare(
                    "SELECT id FROM bookings
                     WHERE slot_id = ?
                       AND status IN ('Pending', 'Confirmed')
                     LIMIT 1"
                );

                $checkStmt->bind_param("i", $slot_id);
                $checkStmt->execute();
                $existingBooking = $checkStmt->get_result()->fetch_assoc();
                $checkStmt->close();

                if ($existingBooking) {
                    $conn->rollback();

                    echo "<p style='color:red;'>
                            This consultation slot has already been booked.
                            Please choose another available slot.
                          </p>";
                } else {

                    // Insert the new booking with Pending status
                    $insertStmt = $conn->prepare(
                        "INSERT INTO bookings
                         (slot_id, student_id, reason, status)
                         VALUES (?, ?, ?, 'Pending')"
                    );

                    $insertStmt->bind_param(
                        "iis",
                        $slot_id,
                        $student_id,
                        $reason
                    );

                    $insertStmt->execute();
                    $insertStmt->close();

                    // Save transaction
                    $conn->commit();

                    echo "<p style='color:green;'>
                            Booking successful!
                            <a href='my_bookings.php'>View My Bookings</a>
                          </p>";
                }
            }

        } catch (mysqli_sql_exception $e) {
            $conn->rollback();

            error_log('Booking error: ' . $e->getMessage());

            echo "<p style='color:red;'>
                    Unable to complete booking. Please try again.
                  </p>";
        }
    }
}

?>

<h2>Book Slot</h2>
<p><strong>Lecturer:</strong> <?= $slot['lecturer_name'] ?></p>
<p><strong>Date:</strong> <?= $slot['slot_date'] ?> (<?= $slot['start_time'] ?> - <?= $slot['end_time'] ?>)</p>
<p><strong>Venue:</strong> <?= $slot['venue'] ?></p>
<?php
$checkStmt = $conn->prepare(
    "SELECT id FROM bookings
     WHERE slot_id = ?
       AND status IN ('Pending', 'Confirmed')
     LIMIT 1"
);

$checkStmt->bind_param("i", $slot_id);
$checkStmt->execute();
$isBooked = $checkStmt->get_result()->num_rows > 0;
$checkStmt->close();
?>

<?php if (!$isBooked): ?>
    <form method="POST">
        Reason: <br>
        <textarea name="reason" required rows="4" cols="30"></textarea>
        <br><br>
        <button type="submit">Confirm Booking</button>
    </form>
<?php endif; ?>

<div style="margin-top: 20px;">
    <a href="available_slots.php"
       style="display: inline-block;
              padding: 10px 16px;
              background-color: #3498db;
              color: white;
              text-decoration: none;
              border-radius: 5px;">
        &larr; Back to Dashboard
    </a>
</div>
<style>
    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; padding: 40px; color: #333; }
    h2 { color: #2c3e50; }
    form { background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); max-width: 400px; }
    input[type="date"], input[type="time"], input[type="text"] { width: 100%; padding: 10px; margin: 8px 0 20px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
    button { background-color: #3498db; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; width: 100%; }
    button:hover { background-color: #2980b9; }
    a { color: #3498db; text-decoration: none; display: inline-block; margin-top: 15px; }
    table { width: 100%; border-collapse: collapse; background: #fff; box-shadow: 0 4px 8px rgba(0,0,0,0.1); margin-top: 20px; }
    th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; }
    th { background-color: #3498db; color: white; }
    tr:hover { background-color: #f1f1f1; }
</style>