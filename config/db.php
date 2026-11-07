<?php
$host = "localhost";
$dbname = "s68160056"; // เปลี่ยนเป็นชื่อฐานข้อมูลใน phpMyAdmin
$username = "s68160056";
$password = "aWyGH9R4";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("เชื่อมต่อฐานข้อมูลล้มเหลว: " . $e->getMessage());
}

