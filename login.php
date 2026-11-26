<?php
require __DIR__ . '/includes/db.php';
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $pass  = $_POST['password'] ?? '';
    $user  = $db->users->findOne(['email' => $email]);

    if ($user && password_verify($pass, $user['password'])) {
        $_SESSION['user_id'] = (string) $user['_id'];
        $_SESSION['user_name'] = $user['name'];
        header("Location: dashboard.php");
        exit;
    }
    $error = "Wrong email or password.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - PantryPal</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-page">
  <form class="auth-box" method="POST">
    <h1>PantryPal</h1>
    <p class="muted">Know what's in your kitchen</p>
    <?php if (isset($_GET['registered'])): ?><p class="msg ok">Account created. Please log in.</p><?php endif; ?>
    <?php if ($error): ?><p class="msg error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <label>Email <input type="email" name="email" required></label>
    <label>Password <input type="password" name="password" required></label>
    <button class="btn" type="submit">Log in</button>
    <p class="muted">New here? <a href="register.php">Create an account</a></p>
  </form>
</body>
</html>
