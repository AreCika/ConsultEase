<?php
session_start();
// Generate CSRF token for delete forms
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Only authenticated lecturers can access this page
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['user_role']) ||
    $_SESSION['user_role'] !== 'lecturer'
) {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../config/db.php';

// Get the logged-in lecturer ID
$lecturer_id = (int) $_SESSION['user_id'];

// Retrieve consultation slots belonging to this lecturer
$stmt = $conn->prepare(
    "SELECT id, slot_date, start_time, end_time, venue
     FROM slots
     WHERE lecturer_id = ?
     ORDER BY slot_date, start_time"
);

$stmt->bind_param("i", $lecturer_id);
$stmt->execute();

$result = $stmt->get_result();

$pageTitle = 'My Consultation Slots - ConsultEase';
include '../includes/header.php';
?>

<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">My Consultation Slots</h2>
            <p class="text-muted-ce mb-0">
                Manage your available consultation slots.
            </p>
        </div>

        <a href="create_slot.php" class="btn btn-primary">
            + Create New Slot
        </a>
    </div>

    <div class="card p-3">

        <?php if ($result->num_rows === 0): ?>

            <div class="text-center py-4">
                <p class="fw-semibold mb-1">
                    No consultation slots found.
                </p>

                <p class="text-muted-ce mb-0">
                    Create a new consultation slot to get started.
                </p>
            </div>

        <?php else: ?>

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Venue</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php while ($row = $result->fetch_assoc()): ?>

                            <?php
                            $dateText = date(
                                'd M Y',
                                strtotime($row['slot_date'])
                            );

                            $startText = date(
                                'h:i A',
                                strtotime($row['start_time'])
                            );

                            $endText = date(
                                'h:i A',
                                strtotime($row['end_time'])
                            );
                            ?>

                            <tr>

                                <td>
                                    <?php echo htmlspecialchars($dateText); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($startText); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($endText); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row['venue']); ?>
                                </td>

                                <td>
                                    <a
                                        href="edit_slot.php?id=<?php echo (int) $row['id']; ?>"
                                        class="btn btn-sm btn-outline-primary">
                                        Edit
                                    </a>

                                    <form
                                        method="POST"
                                        action="delete_slot.php"
                                        class="d-inline"
                                        onsubmit="return confirm('Delete this slot?');">

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?php echo (int) $row['id']; ?>">

                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-outline-danger">
                                            Delete
                                        </button>
                                    </form>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    </tbody>

                </table>

            </div>

        <?php endif; ?>

    </div>

</div>

<?php
$stmt->close();
include '../includes/footer.php';
?>
