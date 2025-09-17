<?php
session_start();
if (isset($_SESSION['user_id'])) {
    // Agar user login hai to dashboard par bhej do
    echo "<script>window.location.href='dashboard.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>ScheduleIt - Home</title>
<style>
    /* Internal CSS */
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin: 0; padding: 0;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #fff;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 20px;
    }
    h1 {
        font-size: 3rem;
        margin-bottom: 0.3em;
        text-shadow: 0 2px 5px rgba(0,0,0,0.3);
    }
    p {
        font-size: 1.25rem;
        max-width: 600px;
        margin: 0 auto 2em;
        line-height: 1.5;
        text-shadow: 0 1px 3px rgba(0,0,0,0.2);
    }
    .btn {
        background: #ff6f61;
        border: none;
        padding: 15px 30px;
        font-size: 1.1rem;
        border-radius: 30px;
        cursor: pointer;
        color: white;
        box-shadow: 0 5px 15px rgba(255,111,97,0.6);
        transition: background 0.3s ease;
        margin: 0 10px;
        text-decoration: none;
        display: inline-block;
    }
    .btn:hover {
        background: #ff3b2e;
        box-shadow: 0 8px 20px rgba(255,59,46,0.8);
    }
    .btn-container {
        margin-top: 1.5em;
    }
    @media (max-width: 600px) {
        h1 {
            font-size: 2.2rem;
        }
        p {
            font-size: 1rem;
        }
        .btn {
            padding: 12px 25px;
            font-size: 1rem;
            margin: 5px 5px;
        }
    }
</style>
<script>
    function redirectTo(page) {
        window.location.href = page;
    }
</script>
</head>
<body>
    <h1>Welcome to ScheduleIt</h1>
    <p>Effortlessly schedule meetings with your clients and friends. Set your availability, share your booking link, and manage your appointments all in one place.</p>
    <div class="btn-container">
        <button class="btn" onclick="redirectTo('signup.php')">Sign Up</button>
        <button class="btn" onclick="redirectTo('login.php')">Log In</button>
        <button class="btn" onclick="redirectTo('booking.php')">Book a Meeting</button>
    </div>
</body>
</html>
