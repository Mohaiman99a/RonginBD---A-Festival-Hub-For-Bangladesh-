<?php
require_once '../auth/auth_check.php';
require_once '../config/connect.php';
require_once '../includes/social_helpers.php';

$id = (int)($_GET['id'] ?? 0);
$sql = 'SELECT Post.Post_ID, Post.Title AS Post_Title, Post.Image AS Post_Image,
               Post.Description AS Post_Description, Post.User_ID,
               Travel.FromLocation, Travel.ToLocation, Travel.Date, Travel.Time,
               Promote.Link, Promote.Cost, Promote.Phone
        FROM Post
        JOIN Travel ON Post.Post_ID=Travel.Post_ID
        LEFT JOIN Promote ON Post.Post_ID=Promote.Post_ID
        WHERE Post.Post_ID=?';
$s = mysqli_prepare($connection, $sql);
mysqli_stmt_bind_param($s, 'i', $id);
mysqli_stmt_execute($s);
$item = mysqli_fetch_assoc(mysqli_stmt_get_result($s));
if (!$item) { header('Location: travel.php?error=Travel post not found.'); exit; }
require_once '../includes/header.php';
?>
<article class="content-detail">
    <?php if ($item['Post_Image']): ?><img src="<?= htmlspecialchars($item['Post_Image']) ?>" alt="<?= htmlspecialchars($item['Post_Title']) ?>"><?php endif; ?>
    <h1><?= htmlspecialchars($item['Post_Title']) ?></h1>
    <p class="date"><?= htmlspecialchars($item['FromLocation'].' → '.$item['ToLocation'].' · '.$item['Date'].' '.$item['Time']) ?></p>
    <p class="full-description"><?= nl2br(htmlspecialchars($item['Post_Description'])) ?></p>
    <?php if ($item['Cost'] !== null): ?><p>Fare: ৳<?= htmlspecialchars($item['Cost']) ?></p><?php endif; ?>
    <?php if ($item['Link']): ?><p><a href="<?= htmlspecialchars($item['Link']) ?>" target="_blank" rel="noopener">Open booking link</a></p><?php endif; ?>
    <?php if ($item['Phone']): ?><p><a href="tel:<?= htmlspecialchars($item['Phone']) ?>">Call <?= htmlspecialchars($item['Phone']) ?></a></p><?php endif; ?>
    <?php creator_button($connection, $_SESSION['user_id'], $item['User_ID']); bookmark_button($connection, $_SESSION['user_id'], 'Travel', $item['Post_ID'], '/RonginBD/pages/travel_details.php?id='.$item['Post_ID']); ?>
</article>
<?php require_once '../includes/footer.php'; ?>
