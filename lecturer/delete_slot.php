<?php
require_once '../config/db.php';
session_start();
if (!isset($_SESSION['user_id'])) { $_SESSION['user_id'] = 1; }

$id = $_GET['id'] ?? 0;
$lecturer_id = $_SESSION['user_id'];

$stmt = $conn->prepare("DELETE FROM slots WHERE id = ? AND lecturer_id = ?");
$stmt->bind_param("ii", $id, $lecturer_id);
$stmt->execute();

header("Location: list_slots.php");
exit;