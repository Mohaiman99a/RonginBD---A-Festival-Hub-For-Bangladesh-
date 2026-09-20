<?php
require_once '../auth/auth_check.php';
require_once '../config/connect.php';

$user_id = $_SESSION['user_id'];
$edit = null;
$shared_user = '';

if (isset($_GET['edit'])) {
    $album_id = (int)$_GET['edit'];
    $s = mysqli_prepare($connection, 'SELECT * FROM Album WHERE Album_ID=? AND User_ID=?');
    mysqli_stmt_bind_param($s, 'is', $album_id, $user_id);
    mysqli_stmt_execute($s);
    $edit = mysqli_fetch_assoc(mysqli_stmt_get_result($s));

    if ($edit) {
        $access = mysqli_prepare($connection, 'SELECT User_ID FROM Album_Access WHERE Album_ID=? LIMIT 1');
        mysqli_stmt_bind_param($access, 'i', $album_id);
        mysqli_stmt_execute($access);
        $access_user = mysqli_fetch_assoc(mysqli_stmt_get_result($access));
        $shared_user = $access_user['User_ID'] ?? '';
    }
}

$sql = 'SELECT Album.*, COUNT(Photos.Photo_ID) AS photo_count, MIN(Photos.Image) AS cover_image 
        FROM Album 
        LEFT JOIN Photos ON Album.Album_ID=Photos.Album_ID 
        LEFT JOIN Album_Access ON Album.Album_ID=Album_Access.Album_ID 
        WHERE Album.User_ID=? OR Album.Privacy="Public" OR Album_Access.User_ID=? 
        GROUP BY Album.Album_ID 
        ORDER BY Album.Album_ID DESC';

$s = mysqli_prepare($connection, $sql);
mysqli_stmt_bind_param($s, 'ss', $user_id, $user_id);
mysqli_stmt_execute($s);
$albums = mysqli_stmt_get_result($s);

require_once '../includes/header.php';
?>


<div class="page-heading">
    <div>
        <p class="eyebrow">FEATURE 07</p>
        <h1>Festival Memories</h1>
        <p>Keep your festival moments in one place.</p>
    </div>
    <button type="button" data-form-target="album-form">+ Create Album</button>
</div>


<section class="cards">
    <?php while ($album = mysqli_fetch_assoc($albums)): ?>
        <article class="card">
            <?php if ($album['cover_image']): ?>
                <img src="<?= htmlspecialchars($album['cover_image']) ?>" alt="<?= htmlspecialchars($album['Title']) ?>">
            <?php else: ?>
                <div class="card-cover">Festival memories</div>
            <?php endif; ?>

            <span class="tag"><?= htmlspecialchars($album['Privacy']) ?></span>
            <h2><?= htmlspecialchars($album['Title']) ?></h2>
            <p class="muted"><?= $album['photo_count'] ?> photo(s)</p>
            <a class="details-link" href="album_details.php?id=<?= $album['Album_ID'] ?>">View album</a>

            <?php if ($album['User_ID'] === $user_id): ?>
                <a href="?edit=<?= $album['Album_ID'] ?>#album-form">Edit</a>

                <form class="inline" method="post" action="../actions/album_action.php">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="album_id" value="<?= $album['Album_ID'] ?>">
                    <button class="link danger" data-confirm="Delete album and its photos?">Delete</button>
                </form>

                <details>
                    <summary>Add a photo</summary>
                    <form method="post" action="../actions/album_action.php" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="photo">
                        <input type="hidden" name="album_id" value="<?= $album['Album_ID'] ?>">
                        <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp" required>
                        <button>Add photo</button>
                    </form>
                </details>
            <?php endif; ?>
        </article>
    <?php endwhile; ?>
</section>


<section id="album-form" class="form-panel <?= $edit ? '' : 'is-hidden' ?>">
    <h2><?= $edit ? 'Edit album' : 'Create an album' ?></h2>

    <form method="post" action="../actions/album_action.php">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="album_id" value="<?= $edit['Album_ID'] ?? 0 ?>">

        <label>
            Album title 
            <input name="title" value="<?= htmlspecialchars($edit['Title'] ?? '') ?>" required>
        </label>

        <label>
            Privacy 
            <select name="privacy">
                <option value="Private" <?= ($edit['Privacy'] ?? '') === 'Private' ? 'selected' : '' ?>>Private</option>
                <option value="Public" <?= ($edit['Privacy'] ?? '') === 'Public' ? 'selected' : '' ?>>Public</option>
            </select>
        </label>

        <label class="share-user">
            Share with @username 
            <input name="shared_user" value="<?= htmlspecialchars($shared_user) ?>" placeholder="username">
            <small>Only used for a Private album. Leave blank for creator-only access.</small>
        </label>

        <button>Save album</button>
    </form>
</section>


<?php require_once '../includes/footer.php'; ?>