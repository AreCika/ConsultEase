<?php
session_start();

// Only authenticated lecturers can view booking requests
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['user_role']) ||
    $_SESSION['user_role'] !== 'lecturer'
) {
    header('Location: ../auth/login.php');
    exit;
}
// Generate CSRF token for booking status update forms
if (empty($_SESSION['booking_csrf_token'])) {
    $_SESSION['booking_csrf_token'] = bin2hex(random_bytes(32));
}
require_once '../config/db.php';

$lecturer_id = (int) $_SESSION['user_id'];

// Retrieve bookings for consultation slots owned by this lecturer
$stmt = $conn->prepare(
    "SELECT
        b.id AS booking_id,
        b.reason,
        b.status,
        s.slot_date,
        s.start_time,
        s.end_time,
        s.venue,
        u.name AS student_name
     FROM bookings b
     JOIN slots s ON b.slot_id = s.id
     JOIN users u ON b.student_id = u.id
     WHERE s.lecturer_id = ?
     ORDER BY
        CASE WHEN b.status = 'Pending' THEN 0 ELSE 1 END,
        s.slot_date,
        s.start_time"
);

$stmt->bind_param("i", $lecturer_id);
$stmt->execute();
$result = $stmt->get_result();

$pageTitle = 'Booking Requests - ConsultEase';
include_once '../includes/header.php';
?>

<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Booking Requests</h2>
            <p class="text-muted-ce mb-0">
                View and manage student consultation booking requests.
            </p>
        </div>

        <a href="list_slots.php" class="btn btn-outline-secondary">
            &larr; Back to My Slots
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-3">

            <?php if ($result->num_rows === 0): ?>

                <div class="text-center py-4">
                    <p class="fw-semibold mb-1">
                        No booking requests found.
                    </p>
                    <p class="text-muted-ce mb-0">
                        Student bookings will appear here.
                    </p>
                </div>

            <?php else: ?>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-light">
                            <tr>
                                <th>Student</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Venue</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <?php echo htmlspecialchars($row['student_name']); ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars(
                                            date('d M Y', strtotime($row['slot_date']))
                                        ); ?>
                                    </td>

                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            date('h:i A', strtotime($row['start_time']))
                                            . ' - ' .
                                            date('h:i A', strtotime($row['end_time']))
                                        );
                                        ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($row['venue']); ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($row['reason']); ?>
                                    </td>

                                    <td>
                                        <span class="badge bg-secondary">
                                            <?php echo htmlspecialchars($row['status']); ?>
                                        </span>
                                    </td>

                                    
<td>
    <?php if ($row['status'] === 'Pending'): ?>

        <div class="d-flex gap-2">

            <!-- Confirm Booking -->
            <form method="POST"
                  action="update_booking_status.php">

                <input type="hidden"
                       name="booking_id"
                       value="<?php echo (int) $row['booking_id']; ?>">

                <input type="hidden"
                       name="action"
                       value="confirm">

                <input type="hidden"
                       name="csrf_token"
                       value="<?php echo htmlspecialchars($_SESSION['booking_csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

                <button type="submit"
                        class="btn btn-success btn-sm"
                        onclick="return confirm('Confirm this booking?');">
                    Confirm
                </button>
            </form>

            <!-- Decline Booking -->
            <form method="POST"
                  action="update_booking_status.php">

                <input type="hidden"
                       name="booking_id"
                       value="<?php echo (int) $row['booking_id']; ?>">

                <input type="hidden"
                       name="action"
                       value="decline">

                <input type="hidden"
                       name="csrf_token"
                       value="<?php echo htmlspecialchars($_SESSION['booking_csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

                <button type="submit"
                        class="btn btn-danger btn-sm"
                        onclick="return confirm('Decline this booking?');">
                    Decline
                </button>
            </form>

        </div>

    <?php else: ?>
        <span class="text-muted">—</span>
    <?php endif; ?>
</td>

                                </tr>
                            <?php endwhile; ?>
                        </tbody>

                    </table>
                </div>

            <?php endif; ?>

        </div>
    </div>

</div>

<?php
$stmt->close();
include_once '../includes/footer.php';
?>
