<?php
require_once '../config/db.php';
session_start();
// Dummy login untuk test (Student ID: 2)
if (!isset($_SESSION['user_id'])) { $_SESSION['user_id'] = 2; } 

$student_id = $_SESSION['user_id'];
$slot_id = $_GET['slot_id'] ?? 1; 

$slotQuery = $conn->query("SELECT s.*, u.name as lecturer_name FROM slots s JOIN users u ON s.lecturer_id = u.id WHERE s.id = $slot_id");
$slot = $slotQuery->fetch_assoc();

if (!$slot) { die("Slot tidak dijumpai. Sila buat slot di page lecturer dulu."); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reason = $_POST['reason'];
    $check = $conn->query("SELECT id FROM bookings WHERE slot_id = $slot_id AND student_id = $student_id");
    
    if ($check->num_rows > 0) {
        echo "<p style='color:red;'>Anda sudah menempah slot ini!</p>";
    } else {
        $stmt = $conn->prepare("INSERT INTO bookings (slot_id, student_id, reason) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $slot_id, $student_id, $reason);
        if ($stmt->execute()) { echo "<p style='color:green;'>Booking berjaya! <a href='my_bookings.php'>Lihat Status</a></p>"; } 
        else { echo "<p style='color:red;'>Error: " . $stmt->error . "</p>"; }
    }
}
?>
<h2>Book Slot</h2>
<p><strong>Lecturer:</strong> <?= $slot['lecturer_name'] ?></p>
<p><strong>Date:</strong> <?= $slot['slot_date'] ?> (<?= $slot['start_time'] ?> - <?= $slot['end_time'] ?>)</p>
<p><strong>Venue:</strong> <?= $slot['venue'] ?></p>
<form method="POST">
    Reason: <br><textarea name="reason" required rows="4" cols="30"></textarea><br><br>
    <button type="submit">Confirm Booking</button>
</form>
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