<?php
require_once '../auth/auth_check.php';
require_once '../config/connect.php';
require_once '../includes/social_helpers.php';

$id = (int)($_GET['id'] ?? 0);
$sql = 'SELECT Post.Post_ID, Post.Title AS Post_Title, Post.Image AS Post_Image,
               Post.Description AS Post_Description, Post.User_ID, Festival.Name AS Festival_Name
        FROM Post
        JOIN Traditions ON Post.Post_ID=Traditions.Post_ID
        JOIN Festival ON Traditions.Festival_ID=Festival.Festival_ID
        WHERE Post.Post_ID=?';
$s = mysqli_prepare($connection, $sql);
mysqli_stmt_bind_param($s, 'i', $id);
mysqli_stmt_execute($s);
$item = mysqli_fetch_assoc(mysqli_stmt_get_result($s));
if (!$item) { header('Location: traditions.php?error=Tradition not found.'); exit; }
require_once '../includes/header.php';
?>
<article class="content-detail">
    <?php if ($item['Post_Image']): ?><img src="<?= htmlspecialchars($item['Post_Image']) ?>" alt="<?= htmlspecialchars($item['Post_Title']) ?>"><?php endif; ?>
    <span class="tag"><?= htmlspecialchars($item['Festival_Name']) ?></span>
    <h1><?= htmlspecialchars($item['Post_Title']) ?></h1>
    <p class="full-description"><?= nl2br(htmlspecialchars($item['Post_Description'])) ?></p>
    <?php creator_button($connection, $_SESSION['user_id'], $item['User_ID']); bookmark_button($connection, $_SESSION['user_id'], 'Tradition', $item['Post_ID'], '/RonginBD/pages/tradition_details.php?id='.$item['Post_ID']); ?>
</article>
<?php require_once '../includes/footer.php'; ?>
