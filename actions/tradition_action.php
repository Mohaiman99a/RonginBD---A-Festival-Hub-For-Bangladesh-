<?php
require_once '../auth/auth_check.php';
require_once '../config/connect.php';
require_once 'upload_image.php';

$action  = $_POST['action'] ?? '';
$post_id = (int)($_POST['post_id'] ?? 0);
$user_id = $_SESSION['user_id'];

if ($action === 'save') {

    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $festival_id = (int)($_POST['festival_id'] ?? 0);

    if ($title === '' || $description === '' || $festival_id < 1) {
        header('Location: /RonginBD/pages/traditions.php?error=Complete all required tradition fields.');
        exit;
    }

    $old_image = '';
    if ($post_id) {
        $owner = mysqli_prepare($connection, 'SELECT Image FROM Post WHERE Post_ID=? AND User_ID=?');
        mysqli_stmt_bind_param($owner, 'is', $post_id, $user_id);
        mysqli_stmt_execute($owner);
        $old = mysqli_fetch_assoc(mysqli_stmt_get_result($owner));

        if (!$old) {
            header('Location: /RonginBD/pages/traditions.php?error=You do not own this contribution.');
            exit;
        }

        $old_image = $old['Image'];
    }

    $image = upload_image('image_file', $old_image);

    if ($image === false) {
        header('Location: /RonginBD/pages/traditions.php?error=Use a JPG, PNG, or WEBP image under 5 MB.');
        exit;
    }

    mysqli_begin_transaction($connection);

    try {
        if ($post_id) {
            $p = mysqli_prepare($connection, 'UPDATE Post SET Title=?, Image=?, Description=? WHERE Post_ID=? AND User_ID=?');
            mysqli_stmt_bind_param($p, 'sssis', $title, $image, $description, $post_id, $user_id);
            mysqli_stmt_execute($p);

            $t = mysqli_prepare($connection, 'UPDATE Traditions SET Festival_ID=? WHERE Post_ID=?');
            mysqli_stmt_bind_param($t, 'ii', $festival_id, $post_id);
            mysqli_stmt_execute($t);

        } else {
            $p = mysqli_prepare($connection, 'INSERT INTO Post (Title, Image, Description, User_ID) VALUES (?, ?, ?, ?)');
            mysqli_stmt_bind_param($p, 'ssss', $title, $image, $description, $user_id);
            mysqli_stmt_execute($p);
            $post_id = mysqli_insert_id($connection);

            $t = mysqli_prepare($connection, 'INSERT INTO Traditions (Post_ID, Festival_ID) VALUES (?, ?)');
            mysqli_stmt_bind_param($t, 'ii', $post_id, $festival_id);
            mysqli_stmt_execute($t);
        }

        mysqli_commit($connection);
        header('Location: /RonginBD/pages/traditions.php?message=Tradition saved.');

    } catch (Throwable $error) {
        mysqli_rollback($connection);
        header('Location: /RonginBD/pages/traditions.php?error=Tradition could not be saved.');
    }

    exit;
}

if ($action === 'delete') {
    $stmt = mysqli_prepare($connection, 'DELETE FROM Post WHERE Post_ID=? AND User_ID=?');
    mysqli_stmt_bind_param($stmt, 'is', $post_id, $user_id);
    mysqli_stmt_execute($stmt);

    header('Location: /RonginBD/pages/traditions.php?message=Tradition deleted.');
    exit;
}
?>