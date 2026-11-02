<?php
// Database connection settings (XAMPP defaults)
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'consultease';

// Base URL of the app, used for links and CSS paths
define('BASE_URL', '/ConsultEase/');

// Make mysqli throw errors so problems are easy to spot
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    die('Database connection failed: ' . $e->getMessage()
        . '. Make sure MySQL is running in XAMPP and database.sql has been imported.');
}
