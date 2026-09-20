<?php
$db_host = '127.0.0.1';
$db_user = 'root';
$db_password = '';
$db_name = 'ronginbd';
$db_port = 3307. ; 

$connection = mysqli_connect($db_host, $db_user, $db_password, $db_name, $db_port);
if (!$connection) {
    die('Database connection failed. Check config/connect.php for the MySQL host, port, user, and password.');
}
mysqli_set_charset($connection, 'utf8mb4');
?>
