<?php
session_start();

// Only authenticated lecturers can edit consultation slots
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['user_role']) ||
    $_SESSION['user_role'] !== 'lecturer'
) {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../config/db.php';

$lecturer_id = (int) $_SESSION['user_id'];
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Validate slot ID
if (!$id || $id <= 0) {
    http_response_code(400);
    exit('Invalid slot ID.');
}

// Get the slot belonging to this lecturer
$stmt = $conn->prepare(
    "SELECT id, slot_date, start_time, end_time, venue
     FROM slots
     WHERE id = ? AND lecturer_id = ?"
);

$stmt->bind_param("ii", $id, $lecturer_id);
$stmt->execute();
$slot = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$slot) {
    http_response_code(404);
    exit("Slot not found or you don't have permission.");
}

$error = '';

// Process the update form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $date  = trim($_POST['slot_date'] ?? '');
    $start = trim($_POST['start_time'] ?? '');
    $end   = trim($_POST['end_time'] ?? '');
    $venue = trim($_POST['venue'] ?? '');

    // Keep submitted values if validation fails
    $slot['slot_date'] = $date;
    $slot['start_time'] = $start;
    $slot['end_time'] = $end;
    $slot['venue'] = $venue;

    if ($date === '' || $start === '' || $end === '' || $venue === '') {
        $error = 'Please fill in all fields.';
    } elseif ($start >= $end) {
        $error = 'End time must be later than start time.';
    } else {

        // Update only a slot owned by the logged-in lecturer
        $update = $conn->prepare(
            "UPDATE slots
             SET slot_date = ?,
                 start_time = ?,
                 end_time = ?,
                 venue = ?
             WHERE id = ? AND lecturer_id = ?"
        );

        $update->bind_param(
            "ssssii",
            $date,
            $start,
            $end,
            $venue,
            $id,
            $lecturer_id
        );

        if ($update->execute()) {
            $update->close();

            header('Location: list_slots.php');
            exit;
        } else {
            $error = 'Unable to update slot.';
        }

        $update->close();
    }
}

$pageTitle = 'Edit Consultation Slot - ConsultEase';
include '../includes/header.php';
?>

<div class="container py-4">

    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">

            <h2 class="fw-bold mb-2">Update Consultation Slot</h2>
            <p class="text-muted-ce mb-4">
                Edit your consultation slot details.
            </p>

            <?php if ($error !== ''): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">

                    <form method="POST">

                        <div class="mb-3">
                            <label for="slot_date" class="form-label">Date</label>
                            <input
                                type="date"
                                id="slot_date"
                                name="slot_date"
                                class="form-control"
                                value="<?php echo htmlspecialchars($slot['slot_date']); ?>"
                                required>
                        </div>

                        <div class="mb-3">
                            <label for="start_time" class="form-label">Start Time</label>
                            <input
                                type="time"
                                id="start_time"
                                name="start_time"
                                class="form-control"
                                value="<?php echo htmlspecialchars($slot['start_time']); ?>"
                                required>
                        </div>

                        <div class="mb-3">
                            <label for="end_time" class="form-label">End Time</label>
                            <input
                                type="time"
                                id="end_time"
                                name="end_time"
                                class="form-control"
                                value="<?php echo htmlspecialchars($slot['end_time']); ?>"
                                required>
                        </div>

                        <div class="mb-3">
                            <label for="venue" class="form-label">Venue</label>
                            <input
                                type="text"
                                id="venue"
                                name="venue"
                                class="form-control"
                                value="<?php echo htmlspecialchars($slot['venue']); ?>"
                                required>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                Update Slot
                            </button>

                            <a href="list_slots.php" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>

</div>

<?php include '../includes/footer.php'; ?>
