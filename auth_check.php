<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ฟังก์ชันตรวจสอบการเข้าสู่ระบบ (Auth Guard)
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

// ฟังก์ชันตรวจสอบสิทธิ์ Admin
function requireAdmin() {
    requireLogin(); // ต้องล็อกอินก่อน
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        echo "คุณไม่มีสิทธิ์เข้าถึงหน้านี้";
        exit();
    }
}
?>