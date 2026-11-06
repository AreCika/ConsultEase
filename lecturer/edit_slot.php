<?php
require_once '../config/db.php';
session_start();
if (!isset($_SESSION['user_id'])) { $_SESSION['user_id'] = 1; } // Dummy lecturer

$lecturer_id = $_SESSION['user_id'];
$id = $_GET['id'] ?? 0;

// Get the slot data to edit
$stmt = $conn->prepare("SELECT * FROM slots WHERE id = ? AND lecturer_id = ?");
$stmt->bind_param("ii", $id, $lecturer_id);
$stmt->execute();
$slot = $stmt->get_result()->fetch_assoc();

if (!$slot) { die("Slot not found or you don't have permission."); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date  = $_POST['slot_date'];
    $start = $_POST['start_time'];
    $end   = $_POST['end_time'];
    $venue = $_POST['venue'];

    $update = $conn->prepare("UPDATE slots SET slot_date=?, start_time=?, end_time=?, venue=? WHERE id=? AND lecturer_id=?");
    $update->bind_param("ssssii", $date, $start, $end, $venue, $id, $lecturer_id);
    
    if ($update->execute()) {
        echo "<p style='color:green;'>Slot updated successfully! <a href='list_slots.php'>Back to List</a></p>";
    } else {
        echo "<p style='color:red;'>Error: " . $update->error . "</p>";
    }
}
?>
<h2>Update Consultation Slot</h2>
<form method="POST">
    Date: <input type="date" name="slot_date" value="<?= $slot['slot_date'] ?>" required><br><br>
    Start: <input type="time" name="start_time" value="<?= $slot['start_time'] ?>" required><br><br>
    End: <input type="time" name="end_time" value="<?= $slot['end_time'] ?>" required><br><br>
    Venue: <input type="text" name="venue" value="<?= $slot['venue'] ?>" required><br><br>
    <button type="submit">Update Slot</button>
</form>
<br><a href="list_slots.php">Cancel</a>