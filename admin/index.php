<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$me = current_user();

// 統計
$stats = db()->query("
  SELECT
    (SELECT COUNT(*) FROM users)   AS users,
    (SELECT COUNT(*) FROM books)   AS books,
    (SELECT COUNT(*) FROM reviews) AS reviews,
    (SELECT COUNT(*) FROM users WHERE role='admin')   AS admins,
    (SELECT COUNT(*) FROM books WHERE status='reading')  AS reading,
    (SELECT COUNT(*) FROM books WHERE status='finished') AS finished
")->fetch();

// 最近註冊
$newUsers = db()->query("SELECT id, username, display_name, role, created_at FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();
// 最近書籍
$newBooks = db()->query("
    SELECT b.id, b.title, b.created_at, u.display_name
      FROM books b JOIN users u ON u.id=b.user_id
  ORDER BY b.created_at DESC LIMIT 5
")->fetchAll();

$page_title = '後台管理';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="flex-between mt-2">
  <h1>管理後台</h1>
  <span class="text-muted">歡迎，<?= e($me['display_name']) ?></span>
</div>

<div class="book-stat-grid" style="margin:24px 0;">
  <div class="book-stat"><span class="num"><?= (int)$stats['users'] ?></span><span class="lbl">位會員</span></div>
  <div class="book-stat"><span class="num"><?= (int)$stats['books'] ?></span><span class="lbl">本書</span></div>
  <div class="book-stat"><span class="num"><?= (int)$stats['reviews'] ?></span><span class="lbl">則書評</span></div>
  <div class="book-stat"><span class="num"><?= (int)$stats['reading'] ?></span><span class="lbl">本閱讀中</span></div>
  <div class="book-stat"><span class="num"><?= (int)$stats['finished'] ?></span><span class="lbl">本已讀完</span></div>
  <div class="book-stat"><span class="num"><?= (int)$stats['admins'] ?></span><span class="lbl">位管理員</span></div>
</div>

<div class="filter-bar" style="margin-bottom:30px;">
  <a href="index.php"      class="chip active">📊 儀表板</a>
  <a href="users.php"      class="chip">👥 會員管理</a>
  <a href="books.php"      class="chip">📚 書籍管理</a>
  <a href="reviews.php"    class="chip">✎ 書評管理</a>
  <a href="categories.php" class="chip">🗂  分類管理</a>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:24px;">
  <div class="card">
    <h3 class="mt-0">最新註冊會員</h3>
    <table class="table">
      <thead><tr><th>帳號</th><th>顯示名稱</th><th>角色</th><th>註冊</th></tr></thead>
      <tbody>
        <?php foreach ($newUsers as $u): ?>
          <tr>
            <td>@<?= e($u['username']) ?></td>
            <td><?= e($u['display_name']) ?></td>
            <td>
              <?php if ($u['role']==='admin'): ?><span class="badge badge-admin">ADMIN</span>
              <?php else: ?><span class="badge badge-user">USER</span><?php endif; ?>
            </td>
            <td><?= e(time_ago($u['created_at'])) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <a href="users.php" class="btn-link">管理會員 →</a>
  </div>

  <div class="card">
    <h3 class="mt-0">最新書籍</h3>
    <table class="table">
      <thead><tr><th>書名</th><th>作者</th><th>新增者</th><th>時間</th></tr></thead>
      <tbody>
        <?php foreach ($newBooks as $b): ?>
          <tr>
            <td><a href="../book_detail.php?id=<?= (int)$b['id'] ?>"><?= e($b['title']) ?></a></td>
            <td><?= e($b['display_name']) ?></td>
            <td><?= e(time_ago($b['created_at'])) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <a href="books.php" class="btn-link">管理書籍 →</a>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
