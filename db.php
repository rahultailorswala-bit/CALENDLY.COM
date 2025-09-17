<?php
// db.php - Database connection
$host = 'localhost';
$dbname = 'dbwaxxmp2iccv7';
$user = 'ujbzsmnzu8vkc';
$pass = 'oka0lihz9y9c';
 
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
