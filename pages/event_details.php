<?php
require_once '../auth/auth_check.php';
require_once '../config/connect.php';

$id = (int)($_GET['id'] ?? 0);

$s = mysqli_prepare($connection, 'SELECT Post.*, Event.Time, Event.Date, Event.Location, Festival.Name AS Festival_Name FROM Post JOIN Event ON Post.Post_ID=Event.Post_ID JOIN Festival ON Event.Festival_ID=Festival.Festival_ID WHERE Post.Post_ID=?');
mysqli_stmt_bind_param($s, 'i', $id);
mysqli_stmt_execute($s);
$event = mysqli_fetch_assoc(mysqli_stmt_get_result($s));

if (!$event) {
    header('Location: events.php?error=Event not found.');
    exit;
}

require_once '../includes/header.php';
?>

<article class="content-detail">
    <?php if ($event['Image']): ?>
        <img src="<?=htmlspecialchars($event['Image'])?>" alt="<?=htmlspecialchars($event['Title'])?>">
    <?php endif; ?>

    <span class="tag"><?=htmlspecialchars($event['Festival_Name'])?></span>
    <h1><?=htmlspecialchars($event['Title'])?></h1>
    <p><?=htmlspecialchars($event['Date'].' · '.$event['Time'].' · '.$event['Location'])?></p>
    <p><?=nl2br(htmlspecialchars($event['Description']))?></p>
    
    <button type="button" class="creator user-trigger" data-user="<?=htmlspecialchars($event['User_ID'])?>">
        @<?=htmlspecialchars($event['User_ID'])?>
    </button>
</article>

<?php require_once '../includes/footer.php'; ?>