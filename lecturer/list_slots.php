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
    <tr>
        <th>Date</th>
        <th>Time</th>
        <th>Venue</th>
        <th>Action</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $row['slot_date'] ?></td>
        <td><?= $row['start_time'] ?> - <?= $row['end_time'] ?></td>
        <td><?= $row['venue'] ?></td>
        <td>
            <a href="edit_slot.php?id=<?= $row['id'] ?>">Edit</a> | 
            <a href="delete_slot.php?id=<?= $row['id'] ?>" onclick="return confirm('Delete this slot?')">Delete</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>