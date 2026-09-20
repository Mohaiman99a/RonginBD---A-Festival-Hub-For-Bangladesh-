<?php
require_once '../auth/auth_check.php';
require_once '../config/connect.php';
require_once '../includes/social_helpers.php';

$user_id = $_SESSION['user_id'];
$edit    = null;

if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $s  = mysqli_prepare($connection, 'SELECT Post.*, Event.Time, Event.Date, Event.Location, Event.Festival_ID FROM Post JOIN Event ON Post.Post_ID=Event.Post_ID WHERE Post.Post_ID=? AND Post.User_ID=?');
    mysqli_stmt_bind_param($s, 'is', $id, $user_id);
    mysqli_stmt_execute($s);
    $edit = mysqli_fetch_assoc(mysqli_stmt_get_result($s));
}

$events    = mysqli_query($connection, 'SELECT Post.*, Event.Time, Event.Date, Event.Location, Festival.Name Festival_Name FROM Post JOIN Event ON Post.Post_ID=Event.Post_ID JOIN Festival ON Event.Festival_ID=Festival.Festival_ID ORDER BY Event.Date, Event.Time');
$festivals = mysqli_query($connection, 'SELECT Festival_ID, Name FROM Festival ORDER BY Date');

require_once '../includes/header.php';
?>

<div class="page-heading">
    <div>
        <p class="eyebrow">FEATURE 02</p>
        <h1>Festival Events</h1>
        <p>Fairs, concerts, and celebrations around Bangladesh.</p>
    </div>
    <button type="button" data-form-target="event-form">+ Share Event</button>
</div>

<section class="cards">
    <?php while ($event = mysqli_fetch_assoc($events)): ?>
        <article class="card">
            <?php if ($event['Image']): ?>
                <img src="<?=htmlspecialchars($event['Image'])?>" alt="<?=htmlspecialchars($event['Title'])?>">
            <?php else: ?>
                <div class="card-cover">Festival event</div>
            <?php endif; ?>

            <span class="tag"><?=htmlspecialchars($event['Festival_Name'])?></span>
            <h2><?=htmlspecialchars($event['Title'])?></h2>
            <p class="date"><?=date('d M Y', strtotime($event['Date']))?> · <?=htmlspecialchars($event['Location'])?></p>
            <p><?=htmlspecialchars(strlen($event['Description']) > 110 ? substr($event['Description'], 0, 110).'...' : $event['Description'])?></p>
            
            <?php creator_button($connection, $user_id, $event['User_ID']); ?>
            
            <div class="card-footer">
                <a class="details-link" href="event_details.php?id=<?=$event['Post_ID']?>">View details</a>
                <?php bookmark_button($connection, $user_id, 'Event', $event['Post_ID'], '/RonginBD/pages/events.php'); ?>
            </div>

            <?php if ($event['User_ID'] === $user_id): ?>
                <div class="owner-actions">
                    <a href="?edit=<?=$event['Post_ID']?>#event-form">Edit</a>
                    <form method="post" action="../actions/event_action.php">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="post_id" value="<?=$event['Post_ID']?>">
                        <button class="link danger" data-confirm="Are you sure?">Delete</button>
                    </form>
                </div>
            <?php endif; ?>
        </article>
    <?php endwhile; ?>
</section>

<section id="event-form" class="form-panel is-hidden">
    <h2><?=$edit ? 'Edit event' : 'Share an event'?></h2>
    <form method="post" action="../actions/event_action.php" enctype="multipart/form-data">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="post_id" value="<?=$edit['Post_ID'] ?? 0?>">
        
        <label>Event title
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
        
        <label>Date
            <input type="date" name="date" value="<?=$edit['Date'] ?? ''?>" required>
        </label>
        
        <label>Time
            <input type="time" name="time" value="<?=$edit['Time'] ?? ''?>" required>
        </label>
        
        <label>Location
            <input name="location" value="<?=htmlspecialchars($edit['Location'] ?? '')?>" required>
        </label>
        
        <label>Image
            <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp">
        </label>
        
        <label>Description
            <textarea name="description" required><?=htmlspecialchars($edit['Description'] ?? '')?></textarea>
        </label>
        
        <button>Save event</button>
    </form>
</section>

<?php require_once '../includes/footer.php'; ?>