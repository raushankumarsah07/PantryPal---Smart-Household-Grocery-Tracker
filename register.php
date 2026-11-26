<?php
require __DIR__ . '/includes/db.php';
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $pass  = $_POST['password'] ?? '';

    if ($name === '' || $email === '' || strlen($pass) < 6) {
        $error = "Fill all fields. Password needs at least 6 characters.";
    } elseif ($db->users->findOne(['email' => $email])) {
        $error = "This email is already registered. Try logging in.";
    } else {
        $db->users->insertOne([
            'name' => $name,
            'email' => $email,
            'password' => password_hash($pass, PASSWORD_DEFAULT),
        ]);
        header("Location: login.php?registered=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Register - PantryPal</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-page">
  <form class="auth-box" method="POST">
    <h1>PantryPal</h1>
    <p class="muted">Create your household account</p>
    <?php if ($error): ?><p class="msg error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <label>Name <input type="text" name="name" required></label>
    <label>Email <input type="email" name="email" required></label>
    <label>Password <input type="password" name="password" minlength="6" required></label>
    <button class="btn" type="submit">Create account</button>
    <p class="muted">Already have an account? <a href="login.php">Log in</a></p>
  </form>
</body>
</html>
