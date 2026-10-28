<?php
session_start();
require_once 'config/db.php';

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $review_id = isset($_POST['review_id']) ? (int)$_POST['review_id'] : 0;
    $comment_text = trim($_POST['comment'] ?? '');
    $user_id = $_SESSION['user_id'];

    if (!empty($comment_text) && $review_id > 0) {
        try {
            // บันทึกคอมเมนต์ลงตาราง gi_comments
            $stmt = $pdo->prepare("INSERT INTO gi_comments (review_id, user_id, comment_text, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$review_id, $user_id, $comment_text]);

            // ดึง game_id เพื่อส่งกลับไปยังหน้ารายละเอียดเกมเดิม
            $g_stmt = $pdo->prepare("SELECT game_id FROM gi_reviews WHERE review_id = ?");
            $g_stmt->execute([$review_id]);
            $review = $g_stmt->fetch();

            if ($review) {
                header("Location: game_detail.php?game_id=" . $review['game_id']);
                exit();
            }
        } catch (PDOException $e) {
            die("เกิดข้อผิดพลาดในการบันทึกคอมเมนต์: " . $e->getMessage());
        }
    }
}

// ถ้าส่งค่ามาไม่ครบหรือไม่ถูกต้อง ให้กลับไปหน้ารายการเกม
header("Location: games.php");
exit();
?>