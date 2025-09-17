<?php
session_start();
require 'db.php';
 
if (isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='dashboard.php';</script>";
    exit;
}
 
$errors = [];
$username = '';
$email = '';
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
 
    // Validate username
    if (!$username || !preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        $errors[] = "Username must be 3-20 characters and contain only letters, numbers, and underscores.";
    }
 
    // Validate email
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
 
    // Validate password length
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }
 
    // Confirm password match
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }
 
    if (empty($errors)) {
        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $errors[] = "Username or email already taken.";
        } else {
            // Insert new user
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            if ($stmt->execute([$username, $email, $password_hash])) {
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['username'] = $username;
                echo "<script>window.location.href='dashboard.php';</script>";
                exit;
            } else {
                $errors[] = "Failed to create account. Please try again.";
            }
        }
    }
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Sign Up - ScheduleIt</title>
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
    .login-link {
        margin-top: 15px;
        text-align: center;
        font-size: 0.9rem;
        color: #555;
    }
    .login-link a {
        color: #667eea;
        text-decoration: none;
        font-weight: 600;
    }
    .login-link a:hover {
        text-decoration: underline;
    }
</style>
</head>
<body>
<div class="container">
    <h2>Create an Account</h2>
    <?php if (!empty($errors)): ?>
        <div class="errors">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?=htmlspecialchars($error)?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <form method="post" action="signup.php" novalidate>
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required value="<?=htmlspecialchars($username)?>" />
 
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required value="<?=htmlspecialchars($email)?>" />
 
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required />
 
        <label for="confirm_password">Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password" required />
 
        <button type="submit">Sign Up</button>
    </form>
    <div class="login-link">
        Already have an account? <a href="login.php">Log In</a>
    </div>
</div>
</body>
</html>
