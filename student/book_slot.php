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

// Message shown above the form after submitting ('success' or 'danger')
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $reason = trim($_POST['reason'] ?? '');

    if ($reason === '') {
        $message = 'Please enter a reason for consultation.';
        $messageType = 'danger';
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
                $message = 'Consultation slot not found.';
                $messageType = 'danger';
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

                    $message = 'This consultation slot has already been booked. Please choose another available slot.';
                    $messageType = 'danger';
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

                    $message = 'Booking successful!';
                    $messageType = 'success';
                }
            }

        } catch (mysqli_sql_exception $e) {
            $conn->rollback();

            error_log('Booking error: ' . $e->getMessage());

            $message = 'Unable to complete booking. Please try again.';
            $messageType = 'danger';
        }
    }
}

// Hide the form if the slot already has an active booking
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

// e.g. "23 November 2026 (Monday)" and "03:00 PM"
$dateText  = date('j F Y (l)', strtotime($slot['slot_date']));
$startText = date('h:i A', strtotime($slot['start_time']));
$endText   = date('h:i A', strtotime($slot['end_time']));

$pageTitle = 'Book Consultation - ConsultEase';
include '../includes/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1">Book Consultation</h1>
        <p class="text-muted-ce mb-0">Confirm your appointment details and provide a reason for consultation with the lecturer.</p>
    </div>
    <a href="available_slots.php" class="btn btn-outline-secondary">&larr; Back to Dashboard</a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">

        <?php if ($message !== ''): ?>
            <div class="alert alert-<?php echo $messageType; ?>" role="<?php echo $messageType === 'success' ? 'status' : 'alert'; ?>">
                <?php echo htmlspecialchars($message); ?>
                <?php if ($messageType === 'success'): ?>
                    <a href="my_bookings.php" class="alert-link">View My Bookings</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="card ce-card">
            <div class="card-body p-3 p-md-4">

                <span class="badge rounded-pill ce-count-badge mb-3">Selected Slot</span>

                <div class="ce-detail-box p-3 mb-4">
                    <div class="mb-3">
                        <div class="ce-detail-label">Lecturer</div>
                        <div class="fw-bold"><?php echo htmlspecialchars($slot['lecturer_name']); ?></div>
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="ce-detail-label">Date</div>
                            <div class="tabular"><?php echo htmlspecialchars($dateText); ?></div>
                        </div>
                        <div class="col-sm-6">
                            <div class="ce-detail-label">Consultation Window</div>
                            <div class="tabular"><?php echo htmlspecialchars($startText . ' - ' . $endText); ?></div>
                        </div>
                        <div class="col-12">
                            <div class="ce-detail-label">Venue / Location</div>
                            <div><?php echo htmlspecialchars($slot['venue']); ?></div>
                        </div>
                    </div>
                </div>

                <?php if (!$isBooked): ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="reason" class="form-label fw-semibold">Reason for Consultation <span class="text-danger">*</span></label>
                            <textarea id="reason" name="reason" class="form-control" rows="4" required
                                      aria-describedby="reasonHelp"
                                      placeholder="e.g. FYP project progress, assignment clarification"></textarea>
                            <div id="reasonHelp" class="form-text">Provide enough detail so the lecturer can prepare for your session.</div>
                        </div>

                        <div class="ce-info-box p-3 mb-4">
                            <div class="fw-semibold">Pending Approval Process</div>
                            <div class="small">Your booking request will be placed in Pending status until the lecturer confirms or declines it.</div>
                        </div>

                        <div class="d-flex justify-content-between gap-2">
                            <a href="available_slots.php" class="btn btn-light border">Back</a>
                            <button type="submit" class="btn btn-ce-primary">Submit Booking &rarr;</button>
                        </div>
                    </form>
                <?php elseif ($message === ''): ?>
                    <p class="text-muted-ce mb-0">This slot has already been booked. Please choose another available slot.</p>
                <?php endif; ?>

            </div>
        </div>

    </div>
</div>

<?php include '../includes/footer.php'; ?>
