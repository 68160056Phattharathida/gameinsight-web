<?php
session_start();
// เชื่อมต่อฐานข้อมูล (ปรับตามไฟล์ config ของคุณ เช่น require 'config/db.php';)
$host = 'localhost';
$dbname = 'gameinsight';
$username = 'root'; 
$password = ''; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// ตรวจสอบว่าผู้ใช้ล็อกอินหรือยัง (ปรับตัวแปร session ตามระบบของคุณ)
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('กรุณาเข้าสู่ระบบก่อนทำการรายงาน'); window.location.href='login.php';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' || isset($_GET['review_id'])) {
    $review_id = isset($_POST['review_id']) ? $_POST['review_id'] : $_GET['review_id'];
    $user_id = $_SESSION['user_id'];
    $reason = isset($_POST['reason']) ? $_POST['reason'] : 'เนื้อหาไม่เหมาะสม / ส่อเสียด';

    // บันทึกลงตาราง reports
    $stmt = $pdo->prepare("INSERT INTO reports (target_type, target_id, user_id, reason, status) VALUES ('review', ?, ?, ?, 'pending')");
    
    if ($stmt->execute([$review_id, $user_id, $reason])) {
        echo "<script>alert('ส่งรายงานเรียบร้อยแล้ว แอดมินจะทำการตรวจสอบครับ'); window.history.back();</script>";
    } else {
        echo "<script>alert('เกิดข้อผิดพลาดในการส่งรายงาน'); window.history.back();</script>";
    }
}
?>