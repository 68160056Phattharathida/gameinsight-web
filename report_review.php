<?php
session_start();
// เรียกใช้ไฟล์ config/db.php ส่วนกลางของระบบ ViewG
require_once __DIR__ . '/config/db.php';

// ตรวจสอบว่าผู้ใช้ล็อกอินหรือยัง
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('กรุณาเข้าสู่ระบบก่อนทำการรายงาน'); window.location.href='login.php';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['review_id'])) {
    $review_id = isset($_POST['review_id']) ? (int)$_POST['review_id'] : (int)$_GET['review_id'];
    $user_id = $_SESSION['user_id'];
    $reason = isset($_POST['reason']) ? trim($_POST['reason']) : 'เนื้อหาไม่เหมาะสม / ส่อเสียด';

    if ($review_id > 0) {
        try {
            // บันทึกลงตาราง gi_reports
            $stmt = $pdo->prepare("
                INSERT INTO gi_reports (target_type, target_id, user_id, reason, status) 
                VALUES ('review', ?, ?, ?, 'pending')
            ");
            
            if ($stmt->execute([$review_id, $user_id, $reason])) {
                echo "<script>alert('ส่งรายงานเรียบร้อยแล้ว แอดมินจะทำการตรวจสอบค่ะ'); window.history.back();</script>";
                exit();
            }
        } catch (PDOException $e) {
            die("เกิดข้อผิดพลาดในการบันทึกรายงาน: " . $e->getMessage());
        }
    }
    
    echo "<script>alert('เกิดข้อผิดพลาดในการส่งรายงาน'); window.history.back();</script>";
    exit();
}