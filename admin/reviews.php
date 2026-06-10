<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $r = db()->prepare('SELECT * FROM reviews WHERE id=?'); $r->execute([$id]); $r=$r->fetch();
        if ($r) {
            db()->prepare('DELETE FROM reviews WHERE id=?')->execute([$id]);
            $avg = (int)round((float)db()->query("SELECT AVG(rating) FROM reviews WHERE book_id=" . (int)$r['book_id'])->fetchColumn());
            db()->prepare('UPDATE books SET rating=? WHERE id=?')->execute([$avg, $r['book_id']]);
            flash_set('success', '已刪除書評');
        }
    }
    redirect('reviews.php');
}

$reviews = db()->query("
    SELECT r.*, b.title AS book_title, u.display_name
      FROM reviews r
      JOIN books b ON b.id=r.book_id
      JOIN users u ON u.id=r.user_id
  ORDER BY r.created_at DESC
")->fetchAll();

$page_title = '書評管理';
require_once __DIR__ . '/../includes/header.php';
?>
<h1>書評管理</h1>
<div class="filter-bar" style="margin-bottom:24px;">
  <a href="index.php"      class="chip">📊 儀表板</a>
  <a href="users.php"      class="chip">👥 會員管理</a>
  <a href="books.php"      class="chip">📚 書籍管理</a>
  <a href="reviews.php"    class="chip active">✎ 書評管理</a>
  <a href="categories.php" class="chip">🗂  分類管理</a>
</div>

<div class="card" style="padding:0;">
<table class="table">
  <thead><tr><th>ID</th><th>書名</th><th>作者</th><th>評分</th><th>內容</th><th>時間</th><th>操作</th></tr></thead>
  <tbody>
    <?php foreach ($reviews as $r): ?>
      <tr>
        <td><?= (int)$r['id'] ?></td>
        <td><a href="../book_detail.php?id=<?= (int)$r['book_id'] ?>"><?= e($r['book_title']) ?></a></td>
        <td><?= e($r['display_name']) ?></td>
        <td><?= str_repeat('★', (int)$r['rating']) ?></td>
        <td style="max-width:380px;">
          <div style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?= e(mb_substr($r['content'],0,80)) ?>...</div>
        </td>
        <td><?= e(date('Y-m-d H:i', strtotime($r['created_at']))) ?></td>
        <td>
          <form method="post" style="display:inline" data-confirm="刪除這則書評？">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <button class="btn-link" style="color:var(--accent);">刪除</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
