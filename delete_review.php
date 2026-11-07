<?php
session_start();
require_once 'config/db.php';
require_once 'auth_check.php';

requireLogin();

$review_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($review_id <= 0) {
    header("Location: games.php");
    exit();
}

try {
    $current_user_id = $_SESSION['user_id'];
    $current_role = $_SESSION['role'] ?? 'member';

    // ดึงข้อมูลรีวิวเพื่อเช็กเจ้าของ
    $stmt = $pdo->prepare("SELECT user_id FROM gi_reviews WHERE review_id = ?");
    $stmt->execute([$review_id]);
    $review = $stmt->fetch();

    if ($review) {
        // อนุญาตให้ลบเฉพาะเจ้าของรีวิว หรือ Admin
        if ($review['user_id'] == $current_user_id || $current_role === 'admin') {
            // ลบคอมเมนต์ใต้รีวิวนี้ออกก่อน
            $del_comm = $pdo->prepare("DELETE FROM gi_comments WHERE review_id = ?");
            $del_comm->execute([$review_id]);

            // ลบรีวิว
            $del_rev = $pdo->prepare("DELETE FROM gi_reviews WHERE review_id = ?");
            $del_rev->execute([$review_id]);
        }
    }

} catch (PDOException $e) {
    // กรณีเกิด error ให้ข้ามไป
}

// 📌 บังคับส่งกลับไปยังหน้าหลักรายการเกมเสมอ
header("Location: games.php");
exit();
?>