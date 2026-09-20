<?php
require_once '../auth/auth_check.php';
require_once '../config/connect.php';
require_once 'upload_image.php';

$action  = $_POST['action'] ?? '';
$post_id = (int)($_POST['post_id'] ?? 0);
$user_id = $_SESSION['user_id'];

if ($action === 'save') {

    $title       = trim($_POST['title']);
    $description = trim($_POST['description']);
    $date        = $_POST['date'];
    $time        = $_POST['time'];
    $location    = trim($_POST['location']);
    $festival_id = (int)$_POST['festival_id'];

    if ($title === '' || $description === '' || $date === '' || $time === '' || $location === '' || $festival_id < 1) {
        header('Location: /RonginBD/pages/events.php?error=Complete all required event fields.');
        exit;
    }

    if ($post_id) {
        $owner = mysqli_prepare($connection, 'SELECT Post.Image FROM Post WHERE Post_ID=? AND User_ID=?');
        mysqli_stmt_bind_param($owner, 'is', $post_id, $user_id);
        mysqli_stmt_execute($owner);
        $old = mysqli_fetch_assoc(mysqli_stmt_get_result($owner));

        if (!$old) {
            header('Location: /RonginBD/pages/events.php?error=You do not own this event.');
            exit;
        }

        $image = upload_image('image_file', $old['Image']);
    } else {
        $image = upload_image('image_file');
    }

    if ($image === false) {
        header('Location: /RonginBD/pages/events.php?error=Use a JPG, PNG, or WEBP image under 5 MB.');
        exit;
    }

    mysqli_begin_transaction($connection);

    try {
        if ($post_id) {
            $p = mysqli_prepare($connection, 'UPDATE Post SET Title=?, Image=?, Description=? WHERE Post_ID=? AND User_ID=?');
            mysqli_stmt_bind_param($p, 'sssis', $title, $image, $description, $post_id, $user_id);
            mysqli_stmt_execute($p);

            $e = mysqli_prepare($connection, 'UPDATE Event SET Title=?, Description=?, Time=?, Date=?, Location=?, Festival_ID=? WHERE Post_ID=?');
            mysqli_stmt_bind_param($e, 'sssssii', $title, $description, $time, $date, $location, $festival_id, $post_id);
            mysqli_stmt_execute($e);

        } else {
            $p = mysqli_prepare($connection, 'INSERT INTO Post (Title, Image, Description, User_ID) VALUES (?, ?, ?, ?)');
            mysqli_stmt_bind_param($p, 'ssss', $title, $image, $description, $user_id);
            mysqli_stmt_execute($p);
            $post_id = mysqli_insert_id($connection);

            $e = mysqli_prepare($connection, 'INSERT INTO Event (Post_ID, Title, Description, Time, Date, Location, Festival_ID) VALUES (?, ?, ?, ?, ?, ?, ?)');
            mysqli_stmt_bind_param($e, 'isssssi', $post_id, $title, $description, $time, $date, $location, $festival_id);
            mysqli_stmt_execute($e);
        }

        mysqli_commit($connection);
        header('Location: /RonginBD/pages/events.php?message=Event saved.');

    } catch (Throwable $error) {
        mysqli_rollback($connection);
        header('Location: /RonginBD/pages/events.php?error=Event could not be saved.');
    }

    exit;
}

if ($action === 'delete') {
    $stmt = mysqli_prepare($connection, 'DELETE FROM Post WHERE Post_ID=? AND User_ID=?');
    mysqli_stmt_bind_param($stmt, 'is', $post_id, $user_id);
    mysqli_stmt_execute($stmt);

    header('Location: /RonginBD/pages/events.php?message=Event deleted.');
    exit;
}
?>