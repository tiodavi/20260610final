<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $act = $_POST['act'] ?? '';
    $uid = (int)($_POST['id'] ?? 0);
    if ($uid > 0 && $uid != current_user()['id']) {
        if ($act === 'toggle_role') {
            $u = db()->prepare('SELECT role FROM users WHERE id=?'); $u->execute([$uid]); $u=$u->fetch();
            $newRole = $u && $u['role']==='admin' ? 'user' : 'admin';
            db()->prepare('UPDATE users SET role=? WHERE id=?')->execute([$newRole, $uid]);
            flash_set('success', '已切換角色');
        } elseif ($act === 'toggle_status') {
            $u = db()->prepare('SELECT status FROM users WHERE id=?'); $u->execute([$uid]); $u=$u->fetch();
            $newStatus = $u && $u['status']==='active' ? 'banned' : 'active';
            db()->prepare('UPDATE users SET status=? WHERE id=?')->execute([$newStatus, $uid]);
            flash_set('success', $newStatus==='banned' ? '已停用帳號' : '已啟用帳號');
        } elseif ($act === 'delete') {
            db()->prepare('DELETE FROM users WHERE id=?')->execute([$uid]);
            flash_set('success', '已刪除會員');
        }
    }
    redirect('users.php');
}

$users = db()->query("
  SELECT u.*, (SELECT COUNT(*) FROM books b WHERE b.user_id=u.id) AS bcnt,
                 (SELECT COUNT(*) FROM reviews r WHERE r.user_id=u.id) AS rcnt
    FROM users u ORDER BY u.created_at DESC
")->fetchAll();

$page_title = '會員管理';
require_once __DIR__ . '/../includes/header.php';
?>
<h1>會員管理</h1>
<div class="filter-bar" style="margin-bottom:24px;">
  <a href="index.php"      class="chip">📊 儀表板</a>
  <a href="users.php"      class="chip active">👥 會員管理</a>
  <a href="books.php"      class="chip">📚 書籍管理</a>
  <a href="reviews.php"    class="chip">✎ 書評管理</a>
  <a href="categories.php" class="chip">🗂  分類管理</a>
</div>

<div class="card" style="padding:0;">
<table class="table">
  <thead>
    <tr>
      <th>ID</th><th>帳號</th><th>顯示名稱</th><th>Email</th>
      <th>角色</th><th>狀態</th><th>書/評</th><th>註冊</th><th>操作</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($users as $u): ?>
      <tr>
        <td><?= (int)$u['id'] ?></td>
        <td>
          <strong>@<?= e($u['username']) ?></strong>
          <?php if ($u['id']==current_user()['id']): ?><span class="badge badge-admin">YOU</span><?php endif; ?>
        </td>
        <td><?= e($u['display_name']) ?></td>
        <td><?= e($u['email']) ?></td>
        <td>
          <?php if ($u['role']==='admin'): ?><span class="badge badge-admin">管理員</span>
          <?php else: ?><span class="badge badge-user">會員</span><?php endif; ?>
        </td>
        <td>
          <?php if ($u['status']==='banned'): ?><span class="badge badge-danger">停用</span>
          <?php else: ?><span class="badge badge-success">啟用</span><?php endif; ?>
        </td>
        <td><?= (int)$u['bcnt'] ?> / <?= (int)$u['rcnt'] ?></td>
        <td><?= e(date('Y-m-d', strtotime($u['created_at']))) ?></td>
        <td>
          <?php if ($u['id']!=current_user()['id']): ?>
            <form method="post" style="display:inline">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
              <button name="act" value="toggle_role"   class="btn-link" title="切換角色"><?= $u['role']==='admin'?'降為會員':'升為管理' ?></button>
            </form>
            <form method="post" style="display:inline">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
              <button name="act" value="toggle_status" class="btn-link" title="啟用/停用"><?= $u['status']==='active'?'停用':'啟用' ?></button>
            </form>
            <form method="post" style="display:inline" data-confirm="刪除此會員？他的書/書評也會一併刪除！">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
              <button name="act" value="delete" class="btn-link" style="color:var(--accent);">刪除</button>
            </form>
          <?php else: ?>
            <span class="text-muted" style="font-size:12px;">—</span>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
