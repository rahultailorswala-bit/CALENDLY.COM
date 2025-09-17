<?php
session_start();
if (isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='dashboard.php';</script>";
    exit;
}
require 'db.php';
 
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_email = trim($_POST['username_email'] ?? '');
    $password = $_POST['password'] ?? '';
 
    if (!$username_email) {
        $errors[] = "Please enter your username or email.";
    }
    if (!$password) {
        $errors[] = "Please enter your password.";
    }
 
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username_email, $username_email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            echo "<script>window.location.href='dashboard.php';</script>";
            exit;
        } else {
            $errors[] = "Invalid username/email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Log In - ScheduleIt</title>
<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #f0f4f8;
        margin: 0; padding: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
    }
    .container {
        background: white;
        padding: 30px 40px;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        width: 100%;
        max-width: 400px;
    }
    h2 {
        margin-bottom: 20px;
        color: #333;
        text-align: center;
    }
    label {
        display: block;
        margin-bottom: 6px;
        font-weight: 600;
        color: #555;
    }
    input[type="text"], input[type="email"], input[type="password"] {
        width: 100%;
        padding: 10px 12px;
        margin-bottom: 15px;
        border: 1.8px solid #ddd;
        border-radius: 8px;
        font-size: 1rem;
        transition: border-color 0.3s ease;
    }
    input[type="text"]:focus, input[type="email"]:focus, input[type="password"]:focus {
        border-color: #667eea;
        outline: none;
    }
    button {
        width: 100%;
        padding: 12px;
        background: #667eea;
        border: none;
        border-radius: 30px;
        color: white;
        font-size: 1.1rem;
        cursor: pointer;
        box-shadow: 0 5px 15px rgba(102,126,234,0.6);
        transition: background 0.3s ease;
    }
    button:hover {
        background: #5a67d8;
    }
    .errors {
        background: #ffe6e6;
        border: 1px solid #ff4d4d;
        color: #b30000;
        padding: 10px 15px;
        border-radius: 8px;
        margin-bottom: 15px;
        font-size: 0.9rem;
    }
    .signup-link {
        margin-top: 15px;
        text-align: center;
        font-size: 0.9rem;
        color: #555;
    }
    .signup-link a {
        color: #667eea;
        text-decoration: none;
        font-weight: 600;
    }
    .signup-link a:hover {
        text-decoration: underline;
    }
</style>
</head>
<body>
<div class="container">
    <h2>Log In to ScheduleIt</h2>
    <?php if (!empty($errors)): ?>
        <div class="errors">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?=htmlspecialchars($e)?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <form method="post" action="login.php" novalidate>
        <label for="username_email">Username or Email</label>
        <input type="text" id="username_email" name="username_email" required value="<?=htmlspecialchars($_POST['username_email'] ?? '')?>" />
 
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required />
 
        <button type="submit">Log In</button>
    </form>
    <div class="signup-link">
        Don't have an account? <a href="signup.php">Sign Up</a>
    </div>
</div>
</body>
</html>
