-- สร้างฐานข้อมูล GameInsight
CREATE DATABASE IF NOT EXISTS gameinsight CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gameinsight;

-- 1. ตารางจัดการผู้ใช้งาน (User Management)
-- รองรับระบบสมัครสมาชิก, บทบาท (User/Admin) และการยอมรับเงื่อนไข (TOS)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    tos_accepted BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. ตารางหมวดหมู่เกม (Game Categories)
-- เช่น Action, RPG, Indie ฯลฯ
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

-- 3. ตารางข้อมูลเกม (Game Catalog)
-- เก็บข้อมูลเกม รายละเอียด ผู้พัฒนา และลิงก์แหล่งจำหน่าย (Steam, Epic ฯลฯ)
CREATE TABLE games (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    developer VARCHAR(100),
    category_id INT,
    store_links TEXT, -- เก็บข้อมูลลิงก์ร้านค้า (เช่น JSON หรือ Text)
    cover_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);