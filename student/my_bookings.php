<?php
session_start();

// Only authenticated students can view their bookings
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['user_role']) ||
    $_SESSION['user_role'] !== 'student'
) {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../config/db.php';

// Generate CSRF token for booking cancellation
if (empty($_SESSION['cancel_csrf_token'])) {
    $_SESSION['cancel_csrf_token'] = bin2hex(random_bytes(32));
}

$student_id = (int) $_SESSION['user_id'];

// Retrieve bookings belonging to the logged-in student
$sql = "SELECT
            b.id AS booking_id,
            s.slot_date,
            s.start_time,
            s.end_time,
            s.venue,
            u.name AS lecturer_name,
            b.status
        FROM bookings b
        JOIN slots s ON b.slot_id = s.id
        JOIN users u ON s.lecturer_id = u.id
        WHERE b.student_id = ?
        ORDER BY s.slot_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

$pageTitle = 'My Bookings - ConsultEase';
include '../includes/header.php';
?>

<div class="mb-4">
    <h1 class="h3 fw-bold mb-1">My Bookings</h1>
    <p class="text-muted-ce mb-0">View your consultation booking history and current booking statuses.</p>
</div>

<div class="card ce-card">
    <div class="p-3 p-md-4">
        <h2 class="h5 fw-bold mb-0">Consultation Schedule Records</h2>
    </div>

    <?php if ($result->num_rows === 0): ?>
        <div class="text-center px-3 py-5 border-top">
            <p class="fw-semibold mb-1">You have no bookings yet.</p>
            <p class="text-muted-ce mb-3">Book an available slot to request a consultation.</p>
            <a href="available_slots.php" class="btn btn-ce-primary">View Available Slots</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle mb-0 ce-table" aria-label="My consultation bookings">
                <thead>
                    <tr>
                        <th scope="col" class="ps-4">Lecturer</th>
                        <th scope="col">Date</th>
                        <th scope="col">Time</th>
                        <th scope="col">Venue</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <?php
                        // e.g. "12 Nov 2026" and "10:00 AM - 11:00 AM"
                        $dateText = date('j M Y', strtotime($row['slot_date']));
                        $timeText = date('h:i A', strtotime($row['start_time']))
                            . ' - ' . date('h:i A', strtotime($row['end_time']));
                        ?>
                        <tr>
                            <td class="ps-4 fw-semibold"><?php echo htmlspecialchars($row['lecturer_name']); ?></td>
                            <td class="tabular"><?php echo htmlspecialchars($dateText); ?></td>
                            <td class="tabular"><?php echo htmlspecialchars($timeText); ?></td>
                            <td><span class="ce-pill"><?php echo htmlspecialchars($row['venue']); ?></span></td>
                            <td>
                                <span class="status-badge status-<?php echo htmlspecialchars(strtolower($row['status'])); ?>">
                                    <?php echo htmlspecialchars($row['status']); ?>
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <?php if (in_array($row['status'], ['Pending', 'Confirmed'], true)): ?>
                                    <form method="POST"
                                          action="cancel_booking.php"
                                          class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                        <input type="hidden"
                                               name="booking_id"
                                               value="<?php echo (int) $row['booking_id']; ?>">
                                        <input type="hidden"
                                               name="csrf_token"
                                               value="<?php echo htmlspecialchars($_SESSION['cancel_csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                            Cancel Booking
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted-ce" aria-label="No action available">&mdash;</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <div class="small text-muted-ce border-top px-3 px-md-4 py-3">
        Only Pending or Confirmed bookings can be cancelled.
    </div>
</div>

<?php
$stmt->close();
include '../includes/footer.php';
?>
