<?php
// แสดง Error ออกมาบนหน้าจอเพื่อเช็กสาเหตุที่แท้จริง
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/db.php';

// 1. ตรวจสอบว่าล็อกอินและเป็น Admin หรือไม่
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("คุณไม่มีสิทธิ์เข้าถึงส่วนนี้ (สำหรับแอดมินเท่านั้น) <br><a href='games.php'>กลับหน้ารายการเกม</a>");
}

$game_id = isset($_GET['game_id']) ? (int)$_GET['game_id'] : 0;

if ($game_id <= 0) {
    die("รหัสเกมไม่ถูกต้อง <br><a href='games.php'>กลับหน้ารายการเกม</a>");
}

try {
    // 2. ดึง review_id ทั้งหมดของเกมนี้
    $rev_stmt = $pdo->prepare("SELECT review_id FROM gi_reviews WHERE game_id = ?");
    $rev_stmt->execute([$game_id]);
    $review_ids = $rev_stmt->fetchAll(PDO::FETCH_COLUMN);

    // 3. ถ้ามีรีวิว ให้ลบคอมเมนต์ใต้รีวิวเหล่านั้นออกก่อน
    if (!empty($review_ids)) {
        foreach ($review_ids as $r_id) {
            $del_comm = $pdo->prepare("DELETE FROM gi_comments WHERE review_id = ?");
            $del_comm->execute([$r_id]);
        }
    }

    // 4. ลบรีวิวทั้งหมดของเกมนี้
    $del_rev = $pdo->prepare("DELETE FROM gi_reviews WHERE game_id = ?");
    $del_rev->execute([$game_id]);

    // 5. ลบตัวเกมออกจาก gi_games
    $del_game = $pdo->prepare("DELETE FROM gi_games WHERE game_id = ?");
    $del_game->execute([$game_id]);

    // 6. ลบสำเร็จ ส่งกลับไปหน้ารายการเกม
    header("Location: games.php");
    exit();

} catch (PDOException $e) {
    die("เกิดข้อผิดพลาดในการลบข้อมูล: " . $e->getMessage() . "<br><a href='games.php'>กลับหน้ารายการเกม</a>");
}