<?php
require_once __DIR__ . '/includes/functions.php';

// 統計數字
$stats = db()->query("
    SELECT
        (SELECT COUNT(*) FROM users)             AS users,
        (SELECT COUNT(*) FROM books)             AS books,
        (SELECT COUNT(*) FROM reviews)           AS reviews,
        (SELECT COUNT(*) FROM books WHERE status='finished') AS finished
")->fetch();

// 最新書籍
$latest = db()->query("
    SELECT b.*, u.display_name, c.name AS cat_name
      FROM books b
      JOIN users u      ON u.id = b.user_id
 LEFT JOIN categories c ON c.id = b.category_id
  ORDER BY b.created_at DESC
     LIMIT 8
")->fetchAll();

// 熱門書籍 (按書評數)
$popular = db()->query("
    SELECT b.*, u.display_name, COUNT(r.id) AS rcnt
      FROM books b
      JOIN users u      ON u.id = b.user_id
 LEFT JOIN reviews r    ON r.book_id = b.id
  GROUP BY b.id
  ORDER BY rcnt DESC, b.rating DESC
     LIMIT 4
")->fetchAll();

// 分類
$cats = db()->query("SELECT * FROM categories ORDER BY id LIMIT 8")->fetchAll();

$page_title = '首頁';
require_once __DIR__ . '/includes/header.php';
?>

<!-- HERO -->
<section class="hero">
  <div class="hero-eyebrow">A BOOKSHELF OF MEMORIES</div>
  <h1>在<span class="accent">字裡行間</span>，<br>拾起那些<span class="accent">溫柔的光</span>。</h1>
  <p>「拾光書架」是一個為閱讀者打造的私人日誌，記錄每一本翻過的書、寫下的想法、走進生命的字句。</p>
  <div class="hero-actions">
    <?php if (!is_logged_in()): ?>
      <a href="register.php" class="btn btn-primary btn-lg">建立我的書架</a>
      <a href="bookshelf.php" class="btn btn-ghost btn-lg">先逛逛看 →</a>
    <?php else: ?>
      <a href="my_shelf.php" class="btn btn-primary btn-lg">回到我的書架</a>
      <a href="add_book.php" class="btn btn-gold btn-lg">＋ 新增一本書</a>
    <?php endif; ?>
  </div>
</section>

<!-- 統計 -->
<div class="book-stat-grid" style="margin-bottom:50px;">
  <div class="book-stat"><span class="num"><?= (int)$stats['users'] ?></span><span class="lbl">位書友</span></div>
  <div class="book-stat"><span class="num"><?= (int)$stats['books'] ?></span><span class="lbl">本書目</span></div>
  <div class="book-stat"><span class="num"><?= (int)$stats['reviews'] ?></span><span class="lbl">篇書評</span></div>
  <div class="book-stat"><span class="num"><?= (int)$stats['finished'] ?></span><span class="lbl">本已讀完</span></div>
</div>

<!-- 熱門書 -->
<div class="section-title">
  <h2>本月最受討論</h2>
  <a href="bookshelf.php?sort=popular" class="more">查看全部 →</a>
</div>
<div class="book-grid" style="margin-bottom:50px;">
  <?php foreach ($popular as $b): ?>
    <article class="book-card">
      <a href="book_detail.php?id=<?= (int)$b['id'] ?>" class="book-cover">
        <span class="book-status <?= e($b['status']) ?>"><?= e(status_label($b['status'])) ?></span>
        <?php if ($b['cover_image']): ?>
          <img src="<?= img_src($b['cover_image']) ?>" alt="<?= e($b['title']) ?>">
        <?php else: ?>
          <img src="<?= asset('assets/images/default_cover.svg') ?>" alt="預設封面">
        <?php endif; ?>
      </a>
      <div class="book-body">
        <h3 class="book-title"><a href="book_detail.php?id=<?= (int)$b['id'] ?>"><?= e($b['title']) ?></a></h3>
        <p class="book-author"><?= e($b['author']) ?></p>
        <?= render_stars($b['rating']) ?>
        <div class="book-meta">
          <span>💬 <?= (int)$b['rcnt'] ?> 則書評</span>
          <span><?= e($b['display_name']) ?></span>
        </div>
      </div>
    </article>
  <?php endforeach; ?>
</div>

<!-- 分類 -->
<div class="section-title"><h2>依分類探索</h2></div>
<div class="filter-bar" style="margin-bottom:50px;">
  <?php foreach ($cats as $c): ?>
    <a href="bookshelf.php?cat=<?= (int)$c['id'] ?>" class="chip"><?= e($c['name']) ?></a>
  <?php endforeach; ?>
</div>

<!-- 最新書 -->
<div class="section-title">
  <h2>新書上架</h2>
  <a href="bookshelf.php" class="more">查看全部 →</a>
</div>
<?php if (!$latest): ?>
  <div class="empty">
    <div class="empty-icon">📖</div>
    <h3>書架上還沒有書</h3>
    <p>成為第一個擺放書本的人吧！</p>
    <?php if (is_logged_in()): ?>
      <a href="add_book.php" class="btn btn-primary mt-2">新增一本書</a>
    <?php endif; ?>
  </div>
<?php else: ?>
  <div class="book-grid">
    <?php foreach ($latest as $b): ?>
      <article class="book-card">
        <a href="book_detail.php?id=<?= (int)$b['id'] ?>" class="book-cover">
          <span class="book-status <?= e($b['status']) ?>"><?= e(status_label($b['status'])) ?></span>
          <?php if ($b['cover_image']): ?>
            <img src="<?= img_src($b['cover_image']) ?>" alt="<?= e($b['title']) ?>">
          <?php else: ?>
            <img src="<?= asset('assets/images/default_cover.svg') ?>" alt="預設封面">
          <?php endif; ?>
        </a>
        <div class="book-body">
          <h3 class="book-title"><a href="book_detail.php?id=<?= (int)$b['id'] ?>"><?= e($b['title']) ?></a></h3>
          <p class="book-author"><?= e($b['author']) ?></p>
          <?= render_stars($b['rating']) ?>
          <div class="book-meta">
            <span><?= e($b['cat_name'] ?? '未分類') ?></span>
            <span title="<?= e($b['created_at']) ?>"><?= e(time_ago($b['created_at'])) ?></span>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
