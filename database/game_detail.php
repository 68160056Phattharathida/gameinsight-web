<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GameInsight - รายละเอียดรีวิวเกม</title>
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
                <a href="#" class="text-gray-600 hover:text-pink-600">เขียนรีวิว</a>
                <a href="#" class="bg-pink-500 text-white px-4 py-2 rounded-full shadow hover:bg-pink-600">เข้าสู่ระบบ</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 py-8">
        
        <!-- Game Header Section -->
        <div class="bg-white rounded-2xl shadow-sm border border-pink-100 p-6 mb-8 flex flex-col md:flex-row gap-6">
            <img src="https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=600&q=80" alt="Game Cover" class="w-full md:w-1/3 rounded-xl object-cover h-56">
            <div class="flex-1">
                <span class="bg-pink-100 text-pink-700 text-xs px-3 py-1 rounded-full font-medium">Action RPG</span>
                <h1 class="text-3xl font-bold text-gray-900 mt-2">Cyber Neon Odyssey</h1>
                <p class="text-gray-500 text-sm mt-1">พัฒนาโดย: Studio X • วางจำหน่าย: Steam, Epic Games</p>
                <p class="text-gray-600 mt-4 text-sm leading-relaxed">เกมแนว Open-world ไซเบอร์พังก์ที่เต็มไปด้วยเนื้อเรื่องเข้มข้น ระบบต่อสู้รวดเร็วและงานภาพสุดอลังการที่ดึงดูดใจผู้เล่นสายสำรวจโลกอนาคต</p>
            </div>
        </div>

        <!-- Review Structured Card (Pros / Cons / Rating) -->
        <div class="bg-white rounded-2xl shadow-sm border border-pink-100 p-6 mb-8">
            <div class="flex justify-between items-center border-b border-gray-100 pb-4 mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-pink-200 rounded-full flex items-center justify-center font-bold text-pink-700">P</div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Phattharathida</h3>
                        <p class="text-xs text-gray-400">รีวิวเมื่อ: 24 ตุลาคม 2569</p>
                    </div>
                </div>
                <div class="bg-amber-100 text-amber-800 px-4 py-1.5 rounded-full font-bold text-lg flex items-center gap-1">
                    ⭐ 4.8 / 5.0
                </div>
            </div>

            <!-- Review Body -->
            <div class="mb-6">
                <h4 class="font-semibold text-gray-800 mb-2">ประสบการณ์การเล่นจริง</h4>
                <p class="text-gray-600 text-sm leading-relaxed">
                    หลังจากใช้เวลาเล่นไปกว่า 50 ชั่วโมง เนื้อเรื่องช่วงกลางทำออกมาได้พีคมากๆ เพลงประกอบบิ้วอารมณ์สุดๆ แต่ช่วงแรกๆ อาจจะต้องทำความเข้าใจระบบแฮกข้อมูลเยอะหน่อย ใครชอบแนวสำรวจเมืองกว้างๆ ห้ามพลาดเลยครับ!
                </p>
            </div>

            <!-- Pros & Cons Grid -->
            <div class="grid md:grid-cols-2 gap-4 mb-6">
                <!-- Pros -->
                <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-4">
                    <h5 class="font-semibold text-emerald-800 mb-2 flex items-center gap-1.5">
                        ✅ ข้อดี (Pros)
                    </h5>
                    <ul class="text-sm text-emerald-700 space-y-1 list-disc list-inside">
                        <li>กราฟิกสวยงาม อิงบรรยากาศไซเบอร์พังก์สมจริง</li>
                        <li>ระบบคอมโบต่อสู้ลื่นไหลและสนุกมาก</li>
                        <li>เนื้อเรื่องหลักมีความยาวคุ้มค่าเงิน</li>
                    </ul>
                </div>
                <!-- Cons -->
                <div class="bg-rose-50 border border-rose-100 rounded-xl p-4">
                    <h5 class="font-semibold text-rose-800 mb-2 flex items-center gap-1.5">
                        ❌ ข้อเสีย (Cons)
                    </h5>
                    <ul class="text-sm text-rose-700 space-y-1 list-disc list-inside">
                        <li>บั๊กประปรายในเควสเสริมบางเควส</li>
                        <li>ต้องการสเปคคอมพิวเตอร์ค่อนข้างสูง</li>
                    </ul>
                </div>
            </div>

            <!-- Store Links -->
            <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                <span class="text-sm font-medium text-gray-600">แหล่งจำหน่าย:</span>
                <a href="#" class="bg-gray-900 text-white text-xs px-3 py-1.5 rounded-lg hover:bg-gray-800">Steam (฿1,090)</a>
                <a href="#" class="bg-blue-600 text-white text-xs px-3 py-1.5 rounded-lg hover:bg-blue-500">Epic Games</a>
            </div>
        </div>

        <!-- Interactive Comments / Q&A Section -->
        <div class="bg-white rounded-2xl shadow-sm border border-pink-100 p-6">
            <h3 class="text-xl font-bold text-gray-900 mb-4">💬 สอบถามข้อมูลเชิงลึก / ความคิดเห็น</h3>
            
            <!-- Comment Form -->
            <form action="submit_comment.php" method="POST" class="mb-6">
                <input type="hidden" name="review_id" value="1">
                <textarea name="comment" rows="3" required placeholder="สอบถามผู้รีวิว เช่น เกมนี้เล่นบนจอยสติ๊กสะดวกไหม หรือมีภาษาไทยไหม..." class="w-full border border-gray-200 rounded-xl p-3 text-sm focus:outline-none focus:border-pink-500"></textarea>
                <div class="flex justify-end mt-2">
                    <button type="submit" class="bg-pink-500 text-white text-sm px-5 py-2 rounded-xl shadow hover:bg-pink-600 font-medium">ส่งคำถาม</button>
                </div>
            </form>

            <!-- Comment List (Q&A) -->
            <div class="space-y-4 border-t border-gray-100 pt-4">
                <!-- Sample Comment 1 -->
                <div class="bg-pink-50/50 rounded-xl p-4">
                    <div class="flex justify-between items-center mb-1">
                        <span class="font-semibold text-sm text-gray-900">Gammer_TH</span>
                        <span class="text-xs text-gray-400">2 ชั่วโมงที่แล้ว</span>
                    </div>
                    <p class="text-sm text-gray-600">เกมนี้รองรับจอย DualSense ของ PS5 แบบสั่นสมจริงไหมครับ?</p>
                    
                    <!-- Owner Reply (Nested) -->
                    <div class="mt-3 pl-4 border-l-2 border-pink-300 ml-2">
                        <div class="flex justify-between items-center mb-1">
                            <span class="font-semibold text-xs text-pink-700">Phattharathida (เจ้าของโพสต์)</span>
                            <span class="text-xs text-gray-400">1 ชั่วโมงที่แล้ว</span>
                        </div>
                        <p class="text-xs text-gray-600">รองรับเต็มรูปแบบเลยครับ มีระบบ Adaptive triggers ด้วย เล่นแล้วฟินมาก!</p>
                    </div>
                </div>
            </div>

        </div>

    </main>

    <!-- Footer -->
    <footer class="text-center py-6 text-gray-400 text-xs">
        &copy; 2026 GameInsight. All rights reserved.
    </footer>

</body>
</html>