<?php
require_once '../config/db.php';
session_start();
if (!isset($_SESSION['user_id'])) { $_SESSION['user_id'] = 2; } 

$student_id = $_SESSION['user_id'];
$sql = "SELECT b.id as booking_id, s.slot_date, s.start_time, s.end_time, s.venue, u.name as lecturer_name, b.status 
        FROM bookings b JOIN slots s ON b.slot_id = s.id JOIN users u ON s.lecturer_id = u.id 
        WHERE b.student_id = $student_id ORDER BY s.slot_date DESC";
$result = $conn->query($sql);
?>
<h2>My Booking Status</h2>
<table border="1" cellpadding="8">
    <tr><th>Lecturer</th><th>Date</th><th>Time</th><th>Venue</th><th>Status</th></tr>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $row['lecturer_name'] ?></td>
        <td><?= $row['slot_date'] ?></td>
        <td><?= $row['start_time'] ?> - <?= $row['end_time'] ?></td>
        <td><?= $row['venue'] ?></td>
        <td><strong><?= $row['status'] ?></strong></td>
    </tr>
    <?php endwhile; ?>
</table>
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