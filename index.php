<?php session_start(); header('Location: '.(isset($_SESSION['user_id']) ? '/RonginBD/pages/dashboard.php' : '/RonginBD/auth/login.php')); exit; ?>
