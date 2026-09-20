<?php
function user_follows($connection, $follower_id, $following_id) {
    if ($follower_id === $following_id) return false;
    $stmt = mysqli_prepare($connection, 'SELECT 1 FROM User_Follow WHERE Follower_ID=? AND Following_ID=?');
    mysqli_stmt_bind_param($stmt, 'ss', $follower_id, $following_id);
    mysqli_stmt_execute($stmt);
    return mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0;
}

function is_bookmarked($connection, $user_id, $item_type, $item_id) {
    $stmt = mysqli_prepare($connection, 'SELECT 1 FROM Bookmark WHERE User_ID=? AND ItemType=? AND ItemID=?');
    mysqli_stmt_bind_param($stmt, 'ssi', $user_id, $item_type, $item_id);
    mysqli_stmt_execute($stmt);
    return mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0;
}

function bookmark_button($connection, $user_id, $item_type, $item_id, $return_page) {
    $saved = is_bookmarked($connection, $user_id, $item_type, $item_id);
    ?>
    <form class="bookmark-form" method="post" action="../actions/bookmark_action.php">
        <input type="hidden" name="item_type" value="<?= htmlspecialchars($item_type) ?>">
        <input type="hidden" name="item_id" value="<?= (int)$item_id ?>">
        <input type="hidden" name="return_page" value="<?= htmlspecialchars($return_page) ?>">
        <button class="star <?= $saved ? 'is-saved' : '' ?>" type="submit" title="<?= $saved ? 'Remove bookmark' : 'Save bookmark' ?>" aria-label="<?= $saved ? 'Remove bookmark' : 'Save bookmark' ?>"><?= $saved ? '★' : '☆' ?></button>
    </form>
    <?php
}

function creator_button($connection, $current_user, $creator) {
    $following = user_follows($connection, $current_user, $creator) ? '1' : '0';
    ?><button type="button" class="creator user-trigger" data-user="<?= htmlspecialchars($creator) ?>" data-following="<?= $following ?>">Posted by @<?= htmlspecialchars($creator) ?></button><?php
}
?>
