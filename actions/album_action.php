<?php
require_once '../auth/auth_check.php';
require_once '../config/connect.php';
require_once 'upload_image.php';

$action = $_POST['action'] ?? ($_GET['action'] ?? '');
$album_id = (int)($_POST['album_id'] ?? 0);



if ($action === 'save') {
    $title = trim($_POST['title']);
    $privacy = $_POST['privacy'];
    $shared_user = ltrim(trim($_POST['shared_user'] ?? ''), '@');

    if ($title === '' || !in_array($privacy, ['Private', 'Public'], true)) {
        header('Location: /RonginBD/pages/albums.php?error=Enter an album title and valid privacy option.');
        exit;
    }

    if ($privacy === 'Private' && $shared_user !== $_SESSION['user_id'] && $shared_user !== '') {
        $user_check = mysqli_prepare($connection, 'SELECT User_ID FROM `User` WHERE User_ID=?');
        mysqli_stmt_bind_param($user_check, 's', $shared_user);
        mysqli_stmt_execute($user_check);

        if (!mysqli_num_rows(mysqli_stmt_get_result($user_check))) {
            header('Location: /RonginBD/pages/albums.php?error=The entered username is not registered.');
            exit;
        }
    }

    if ($shared_user === $_SESSION['user_id']) {
        header('Location: /RonginBD/pages/albums.php?error=You cannot share an album with yourself.');
        exit;
    }

    mysqli_begin_transaction($connection);

    try {
        if ($album_id) {
            $owner = mysqli_prepare($connection, 'SELECT Album_ID FROM Album WHERE Album_ID=? AND User_ID=?');
            mysqli_stmt_bind_param($owner, 'is', $album_id, $_SESSION['user_id']);
            mysqli_stmt_execute($owner);

            if (!mysqli_num_rows(mysqli_stmt_get_result($owner))) {
                throw new Exception();
            }

            $stmt = mysqli_prepare($connection, 'UPDATE Album SET Title=?, Privacy=? WHERE Album_ID=? AND User_ID=?');
            mysqli_stmt_bind_param($stmt, 'ssis', $title, $privacy, $album_id, $_SESSION['user_id']);
            mysqli_stmt_execute($stmt);

            $clear = mysqli_prepare($connection, 'DELETE FROM Album_Access WHERE Album_ID=?');
            mysqli_stmt_bind_param($clear, 'i', $album_id);
            mysqli_stmt_execute($clear);
        } else {
            $stmt = mysqli_prepare($connection, 'INSERT INTO Album (Title, Privacy, User_ID) VALUES (?, ?, ?)');
            mysqli_stmt_bind_param($stmt, 'sss', $title, $privacy, $_SESSION['user_id']);
            mysqli_stmt_execute($stmt);
            $album_id = mysqli_insert_id($connection);
        }

        if ($privacy === 'Private' && $shared_user !== '') {
            $access = mysqli_prepare($connection, 'INSERT INTO Album_Access (User_ID, Album_ID) VALUES (?, ?)');
            mysqli_stmt_bind_param($access, 'si', $shared_user, $album_id);
            mysqli_stmt_execute($access);
        }

        mysqli_commit($connection);
        header('Location: /RonginBD/pages/albums.php?message=Album saved.');
    } catch (Throwable $error) {
        mysqli_rollback($connection);
        header('Location: /RonginBD/pages/albums.php?error=Album could not be saved.');
    }

    exit;
}



if ($action === 'photo') {
    $owner = mysqli_prepare($connection, 'SELECT Album_ID FROM Album WHERE Album_ID=? AND User_ID=?');
    mysqli_stmt_bind_param($owner, 'is', $album_id, $_SESSION['user_id']);
    mysqli_stmt_execute($owner);

    if (!mysqli_num_rows(mysqli_stmt_get_result($owner))) {
        header('Location: /RonginBD/pages/albums.php?error=You do not own this album.');
        exit;
    }

    $image = upload_image('image_file');

    if ($image === false || $image === '') {
        header('Location: /RonginBD/pages/albums.php?error=Choose a JPG, PNG, or WEBP image smaller than 5 MB.');
        exit;
    }

    $stmt = mysqli_prepare($connection, 'INSERT INTO Photos (Album_ID, Image) VALUES (?, ?)');
    mysqli_stmt_bind_param($stmt, 'is', $album_id, $image);
    mysqli_stmt_execute($stmt);

    header('Location: /RonginBD/pages/albums.php?message=Photo added.');
    exit;
}



if ($action === 'delete_photo') {
    $photo_id = (int)($_POST['photo_id'] ?? 0);

    $stmt = mysqli_prepare($connection,
        'SELECT Photos.Image
         FROM Photos
         JOIN Album ON Photos.Album_ID = Album.Album_ID
         WHERE Photos.Photo_ID = ? AND Album.User_ID = ?');
    mysqli_stmt_bind_param($stmt, 'is', $photo_id, $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    $photo = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$photo) {
        header('Location: /RonginBD/pages/albums.php?error=Photo not found or you do not own it.');
        exit;
    }

    $del = mysqli_prepare($connection, 'DELETE FROM Photos WHERE Photo_ID=?');
    mysqli_stmt_bind_param($del, 'i', $photo_id);
    mysqli_stmt_execute($del);

    delete_image($photo['Image']);

    header('Location: /RonginBD/pages/albums.php?message=Photo deleted.');
    exit;
}



if ($action === 'download_photo') {
    $photo_id = (int)($_GET['photo_id'] ?? 0);

    $stmt = mysqli_prepare($connection,
        'SELECT Photos.Image, Album.User_ID, Album.Privacy, Album.Album_ID
         FROM Photos
         JOIN Album ON Photos.Album_ID = Album.Album_ID
         WHERE Photos.Photo_ID = ?');
    mysqli_stmt_bind_param($stmt, 'i', $photo_id);
    mysqli_stmt_execute($stmt);
    $photo = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$photo) {
        http_response_code(404);
        exit('Photo not found.');
    }

    $has_access = ($photo['User_ID'] === $_SESSION['user_id']) || ($photo['Privacy'] === 'Public');

    if (!$has_access) {
        $check = mysqli_prepare($connection, 'SELECT 1 FROM Album_Access WHERE Album_ID=? AND User_ID=?');
        mysqli_stmt_bind_param($check, 'is', $photo['Album_ID'], $_SESSION['user_id']);
        mysqli_stmt_execute($check);
        $has_access = mysqli_num_rows(mysqli_stmt_get_result($check)) > 0;
    }

    if (!$has_access) {
        http_response_code(403);
        exit('Access denied.');
    }

    $file_path = __DIR__ . '/../assets/uploads/' . basename($photo['Image']);
    if (!is_file($file_path)) {
        http_response_code(404);
        exit('File is missing on the server.');
    }

    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
    header('Content-Length: ' . filesize($file_path));
    readfile($file_path);
    exit;
}



if ($action === 'delete') {
    $stmt = mysqli_prepare($connection, 'DELETE FROM Album WHERE Album_ID=? AND User_ID=?');
    mysqli_stmt_bind_param($stmt, 'is', $album_id, $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);

    header('Location: /RonginBD/pages/albums.php?message=Album deleted.');
    exit;
}
?>