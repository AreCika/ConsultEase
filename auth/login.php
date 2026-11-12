<?php
session_start();
require_once '../config/db.php';

$error = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter your email and password.';
    } else {

        // Find user by email using a prepared statement
        $stmt = $conn->prepare(
            "SELECT id, name, email, password, role
             FROM users
             WHERE email = ?
             LIMIT 1"
        );

        $stmt->bind_param('s', $email);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {

            // Prevent session fixation
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            // Redirect according to user role
            if ($user['role'] === 'student') {
                header('Location: ../student/available_slots.php');
                exit;
            } elseif ($user['role'] === 'lecturer') {
                header('Location: ../lecturer/list_slots.php');
                exit;
            }

            $error = 'Invalid account role.';

        } else {
            $error = 'Invalid email or password.';
        }
    }
}

$page_title = 'Login - ConsultEase';
include '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">

                <h2 class="text-center mb-3">Welcome Back</h2>
                <p class="text-center text-muted mb-4">
                    Login to your ConsultEase account
                </p>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="login.php">

                    <div class="mb-3">
                        <label for="email" class="form-label">
                            Email Address
                        </label>
                        <input
                            type="email"
                            class="form-control"
                            id="email"
                            name="email"
                            placeholder="Enter your email"
                            required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">
                            Password
                        </label>
                        <input
                            type="password"
                            class="form-control"
                            id="password"
                            name="password"
                            placeholder="Enter your password"
                            required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        Login
                    </button>

                </form>

            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
