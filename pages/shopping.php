<?php
require_once '../auth/auth_check.php';
require_once '../config/connect.php';
require_once '../includes/social_helpers.php';

$user_id = $_SESSION['user_id'];
$edit    = null;

if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $s  = mysqli_prepare($connection, 'SELECT Post.*, Shopping.Category, Promote.Link, Promote.Cost, Promote.Phone FROM Post JOIN Shopping ON Post.Post_ID=Shopping.Post_ID JOIN Promote ON Post.Post_ID=Promote.Post_ID WHERE Post.Post_ID=? AND Post.User_ID=?');
    mysqli_stmt_bind_param($s, 'is', $id, $user_id);
    mysqli_stmt_execute($s);
    $edit = mysqli_fetch_assoc(mysqli_stmt_get_result($s));
}

$items = mysqli_query($connection, 'SELECT Post.*, Shopping.Category, Promote.Link, Promote.Cost, Promote.Phone FROM Post JOIN Shopping ON Post.Post_ID=Shopping.Post_ID JOIN Promote ON Post.Post_ID=Promote.Post_ID ORDER BY Post.Post_ID DESC');

require_once '../includes/header.php';
?>

<div class="page-heading">
    <div>
        <p class="eyebrow">FEATURE 04</p>
        <h1>Shopping Guide</h1>
        <p>Festival products, gifts, and useful seller contacts.</p>
    </div>
    <button type="button" data-form-target="shopping-form">+ Add Listing</button>
</div>

<section class="cards">
    <?php while ($item = mysqli_fetch_assoc($items)): ?>
        <article class="card">
            <?php if ($item['Image']): ?>
                <img src="<?=htmlspecialchars($item['Image'])?>" alt="<?=htmlspecialchars($item['Title'])?>">
            <?php else: ?>
                <div class="card-cover">Festival shopping</div>
            <?php endif; ?>

            <span class="tag"><?=htmlspecialchars($item['Category'])?></span>
            <h2><?=htmlspecialchars($item['Title'])?></h2>
            <p><?=htmlspecialchars(strlen($item['Description']) > 110 ? substr($item['Description'], 0, 110).'...' : $item['Description'])?></p>
            
            <?php creator_button($connection, $user_id, $item['User_ID']); ?>
            
            <div class="card-footer">
                <a class="details-link" href="shopping_details.php?id=<?=$item['Post_ID']?>">View details</a>
                <?php bookmark_button($connection, $user_id, 'Shopping', $item['Post_ID'], '/RonginBD/pages/shopping.php'); ?>
            </div>

            <?php if ($item['User_ID'] === $user_id): ?>
                <div class="owner-actions">
                    <a href="?edit=<?=$item['Post_ID']?>#shopping-form">Edit</a>
                    <form method="post" action="../actions/shopping_action.php">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="post_id" value="<?=$item['Post_ID']?>">
                        <button class="link danger" data-confirm="Are you sure?">Delete</button>
                    </form>
                </div>
            <?php endif; ?>
        </article>
    <?php endwhile; ?>
</section>

<section id="shopping-form" class="form-panel is-hidden">
    <h2><?=$edit ? 'Edit listing' : 'Add listing'?></h2>
    <form method="post" action="../actions/shopping_action.php" enctype="multipart/form-data">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="post_id" value="<?=$edit['Post_ID'] ?? 0?>">
        
        <label>Product title
            <input name="title" value="<?=htmlspecialchars($edit['Title'] ?? '')?>" required>
        </label>
        
        <label>Category
            <input name="category" value="<?=htmlspecialchars($edit['Category'] ?? '')?>" required>
        </label>
        
        <label>Website or Facebook link
            <input name="link" type="url" value="<?=htmlspecialchars($edit['Link'] ?? '')?>">
        </label>
        
        <label>Phone number
            <input name="phone" value="<?=htmlspecialchars($edit['Phone'] ?? '')?>">
        </label>
        
        <label>Cost (optional)
            <input name="cost" type="number" step="0.01" value="<?=htmlspecialchars($edit['Cost'] ?? '')?>">
        </label>
        
        <label>Image
            <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp">
        </label>
        
        <label>Description
            <textarea name="description" required><?=htmlspecialchars($edit['Description'] ?? '')?></textarea>
        </label>
        
        <button>Save listing</button>
    </form>
</section>

<?php require_once '../includes/footer.php'; ?>