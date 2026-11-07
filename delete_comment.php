<?php
session_start();
require_once 'config/db.php';
require_once 'auth_check.php';

requireLogin();

$comment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$game_id = isset($_GET['game_id']) ? (int)$_GET['game_id'] : 0;

if ($comment_id <= 0) {
    header("Location: games.php");
    exit();
}

try {
    $current_user_id = $_SESSION['user_id'];
    $current_role = $_SESSION['role'] ?? 'member';

    // เช็กว่าใครเป็นเจ้าของคอมเมนต์
    $stmt = $pdo->prepare("SELECT user_id FROM gi_comments WHERE comment_id = ?");
    $stmt->execute([$comment_id]);
    $comment = $stmt->fetch();

    if ($comment) {
        // อนุญาตให้ลบเฉพาะเจ้าของคอมเมนต์ หรือ Admin
        if ($comment['user_id'] == $current_user_id || $current_role === 'admin') {
            $delStmt = $pdo->prepare("DELETE FROM gi_comments WHERE comment_id = ?");
            $delStmt->execute([$comment_id]);
        }
    }

} catch (PDOException $e) {
    // ซ่อน Error ในระบบ production
}

// เด้งกลับไปที่หน้ารายละเอียดเกมเดิม
if ($game_id > 0) {
    header("Location: game_detail.php?game_id=" . $game_id);
} else {
    header("Location: games.php");
}
exit();
?>