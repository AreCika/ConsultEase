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

<style>
    /* Colours taken from the team UI mockup (docs/ConsultEase UI Mockup.pdf) */
    .slots-page {
        --slots-blue: #1d4ed8;
        --slots-blue-hover: #1e40af;
        --slots-blue-soft: #dbeafe;
        --slots-surface: #ffffff;
        --slots-header-bg: #f8fafc;
        --slots-pill-bg: #f1f5f9;
        --slots-border: #e2e8f0;
    }
    .slots-card {
        background-color: var(--slots-surface);
        border: 1px solid var(--slots-border);
    }
    /* The table only needs to scroll sideways on small screens, never up and down */
    .slots-card .table-responsive {
        overflow-y: hidden;
    }
    .slots-table th {
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--ce-muted);
        background-color: var(--slots-header-bg);
        white-space: nowrap;
    }
    .slots-table td {
        white-space: nowrap;
        border-color: var(--slots-border);
    }
    /* Keeps dates and times lined up in their columns */
    .slots-table .tabular {
        font-variant-numeric: tabular-nums;
    }
    .venue-pill {
        display: inline-block;
        padding: 0.25rem 0.65rem;
        border-radius: 6px;
        background-color: var(--slots-pill-bg);
        border: 1px solid var(--slots-border);
        color: var(--ce-text);
        font-size: 0.875rem;
    }
    .count-badge {
        background-color: var(--slots-blue-soft);
        color: var(--slots-blue);
        font-weight: 600;
        font-size: 0.8rem;
    }
    /* Bootstrap button variables, so hover and the focus ring use the mockup blue */
    .btn-book {
        --bs-btn-bg: var(--slots-blue);
        --bs-btn-border-color: var(--slots-blue);
        --bs-btn-color: #fff;
        --bs-btn-hover-bg: var(--slots-blue-hover);
        --bs-btn-hover-border-color: var(--slots-blue-hover);
        --bs-btn-hover-color: #fff;
        --bs-btn-active-bg: var(--slots-blue-hover);
        --bs-btn-active-border-color: var(--slots-blue-hover);
        --bs-btn-active-color: #fff;
        --bs-btn-focus-shadow-rgb: 29, 78, 216;
        font-weight: 600;
        min-height: 44px;
        display: inline-flex;
        align-items: center;
        padding: 0 1rem;
    }
</style>

<div class="slots-page">
    <div class="mb-4">
        <h1 class="h3 fw-bold mb-1">Student Dashboard</h1>
        <p class="text-muted-ce mb-0">View available consultation slots and schedule an appointment.</p>
    </div>

    <div class="card slots-card">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 p-3 p-md-4">
            <div>
                <h2 class="h5 fw-bold mb-1">Available Consultation Slots</h2>
                <p class="small text-muted-ce mb-0">Showing upcoming open slots for all lecturers.</p>
            </div>
            <span class="badge rounded-pill count-badge">
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
                <table class="table align-middle mb-0 slots-table" aria-label="Available consultation slots, sorted by date and time">
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
                                <td><span class="venue-pill"><?php echo htmlspecialchars($row['venue']); ?></span></td>
                                <td class="text-end pe-4">
                                    <a href="book_slot.php?slot_id=<?php echo (int) $row['id']; ?>"
                                       class="btn btn-book"
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
</div>

<?php include '../includes/footer.php'; ?>
