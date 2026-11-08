<?php
$host = "localhost";
$dbname = "your_database_name"; // ใส่ชื่อฐานข้อมูล
$username = "your_username";  // ใส่ชื่อผู้ใช้ฐานข้อมูล
$password = "your_password"; // ใส่รหัสผ่านฐานข้อมูล

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("เชื่อมต่อฐานข้อมูลล้มเหลว: " . $e->getMessage());
}

