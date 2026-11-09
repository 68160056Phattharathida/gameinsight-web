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
    $review_id = isset($_POST['review_id']) ? (int)$_POST['review_id'] : (isset($_GET['review_id']) ? (int)$_GET['review_id'] : 0);
    $game_id   = isset($_POST['game_id']) ? (int)$_POST['game_id'] : (isset($_GET['game_id']) ? (int)$_GET['game_id'] : 0);
    $user_id   = $_SESSION['user_id'];
    $reason    = !empty($_POST['reason']) ? trim($_POST['reason']) : (!empty($_GET['reason']) ? trim($_GET['reason']) : 'เนื้อหาไม่เหมาะสม / หยาบคาย / ส่อเสียด');

    if ($review_id > 0) {
        try {
            // บันทึกลงตาราง gi_reports
            $stmt = $pdo->prepare("
                INSERT INTO gi_reports (target_type, target_id, user_id, reason, status) 
                VALUES ('review', ?, ?, ?, 'pending')
            ");
            
            if ($stmt->execute([$review_id, $user_id, $reason])) {
                $redirect_url = $game_id > 0 ? "game_detail.php?game_id={$game_id}" : "games.php";
                echo "<script>alert('ส่งรายงานเรียบร้อยแล้ว แอดมินจะทำการตรวจสอบค่ะ'); window.location.href='{$redirect_url}';</script>";
                exit();
            }
        } catch (PDOException $e) {
            die("เกิดข้อผิดพลาดในการบันทึกรายงาน: " . $e->getMessage());
        }
    }
    
    echo "<script>alert('เกิดข้อผิดพลาดในการส่งรายงาน'); window.history.back();</script>";
    exit();
}