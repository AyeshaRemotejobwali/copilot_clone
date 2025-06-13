<?php
$host = 'localhost';
$username = 'uxgukysg8xcbd';
$password = '6imcip8yfmic';
$dbname = 'dby5gc0jaiqhyl';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(['error' => 'Connection failed: ' . $e->getMessage()]));
}
?>
