<?php
require_once '../auth/auth_check.php';
require_once '../config/connect.php';
require_once '../includes/social_helpers.php';

$user_id = $_SESSION['user_id'];
$edit    = null;

if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $s  = mysqli_prepare($connection, 'SELECT Post.*, Traditions.Festival_ID FROM Post JOIN Traditions ON Post.Post_ID=Traditions.Post_ID WHERE Post.Post_ID=? AND Post.User_ID=?');
    mysqli_stmt_bind_param($s, 'is', $id, $user_id);
    mysqli_stmt_execute($s);
    $edit = mysqli_fetch_assoc(mysqli_stmt_get_result($s));
}

$items     = mysqli_query($connection, 'SELECT Post.*, Festival.Name AS Festival_Name, Traditions.Festival_ID FROM Post JOIN Traditions ON Post.Post_ID=Traditions.Post_ID JOIN Festival ON Traditions.Festival_ID=Festival.Festival_ID ORDER BY Post.Post_ID DESC');
$festivals = mysqli_query($connection, 'SELECT Festival_ID, Name FROM Festival ORDER BY Date');

require_once '../includes/header.php';
?>

<div class="page-heading">
    <div>
        <p class="eyebrow">FEATURE 06</p>
        <h1>Cultural Traditions</h1>
        <p>History, customs, rituals, clothing, and significance.</p>
    </div>
    <button type="button" data-form-target="tradition-form">+ Share Tradition</button>
</div>

<section class="cards">
    <?php while ($item = mysqli_fetch_assoc($items)): ?>
        <article class="card">
            <?php if ($item['Image']): ?>
                <img src="<?=htmlspecialchars($item['Image'])?>" alt="<?=htmlspecialchars($item['Title'])?>">
            <?php else: ?>
                <div class="card-cover">Cultural tradition</div>
            <?php endif; ?>

            <span class="tag"><?=htmlspecialchars($item['Festival_Name'])?></span>
            <h2><?=htmlspecialchars($item['Title'])?></h2>
            <p><?=htmlspecialchars(strlen($item['Description']) > 110 ? substr($item['Description'], 0, 110).'...' : $item['Description'])?></p>
            
            <?php creator_button($connection, $user_id, $item['User_ID']); ?>
            
            <a class="details-link" href="tradition_details.php?id=<?=$item['Post_ID']?>">View details</a>
            <?php bookmark_button($connection, $user_id, 'Tradition', $item['Post_ID'], '/RonginBD/pages/traditions.php'); ?>

            <?php if ($item['User_ID'] === $user_id): ?>
                <div class="card-actions">
                    <a href="?edit=<?=$item['Post_ID']?>#tradition-form">Edit</a>
                    <form method="post" action="../actions/tradition_action.php">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="post_id" value="<?=$item['Post_ID']?>">
                        <button class="link danger" data-confirm="Are you sure?">Delete</button>
                    </form>
                </div>
            <?php endif; ?>
        </article>
    <?php endwhile; ?>
</section>

<section id="tradition-form" class="form-panel is-hidden">
    <h2><?=$edit ? 'Edit tradition' : 'Share a tradition'?></h2>
    <form method="post" action="../actions/tradition_action.php" enctype="multipart/form-data">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="post_id" value="<?=$edit['Post_ID'] ?? 0?>">
        
        <label>Title
            <input name="title" value="<?=htmlspecialchars($edit['Title'] ?? '')?>" required>
        </label>
        
        <label>Festival
            <select name="festival_id" required>
                <option value="">Choose festival</option>
                <?php while ($festival = mysqli_fetch_assoc($festivals)): ?>
                    <option value="<?=$festival['Festival_ID']?>" <?=($edit['Festival_ID'] ?? 0) == $festival['Festival_ID'] ? 'selected' : ''?>>
                        <?=htmlspecialchars($festival['Name'])?>
                    </option>
                <?php endwhile; ?>
            </select>
        </label>
        
        <label>Image
            <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp">
        </label>
        
        <label>Description
            <textarea name="description" required><?=htmlspecialchars($edit['Description'] ?? '')?></textarea>
        </label>
        
        <button>Save tradition</button>
    </form>
</section>

<?php require_once '../includes/footer.php'; ?>