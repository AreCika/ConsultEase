
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

?>
<h2>My Booking Status</h2>
<table border="1" cellpadding="8">
    <tr>
    <th>Lecturer</th>
    <th>Date</th>
    <th>Time</th>
    <th>Venue</th>
    <th>Status</th>
    <th>Action</th>
</tr>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $row['lecturer_name'] ?></td>
        <td><?= $row['slot_date'] ?></td>
        <td><?= $row['start_time'] ?> - <?= $row['end_time'] ?></td>
        <td><?= $row['venue'] ?></td>
        <td>
    <strong><?= htmlspecialchars($row['status']) ?></strong>
</td>


<td>
    <?php if (in_array($row['status'], ['Pending', 'Confirmed'], true)): ?>

        <form method="POST"
      action="cancel_booking.php"
      class="cancel-booking-form"
      onsubmit="return confirm('Are you sure you want to cancel this booking?');">

            <input type="hidden"
                   name="booking_id"
                   value="<?php echo (int) $row['booking_id']; ?>">

            <input type="hidden"
                   name="csrf_token"
                   value="<?php echo htmlspecialchars($_SESSION['cancel_csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

            <button type="submit"
                    class="btn btn-outline-danger btn-sm">
                Cancel Booking
            </button>

        </form>

    <?php else: ?>
        <span>—</span>
    <?php endif; ?>
</td>

    </tr>
    <?php endwhile; ?>
</table>
<div style="margin-top: 20px;">
    <a href="available_slots.php" class="btn btn-outline-primary">
        ← Back to Dashboard
    </a>
</div>
<style>
    /* Remove the white box around the Cancel Booking button */
form.cancel-booking-form {
    background: transparent;
    padding: 0;
    margin: 0;
    border-radius: 0;
    box-shadow: none;
    max-width: none;
}
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