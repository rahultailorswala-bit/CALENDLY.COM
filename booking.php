<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
 
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}
 
require 'db.php';
 
$user_id = $_SESSION['user_id'];
$errors = [];
$success = '';
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $date = $_POST['date'] ?? '';
    $time = $_POST['time'] ?? '';
    $duration = intval($_POST['duration'] ?? 30);
    $meetingType = trim($_POST['meetingType'] ?? '');
 
    // Basic validation
    if (!$name) $errors[] = "Name is required.";
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
    if (!$date) $errors[] = "Date is required.";
    if (!$time) $errors[] = "Time is required.";
    if (!$meetingType) $errors[] = "Meeting type is required.";
 
    if (empty($errors)) {
        // Check if slot is free (optional)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE user_id = ? AND appointment_date = ? AND appointment_start = ? AND status = 'booked'");
        $stmt->execute([$user_id, $date, $time]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "This time slot is already booked.";
        } else {
            // Insert appointment
            $stmt = $pdo->prepare("INSERT INTO appointments (user_id, visitor_name, visitor_email, appointment_date, appointment_start, appointment_end, status, meetingType) VALUES (?, ?, ?, ?, ?, ?, 'booked', ?)");
            // Calculate end time
            $startDateTime = new DateTime("$date $time");
            $endDateTime = clone $startDateTime;
            $endDateTime->modify("+$duration minutes");
            $startStr = $startDateTime->format('H:i:s');
            $endStr = $endDateTime->format('H:i:s');
 
            if ($stmt->execute([$user_id, $name, $email, $date, $startStr, $endStr, $meetingType])) {
                $success = "Appointment booked successfully!";
            } else {
                $errors[] = "Failed to book appointment. Please try again.";
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
<title>Book a Meeting - ScheduleIt</title>
<style>
    body { font-family: Arial, sans-serif; background: #f9f9f9; margin: 0; padding: 20px; }
    .container { max-width: 500px; margin: auto; background: white; padding: 20px; border-radius: 8px; }
    h1 { color: #333; }
    label { display: block; margin-top: 15px; font-weight: bold; }
    input, select { width: 100%; padding: 8px; margin-top: 5px; border-radius: 4px; border: 1px solid #ccc; }
    button { margin-top: 20px; padding: 10px 15px; background: #2563eb; color: white; border: none; border-radius: 6px; cursor: pointer; }
    button:hover { background: #1e40af; }
    .error { background: #fee2e2; color: #991b1b; padding: 10px; border-radius: 6px; margin-bottom: 15px; }
    .success { background: #d1fae5; color: #065f46; padding: 10px; border-radius: 6px; margin-bottom: 15px; }
    nav a { margin-right: 15px; text-decoration: none; color: #007BFF; }
    nav a:hover { text-decoration: underline; }
</style>
</head>
<body>
<div class="container">
    <h1>Book a Meeting</h1>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </nav>
 
    <?php if ($errors): ?>
        <div class="error">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?=htmlspecialchars($e)?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
 
    <?php if ($success): ?>
        <div class="success"><?=htmlspecialchars($success)?></div>
    <?php endif; ?>
 
    <form method="post" action="booking.php" novalidate>
        <label for="name">Your Name</label>
        <input type="text" id="name" name="name" required value="<?=htmlspecialchars($_POST['name'] ?? '')?>" />
 
        <label for="email">Your Email</label>
        <input type="email" id="email" name="email" required value="<?=htmlspecialchars($_POST['email'] ?? '')?>" />
 
        <label for="date">Date</label>
        <input type="date" id="date" name="date" required value="<?=htmlspecialchars($_POST['date'] ?? '')?>" />
 
        <label for="time">Time</label>
        <input type="time" id="time" name="time" required value="<?=htmlspecialchars($_POST['time'] ?? '')?>" />
 
        <label for="duration">Duration (minutes)</label>
        <select id="duration" name="duration" required>
            <option value="15" <?= (($_POST['duration'] ?? '') == '15') ? 'selected' : '' ?>>15</option>
            <option value="30" <?= (($_POST['duration'] ?? '') == '30' || !isset($_POST['duration'])) ? 'selected' : '' ?>>30</option>
            <option value="45" <?= (($_POST['duration'] ?? '') == '45') ? 'selected' : '' ?>>45</option>
            <option value="60" <?= (($_POST['duration'] ?? '') == '60') ? 'selected' : '' ?>>60</option>
        </select>
 
        <label for="meetingType">Meeting Type</label>
        <input type="text" id="meetingType" name="meetingType" required placeholder="e.g. 30-min Consultation" value="<?=htmlspecialchars($_POST['meetingType'] ?? '')?>" />
 
        <button type="submit">Book Appointment</button>
    </form>
</div>
</body>
</html>
