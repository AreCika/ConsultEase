<?php
// Start session only if it has not been started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set page title
if (!isset($pageTitle)) {
    $pageTitle = 'ConsultEase';
}

// Check authenticated user
$isLoggedIn = isset($_SESSION['user_id'], $_SESSION['user_role']);
$userRole = $isLoggedIn ? $_SESSION['user_role'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?php echo htmlspecialchars($pageTitle); ?></title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        href="<?php echo BASE_URL; ?>assets/css/style.css"
        rel="stylesheet">
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark app-navbar">
        <div class="container">

            <a class="navbar-brand fw-bold"
               href="<?php echo BASE_URL; ?>">
                ConsultEase
            </a>

            <div class="d-flex align-items-center gap-3">

                <?php if ($isLoggedIn): ?>

                    <?php if ($userRole === 'student'): ?>

                        <a class="nav-link text-white"
                           href="<?php echo BASE_URL; ?>student/available_slots.php">
                            Available Slots
                        </a>

                        <a class="nav-link text-white"
                           href="<?php echo BASE_URL; ?>student/my_bookings.php">
                            My Bookings
                        </a>

                    <?php elseif ($userRole === 'lecturer'): ?>

                        <a class="nav-link text-white"
                           href="<?php echo BASE_URL; ?>lecturer/list_slots.php">
                            My Slots
                        </a>

                        <a class="nav-link text-white"
                           href="<?php echo BASE_URL; ?>lecturer/create_slot.php">
                            Create Slot
                        </a>

                        <a class="nav-link text-white"
                           href="<?php echo BASE_URL; ?>lecturer/booking_requests.php">
                            Booking Requests
                        </a>

                    <?php endif; ?>

                    <a class="btn btn-outline-light btn-sm"
                       href="<?php echo BASE_URL; ?>auth/logout.php">
                        Logout
                    </a>

                <?php else: ?>

                    <a class="btn btn-outline-light btn-sm"
                       href="<?php echo BASE_URL; ?>auth/login.php">
                        Login
                    </a>

                <?php endif; ?>

            </div>

        </div>
    </nav>

    <main class="container py-5">
