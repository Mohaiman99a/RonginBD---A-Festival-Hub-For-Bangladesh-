<?php
require_once '../auth/auth_check.php';
require_once '../config/connect.php';
require_once '../includes/social_helpers.php';

$user_id = $_SESSION['user_id'];
$edit    = null;

if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $s  = mysqli_prepare($connection, 'SELECT Post.*, Recipe.Recipe, Recipe_Ingredients.Ingredients FROM Post JOIN Recipe ON Post.Post_ID=Recipe.Post_ID JOIN Recipe_Ingredients ON Post.Post_ID=Recipe_Ingredients.Post_ID WHERE Post.Post_ID=? AND Post.User_ID=?');
    mysqli_stmt_bind_param($s, 'is', $id, $user_id);
    mysqli_stmt_execute($s);
    $edit = mysqli_fetch_assoc(mysqli_stmt_get_result($s));
}

$recipes = mysqli_query($connection, 'SELECT Post.*, Recipe.Recipe, Recipe_Ingredients.Ingredients FROM Post JOIN Recipe ON Post.Post_ID=Recipe.Post_ID JOIN Recipe_Ingredients ON Post.Post_ID=Recipe_Ingredients.Post_ID ORDER BY Post.Post_ID DESC');

require_once '../includes/header.php';
?>

<div class="page-heading">
    <div>
        <p class="eyebrow">FEATURE 03</p>
        <h1>Traditional Food</h1>
        <p>Recipes shared by the RonginBD community.</p>
    </div>
    <a class="button" href="#recipe-form">Share recipe</a>
</div>

<section class="cards">
    <?php while ($recipe = mysqli_fetch_assoc($recipes)): ?>
        <article class="card">
            <?php if ($recipe['Image']): ?>
                <img src="<?=htmlspecialchars($recipe['Image'])?>" alt="<?=htmlspecialchars($recipe['Title'])?>">
            <?php else: ?>
                <div class="card-cover">Traditional food</div>
            <?php endif; ?>

            <h2><?=htmlspecialchars($recipe['Title'])?></h2>
            <p><?=htmlspecialchars(strlen($recipe['Description']) > 125 ? substr($recipe['Description'], 0, 125).'...' : $recipe['Description'])?></p>
            
            <?php creator_button($connection, $user_id, $recipe['User_ID']); ?>
            
            <div class="card-footer">
                <a class="details-link" href="recipe_details.php?id=<?=$recipe['Post_ID']?>">View details</a>
                <?php bookmark_button($connection, $user_id, 'Recipe', $recipe['Post_ID'], '/RonginBD/pages/food.php'); ?>
            </div>

            <?php if ($recipe['User_ID'] === $user_id): ?>
                <div class="owner-actions">
                    <a href="?edit=<?=$recipe['Post_ID']?>#recipe-form">Edit</a>
                    <form method="post" action="../actions/recipe_action.php">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="post_id" value="<?=$recipe['Post_ID']?>">
                        <button class="link danger" data-confirm="Are you sure?">Delete</button>
                    </form>
                </div>
            <?php endif; ?>
        </article>
    <?php endwhile; ?>
</section>

<section id="recipe-form" class="form-panel">
    <h2><?=$edit ? 'Edit recipe' : 'Share a recipe'?></h2>
    <form method="post" action="../actions/recipe_action.php" enctype="multipart/form-data">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="post_id" value="<?=$edit['Post_ID'] ?? 0?>">
        
        <label>Recipe name
            <input name="title" value="<?=htmlspecialchars($edit['Title'] ?? '')?>" required>
        </label>
        
        <label>Recipe image
            <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp">
        </label>
        
        <label>Description
            <textarea name="description" required><?=htmlspecialchars($edit['Description'] ?? '')?></textarea>
        </label>
        
        <label>Ingredients
            <textarea name="ingredients" required><?=htmlspecialchars($edit['Ingredients'] ?? '')?></textarea>
        </label>
        
        <label>Recipe method
            <textarea name="recipe" required><?=htmlspecialchars($edit['Recipe'] ?? '')?></textarea>
        </label>
        
        <button>Save recipe</button>
    </form>
</section>

<?php require_once '../includes/footer.php'; ?>