# ViewG (gameinsight-web)
# โครงร่างโปรเจกต์: ViewG [8-BIT ARCADE]

## 1. หัวข้อและ Pain Point
- **ชื่อโปรเจกต์:** ViewG (เว็บไซต์รีวิวเกมจากผู้เล่นจริงในสไตล์ Pixel 8-bit Retro Dark)
- **Pain Point:**
  - ปัจจุบันรีวิวเกมมักเจอปัญหารีวิวปั่น รีวิวที่เยอะแต่ไม่เป็นประโยชน์ หรือโฆษณาแฝง ทำให้ผู้ซื้อได้รับข้อมูลไม่ตรงกับความจริง
  - ข้อมูลประกอบการตัดสินใจกระจัดกระจาย ต้องเปิดค้นหาหลายแพลตฟอร์มเพื่อดูทั้งความสนุก ความคุ้มค่า และพิกัดร้านค้า

## 2. กลุ่มผู้ใช้งานและทางออก (Target Users & Solution)
- **กลุ่มผู้ใช้งาน:**
  - ผู้เล่นทั่วไป / ผู้ที่กำลังตัดสินใจซื้อ: ต้องการอ่านมุมมองจากคนเล่นจริง และสอบถามข้อสงสัยก่อนซื้อ
  - เกมเมอร์สายแชร์ประสบการณ์: ต้องการพื้นที่แบ่งปันความคิดเห็น เล่าจุดเด่น-จุดด้อยของเกมที่เล่นจบแล้ว
- **Solution:**
  - เว็บไซต์รวบรวมรีวิวแบบมีโครงสร้างชัดเจน (แยกข้อดี ข้อเสีย ลิงก์ร้านค้าทางการ และคะแนนดาวเฉลี่ย)
  - ระบบค้นหาเกมแบบ Case-Insensitive พร้อมระบบแนะนำชื่อเกมอัตโนมัติ (Auto-suggest)
  - ระบบถาม-ตอบและตอบกลับคอมเมนต์ (Nested Comments) ในแต่ละรีวิว เพื่อสอบถามข้อมูลเชิงลึกจากเจ้าของโพสต์โดยตรง
  - ระบบรายงานความไม่เหมาะสม (Report System) พร้อม Modal เลือกเหตุผลการละเมิด 5 ข้อตามข้อกำหนดการให้บริการ (Terms of Service)
  - ระบบจัดการหลังบ้านสำหรับผู้ดูแลระบบ (Admin Console) ตรวจสอบสถิติและคิวการแจ้งรายงานรีวิว

## 3. ขอบเขตฟีเจอร์ (Scope of Features)
- **ระบบจัดการผู้ใช้ (User Management):** สมัครสมาชิกพร้อมระบบกดยอมรับข้อกำหนดการใช้งาน (Terms of Service Checkbox เชื่อมโยงหน้า `terms.php`), เข้าสู่ระบบ, ป้องกัน CSRF Token และแยกสิทธิ์ผู้ใช้ทั่วไป / ผู้ดูแลระบบ (Admin)
- **ระบบคลังเกมและหมวดหมู่ (Game Catalog & Filter):** ค้นหาชื่อเกม/ผู้พัฒนาแบบไม่สนตัวพิมพ์เล็ก-ใหญ่ (`LOWER()`), Auto-suggest ผ่าน `<datalist>`, กรองตามหมวดหมู่, แสดงคะแนนเฉลี่ย (`AVG`) และจำนวนรีวิวรวม
- **ระบบรีวิว (Review System):** โพสต์รีวิวเกม, แยกข้อดี (PROS) / ข้อเสีย (CONS), ให้คะแนน 1.0 - 5.0, แนบภาพประกอบ, แก้ไขและลบรีวิวของตนเอง
- **ระบบถาม-ตอบใต้รีวิว (Q&A Discussions):** ส่งคอมเมนต์สอบถามใต้รีวิว และสามารถกดปุ่มตอบกลับ (Reply) แยกชั้นคอมเมนต์ย่อยได้ (`parent_id`)
- **ระบบรายงานและดูแลความเรียบร้อย (Content Moderation & Report Queue):** ผู้ใช้สามารถกดรายงานรีวิวที่ไม่เหมาะสมผ่าน Modal เลือกเหตุผลได้ และผู้ดูแลระบบสามารถตรวจสอบผ่าน Admin Console เพื่อเลือก "ลบรีวิวทิ้ง" หรือ "เพิกเฉย" ได้

## 4. Draft ER Diagram & Database Schema
- **gi_users:** `user_id` (PK), `username`, `email`, `password_hash`, `role`, `agreed_terms`, `agreed_at`, `created_at`
- **gi_categories:** `category_id` (PK), `category_name`, `description`
- **gi_games:** `game_id` (PK), `category_id` (FK), `title`, `developer`, `cover_image`, `store_url`, `created_at`
- **gi_reviews:** `review_id` (PK), `game_id` (FK), `user_id` (FK), `title`, `content_pros`, `content_cons`, `rating`, `review_image`, `created_at`
- **gi_comments:** `comment_id` (PK), `review_id` (FK), `user_id` (FK), `parent_id` (FK/NULL), `comment_text`, `created_at`
- **gi_reports:** `report_id` (PK), `target_type`, `target_id`, `user_id` (FK), `reason`, `status`, `created_at`

## 5. Project Plan & GitHub Setup
- **Milestone 1: Planning & Schema:** ออกแบบ Wireframe สไตล์ 8-bit, ร่าง ToS และเขียนโครงสร้างฐานข้อมูลตาราง `gi_*`
- **Milestone 2: Auth & Game Catalog:** ระบบ Register (ToS Link) / Login, แสดงคลังเกมพร้อมระบบ Auto-suggest และระบบคำนวณคะแนนเฉลี่ย
- **Milestone 3: Reviews & Discussion:** ระบบโพสต์รีวิว, แยก Pros/Cons, ระบบตอบกลับคอมเมนต์แบบมีลำดับชั้น
- **Milestone 4: Moderation & Deployment:** ระบบ Modal รายงานรีวิว, แดชบอร์ดแอดมินตรวจสอบคิวรายงาน และนำขึ้นเซิร์ฟเวอร์ฐานข้อมูล `angsila.cs.buu.ac.th`