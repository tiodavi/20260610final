# 拾光書架 · Bookshelf of Memories

> 一個以「復古書房 / 羊皮紙 / 燙金」為視覺主題的個人閱讀日誌平台。
> 課堂專題作業 · PHP + MySQL 純手刻，無前端框架。

---

## 🌟 功能特色

| 模組 | 說明 |
|---|---|
| 會員系統 | 註冊、登入、修改密碼、編輯個人檔案、上傳頭像 |
| 書籍管理 | 新增 / 編輯 / 刪除書本、上傳書封、評分、閱讀狀態 |
| 書評系統 | 為書寫書評、星等評分、自動計算平均 |
| 收藏書架 | 把喜歡的書加入個人書架 |
| 分類探索 | 8 大分類、依狀態/評分/書名排序、關鍵字搜尋 |
| 後台管理 | 儀表板統計、會員/書/書評/分類 CRUD、權限控管 |
| 圖片上傳 | 書封 + 頭像、類型/大小驗證、自動縮圖 |

---

## 🚀 一鍵啟動

**雙擊 `start.bat`** → 自動啟動 Apache + MySQL + 開瀏覽器。

```
http://localhost/20260610final/
```

**測試帳號**：

| 角色 | 帳號 | 密碼 |
|------|------|------|
| 管理員 | `admin` | `admin123` |
| 一般會員 | `reader` | `reader123` |

**停止服務**：雙擊 `stop.bat`

**重建資料庫**（清空所有資料）：雙擊 `reset_db.bat`

---

## 🛠 環境需求

- **XAMPP**（PHP 8.0+、MySQL 8.0+ / MariaDB 10.4+）
- Windows 10 / 11
- 瀏覽器：Chrome / Edge / Firefox（支援中文襯線字型）

### 驗證 XAMPP 已就緒

1. 啟動 XAMPP Control Panel
2. 啟動 **Apache**（port 80）與 **MySQL**（port 3306）
3. 確認 MySQL `root` 帳號無密碼（XAMPP 預設）

---

## 📁 專案結構

```
D:\2016pra\20260610final\
├── start.bat / stop.bat / reset_db.bat    ← 一鍵執行檔
├── README.md
│
├── index.php              ← 首頁
├── login.php              ← 登入
├── register.php           ← 註冊
├── logout.php
├── bookshelf.php          ← 書架探索 (搜尋/篩選/分頁)
├── book_detail.php        ← 書本詳情 + 書評
├── my_shelf.php           ← 我的書架 (收藏/我新增)
├── add_book.php           ← 新增書 + 上傳封面
├── edit_book.php          ← 編輯書
├── delete_book.php
├── add_review.php         ← 撰寫/編輯書評
├── delete_review.php
├── profile.php            ← 個人檔案
├── about.php              ← 關於本站
│
├── admin/                 ← 後台
│   ├── index.php          ← 儀表板
│   ├── users.php          ← 會員管理
│   ├── books.php          ← 書籍管理
│   ├── reviews.php        ← 書評管理
│   └── categories.php     ← 分類管理
│
├── includes/              ← 共用元件
│   ├── config.php         ← 設定 (DB / BASE_URL / 上傳)
│   ├── db.php             ← PDO 連線
│   ├── functions.php      ← 認證 / 上傳 / CSRF / 助手
│   ├── header.php         ← 共用頁首
│   └── footer.php         ← 共用頁尾
│
├── assets/
│   ├── css/style.css      ← 主樣式
│   ├── js/main.js         ← 前端互動
│   └── images/            ← 預設圖、書封
│       ├── default_avatar.svg
│       ├── default_cover.svg
│       ├── hero_bg.svg
│       └── books/         ← 5 張示範書封
│
├── uploads/               ← 使用者上傳 (自動建立)
│   ├── covers/
│   └── avatars/
│
├── sql/init.sql           ← 資料庫初始化
└── screenshots/           ← 成品截圖
```

---

## 🗄 資料庫結構

```sql
users        (id, username, email, password_hash, display_name, avatar, bio, role, status, created_at)
categories   (id, name, slug)
books        (id, user_id, title, author, isbn, cover_image, description,
              category_id, status, rating, page_count, published_year, publisher, created_at)
reviews      (id, book_id, user_id, content, rating, created_at)
favorites    (id, user_id, book_id, created_at)  -- UNIQUE(user, book)
```

關聯：

```
users ──┬─< books ──< reviews
        │     ↑
        │     └──< favorites
        └─< reviews
```

---

## 🔧 技術棧

- **後端**：PHP 8 + PDO (prepared statements 防 SQL injection)
- **資料庫**：MySQL / MariaDB，utf8mb4
- **前端**：原生 HTML / CSS / JavaScript，無框架
- **字體**：Google Fonts — Noto Serif TC, Noto Sans TC, Cormorant Garamond
- **安全機制**：
  - `password_hash()` bcrypt
  - CSRF token
  - 圖片類型/大小/`getimagesize` 雙重驗證
  - 自動縮圖 (寬度超過 1600px)
  - `htmlspecialchars` XSS 防護

---

## 🎨 設計風格

- **配色**：羊皮紙米白 `#FAF6F0` × 墨黑 `#2C1810` × 酒紅 `#8B3A3A` × 燙金 `#C9A961`
- **字體**：襯線 (Noto Serif TC) 用於標題與書名，營造書卷氣
- **互動細節**：
  - 書封 hover 時輕浮 + 陰影加深
  - 「已讀完」使用紅色書法印章 (旋轉 -4°)
  - 段落標題前綴 ❦ 裝飾符
  - Hero 區使用燙金放射狀漸層

---

## 📦 移植到別台電腦

1. 複製整個 `20260610final` 資料夾
2. 安裝 XAMPP
3. 雙擊 `start.bat`（會自動建目錄聯接 + 啟動服務）
4. 第一次啟動若 DB 沒建，執行 `reset_db.bat` 建庫

**修改網站根 URL**（如搬到 `/myproject/`）：

編輯 `includes/config.php`：

```php
define('BASE_URL', '/myproject');  // ← 改這行
```

---

## 📷 成品截圖

`screenshots/` 資料夾內有：
- `home.png` — 首頁
- `login.png` — 登入頁
- `bookshelf.png` — 書架探索
- `book_detail.png` — 書本詳情
- `admin_logged_in.png` — 後台儀表板

---

## 📝 授權

課堂專題作品 · 僅供學術用途
