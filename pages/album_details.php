<?php
require_once '../auth/auth_check.php';
require_once '../config/connect.php';
$album_id = (int)($_GET['id'] ?? 0);
$user_id = $_SESSION['user_id'];
$sql = 'SELECT Album.* 
        FROM Album 
        LEFT JOIN Album_Access ON Album.Album_ID=Album_Access.Album_ID 
        WHERE Album.Album_ID=? AND (Album.User_ID=? OR Album.Privacy="Public" OR Album_Access.User_ID=?)';
$s = mysqli_prepare($connection, $sql);
mysqli_stmt_bind_param($s, 'iss', $album_id, $user_id, $user_id);
mysqli_stmt_execute($s);
$album = mysqli_fetch_assoc(mysqli_stmt_get_result($s));
if (!$album) {
    header('Location: albums.php?error=You do not have access to that album.');
    exit;
}
$photos = mysqli_prepare($connection, 'SELECT * FROM Photos WHERE Album_ID=? ORDER BY Photo_ID DESC');
mysqli_stmt_bind_param($photos, 'i', $album_id);
mysqli_stmt_execute($photos);
$photo_list = mysqli_stmt_get_result($photos);
require_once '../includes/header.php';
?>
<div class="page-heading">
    <div>
        <p class="eyebrow">FESTIVAL MEMORIES</p>
        <h1><?= htmlspecialchars($album['Title']) ?></h1>
        <p><?= htmlspecialchars($album['Privacy']) ?> album</p>
    </div>
    <a class="secondary button" href="albums.php">Back to albums</a>
</div>
<section class="photo-grid">
    <?php while ($photo = mysqli_fetch_assoc($photo_list)): ?>
        <div class="photo-item">
            <img src="<?= htmlspecialchars($photo['Image']) ?>" alt="Album memory">

            <div class="photo-actions">
                <a class="secondary button"
                   href="../actions/album_action.php?action=download_photo&photo_id=<?= $photo['Photo_ID'] ?>">
                    Download
                </a>

                <?php if ($album['User_ID'] === $user_id): ?>
                    <form class="inline" method="post" action="../actions/album_action.php">
                        <input type="hidden" name="action" value="delete_photo">
                        <input type="hidden" name="photo_id" value="<?= $photo['Photo_ID'] ?>">
                        <button class="link danger" data-confirm="Delete this photo?">Delete</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endwhile; ?>
</section>
<?php require_once '../includes/footer.php'; ?>