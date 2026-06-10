<?php
require_once __DIR__ . '/includes/functions.php';
$page_title = '關於拾光書架';
require_once __DIR__ . '/includes/header.php';
?>
<div class="card" style="max-width:780px; margin:0 auto;">
  <h2>關於 <?= e(SITE_NAME) ?></h2>
  <p style="font-size:1.05rem; color:var(--ink-soft); line-height:1.9;">
    <strong>拾光書架</strong>是一個屬於閱讀者的私人日誌平台。
    在這裡，你可以：</p>
  <ul style="line-height:2; color:var(--ink-soft);">
    <li>📖 為每一本你讀過、正在讀、或想讀的書建立紀錄</li>
    <li>🖼  上傳書封照片，打造專屬的視覺書架</li>
    <li>✎  寫下書評，與其他書友分享你的想法</li>
    <li>⭐ 為書本評分，標記閱讀狀態</li>
    <li>👥 收藏喜歡的書友的書，互相激盪</li>
  </ul>

  <h3 class="mt-3">設計理念</h3>
  <p style="line-height:1.9; color:var(--ink-soft);">
    靈感來自一間老式書房：羊皮紙的米色、墨黑的文字、酒紅與燙金的點綴。
    整個介面採用 <em>Noto Serif TC</em> 襯線字體，希望你在翻動「書頁」時，
    能感受到紙張的溫度，與文字的份量。
  </p>

  <div class="card mt-3" style="background:var(--bg-2);">
    <h3 class="mt-0">技術簡介</h3>
    <p>本作品為課堂專題，使用純 PHP + MySQL 開發，無前端框架。</p>
    <ul style="line-height:1.9;">
      <li><strong>後端：</strong>PHP 8 + PDO + 預處理語法（防 SQL injection）</li>
      <li><strong>資料庫：</strong>MySQL 8 / MariaDB（utf8mb4）</li>
      <li><strong>前端：</strong>原生 HTML / CSS / JavaScript</li>
      <li><strong>字體：</strong>Google Fonts — Noto Serif TC, Noto Sans TC, Cormorant Garamond</li>
      <li><strong>圖床：</strong>本地儲存（書封、頭像）</li>
      <li><strong>安全：</strong>password_hash 雜湊、CSRF token、prepared statements、圖片類型/大小驗證</li>
    </ul>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
