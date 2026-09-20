<?php
require_once '../auth/auth_check.php';
require_once '../config/connect.php';
require_once '../includes/social_helpers.php';

$edit     = null;
$category = $_GET['category'] ?? '';

if (isset($_GET['edit']) && $is_admin) {
    $id = (int)$_GET['edit'];
    $s  = mysqli_prepare($connection, 'SELECT * FROM Festival WHERE Festival_ID=? AND AdminUser_ID=?');
    mysqli_stmt_bind_param($s, 'is', $id, $_SESSION['user_id']);
    mysqli_stmt_execute($s);
    $edit = mysqli_fetch_assoc(mysqli_stmt_get_result($s));
}

if ($category) {
    $s = mysqli_prepare($connection, 'SELECT * FROM Festival WHERE Category=? ORDER BY Date');
    mysqli_stmt_bind_param($s, 's', $category);
    mysqli_stmt_execute($s);
    $festivals = mysqli_stmt_get_result($s);
} else {
    $festivals = mysqli_query($connection, 'SELECT * FROM Festival ORDER BY Date');
}

require_once '../includes/header.php';
?>

<div class="page-heading">
    <div>
        <p class="eyebrow">FEATURE 01</p>
        <h1>Festival Calendar</h1>
        <p>Discover celebrations happening across Bangladesh.</p>
    </div>
    <?php if ($is_admin): ?>
        <a class="button" href="#festival-form">Add festival</a>
    <?php endif; ?>
</div>

<form class="filter" method="get">
    <label>Category
        <select name="category">
            <option value="">All categories</option>
            <?php foreach (['Religion Based', 'Culture Based', 'Community'] as $c): ?>
                <option value="<?=$c?>" <?=$category === $c ? 'selected' : ''?>><?=$c?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <button>Filter</button>
</form>

<section class="cards">
    <?php while ($festival = mysqli_fetch_assoc($festivals)): ?>
        <article class="card">
            <?php if ($festival['Image']): ?>
                <img src="<?=htmlspecialchars($festival['Image'])?>" alt="<?=htmlspecialchars($festival['Name'])?>">
            <?php else: ?>
                <div class="card-cover">Festival calendar</div>
            <?php endif; ?>

            <span class="tag"><?=htmlspecialchars($festival['Category'])?></span>
            <h2><?=htmlspecialchars($festival['Name'])?></h2>
            <p class="date"><?=date('d M Y', strtotime($festival['Date']))?></p>
            <p><?=htmlspecialchars(strlen($festival['Description']) > 125 ? substr($festival['Description'], 0, 125).'...' : $festival['Description'])?></p>
            
            <?php creator_button($connection, $_SESSION['user_id'], $festival['AdminUser_ID']); ?>
            
            <div class="card-footer">
                <a class="details-link" href="festival_details.php?id=<?=$festival['Festival_ID']?>">View details</a>
                <?php bookmark_button($connection, $_SESSION['user_id'], 'Festival', $festival['Festival_ID'], '/RonginBD/pages/festivals.php'); ?>
            </div>

            <?php if ($is_admin && $festival['AdminUser_ID'] === $_SESSION['user_id']): ?>
                <div class="owner-actions">
                    <a href="?edit=<?=$festival['Festival_ID']?>#festival-form">Edit</a>
                    <form method="post" action="../actions/festival_action.php">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="festival_id" value="<?=$festival['Festival_ID']?>">
                        <button class="link danger" data-confirm="Are you sure?">Delete</button>
                    </form>
                </div>
            <?php endif; ?>
        </article>
    <?php endwhile; ?>
</section>

<?php if ($is_admin): ?>
    <section id="festival-form" class="form-panel">
        <h2><?=$edit ? 'Edit festival' : 'Add a festival'?></h2>
        <form method="post" action="../actions/festival_action.php" enctype="multipart/form-data">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="festival_id" value="<?=$edit['Festival_ID'] ?? 0?>">
            <input type="hidden" name="existing_image" value="<?=htmlspecialchars($edit['Image'] ?? '')?>">
            
            <label>Festival name
                <input name="name" value="<?=htmlspecialchars($edit['Name'] ?? '')?>" required>
            </label>
            
            <label>Category
                <select name="category">
                    <?php foreach (['Religion Based', 'Culture Based', 'Community'] as $c): ?>
                        <option value="<?=$c?>" <?=($edit['Category'] ?? '') === $c ? 'selected' : ''?>><?=$c?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            
            <label>Date
                <input type="date" name="date" value="<?=$edit['Date'] ?? ''?>" required>
            </label>
            
            <label>Festival image
                <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp">
            </label>
            
            <label>Description
                <textarea name="description" required><?=htmlspecialchars($edit['Description'] ?? '')?></textarea>
            </label>
            
            <button>Save festival</button>
        </form>
    </section>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>