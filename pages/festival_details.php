<?php
require_once '../auth/auth_check.php';
require_once '../config/connect.php';

$festival_id = (int)($_GET['id'] ?? 0);

$s = mysqli_prepare($connection, 'SELECT * FROM Festival WHERE Festival_ID=?');
mysqli_stmt_bind_param($s, 'i', $festival_id);
mysqli_stmt_execute($s);
$festival = mysqli_fetch_assoc(mysqli_stmt_get_result($s));

if (!$festival) {
    header('Location: festivals.php?error=Festival not found.');
    exit;
}

require_once '../includes/header.php';
?>

<article class="content-detail">
    <?php if ($festival['Image']): ?>
        <img src="<?=htmlspecialchars($festival['Image'])?>" alt="<?=htmlspecialchars($festival['Name'])?>">
    <?php endif; ?>

    <span class="tag"><?=htmlspecialchars($festival['Category'])?></span>
    <h1><?=htmlspecialchars($festival['Name'])?></h1>
    <p class="date"><?=date('d M Y', strtotime($festival['Date']))?></p>
    <p class="full-description"><?=nl2br(htmlspecialchars($festival['Description']))?></p>
    <p class="muted">Created by <?=htmlspecialchars($festival['AdminUser_ID'])?></p>
    
    <a class="button secondary" href="festivals.php">Back to calendar</a>
</article>

<?php require_once '../includes/footer.php'; ?>