-- ============================================================
--  拾光書架 - 個人閱讀日誌平台  資料庫初始化
--  適用 MySQL 5.7+ / MariaDB
-- ============================================================

DROP DATABASE IF EXISTS bookshelf_db;
CREATE DATABASE bookshelf_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bookshelf_db;

-- ------------------------------------------------------------
--  使用者
-- ------------------------------------------------------------
CREATE TABLE users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(50)  NOT NULL UNIQUE,
    email         VARCHAR(120) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    display_name  VARCHAR(80)  NOT NULL,
    avatar        VARCHAR(255) DEFAULT NULL,
    bio           TEXT         DEFAULT NULL,
    role          ENUM('user','admin') NOT NULL DEFAULT 'user',
    status        ENUM('active','banned') NOT NULL DEFAULT 'active',
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
--  分類
-- ------------------------------------------------------------
CREATE TABLE categories (
    id   INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    slug VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
--  書籍
-- ------------------------------------------------------------
CREATE TABLE books (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT          NOT NULL,
    title         VARCHAR(200) NOT NULL,
    author        VARCHAR(120) NOT NULL,
    isbn          VARCHAR(20)  DEFAULT NULL,
    cover_image   VARCHAR(255) DEFAULT NULL,
    description   TEXT         DEFAULT NULL,
    category_id   INT          DEFAULT NULL,
    status        ENUM('want','reading','finished') NOT NULL DEFAULT 'want',
    rating        TINYINT      DEFAULT NULL,        -- 1~5
    page_count    INT          DEFAULT NULL,
    published_year INT         DEFAULT NULL,
    publisher     VARCHAR(120) DEFAULT NULL,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)     REFERENCES users(id)      ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
--  書評
-- ------------------------------------------------------------
CREATE TABLE reviews (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    book_id    INT  NOT NULL,
    user_id    INT  NOT NULL,
    content    TEXT NOT NULL,
    rating     TINYINT NOT NULL,                     -- 1~5
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
--  收藏 (書架上的書)
-- ------------------------------------------------------------
CREATE TABLE favorites (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT  NOT NULL,
    book_id    INT  NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_fav (user_id, book_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
--  種子資料
-- ============================================================

-- 預設管理員  (帳號 admin  / 密碼 admin123)
-- 預設使用者  (帳號 reader / 密碼 reader123)
INSERT INTO users (username, email, password_hash, display_name, role, bio) VALUES
('admin',  'admin@bookshelf.local',  '$2y$10$6psgbXi8Nx2r.k.H7Lp/P.7SGh3I1E/a6xRUp2cE/VJGEhbdO349K', '站長小編',     'admin', '管理這個書架的小編，喜歡看推理小說。'),
('reader', 'reader@bookshelf.local', '$2y$10$vbdPB8FTprkTX60P0M0t.eTHinRElYDSoPfGxFMh8kQeZ3j3HMXrK', '閱讀愛好者',  'user',  '一年讀 50 本書的普通上班族。');

-- 分類
INSERT INTO categories (name, slug) VALUES
('文學小說', 'literature'),
('商業理財', 'business'),
('心理勵志', 'mindfulness'),
('科幻奇幻', 'scifi'),
('歷史人文', 'history'),
('藝術設計', 'art'),
('生活風格', 'lifestyle'),
('程式科技', 'tech');

-- 示範書籍 (管理員的)
INSERT INTO books (user_id, title, author, description, category_id, status, rating, page_count, published_year, publisher) VALUES
(1, '百年孤寂', '加布列·賈西亞·馬奎斯', '馬康多小鎮的布恩迪亞家族七代興衰，魔幻寫實主義的經典之作。', 1, 'finished', 5, 360, 1967, '皇冠出版社'),
(1, '人類大歷史', '哈拉瑞', '從認知革命到人工智慧，重新審視人類物種的過去與未來。', 5, 'reading', 4, 440, 2014, '遠見天下'),
(2, '原子習慣', '詹姆斯·克利爾', '細微改變帶來巨大成就的實證法則，每天進步 1%。', 3, 'finished', 5, 320, 2018, '方智出版'),
(2, '三體', '劉慈欣', '地球文明與三體文明的史詩級接觸，中國科幻的里程碑。', 4, 'finished', 5, 480, 2008, '貓頭鷹出版'),
(2, '深度工作力', '卡爾·紐波特', '在分心的時代，建立專注力的具體策略。', 3, 'reading', NULL, 280, 2016, '行路出版');
