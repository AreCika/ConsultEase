<?php
session_start();

// Only authenticated students can access this page
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['user_role']) ||
    $_SESSION['user_role'] !== 'student'
) {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../config/db.php';

// Upcoming slots (today or later) that nobody has an active booking on.
// A slot with a Declined or Cancelled booking is free again, so it is shown.
$today = date('Y-m-d');

$sql = "SELECT s.id, s.slot_date, s.start_time, s.end_time, s.venue, u.name AS lecturer_name
        FROM slots s
        JOIN users u ON s.lecturer_id = u.id
        WHERE s.slot_date >= ?
          AND NOT EXISTS (
              SELECT 1 FROM bookings b
              WHERE b.slot_id = s.id AND b.status IN ('Pending', 'Confirmed')
          )
        ORDER BY s.slot_date, s.start_time";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $today);
$stmt->execute();
$result = $stmt->get_result();
$slotCount = $result->num_rows;

$pageTitle = 'Student Dashboard - ConsultEase';
include '../includes/header.php';
?>

<div class="mb-4">
    <h1 class="h3 fw-bold mb-1">Student Dashboard</h1>
    <p class="text-muted-ce mb-0">View available consultation slots and schedule an appointment.</p>
</div>

<div class="card ce-card">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 p-3 p-md-4">
        <div>
            <h2 class="h5 fw-bold mb-1">Available Consultation Slots</h2>
            <p class="small text-muted-ce mb-0">Showing upcoming open slots for all lecturers.</p>
        </div>
        <span class="badge rounded-pill ce-count-badge">
            <?php echo $slotCount; ?> <?php echo $slotCount === 1 ? 'Slot' : 'Slots'; ?> Active
        </span>
    </div>

    <?php if ($slotCount === 0): ?>
        <div class="text-center px-3 py-5 border-top">
            <p class="fw-semibold mb-1">No consultation slots are available right now.</p>
            <p class="text-muted-ce mb-3">Lecturers add new slots regularly, so please check back later.</p>
            <a href="my_bookings.php" class="btn btn-outline-secondary">View My Bookings</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle mb-0 ce-table" aria-label="Available consultation slots, sorted by date and time">
                <thead>
                    <tr>
                        <th scope="col" class="ps-4">Lecturer</th>
                        <th scope="col">Date</th>
                        <th scope="col">Start Time</th>
                        <th scope="col">End Time</th>
                        <th scope="col">Venue</th>
                        <th scope="col" class="text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <?php
                        // e.g. "12 Nov 2026 (Thursday)" and "10:00 AM"
                        $dateText  = date('j M Y (l)', strtotime($row['slot_date']));
                        $startText = date('h:i A', strtotime($row['start_time']));
                        $endText   = date('h:i A', strtotime($row['end_time']));
                        ?>
                        <tr>
                            <td class="ps-4 fw-semibold"><?php echo htmlspecialchars($row['lecturer_name']); ?></td>
                            <td class="tabular"><?php echo htmlspecialchars($dateText); ?></td>
                            <td class="tabular"><?php echo htmlspecialchars($startText); ?></td>
                            <td class="tabular"><?php echo htmlspecialchars($endText); ?></td>
                            <td><span class="ce-pill"><?php echo htmlspecialchars($row['venue']); ?></span></td>
                            <td class="text-end pe-4">
                                <a href="book_slot.php?slot_id=<?php echo (int) $row['id']; ?>"
                                   class="btn btn-ce-primary"
                                   aria-label="Book slot with <?php echo htmlspecialchars($row['lecturer_name']); ?> on <?php echo htmlspecialchars($dateText); ?> at <?php echo htmlspecialchars($startText); ?>">
                                    Book Slot
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <div class="small text-muted-ce border-top px-3 px-md-4 py-3">
        Submitted bookings will be placed in Pending status until confirmed or declined by the lecturer.
    </div>
</div>

<?php include '../includes/footer.php'; ?>
