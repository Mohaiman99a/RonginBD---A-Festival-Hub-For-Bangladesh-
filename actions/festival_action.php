<?php
require_once '../auth/auth_check.php';
require_once '../config/connect.php';
require_once 'upload_image.php';

if ($_SESSION['role'] !== 'Admin') {
    header('Location: /RonginBD/pages/festivals.php?error=Only Admin can manage festivals.');
    exit;
}

$action = $_POST['action'] ?? '';
$festival_id = (int)($_POST['festival_id'] ?? 0);


if ($action === 'save') {
    $name = trim($_POST['name']);
    $category = $_POST['category'];
    $date = $_POST['date'];
    $description = trim($_POST['description']);
    $existing_image = $_POST['existing_image'] ?? '';

    if ($name === '' || $date === '' || $description === '' || !in_array($category, ['Religion Based', 'Culture Based', 'Community'], true)) {
        header('Location: /RonginBD/pages/festivals.php?error=Enter the required fields and choose a valid category.');
        exit;
    }

    $image = upload_image('image_file', $existing_image);

    if ($image === false) {
        header('Location: /RonginBD/pages/festivals.php?error=Upload a JPG, PNG, or WEBP image smaller than 5 MB.');
        exit;
    }

    if ($festival_id) {
        $stmt = mysqli_prepare($connection, 'UPDATE Festival SET Name=?, Category=?, Date=?, Image=?, Description=? WHERE Festival_ID=? AND AdminUser_ID=?');
        mysqli_stmt_bind_param($stmt, 'sssssis', $name, $category, $date, $image, $description, $festival_id, $_SESSION['user_id']);
    } else {
        $stmt = mysqli_prepare($connection, 'INSERT INTO Festival (Name, Category, Date, Image, Description, AdminUser_ID) VALUES (?, ?, ?, ?, ?, ?)');
        mysqli_stmt_bind_param($stmt, 'ssssss', $name, $category, $date, $image, $description, $_SESSION['user_id']);
    }

    mysqli_stmt_execute($stmt);
    header('Location: /RonginBD/pages/festivals.php?message=Festival saved.');
    exit;
}


if ($action === 'delete') {
    $stmt = mysqli_prepare($connection, 'DELETE FROM Festival WHERE Festival_ID=? AND AdminUser_ID=?');
    mysqli_stmt_bind_param($stmt, 'is', $festival_id, $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);

    header('Location: /RonginBD/pages/festivals.php?message=Festival deleted.');
    exit;
}
?>