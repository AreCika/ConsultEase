<?php
session_start();

// Only authenticated lecturers can create slots
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['user_role']) ||
    $_SESSION['user_role'] !== 'lecturer'
) {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../config/db.php';

// Message shown above the form after submitting ('success' or 'danger')
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Use the logged-in lecturer ID
    $lecturer_id = (int) $_SESSION['user_id'];

    $date  = trim($_POST['slot_date'] ?? '');
    $start = trim($_POST['start_time'] ?? '');
    $end   = trim($_POST['end_time'] ?? '');
    $venue = trim($_POST['venue'] ?? '');

    if ($date === '' || $start === '' || $end === '' || $venue === '') {
        $message = 'Please fill in all fields.';
        $messageType = 'danger';
    } elseif ($start >= $end) {
        $message = 'End time must be later than start time.';
        $messageType = 'danger';
    } else {

        // Insert a consultation slot for the logged-in lecturer
        $stmt = $conn->prepare(
            "INSERT INTO slots
             (lecturer_id, slot_date, start_time, end_time, venue)
             VALUES (?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "issss",
            $lecturer_id,
            $date,
            $start,
            $end,
            $venue
        );

        if ($stmt->execute()) {
            $message = 'Slot created successfully!';
            $messageType = 'success';
        } else {
            $message = 'Unable to create slot.';
            $messageType = 'danger';
        }

        $stmt->close();
    }
}

$pageTitle = 'Create Slot - ConsultEase';
include '../includes/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1">Create Consultation Slot</h1>
        <p class="text-muted-ce mb-0">Specify the time and room location for student bookings.</p>
    </div>
    <a href="list_slots.php" class="btn btn-outline-secondary">&larr; Back to My Slots</a>
</div>

<?php if ($message !== ''): ?>
    <div class="alert alert-<?php echo $messageType; ?>" role="<?php echo $messageType === 'success' ? 'status' : 'alert'; ?>">
        <?php echo htmlspecialchars($message); ?>
        <?php if ($messageType === 'success'): ?>
            <a href="list_slots.php" class="alert-link">View My Slots</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="card ce-card">
    <div class="card-body p-3 p-md-4">
        <form method="POST">
            <div class="row g-3">
                <div class="col-sm-6 col-lg-3">
                    <label for="slot_date" class="form-label fw-semibold">Consultation Date <span class="text-danger">*</span></label>
                    <input type="date" id="slot_date" name="slot_date" class="form-control" required>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <label for="start_time" class="form-label fw-semibold">Start Time <span class="text-danger">*</span></label>
                    <input type="time" id="start_time" name="start_time" class="form-control" required>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <label for="end_time" class="form-label fw-semibold">End Time <span class="text-danger">*</span></label>
                    <input type="time" id="end_time" name="end_time" class="form-control" required>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <label for="venue" class="form-label fw-semibold">Venue / Location <span class="text-danger">*</span></label>
                    <input type="text" id="venue" name="venue" class="form-control" placeholder="e.g. Lecturer Office B-3-14" required>
                </div>
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-4">
                <p class="small text-muted-ce mb-0">Created slots will be visible to students for consultation bookings.</p>
                <button type="submit" class="btn btn-ce-primary">+ Create Slot</button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
