<?php require_once '../config/connect.php'; session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = trim($_POST['user_id']); $password = $_POST['password'];
    $stmt = mysqli_prepare($connection, 'SELECT User_ID, Password FROM `User` WHERE User_ID = ?'); mysqli_stmt_bind_param($stmt, 's', $user_id); mysqli_stmt_execute($stmt); $result = mysqli_stmt_get_result($stmt); $user = mysqli_fetch_assoc($result);
    if ($user && password_verify($password, $user['Password'])) {
        $role_query = mysqli_prepare($connection, 'SELECT User_ID FROM Admin WHERE User_ID = ?'); mysqli_stmt_bind_param($role_query, 's', $user_id); mysqli_stmt_execute($role_query);
        $_SESSION['user_id'] = $user_id; $_SESSION['role'] = mysqli_num_rows(mysqli_stmt_get_result($role_query)) ? 'Admin' : 'Regular User';
        header('Location: /RonginBD/pages/dashboard.php'); exit;
    } $error = 'Invalid login ID or password.';
} ?>
<!doctype html><html><head><link rel="stylesheet" href="/RonginBD/assets/css/style.css"><title>Login - RonginBD</title></head><body class="auth-page"><form class="auth-card" method="post"><h1>Welcome back</h1><p>Login ID uses <code>User_ID</code> from the given schema.</p><?php if(isset($error)) echo '<div class="notice error">'.$error.'</div>'; ?><label>Login ID<input name="user_id" required></label><label>Password<input type="password" name="password" required></label><button>Log in</button><p>New here? <a href="register.php">Create a Regular User account</a></p></form></body></html>
