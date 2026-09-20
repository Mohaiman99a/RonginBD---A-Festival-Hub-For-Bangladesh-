<?php
require_once '../auth/auth_check.php';
require_once '../config/connect.php';

$target_user = trim($_POST['target_user'] ?? '');
$action = $_POST['action'] ?? '';
$return_page = $_POST['return_page'] ?? '/RonginBD/pages/dashboard.php';

if (strpos($return_page, '/RonginBD/pages/') !== 0) {
    $return_page = '/RonginBD/pages/dashboard.php';
}

if ($target_user === '' || $target_user === $_SESSION['user_id']) {
    header('Location: /RonginBD/pages/dashboard.php?error=You cannot follow yourself.');
    exit;
}


if ($action === 'follow') {
    $check = mysqli_prepare($connection, 'SELECT User_ID FROM `User` WHERE User_ID=?');
    mysqli_stmt_bind_param($check, 's', $target_user);
    mysqli_stmt_execute($check);

    if (!mysqli_num_rows(mysqli_stmt_get_result($check))) {
        header('Location: ' . $return_page . '?error=User not found.');
        exit;
    }

    $exists = mysqli_prepare($connection, 'SELECT Follower_ID FROM User_Follow WHERE Follower_ID=? AND Following_ID=?');
    mysqli_stmt_bind_param($exists, 'ss', $_SESSION['user_id'], $target_user);
    mysqli_stmt_execute($exists);

    if (mysqli_num_rows(mysqli_stmt_get_result($exists))) {
        header('Location: ' . $return_page . '?error=You already follow this user.');
        exit;
    }

    $stmt = mysqli_prepare($connection, 'INSERT INTO User_Follow (Follower_ID, Following_ID) VALUES (?, ?)');
    mysqli_stmt_bind_param($stmt, 'ss', $_SESSION['user_id'], $target_user);
    mysqli_stmt_execute($stmt);

    header('Location: ' . $return_page . '?message=User followed.');
    exit;
}


if ($action === 'unfollow') {
    $stmt = mysqli_prepare($connection, 'DELETE FROM User_Follow WHERE Follower_ID=? AND Following_ID=?');
    mysqli_stmt_bind_param($stmt, 'ss', $_SESSION['user_id'], $target_user);
    mysqli_stmt_execute($stmt);

    header('Location: ' . $return_page . '?message=User unfollowed.');
    exit;
}
?>
