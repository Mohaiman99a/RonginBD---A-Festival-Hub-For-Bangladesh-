<?php
require_once '../auth/auth_check.php';
require_once '../config/connect.php';

$item_type = $_POST['item_type'] ?? '';
$item_id = (int)($_POST['item_id'] ?? 0);
$return_page = $_POST['return_page'] ?? '/RonginBD/pages/bookmarks.php';
$user_id = $_SESSION['user_id'];

$allowed_types = ['Festival', 'Event', 'Recipe', 'Shopping', 'Travel', 'Tradition'];
if (!in_array($item_type, $allowed_types, true) || $item_id < 1 || strpos($return_page, '/RonginBD/pages/') !== 0) {
    header('Location: ' . $return_page . '?error=Invalid bookmark item.');
    exit;
}

$exists = mysqli_prepare($connection, 'SELECT Bookmark_ID FROM Bookmark WHERE ItemType=? AND ItemID=? AND User_ID=?');
mysqli_stmt_bind_param($exists, 'sis', $item_type, $item_id, $user_id);
mysqli_stmt_execute($exists);
$bookmark = mysqli_fetch_assoc(mysqli_stmt_get_result($exists));

if ($bookmark) {
    $delete = mysqli_prepare($connection, 'DELETE FROM Bookmark WHERE Bookmark_ID=? AND User_ID=?');
    mysqli_stmt_bind_param($delete, 'is', $bookmark['Bookmark_ID'], $user_id);
    mysqli_stmt_execute($delete);
    header('Location: ' . $return_page . '?message=Bookmark removed.');
} else {
    $insert = mysqli_prepare($connection, 'INSERT INTO Bookmark (ItemType, ItemID, User_ID) VALUES (?, ?, ?)');
    mysqli_stmt_bind_param($insert, 'sis', $item_type, $item_id, $user_id);
    mysqli_stmt_execute($insert);
    header('Location: ' . $return_page . '?message=Bookmark saved.');
}
exit;
?>
