<?php
require_once '../auth/auth_check.php';
require_once '../config/connect.php';
require_once '../includes/social_helpers.php';

$id = (int)($_GET['id'] ?? 0);
$sql = 'SELECT Post.Post_ID, Post.Title AS Post_Title, Post.Image AS Post_Image,
               Post.Description AS Post_Description, Post.User_ID, Shopping.Category,
               Promote.Link, Promote.Cost, Promote.Phone
        FROM Post
        JOIN Shopping ON Post.Post_ID=Shopping.Post_ID
        JOIN Promote ON Post.Post_ID=Promote.Post_ID
        WHERE Post.Post_ID=?';
$s = mysqli_prepare($connection, $sql);
mysqli_stmt_bind_param($s, 'i', $id);
mysqli_stmt_execute($s);
$item = mysqli_fetch_assoc(mysqli_stmt_get_result($s));
if (!$item) { header('Location: shopping.php?error=Listing not found.'); exit; }
require_once '../includes/header.php';
?>
<article class="content-detail">
    <?php if ($item['Post_Image']): ?><img src="<?= htmlspecialchars($item['Post_Image']) ?>" alt="<?= htmlspecialchars($item['Post_Title']) ?>"><?php endif; ?>
    <span class="tag"><?= htmlspecialchars($item['Category']) ?></span>
    <h1><?= htmlspecialchars($item['Post_Title']) ?></h1>
    <p class="full-description"><?= nl2br(htmlspecialchars($item['Post_Description'])) ?></p>
    <?php if ($item['Cost'] !== null): ?><p>Cost: ৳<?= htmlspecialchars($item['Cost']) ?></p><?php endif; ?>
    <?php if ($item['Link']): ?><p><a href="<?= htmlspecialchars($item['Link']) ?>" target="_blank" rel="noopener">Visit seller link</a></p><?php endif; ?>
    <?php if ($item['Phone']): ?><p><a href="tel:<?= htmlspecialchars($item['Phone']) ?>">Call <?= htmlspecialchars($item['Phone']) ?></a></p><?php endif; ?>
    <?php creator_button($connection, $_SESSION['user_id'], $item['User_ID']); bookmark_button($connection, $_SESSION['user_id'], 'Shopping', $item['Post_ID'], '/RonginBD/pages/shopping_details.php?id='.$item['Post_ID']); ?>
</article>
<?php require_once '../includes/footer.php'; ?>
