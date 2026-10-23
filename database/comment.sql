-- 4. ตารางรีวิวเกม (Review System)
-- ออกแบบให้มีโครงสร้างชัดเจน แยกคะแนน, ข้อดี (Pros), ข้อเสีย (Cons) และเนื้อหาประสบการณ์จริง
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_id INT NOT NULL,
    user_id INT NOT NULL,
    rating DECIMAL(2,1) NOT NULL CHECK (rating >= 1.0 AND rating <= 5.0),
    pros TEXT NOT NULL, -- จุดเด่น / ข้อดี
    cons TEXT NOT NULL, -- จุดด้อย / ข้อเสีย
    content TEXT NOT NULL, -- ประสบการณ์การเล่นจริง
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 5. ตารางระบบถาม-ตอบ / ความคิดเห็น (Interactive Comments / Q&A)
-- กล่องถาม-ตอบใต้รีวิว เพื่อให้ผู้ซื้อสอบถามข้อมูลเชิงลึกจากเจ้าของโพสต์
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    review_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (review_id) REFERENCES reviews(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 6. ตารางระบบความปลอดภัยและการดูแลเนื้อหา (Content Moderation & Security)
-- สำหรับให้ผู้ใช้งานกดรายงานรีวิวหรือคอมเมนต์ที่ละเมิดกฎ เพื่อให้ Admin จัดการ
CREATE TABLE reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    target_type ENUM('review', 'comment') NOT NULL, -- ระบุว่ารายงานรีวิวหรือคอมเมนต์
    target_id INT NOT NULL,
    user_id INT NOT NULL, -- ผู้ที่กดรายงาน
    reason TEXT NOT NULL,
    status ENUM('pending', 'resolved', 'dismissed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);