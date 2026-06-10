<?php
require_once __DIR__ . '/includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect('bookshelf.php');

$stmt = db()->prepare("
    SELECT b.*, u.display_name, u.bio AS user_bio, u.avatar AS user_avatar, c.name AS cat_name
      FROM books b
      JOIN users u      ON u.id = b.user_id
 LEFT JOIN categories c ON c.id = b.category_id
     WHERE b.id = ?
");
$stmt->execute([$id]);
$book = $stmt->fetch();
if (!$book) { flash_set('danger', '找不到這本書'); redirect('bookshelf.php'); }

// 是否已收藏
$isFav = false;
if (is_logged_in()) {
    $f = db()->prepare('SELECT 1 FROM favorites WHERE user_id=? AND book_id=?');
    $f->execute([current_user()['id'], $id]);
    $isFav = (bool)$f->fetchColumn();
}

// 書評
$reviews = db()->prepare("
    SELECT r.*, u.display_name, u.avatar
      FROM reviews r JOIN users u ON u.id = r.user_id
     WHERE r.book_id = ?
  ORDER BY r.created_at DESC
");
$reviews->execute([$id]);
$reviews = $reviews->fetchAll();

$me = current_user();

// 處理 收藏 / 取消收藏
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    require_login();
    $meId = $me['id'];
    if (isset($_POST['toggle_fav'])) {
        if ($isFav) {
            db()->prepare('DELETE FROM favorites WHERE user_id=? AND book_id=?')->execute([$meId, $id]);
            flash_set('info', '已從書架移除');
        } else {
            db()->prepare('INSERT INTO favorites (user_id, book_id) VALUES (?,?)')->execute([$meId, $id]);
            flash_set('success', '已加入書架');
        }
        redirect('book_detail.php?id=' . $id);
    }
}

$page_title = $book['title'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="book-detail">
  <div class="book-detail-cover">
    <?php if ($book['cover_image']): ?>
      <img src="<?= img_src($book['cover_image']) ?>" alt="<?= e($book['title']) ?>">
    <?php else: ?>
      <img src="<?= asset('assets/images/default_cover.svg') ?>" alt="預設封面">
    <?php endif; ?>
  </div>

  <div class="book-detail-info">
    <h1><?= e($book['title']) ?>
      <?php if ($book['status']==='finished'): ?><span class="seal">已讀完</span><?php endif; ?>
    </h1>
    <p class="author"><strong><?= e($book['author']) ?></strong>
      <?php if ($book['publisher']): ?> · <?= e($book['publisher']) ?><?php endif; ?>
      <?php if ($book['published_year']): ?> · <?= e($book['published_year']) ?> 年<?php endif; ?>
    </p>

    <?= render_stars($book['rating']) ?>

    <div class="book-tags">
      <span class="tag">📂 <?= e($book['cat_name'] ?? '未分類') ?></span>
      <span class="tag">📖 <?= e(status_label($book['status'])) ?></span>
      <?php if ($book['page_count']): ?><span class="tag">📄 <?= (int)$book['page_count'] ?> 頁</span><?php endif; ?>
      <?php if ($book['isbn']): ?><span class="tag">ISBN <?= e($book['isbn']) ?></span><?php endif; ?>
    </div>

    <div class="book-stat-grid">
      <div class="book-stat"><span class="num"><?= count($reviews) ?></span><span class="lbl">篇書評</span></div>
      <div class="book-stat"><span class="num"><?= $book['rating'] ? number_format($book['rating'],1) : '—' ?></span><span class="lbl">平均星等</span></div>
      <div class="book-stat"><span class="num"><?= e(date('Y', strtotime($book['created_at']))) ?></span><span class="lbl">加入年份</span></div>
    </div>

    <div class="flex gap-2" style="flex-wrap:wrap; margin-top:8px;">
      <?php if ($me): ?>
        <form method="post" style="display:inline">
          <?= csrf_field() ?>
          <button class="btn <?= $isFav?'btn-gold':'btn-primary' ?>" name="toggle_fav" value="1">
            <?= $isFav ? '★ 已在書架' : '☆ 加入我的書架' ?>
          </button>
        </form>
        <a href="add_review.php?book_id=<?= (int)$book['id'] ?>" class="btn btn-ghost">✎ 撰寫書評</a>
      <?php endif; ?>
      <?php if ($me && ($me['id']==$book['user_id'] || is_admin())): ?>
        <a href="edit_book.php?id=<?= (int)$book['id'] ?>" class="btn btn-ghost">編輯</a>
        <form method="post" action="delete_book.php" style="display:inline" data-confirm="確定刪除這本書？此操作無法復原。">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= (int)$book['id'] ?>">
          <button class="btn btn-danger">刪除</button>
        </form>
      <?php endif; ?>
    </div>

    <hr style="border:0; border-top:1px solid var(--line); margin:28px 0;">

    <h3>關於這本書</h3>
    <p class="book-description"><?= $book['description'] ? e($book['description']) : '（尚無簡介）' ?></p>

    <p class="text-muted" style="margin-top:24px; font-size:13px;">
      由 <a href="profile.php?id=<?= (int)$book['user_id'] ?>"><?= e($book['display_name']) ?></a> 收錄 · <?= e(time_ago($book['created_at'])) ?>
    </p>
  </div>
</div>

<!-- 書評 -->
<div class="section-title">
  <h2>書評 · <?= count($reviews) ?></h2>
  <?php if ($me): ?>
    <a href="add_review.php?book_id=<?= (int)$book['id'] ?>" class="more">＋ 寫下你的想法</a>
  <?php endif; ?>
</div>

<?php if (!$reviews): ?>
  <div class="empty">
    <div class="empty-icon">✎</div>
    <h3>還沒有書評</h3>
    <p>成為第一個分享讀後感的人。</p>
  </div>
<?php else: ?>
  <div class="review-list">
    <?php foreach ($reviews as $r): ?>
      <article class="review-item">
        <div class="review-head">
          <div class="review-user">
            <span class="avatar-sm" style="background-image:url('<?= img_src($r['avatar'], 'assets/images/default_avatar.svg') ?>')"></span>
            <div>
              <strong><?= e($r['display_name']) ?></strong>
              <small><?= e(time_ago($r['created_at'])) ?></small>
            </div>
          </div>
          <?= render_stars($r['rating']) ?>
        </div>
        <div class="review-content"><?= nl2br(e($r['content'])) ?></div>
        <?php if ($me && ($me['id']==$r['user_id'] || is_admin())): ?>
          <div class="review-actions">
            <form method="post" action="delete_review.php" style="display:inline" data-confirm="刪除這則書評？">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
              <input type="hidden" name="book_id" value="<?= (int)$book['id'] ?>">
              <button class="btn-link">刪除</button>
            </form>
          </div>
        <?php endif; ?>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
