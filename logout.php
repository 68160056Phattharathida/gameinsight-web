<?php
session_start();

// ล้างค่า Session ทั้งหมด
$_SESSION = array();

// ลบ Session Cookie ในเบราว์เซอร์
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// ทำลาย Session บนเซิร์ฟเวอร์
session_destroy();

// ส่งกลับไปหน้าเข้าสู่ระบบ
header("Location: login.php");
exit();
?>