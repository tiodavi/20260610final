<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $act = $_POST['act'] ?? '';
    if ($act === 'add') {
        $name = trim($_POST['name'] ?? '');
        if ($name !== '') {
            $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
            try {
                db()->prepare('INSERT INTO categories (name, slug) VALUES (?,?)')->execute([$name, $slug]);
                flash_set('success', '已新增分類');
            } catch (PDOException $e) {
                flash_set('danger', '分類已存在或 slug 重複');
            }
        }
    } elseif ($act === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        db()->prepare('DELETE FROM categories WHERE id=?')->execute([$id]);
        flash_set('success', '已刪除分類（相關書籍會改為未分類）');
    }
    redirect('categories.php');
}

$cats = db()->query("
    SELECT c.*, (SELECT COUNT(*) FROM books b WHERE b.category_id=c.id) AS bcnt
      FROM categories c ORDER BY c.id
")->fetchAll();

$page_title = '分類管理';
require_once __DIR__ . '/../includes/header.php';
?>
<h1>分類管理</h1>
<div class="filter-bar" style="margin-bottom:24px;">
  <a href="index.php"      class="chip">📊 儀表板</a>
  <a href="users.php"      class="chip">👥 會員管理</a>
  <a href="books.php"      class="chip">📚 書籍管理</a>
  <a href="reviews.php"    class="chip">✎ 書評管理</a>
  <a href="categories.php" class="chip active">🗂  分類管理</a>
</div>

<div class="card" style="max-width:560px; margin-bottom:24px;">
  <h3 class="mt-0">新增分類</h3>
  <form method="post" class="flex gap-2">
    <?= csrf_field() ?>
    <input type="hidden" name="act" value="add">
    <input type="text" name="name" placeholder="分類名稱..." required style="flex:1;">
    <button class="btn btn-primary">新增</button>
  </form>
</div>

<div class="card" style="padding:0;">
<table class="table">
  <thead><tr><th>ID</th><th>名稱</th><th>Slug</th><th>書籍數</th><th>操作</th></tr></thead>
  <tbody>
    <?php foreach ($cats as $c): ?>
      <tr>
        <td><?= (int)$c['id'] ?></td>
        <td><strong><?= e($c['name']) ?></strong></td>
        <td><code><?= e($c['slug']) ?></code></td>
        <td><?= (int)$c['bcnt'] ?></td>
        <td>
          <form method="post" style="display:inline" data-confirm="刪除分類？">
            <?= csrf_field() ?>
            <input type="hidden" name="act" value="delete">
            <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
            <button class="btn-link" style="color:var(--accent);">刪除</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
