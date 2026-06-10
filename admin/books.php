<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id  = (int)($_POST['id'] ?? 0);
    $act = $_POST['act'] ?? '';
    if ($id > 0 && $act === 'delete') {
        $b = db()->prepare('SELECT * FROM books WHERE id=?'); $b->execute([$id]); $b=$b->fetch();
        if ($b) { delete_upload($b['cover_image']); db()->prepare('DELETE FROM books WHERE id=?')->execute([$id]); }
        flash_set('success', '已刪除書籍');
    }
    redirect('books.php');
}

$q = trim($_GET['q'] ?? '');
$sql = "SELECT b.*, u.display_name, c.name AS cat_name FROM books b
        JOIN users u ON u.id=b.user_id
   LEFT JOIN categories c ON c.id=b.category_id";
$params = [];
if ($q!=='') { $sql .= " WHERE b.title LIKE ? OR b.author LIKE ?"; $params=["%$q%","%$q%"]; }
$sql .= " ORDER BY b.created_at DESC";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll();

$page_title = '書籍管理';
require_once __DIR__ . '/../includes/header.php';
?>
<h1>書籍管理</h1>
<div class="filter-bar" style="margin-bottom:24px;">
  <a href="index.php"      class="chip">📊 儀表板</a>
  <a href="users.php"      class="chip">👥 會員管理</a>
  <a href="books.php"      class="chip active">📚 書籍管理</a>
  <a href="reviews.php"    class="chip">✎ 書評管理</a>
  <a href="categories.php" class="chip">🗂  分類管理</a>
</div>

<form method="get" class="filter-bar" style="margin-bottom:16px;">
  <input type="search" name="q" placeholder="🔍 搜尋書名/作者..." value="<?= e($q) ?>" class="search-input">
  <button class="btn btn-primary btn-sm">搜尋</button>
</form>

<div class="card" style="padding:0;">
<table class="table">
  <thead>
    <tr><th>ID</th><th>封面</th><th>書名</th><th>作者</th><th>分類</th><th>狀態</th><th>評分</th><th>新增者</th><th>時間</th><th>操作</th></tr>
  </thead>
  <tbody>
    <?php foreach ($books as $b): ?>
      <tr>
        <td><?= (int)$b['id'] ?></td>
        <td>
          <img src="<?= e($b['cover_image'] ?: 'assets/images/default_cover.svg') ?>" style="width:40px; height:56px; object-fit:cover; border-radius:3px; box-shadow:var(--shadow-sm);">
        </td>
        <td><a href="../book_detail.php?id=<?= (int)$b['id'] ?>"><?= e($b['title']) ?></a></td>
        <td><?= e($b['author']) ?></td>
        <td><?= e($b['cat_name'] ?? '—') ?></td>
        <td><span class="badge"><?= e(status_label($b['status'])) ?></span></td>
        <td><?= $b['rating'] ? str_repeat('★',(int)$b['rating']) : '—' ?></td>
        <td><?= e($b['display_name']) ?></td>
        <td><?= e(date('Y-m-d', strtotime($b['created_at']))) ?></td>
        <td>
          <a href="../edit_book.php?id=<?= (int)$b['id'] ?>" class="btn-link">編輯</a>
          <form method="post" style="display:inline" data-confirm="確定刪除這本書？">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
            <button name="act" value="delete" class="btn-link" style="color:var(--accent);">刪除</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
