<?php
require_once '../auth/auth_check.php';
require_once '../config/connect.php';
require_once '../includes/social_helpers.php';

$user_id = $_SESSION['user_id'];
$edit    = null;

if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $s  = mysqli_prepare($connection, 'SELECT Post.*, Travel.FromLocation, Travel.ToLocation, Travel.Date, Travel.Time, Promote.Link, Promote.Cost, Promote.Phone FROM Post JOIN Travel ON Post.Post_ID=Travel.Post_ID LEFT JOIN Promote ON Post.Post_ID=Promote.Post_ID WHERE Post.Post_ID=? AND Post.User_ID=?');
    mysqli_stmt_bind_param($s, 'is', $id, $user_id);
    mysqli_stmt_execute($s);
    $edit = mysqli_fetch_assoc(mysqli_stmt_get_result($s));
}

$items = mysqli_query($connection, 'SELECT Post.*, Travel.FromLocation, Travel.ToLocation, Travel.Date, Travel.Time, Promote.Link, Promote.Cost, Promote.Phone FROM Post JOIN Travel ON Post.Post_ID=Travel.Post_ID LEFT JOIN Promote ON Post.Post_ID=Promote.Post_ID ORDER BY Travel.Date, Travel.Time');

require_once '../includes/header.php';
?>

<div class="page-heading">
    <div>
        <p class="eyebrow">FEATURE 05</p>
        <h1>Travel &amp; Transport</h1>
        <p>Routes, schedules, resale tickets, and transport contacts.</p>
    </div>
    <button type="button" data-form-target="travel-form">+ Share Travel Info</button>
</div>

<section class="cards">
    <?php while ($item = mysqli_fetch_assoc($items)): ?>
        <article class="card">
            <?php if ($item['Image']): ?>
                <img src="<?=htmlspecialchars($item['Image'])?>" alt="<?=htmlspecialchars($item['Title'])?>">
            <?php else: ?>
                <div class="card-cover">Travel &amp; transport</div>
            <?php endif; ?>

            <h2><?=htmlspecialchars($item['Title'])?></h2>
            <p class="date"><?=htmlspecialchars($item['FromLocation'].' → '.$item['ToLocation'])?> · <?=date('d M Y', strtotime($item['Date']))?></p>
            <p><?=htmlspecialchars(strlen($item['Description']) > 110 ? substr($item['Description'], 0, 110).'...' : $item['Description'])?></p>
            
            <?php creator_button($connection, $user_id, $item['User_ID']); ?>
            
            <a class="details-link" href="travel_details.php?id=<?=$item['Post_ID']?>">View details</a>
            <?php bookmark_button($connection, $user_id, 'Travel', $item['Post_ID'], '/RonginBD/pages/travel.php'); ?>

            <?php if ($item['User_ID'] === $user_id): ?>
                <div class="card-actions">
                    <a href="?edit=<?=$item['Post_ID']?>#travel-form">Edit</a>
                    <form method="post" action="../actions/travel_action.php">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="post_id" value="<?=$item['Post_ID']?>">
                        <button class="link danger" data-confirm="Are you sure?">Delete</button>
                    </form>
                </div>
            <?php endif; ?>
        </article>
    <?php endwhile; ?>
</section>

<section id="travel-form" class="form-panel is-hidden">
    <h2><?=$edit ? 'Edit travel post' : 'Share travel information'?></h2>
    <form method="post" action="../actions/travel_action.php" enctype="multipart/form-data">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="post_id" value="<?=$edit['Post_ID'] ?? 0?>">
        
        <label>Title
            <input name="title" value="<?=htmlspecialchars($edit['Title'] ?? '')?>" required>
        </label>
        
        <label>From
            <input name="from_location" value="<?=htmlspecialchars($edit['FromLocation'] ?? '')?>" required>
        </label>
        
        <label>To
            <input name="to_location" value="<?=htmlspecialchars($edit['ToLocation'] ?? '')?>" required>
        </label>
        
        <label>Date
            <input type="date" name="date" value="<?=$edit['Date'] ?? ''?>" required>
        </label>
        
        <label>Schedule
            <input type="time" name="time" value="<?=$edit['Time'] ?? ''?>" required>
        </label>
        
        <label>Fare (optional)
            <input name="fare" type="number" step="0.01" value="<?=htmlspecialchars($edit['Cost'] ?? '')?>">
        </label>
        
        <label>Booking link
            <input type="url" name="link" value="<?=htmlspecialchars($edit['Link'] ?? '')?>">
        </label>
        
        <label>Contact number
            <input name="phone" value="<?=htmlspecialchars($edit['Phone'] ?? '')?>">
        </label>
        
        <label>Image
            <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp">
        </label>
        
        <label>Description
            <textarea name="description" required><?=htmlspecialchars($edit['Description'] ?? '')?></textarea>
        </label>
        
        <button>Save travel post</button>
    </form>
</section>

<?php require_once '../includes/footer.php'; ?>