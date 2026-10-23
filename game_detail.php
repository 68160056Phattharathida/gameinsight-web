<?php
// เชื่อมต่อฐานข้อมูล (ปรับ Username / Password ให้ตรงกับเซิร์ฟเวอร์ของคุณ เช่น angsila)
$host = 'localhost';
$dbname = 'gameinsight';
$username = 'root'; 
$password = ''; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// รับค่า game_id จาก URL (ถ้าไม่มีให้กำหนดค่าเริ่มต้นเป็น 1)
$game_id = isset($_GET['game_id']) ? (int)$_GET['game_id'] : 1;

// ดึงข้อมูลเกมและหมวดหมู่
$stmt = $pdo->prepare("SELECT g.*, c.name as category_name FROM games g LEFT JOIN categories c ON g.category_id = c.id WHERE g.id = ?");
$stmt->execute([$game_id]);
$game = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$game) {
    die("ไม่พบข้อมูลเกมนี้ในระบบ");
}

// ดึงข้อมูลรีวิวทั้งหมดของเกมนี้ พร้อมชื่อผู้ใช้งาน
$review_stmt = $pdo->prepare("
    SELECT r.*, u.username 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.game_id = ? 
    ORDER BY r.created_at DESC
");
$review_stmt->execute([$game_id]);
$reviews = $review_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GameInsight - <?php echo htmlspecialchars($game['title']); ?></title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Prompt', sans-serif; }
    </style>
</head>
<body class="bg-pink-50 text-gray-800 min-h-screen">

    <!-- Navbar -->
    <nav class="bg-white shadow-sm border-b border-pink-100">
        <div class="max-w-6xl mx-auto px-4 py-4 flex justify-between items-center">
            <a href="#" class="text-2xl font-bold text-pink-600">🎮 GameInsight</a>
            <div class="space-x-4">
                <a href="#" class="text-gray-600 hover:text-pink-600">หน้าแรก</a>
                <a href="add_review.php?game_id=<?php echo $game_id; ?>" class="text-gray-600 hover:text-pink-600">เขียนรีวิว</a>
                <a href="#" class="bg-pink-500 text-white px-4 py-2 rounded-full shadow hover:bg-pink-600">เข้าสู่ระบบ</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 py-8">
        
        <!-- Game Header Section -->
        <div class="bg-white rounded-2xl shadow-sm border border-pink-100 p-6 mb-8 flex flex-col md:flex-row gap-6">
            <img src="<?php echo htmlspecialchars($game['cover_image'] ?? 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=600&q=80'); ?>" alt="Game Cover" class="w-full md:w-1/3 rounded-xl object-cover h-56">
            <div class="flex-1">
                <span class="bg-pink-100 text-pink-700 text-xs px-3 py-1 rounded-full font-medium"><?php echo htmlspecialchars($game['category_name'] ?? 'Action RPG'); ?></span>
                <h1 class="text-3xl font-bold text-gray-900 mt-2"><?php echo htmlspecialchars($game['title']); ?></h1>
                <p class="text-gray-500 text-sm mt-1">พัฒนาโดย: <?php echo htmlspecialchars($game['developer']); ?></p>
                <p class="text-gray-600 mt-4 text-sm leading-relaxed"><?php echo htmlspecialchars($game['description']); ?></p>
                <div class="mt-4">
                    <a href="<?php echo htmlspecialchars($game['store_links'] ?? '#'); ?>" target="_blank" class="bg-gray-900 text-white text-xs px-4 py-2 rounded-xl hover:bg-gray-800 font-medium inline-block">หน้าร้านค้าทางการ →</a>
                </div>
            </div>
        </div>

        <!-- Review Section Header & Add Review Button -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-900">รีวิวจากผู้เล่น (<?php echo count($reviews); ?>)</h2>
            <a href="add_review.php?game_id=<?php echo $game_id; ?>" class="bg-pink-500 text-white text-sm px-4 py-2 rounded-xl shadow hover:bg-pink-600 font-medium flex items-center gap-1.5">
                ✍️ เขียนรีวิวเกมนี้
            </a>
        </div>

        <?php if (empty($reviews)): ?>
            <!-- หากยังไม่มีรีวิวในระบบ -->
            <div class="bg-white rounded-2xl shadow-sm border border-pink-100 p-8 text-center text-gray-400 mb-8">
                <p>ยังไม่มีบทวิจารณ์สำหรับเกมนี้ในระบบ</p>
                <p class="text-xs mt-1">มาเป็นคนแรกที่แชร์ประสบการณ์การเล่นเกมนี้กันเถอะ!</p>
            </div>
        <?php else: ?>
            <?php foreach ($reviews as $review): ?>
                <!-- Review Structured Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-pink-100 p-6 mb-6">
                    <div class="flex justify-between items-center border-b border-gray-100 pb-4 mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-pink-200 rounded-full flex items-center justify-center font-bold text-pink-700">
                                <?php echo mb_substr($review['username'], 0, 1); ?>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($review['username']); ?></h3>
                                <p class="text-xs text-gray-400">รีวิวเมื่อ: <?php echo $review['created_at']; ?></p>
                            </div>
                        </div>
                        <div class="bg-amber-100 text-amber-800 px-4 py-1.5 rounded-full font-bold text-lg flex items-center gap-1">
                            ⭐ <?php echo number_format($review['rating'], 1); ?> / 5.0
                        </div>
                    </div>

                    <!-- Review Body -->
                    <div class="mb-6">
                        <h4 class="font-semibold text-gray-800 mb-2">ประสบการณ์การเล่นจริง</h4>
                        <p class="text-gray-600 text-sm leading-relaxed">
                            <?php echo nl2br(htmlspecialchars($review['content'])); ?>
                        </p>
                    </div>

                    <!-- Pros & Cons Grid -->
                    <div class="grid md:grid-cols-2 gap-4 mb-6">
                        <!-- Pros -->
                        <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-4">
                            <h5 class="font-semibold text-emerald-800 mb-2 flex items-center gap-1.5">✅ ข้อดี (Pros)</h5>
                            <p class="text-sm text-emerald-700 whitespace-pre-line"><?php echo htmlspecialchars($review['pros']); ?></p>
                        </div>
                        <!-- Cons -->
                        <div class="bg-rose-50 border border-rose-100 rounded-xl p-4">
                            <h5 class="font-semibold text-rose-800 mb-2 flex items-center gap-1.5">❌ ข้อเสีย (Cons)</h5>
                            <p class="text-sm text-rose-700 whitespace-pre-line"><?php echo htmlspecialchars($review['cons']); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Interactive Comments / Q&A Section -->
                <div class="bg-white rounded-2xl shadow-sm border border-pink-100 p-6 mb-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">💬 สอบถามข้อมูลเชิงลึก / ความคิดเห็น</h3>
                    
                    <!-- Comment Form -->
                    <form action="submit_comment.php" method="POST" class="mb-6">
                        <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                        <textarea name="comment" rows="3" required placeholder="สอบถามผู้รีวิว เช่น เกมนี้เล่นบนจอยสติ๊กสะดวกไหม หรือมีภาษาไทยไหม..." class="w-full border border-gray-200 rounded-xl p-3 text-sm focus:outline-none focus:border-pink-500"></textarea>
                        <div class="flex justify-end mt-2">
                            <button type="submit" class="bg-pink-500 text-white text-sm px-5 py-2 rounded-xl shadow hover:bg-pink-600 font-medium">ส่งคำถาม</button>
                        </div>
                    </form>

                    <!-- Comment List (Q&A) -->
                    <div class="space-y-4 border-t border-gray-100 pt-4">
                        <?php
                        // ดึงคอมเมนต์ของรีวิวนี้
                        $comm_stmt = $pdo->prepare("
                            SELECT c.*, u.username 
                            FROM comments c 
                            JOIN users u ON c.user_id = u.id 
                            WHERE c.review_id = ? 
                            ORDER BY c.created_at ASC
                        ");
                        $comm_stmt->execute([$review['id']]);
                        $comments = $comm_stmt->fetchAll(PDO::FETCH_ASSOC);
                        ?>

                        <?php if (empty($comments)): ?>
                            <p class="text-xs text-gray-400 text-center py-2">ยังไม่มีคำถามหรือความคิดเห็นใต้รีวิวนี้</p>
                        <?php else: ?>
                            <?php foreach ($comments as $comm): ?>
                                <div class="bg-pink-50/50 rounded-xl p-4">
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="font-semibold text-sm text-gray-900"><?php echo htmlspecialchars($comm['username']); ?></span>
                                        <span class="text-xs text-gray-400"><?php echo $comm['created_at']; ?></span>
                                    </div>
                                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($comm['comment']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </main>

    <!-- Footer -->
    <footer class="text-center py-6 text-gray-400 text-xs">
        &copy; 2026 GameInsight. All rights reserved.
    </footer>

</body>
</html>