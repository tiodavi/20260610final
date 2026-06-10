<?php
/**
 * 拾光書架 - 設定檔
 * 連線資訊、上傳限制、網站基本資訊
 */

// 顯示錯誤（開發用；上線請改為 0）
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 時區
date_default_timezone_set('Asia/Taipei');

// 啟動 session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---------- 資料庫 ----------
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'bookshelf_db');
define('DB_USER', 'root');
define('DB_PASS', '');                // XAMPP 預設無密碼
define('DB_CHARSET', 'utf8mb4');

// ---------- 網站 ----------
define('SITE_NAME',  '拾光書架');
define('SITE_TAG',   '一本一本，慢慢讀，慢慢活。');
define('SITE_URL',   'http://localhost/20260610final');
// 專案根 URL (在子目錄如 /admin/ 下也能正確載入 CSS/JS/圖片)
// 若把專案搬到別的位置，只改這個常數即可
define('BASE_URL',   '/20260610final');

// ---------- 上傳 ----------
define('UPLOAD_DIR',     __DIR__ . '/../uploads/');
define('UPLOAD_URL',     'uploads/');
define('MAX_UPLOAD_MB',  5);
define('ALLOWED_IMG',    ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('MAX_IMG_WIDTH',  1600);     // 超過自動縮圖
