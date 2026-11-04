<?php
require_once '../config/db.php';
session_start();
if (!isset($_SESSION['user_id'])) { $_SESSION['user_id'] = 1; }

$lecturer_id = $_SESSION['user_id'];
$result = $conn->query("SELECT * FROM slots WHERE lecturer_id = $lecturer_id ORDER BY slot_date, start_time");
?>
<h2>My Consultation Slots</h2>
<a href="create_slot.php">+ Create New Slot</a><br><br>
<table border="1" cellpadding="8">
    <tr><th>Date</th><th>Time</th><th>Venue</th><th>Action</th></tr>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $row['slot_date'] ?></td>
        <td><?= $row['start_time'] ?> - <?= $row['end_time'] ?></td>
        <td><?= $row['venue'] ?></td>
        <td><a href="delete_slot.php?id=<?= $row['id'] ?>" onclick="return confirm('Padam slot ni?')">Delete</a></td>
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