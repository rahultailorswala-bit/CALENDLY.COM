<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
 
require 'db.php';
 
$user_id = $_SESSION['user_id'];
$message = '';
 
// Handle Confirm / Cancel actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['appointment_id'])) {
    $action = $_POST['action'];
    $appointment_id = intval($_POST['appointment_id']);
 
    // Validate action
    if (in_array($action, ['confirm', 'cancel'])) {
        $new_status = $action === 'confirm' ? 'confirmed' : 'cancelled';
 
        // Update appointment status only if it belongs to logged-in user
        $stmt = $pdo->prepare("UPDATE appointments SET status = ? WHERE id = ? AND user_id = ?");
        if ($stmt->execute([$new_status, $appointment_id, $user_id])) {
            $message = "Appointment has been " . ($new_status === 'confirmed' ? "confirmed." : "cancelled.");
        } else {
            $message = "Failed to update appointment status.";
        }
    }
}
 
// Fetch upcoming appointments (status booked or confirmed, date >= today)
$stmt = $pdo->prepare("SELECT * FROM appointments WHERE user_id = ? AND appointment_date >= CURDATE() AND status IN ('booked', 'confirmed') ORDER BY appointment_date, appointment_start");
$stmt->execute([$user_id]);
$upcoming = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
// Fetch past appointments (date < today or cancelled)
$stmt = $pdo->prepare("SELECT * FROM appointments WHERE user_id = ? AND (appointment_date < CURDATE() OR status = 'cancelled') ORDER BY appointment_date DESC, appointment_start DESC");
$stmt->execute([$user_id]);
$past = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
function statusBadge($status) {
    switch ($status) {
        case 'confirmed': return '<span class="badge confirmed">Confirmed</span>';
        case 'booked': return '<span class="badge booked">Booked</span>';
        case 'cancelled': return '<span class="badge cancelled">Cancelled</span>';
        default: return '<span class="badge unknown">Unknown</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Dashboard - ScheduleIt</title>
<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #f9fafb;
        margin: 0; padding: 0;
        color: #1e293b;
    }
    header {
        background: #2563eb;
        color: white;
        padding: 1rem 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    header h1 {
        margin: 0;
        font-size: 1.5rem;
    }
    header nav a {
        color: white;
        text-decoration: none;
        font-weight: 600;
        margin-left: 1rem;
    }
    header nav a:hover {
        text-decoration: underline;
    }
    main {
        max-width: 900px;
        margin: 2rem auto;
        padding: 0 1rem;
    }
    h2 {
        font-weight: 700;
        font-size: 1.75rem;
        margin-bottom: 1rem;
    }
    .message {
        background: #d1fae5;
        color: #065f46;
        padding: 10px 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-weight: 600;
    }
    .appointments-list {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    @media(min-width: 600px) {
        .appointments-list {
            grid-template-columns: 1fr 1fr;
        }
    }
    .appointment-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgb(0 0 0 / 0.1);
        padding: 1rem 1.5rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: box-shadow 0.3s ease;
    }
    .appointment-card:hover {
        box-shadow: 0 4px 16px rgb(0 0 0 / 0.15);
    }
    .appointment-header {
        font-weight: 700;
        font-size: 1.1rem;
        margin-bottom: 0.5rem;
        color: #1e293b;
    }
    .appointment-date {
        font-size: 0.9rem;
        color: #6b7280;
        margin-bottom: 0.5rem;
    }
    .appointment-details {
        font-size: 0.95rem;
        margin-bottom: 0.5rem;
        color: #374151;
    }
    .badge {
        display: inline-block;
        padding: 0.3rem 0.8rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        user-select: none;
        width: fit-content;
    }
    .badge.confirmed {
        background-color: #d1fae5;
        color: #065f46;
    }
    .badge.booked {
        background-color: #bfdbfe;
        color: #1e40af;
    }
    .badge.cancelled {
        background-color: #fee2e2;
        color: #991b1b;
    }
    .actions {
        margin-top: auto;
        display: flex;
        gap: 10px;
    }
    .btn {
        padding: 8px 14px;
        border: none;
        border-radius: 30px;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.3s ease;
        font-size: 0.9rem;
    }
    .btn.confirm {
        background-color: #2563eb;
        color: white;
    }
    .btn.confirm:hover {
        background-color: #1e40af;
    }
    .btn.cancel {
        background-color: #ef4444;
        color: white;
    }
    .btn.cancel:hover {
        background-color: #b91c1c;
    }
    .no-appointments {
        color: #6b7280;
        font-style: italic;
    }
</style>
</head>
<body>
<header>
    <h1>ScheduleIt</h1>
    <nav>
        <a href="booking.php">Book a Meeting</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>
<main>
    <?php if ($message): ?>
        <div class="message"><?=htmlspecialchars($message)?></div>
    <?php endif; ?>
 
    <section aria-label="Upcoming Meetings">
        <h2>Upcoming Meetings</h2>
        <?php if (count($upcoming) === 0): ?>
            <p class="no-appointments">No upcoming meetings.</p>
        <?php else: ?>
            <div class="appointments-list">
                <?php foreach ($upcoming as $apt): ?>
                    <article class="appointment-card" tabindex="0" aria-label="Meeting with <?=htmlspecialchars($apt['visitor_name'])?> on <?=htmlspecialchars($apt['appointment_date'])?> at <?=htmlspecialchars(substr($apt['appointment_start'], 0, 5))?>">
                        <div class="appointment-header"><?=htmlspecialchars($apt['meetingType'])?></div>
                        <div class="appointment-date"><?=htmlspecialchars(date('l, F j, Y', strtotime($apt['appointment_date'])))?> at <?=htmlspecialchars(substr($apt['appointment_start'], 0, 5))?></div>
                        <div class="appointment-details">With <?=htmlspecialchars($apt['visitor_name'])?> (<?=htmlspecialchars($apt['visitor_email'])?>)</div>
                        <?=statusBadge($apt['status'])?>
                        <?php if ($apt['status'] === 'booked'): ?>
                            <form method="post" class="actions" onsubmit="return confirm('Are you sure?');">
                                <input type="hidden" name="appointment_id" value="<?=intval($apt['id'])?>" />
                                <button type="submit" name="action" value="confirm" class="btn confirm">Confirm</button>
                                <button type="submit" name="action" value="cancel" class="btn cancel">Cancel</button>
                            </form>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
 
    <section aria-label="Past Meetings" style="margin-top: 3rem;">
        <h2>Past Meetings</h2>
        <?php if (count($past) === 0): ?>
            <p class="no-appointments">No past meetings.</p>
        <?php else: ?>
            <div class="appointments-list">
                <?php foreach ($past as $apt): ?>
                    <article class="appointment-card" tabindex="0" aria-label="Past meeting with <?=htmlspecialchars($apt['visitor_name'])?> on <?=htmlspecialchars($apt['appointment_date'])?> at <?=htmlspecialchars(substr($apt['appointment_start'], 0, 5))?>">
                        <div class="appointment-header"><?=htmlspecialchars($apt['meetingType'])?></div>
                        <div class="appointment-date"><?=htmlspecialchars(date('l, F j, Y', strtotime($apt['appointment_date'])))?> at <?=htmlspecialchars(substr($apt['appointment_start'], 0, 5))?></div>
                        <div class="appointment-details">With <?=htmlspecialchars($apt['visitor_name'])?> (<?=htmlspecialchars($apt['visitor_email'])?>)</div>
                        <?=statusBadge($apt['status'])?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
