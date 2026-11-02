<?php
require_once 'config/db.php';

// If we reach this line, the connection in config/db.php worked
$pageTitle = 'ConsultEase';
include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card p-4 text-center">
            <h1 class="fw-bold mb-2">ConsultEase</h1>
            <p class="text-muted-ce mb-4">Book consultation sessions with your lecturers.</p>

            <div class="alert alert-success mb-0" role="status">
                <strong>Database connected.</strong>
                Connected to "<?php echo htmlspecialchars($db_name); ?>" on <?php echo htmlspecialchars($db_host); ?>.
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
