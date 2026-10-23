-- 1. ตาราง Users (รองรับการยืนยัน Terms of Service)
CREATE TABLE IF NOT EXISTS gi_users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('member', 'admin') DEFAULT 'member',
    agreed_terms TINYINT(1) NOT NULL DEFAULT 0,
    agreed_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. ตาราง Categories
CREATE TABLE IF NOT EXISTS gi_categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. ตาราง Games
CREATE TABLE IF NOT EXISTS gi_games (
    game_id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    developer VARCHAR(100) NULL,
    cover_image VARCHAR(255) NULL,
    store_url VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_gi_games_category FOREIGN KEY (category_id) 
        REFERENCES gi_categories(category_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. ตาราง Reviews
CREATE TABLE IF NOT EXISTS gi_reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    game_id INT NOT NULL,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    content_pros TEXT NULL,
    content_cons TEXT NULL,
    rating TINYINT UNSIGNED NOT NULL CHECK (rating BETWEEN 1 AND 5),
    review_image VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_gi_reviews_game FOREIGN KEY (game_id) 
        REFERENCES gi_games(game_id) ON DELETE CASCADE,
    CONSTRAINT fk_gi_reviews_user FOREIGN KEY (user_id) 
        REFERENCES gi_users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. ตาราง Comments
CREATE TABLE IF NOT EXISTS gi_comments (
    comment_id INT AUTO_INCREMENT PRIMARY KEY,
    review_id INT NOT NULL,
    user_id INT NOT NULL,
    comment_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_gi_comments_review FOREIGN KEY (review_id) 
        REFERENCES gi_reviews(review_id) ON DELETE CASCADE,
    CONSTRAINT fk_gi_comments_user FOREIGN KEY (user_id) 
        REFERENCES gi_users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;