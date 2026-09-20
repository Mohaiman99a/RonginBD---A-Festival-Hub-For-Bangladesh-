<?php
require_once '../auth/auth_check.php'; 
require_once '../config/connect.php'; 

$id = $_SESSION['user_id'];
$counts = []; 

foreach (['Festival' => 'AdminUser_ID', 'Post' => 'User_ID', 'Album' => 'User_ID'] as $table => $column) {
    $s = mysqli_prepare($connection, "SELECT COUNT(*) AS total FROM $table WHERE $column=?");
    mysqli_stmt_bind_param($s, 's', $id);
    mysqli_stmt_execute($s);
    $counts[$table] = mysqli_fetch_assoc(mysqli_stmt_get_result($s))['total'];
}

$search = trim($_GET['user_search'] ?? ''); 

if ($search !== '') {
    $s = mysqli_prepare($connection, 'SELECT User_ID, First_Name, Last_Name FROM `User` WHERE User_ID<>? AND (User_ID LIKE ? OR First_Name LIKE ? OR Last_Name LIKE ?) ORDER BY User_ID');
    $term = '%' . $search . '%';
    mysqli_stmt_bind_param($s, 'ssss', $id, $term, $term, $term);
    mysqli_stmt_execute($s);
    $users = mysqli_stmt_get_result($s);
} else {
    $s = mysqli_prepare($connection, 'SELECT User_ID, First_Name, Last_Name FROM `User` WHERE User_ID<>? ORDER BY User_ID LIMIT 8');
    mysqli_stmt_bind_param($s, 's', $id);
    mysqli_stmt_execute($s);
    $users = mysqli_stmt_get_result($s);
}

$s = mysqli_prepare($connection, 'SELECT Following_ID FROM User_Follow WHERE Follower_ID=?');
mysqli_stmt_bind_param($s, 's', $id);
mysqli_stmt_execute($s);
$following = [];
$following_result = mysqli_stmt_get_result($s);
while ($row = mysqli_fetch_assoc($following_result)) $following[] = $row['Following_ID']; 

require_once '../includes/header.php';
?>

<div class="page-heading">
    <div>
        <p class="eyebrow">MY SPACE</p>
        <h1>My Dashboard</h1>
        <p>Your activity and community connections.</p>
    </div>
</div>

<section class="stats">
    <div><strong><?=$counts['Festival']?></strong><span>Festivals created</span></div>
    <div><strong><?=$counts['Post']?></strong><span>Recipes shared</span></div>
    <div><strong><?=$counts['Album']?></strong><span>Memory albums</span></div>
</section>

<section class="form-panel profile-panel">
    <h2>Profile information</h2>
    <p><b>User ID:</b> <?=htmlspecialchars($id)?></p>
    <p><b>Role:</b> <?=htmlspecialchars($_SESSION['role'])?></p>
</section>

<section class="form-panel follow-panel">
    <h2>Find people</h2>
    <form class="user-search" method="get">
        <label>Search registered users 
            <input name="user_search" value="<?=htmlspecialchars($search)?>" placeholder="Search by ID or name">
        </label>
        <button>Search</button>
    </form>
    <div class="user-list">
        <?php while($user=mysqli_fetch_assoc($users)): ?>
            <div class="user-row">
                <span>
                    <b><?=htmlspecialchars($user['First_Name'].' '.$user['Last_Name'])?></b>
                    <small>@<?=htmlspecialchars($user['User_ID'])?></small>
                </span>
                <form method="post" action="../actions/follow_action.php">
                    <input type="hidden" name="target_user" value="<?=htmlspecialchars($user['User_ID'])?>">
                    <?php if(in_array($user['User_ID'],$following,true)):?>
                        <input type="hidden" name="action" value="unfollow">
                        <button class="secondary">Unfollow</button>
                    <?php else:?>
                        <input type="hidden" name="action" value="follow">
                        <button>Follow</button>
                    <?php endif;?>
                </form>
            </div>
        <?php endwhile;?>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>