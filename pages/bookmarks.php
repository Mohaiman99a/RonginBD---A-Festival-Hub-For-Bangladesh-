<?php
require_once '../auth/auth_check.php';
require_once '../config/connect.php';

$user_id = $_SESSION['user_id'];
$section = $_GET['section'] ?? 'bookmarks';

if ($section === 'following') {
    $sql = 'SELECT `User`.User_ID, First_Name, Last_Name FROM User_Follow JOIN `User` ON User_Follow.Following_ID=`User`.User_ID WHERE User_Follow.Follower_ID=? ORDER BY First_Name, Last_Name';
    $s = mysqli_prepare($connection, $sql);
    mysqli_stmt_bind_param($s, 's', $user_id);
    mysqli_stmt_execute($s);
    $following = mysqli_stmt_get_result($s);
} else {
    $sql = 'SELECT Bookmark.ItemType, Bookmark.ItemID, Festival.Name AS Festival_Name, Festival.Image AS Festival_Image, Post.Title, Post.Image, Post.Description FROM Bookmark LEFT JOIN Festival ON Bookmark.ItemType="Festival" AND Bookmark.ItemID=Festival.Festival_ID LEFT JOIN Post ON Bookmark.ItemType<>"Festival" AND Bookmark.ItemID=Post.Post_ID WHERE Bookmark.User_ID=? ORDER BY Bookmark.Bookmark_ID DESC';
    $s = mysqli_prepare($connection, $sql);
    mysqli_stmt_bind_param($s, 's', $user_id);
    mysqli_stmt_execute($s);
    $bookmarks = mysqli_stmt_get_result($s);
}

require_once '../includes/header.php';
?>

<div class="page-heading">
    <div>
        <p class="eyebrow">SAVED FOR YOU</p>
        <h1>Bookmarks</h1>
        <p>Keep your saved content and community connections together.</p>
    </div>
</div>

<div class="tabs">
    <a class="<?=$section!=='following'?'active':''?>" href="?section=bookmarks">Bookmarks</a>
    <a class="<?=$section==='following'?'active':''?>" href="?section=following">Following</a>
</div>

<?php if ($section === 'following'): ?>
    <section class="form-panel">
        <h2>Following</h2>
        <div class="user-list">
            <?php while ($user = mysqli_fetch_assoc($following)): ?>
                <div class="user-row">
                    <button type="button" class="creator user-trigger" data-user="<?=htmlspecialchars($user['User_ID'])?>">
                        @<?=htmlspecialchars($user['User_ID'])?>
                    </button>
                    <span><?=htmlspecialchars($user['First_Name'].' '.$user['Last_Name'])?></span>
                </div>
            <?php endwhile; ?>
        </div>
    </section>
<?php else: ?>
    <section class="cards">
        <?php while ($bookmark = mysqli_fetch_assoc($bookmarks)): 
            $title = $bookmark['ItemType'] === 'Festival' ? $bookmark['Festival_Name'] : $bookmark['Title'];
            $image = $bookmark['ItemType'] === 'Festival' ? $bookmark['Festival_Image'] : $bookmark['Image'];
        ?>
            <article class="card">
                <?php if ($image): ?>
                    <img src="<?=htmlspecialchars($image)?>" alt="<?=htmlspecialchars($title)?>">
                <?php else: ?>
                    <div class="card-cover">Saved content</div>
                <?php endif; ?>
                
                <span class="tag"><?=htmlspecialchars($bookmark['ItemType'])?></span>
                <h2><?=htmlspecialchars($title ?? 'Saved item')?></h2>
                <p><?=htmlspecialchars(strlen($bookmark['Description'] ?? '') > 110 ? substr($bookmark['Description'], 0, 110).'...' : ($bookmark['Description'] ?? ''))?></p>
                
                <form class="bookmark-form" method="post" action="../actions/bookmark_action.php">
                    <input type="hidden" name="item_type" value="<?=htmlspecialchars($bookmark['ItemType'])?>">
                    <input type="hidden" name="item_id" value="<?=$bookmark['ItemID']?>">
                    <input type="hidden" name="return_page" value="/RonginBD/pages/bookmarks.php">
                    <button class="star">★ Remove</button>
                </form>
            </article>
        <?php endwhile; ?>
    </section>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>