<?php
require_once '../config/db.php';
session_start();
// Dummy login untuk test (Lecturer ID: 1)
if (!isset($_SESSION['user_id'])) { $_SESSION['user_id'] = 1; $_SESSION['role'] = 'lecturer'; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lecturer_id = $_SESSION['user_id'];
    $date  = $_POST['slot_date'];
    $start = $_POST['start_time'];
    $end   = $_POST['end_time'];
    $venue = $_POST['venue'];

    $stmt = $conn->prepare("INSERT INTO slots (lecturer_id, slot_date, start_time, end_time, venue) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $lecturer_id, $date, $start, $end, $venue);
    if ($stmt->execute()) { echo "<p style='color:green;'>Slot berjaya dicipta! <a href='list_slots.php'>Lihat Senarai</a></p>"; } 
    else { echo "<p style='color:red;'>Error: " . $stmt->error . "</p>"; }
}
?>
<h2>Create Consultation Slot</h2>
<form method="POST">
    Date: <input type="date" name="slot_date" required><br><br>
    Start: <input type="time" name="start_time" required><br><br>
    End: <input type="time" name="end_time" required><br><br>
    Venue: <input type="text" name="venue" required><br><br>
    <button type="submit">Create Slot</button>
</form>
<br><a href="list_slots.php">Back to List</a>
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