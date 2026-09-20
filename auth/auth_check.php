<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) { header('Location: /RonginBD/auth/login.php?error=Please log in first.'); exit; }
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'Admin';
?>
