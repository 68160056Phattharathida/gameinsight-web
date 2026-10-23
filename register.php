<?php
session_start();
require_once 'config/db.php';

// สร้าง CSRF Token
if (empty($_SESSION['csrf_token'])) {$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. ตรวจสอบ CSRF Token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !==$_SESSION['csrf_token']) {
        die("CSRF Token Validation Failed");
    }

    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password =$_POST['password'] ?? '';
    $confirm_password =$_POST['confirm_password'] ?? '';
    $agreed_terms = isset($_POST['agreed_terms']) ? 1 : 0;

    // 2. Validation
    if (empty($username) || empty($email) \vert{}\vert{} empty($password) || empty($confirm_password)) {$errors[] = "กรุณากรอกข้อมูลให้ครบทุกช่อง";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {$errors[] = "รูปแบบอีเมลไม่ถูกต้อง";
    }
    if ($password !== $confirm_password) {$errors[] = "รหัสผ่านและยืนยันรหัสผ่านไม่ตรงกัน";
    }
    if (!$agreed_terms) {$errors[] = "คุณต้องยอมรับข้อกำหนดการให้บริการก่อนสมัครสมาชิก";
    }

    // 3. บันทึกลงฐานข้อมูล (ใช้ตาราง users ตาม Schema ใหม่)
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt =$pdo->prepare("INSERT INTO users (username, email, password_hash, agreed_terms, agreed_at) VALUES (?, ?, ?, ?, NOW())");

        try {
            $stmt->execute([$username, $email,$hashed_password, $agreed_terms]);$success = "สมัครสมาชิกสำเร็จเรียบร้อยแล้ว!";
            unset($_SESSION['csrf_token']);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {$errors[] = "Username หรือ Email นี้ถูกใช้งานแล้ว";
            } else {
                $errors[] = "เกิดข้อผิดพลาด: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก - GameInsight</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; display: flex; justify-content: center; padding-top: 40px; }
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input[type="text"], .form-group input[type="email"], .form-group input[type="password"] {
            width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;
        }
        .btn { width: 100%; padding: 10px; background-color: #28a745; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; }
        .btn:hover { background-color: #218838; }
        .btn-login { display: inline-block; width: 100%; padding: 10px; background-color: #007bff; color: