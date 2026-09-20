<?php
require_once '../auth/auth_check.php';
require_once '../config/connect.php';

$post_id = (int)($_GET['id'] ?? 0);

$s = mysqli_prepare($connection, 'SELECT Post.*, Recipe.Recipe, Recipe_Ingredients.Ingredients FROM Post JOIN Recipe ON Post.Post_ID=Recipe.Post_ID JOIN Recipe_Ingredients ON Post.Post_ID=Recipe_Ingredients.Post_ID WHERE Post.Post_ID=?');
mysqli_stmt_bind_param($s, 'i', $post_id);
mysqli_stmt_execute($s);
$recipe = mysqli_fetch_assoc(mysqli_stmt_get_result($s));

if (!$recipe) {
    header('Location: food.php?error=Recipe not found.');
    exit;
}

require_once '../includes/header.php';
?>

<article class="content-detail">
    <?php if ($recipe['Image']): ?>
        <img src="<?=htmlspecialchars($recipe['Image'])?>" alt="<?=htmlspecialchars($recipe['Title'])?>">
    <?php endif; ?>

    <h1><?=htmlspecialchars($recipe['Title'])?></h1>
    <p class="muted">Shared by <?=htmlspecialchars($recipe['User_ID'])?></p>
    <p class="full-description"><?=nl2br(htmlspecialchars($recipe['Description']))?></p>
    
    <h2>Ingredients</h2>
    <p><?=nl2br(htmlspecialchars($recipe['Ingredients']))?></p>
    
    <h2>Method</h2>
    <p><?=nl2br(htmlspecialchars($recipe['Recipe']))?></p>
    
    <a class="button secondary" href="food.php">Back to food</a>
</article>

<?php require_once '../includes/footer.php'; ?>