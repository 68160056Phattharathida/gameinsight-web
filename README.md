# gameinsight-web
# โครงร่างโปรเจกต์: GameInsight

## 1. หัวข้อและ Pain Point
- **ชื่อโปรเจกต์:** GameInsight (เว็บไซต์รีวิวเกมจากผู้เล่นจริง)
- **Pain Point:**
  - ปัจจุบันรีวิวเกมมักเจอปัญหารีวิวปั่น รีวิวที่เยอะแต่ไม่เป็นประโยชน์ หรือโฆษณาแฝง ทำให้ผู้ซื้อได้รับข้อมูลไม่ตรงกับความจริง
  - ข้อมูลประกอบการตัดสินใจกระจัดกระจาย ต้องเปิดค้นหาหลายแพลตฟอร์มเพื่อดูทั้งความสนุก ความคุ้มค่า และพิกัดร้านค้า

## 2. กลุ่มผู้ใช้งานและทางออก (Target Users & Solution)
- **กลุ่มผู้ใช้งาน:**
  - ผู้เล่นทั่วไป / ผู้ที่กำลังตัดสินใจซื้อ: ต้องการอ่านมุมมองจากคนเล่นจริง และสอบถามข้อสงสัยก่อนซื้อ
  - เกมเมอร์สายแชร์ประสบการณ์: ต้องการพื้นที่แบ่งปันความคิดเห็น เล่าจุดเด่น-จุดด้อยของเกมที่เล่นจบแล้ว
- **Solution:**
  - เว็บไซต์รวบรวมรีวิวแบบมีโครงสร้างชัดเจน (แยกข้อดี ข้อเสีย ลิงก์ร้านค้าทางการ และคะแนน)
  - ระบบถาม-ตอบในแต่ละรีวิว เพื่อสอบถามข้อมูลเชิงลึกจากเจ้าของโพสต์โดยตรง
  - ระบบข้อกำหนดการให้บริการ (Terms of Service) และปฏิเสธความรับผิดชอบ เพื่อป้องกันปัญหาการละเมิดลิขสิทธิ์และสแปม

## 3. ขอบเขตฟีเจอร์ (Scope of Features)
- **ระบบจัดการผู้ใช้ (User Management):** สมัครสมาชิกพร้อมระบบกดยอมรับข้อกำหนดการใช้งาน (Terms of Service Checkbox), เข้าสู่ระบบ, จัดการโปรไฟล์
- **ระบบจัดการข้อมูลเกมและหมวดหมู่ (Game & Category Catalog):** จัดแบ่งตามประเภทเกม, ข้อมูลผู้พัฒนา, แปะลิงก์ร้านค้าทางการ (Steam, Epic Games ฯลฯ)
- **ระบบรีวิว (Review System):** โพสต์รีวิวเกม (แยกข้อดี/ข้อเสีย), แนบภาพประกอบ, ให้คะแนน (Rating)
- **ระบบถาม-ตอบใต้รีวิว (Interactive Comments):** สอบถามและตอบกลับข้อสงสัยระหว่างผู้ซื้อกับผู้รีวิว
- **ระบบดูแลความเรียบร้อย (Content Moderation):** ผู้ดูแลระบบสามารถลบรีวิวหรือคอมเมนต์ที่ละเมิดข้อตกลงได้

## 4. Draft ER Diagram
- **users:** `user_id` (PK), `username`, `email`, `password_hash`, `role`, `agreed_terms`, `agreed_at`, `created_at`
- **categories:** `category_id` (PK), `category_name`, `description`
- **games:** `game_id` (PK), `category_id` (FK), `title`, `developer`, `cover_image`, `store_url`, `created_at`
- **reviews:** `review_id` (PK), `game_id` (FK), `user_id` (FK), `title`, `content_pros`, `content_cons`, `rating`, `review_image`, `created_at`
- **comments:** `comment_id` (PK), `review_id` (FK), `user_id` (FK), `comment_text`, `created_at`

## 5. Project Plan & GitHub Setup
- **Milestone 1: Planning & Schema:** ออกแบบ Wireframe, ร่าง ToS และเขียน `schema.sql`
- **Milestone 2: Auth & Game Catalog:** ระบบ Register (ตรวจ Checkbox ToS) / Login และ CRUD รายชื่อเกม
- **Milestone 3: Reviews & Discussion:** ระบบโพสต์รีวิว อัปโหลดภาพ และคอมเมนต์ถาม-ตอบ
- **Milestone 4: Testing & Deployment:** ทดสอบความปลอดภัย (XSS, SQL Injection, CSRF) และนำขึ้นเซิร์ฟเวอร์
