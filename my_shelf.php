<?php
require_once __DIR__ . '/includes/functions.php';
require_login();
$me = current_user();

// 我的書 (放在收藏裡的 + 我自己新增的)
$tab = $_GET['tab'] ?? 'fav';
$myBooks = [];
$favBooks = [];

if ($tab === 'mine') {
    $stmt = db()->prepare("
        SELECT b.*, c.name AS cat_name,
               (SELECT COUNT(*) FROM reviews r WHERE r.book_id=b.id) AS rcnt
          FROM books b LEFT JOIN categories c ON c.id=b.category_id
         WHERE b.user_id = ?
      ORDER BY b.created_at DESC
    ");
    $stmt->execute([$me['id']]);
    $myBooks = $stmt->fetchAll();
} else {
    $stmt = db()->prepare("
        SELECT b.*, u.display_name, c.name AS cat_name,
               (SELECT COUNT(*) FROM reviews r WHERE r.book_id=b.id) AS rcnt
          FROM favorites f
          JOIN books b       ON b.id = f.book_id
          JOIN users u       ON u.id = b.user_id
     LEFT JOIN categories c  ON c.id = b.category_id
         WHERE f.user_id = ?
      ORDER BY f.created_at DESC
    ");
    $stmt->execute([$me['id']]);
    $favBooks = $stmt->fetchAll();
}

$page_title = '我的書架';
require_once __DIR__ . '/includes/header.php';
?>

<div class="section-title">
  <h2>我的書架</h2>
  <a href="add_book.php" class="btn btn-primary btn-sm">＋ 新增書本</a>
</div>

<div class="filter-bar">
  <a href="?tab=fav"  class="chip <?= $tab==='fav'?'active':'' ?>">★ 收藏的書</a>
  <a href="?tab=mine" class="chip <?= $tab==='mine'?'active':'' ?>">📖 我新增的書</a>
</div>

<?php
$list = $tab==='mine' ? $myBooks : $favBooks;
if (!$list):
?>
  <div class="empty">
    <div class="empty-icon">📚</div>
    <h3>書架上空空的</h3>
    <p><?= $tab==='mine'?'去新增你的第一本書吧！':'去書架探索找幾本喜歡的書收藏吧。' ?></p>
    <a href="<?= $tab==='mine'?'add_book.php':'bookshelf.php' ?>" class="btn btn-primary mt-2">
      <?= $tab==='mine'?'＋ 新增書本':'→ 去探索' ?>
    </a>
  </div>
<?php else: ?>
  <div class="book-grid">
    <?php foreach ($list as $b): ?>
      <article class="book-card">
        <a href="book_detail.php?id=<?= (int)$b['id'] ?>" class="book-cover">
          <span class="book-status <?= e($b['status']) ?>"><?= e(status_label($b['status'])) ?></span>
          <?php if ($b['cover_image']): ?>
            <img src="<?= e($b['cover_image']) ?>" alt="<?= e($b['title']) ?>">
          <?php else: ?>
            <img src="assets/images/default_cover.svg" alt="預設封面">
          <?php endif; ?>
        </a>
        <div class="book-body">
          <h3 class="book-title"><a href="book_detail.php?id=<?= (int)$b['id'] ?>"><?= e($b['title']) ?></a></h3>
          <p class="book-author"><?= e($b['author']) ?></p>
          <?= render_stars($b['rating']) ?>
          <div class="book-meta">
            <span><?= e($b['cat_name'] ?? '未分類') ?></span>
            <span title="書評數">💬 <?= (int)$b['rcnt'] ?></span>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
