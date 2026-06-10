<?php
require_once __DIR__ . '/includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
$me = current_user();

// 設定模式：看別人或自己
$isSelf = false;
if (!$id && $me) { $id = $me['id']; $isSelf = true; }
elseif ($id && $me && $id == $me['id']) $isSelf = true;

$stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) { flash_set('danger', '找不到使用者'); redirect('index.php'); }

// 處理更新自己的檔案
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isSelf) {
    csrf_check();
    $display = trim($_POST['display_name'] ?? '');
    $bio     = trim($_POST['bio'] ?? '');
    $email   = trim($_POST['email'] ?? '');

    if ($display === '')   $error = '顯示名稱不可為空';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error = 'Email 格式不正確';
    else {
        $avatar = $user['avatar'];
        if (!empty($_FILES['avatar']['name'])) {
            $new = handle_upload($_FILES['avatar'], 'avatars', $err);
            if ($new) { delete_upload($avatar); $avatar = $new; }
            else $error = $err;
        }
        if (!$error) {
            db()->prepare('UPDATE users SET display_name=?, bio=?, email=?, avatar=? WHERE id=?')
                ->execute([$display, $bio, $email, $avatar, $id]);
            // 同步 session
            $_SESSION['user']['display_name'] = $display;
            $_SESSION['user']['bio'] = $bio;
            $_SESSION['user']['avatar'] = $avatar;
            flash_set('success', '已更新個人檔案');
            redirect('profile.php');
        }
    }
}

// 修改密碼
$pwError = ''; $pwOk = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_pw']) && $isSelf) {
    csrf_check();
    $cur = $_POST['current_pw'] ?? '';
    $new = $_POST['new_pw'] ?? '';
    $new2= $_POST['new_pw2'] ?? '';
    if (!verify_password($cur, $user['password_hash'])) $pwError = '目前密碼不正確';
    elseif (strlen($new) < 6) $pwError = '新密碼至少 6 個字元';
    elseif ($new !== $new2)   $pwError = '兩次新密碼不一致';
    else {
        db()->prepare('UPDATE users SET password_hash=? WHERE id=?')
            ->execute([hash_password($new), $id]);
        $pwOk = true;
    }
}

// 統計
$bookCount = (int)db()->query('SELECT COUNT(*) FROM books WHERE user_id=' . (int)$id)->fetchColumn();
$revCount  = (int)db()->query('SELECT COUNT(*) FROM reviews WHERE user_id=' . (int)$id)->fetchColumn();

// 該使用者的書
$books = db()->prepare("
    SELECT b.*, c.name AS cat_name FROM books b
 LEFT JOIN categories c ON c.id=b.category_id
    WHERE b.user_id = ? ORDER BY b.created_at DESC LIMIT 12
");
$books->execute([$id]);
$books = $books->fetchAll();

$page_title = $user['display_name'] . ' 的檔案';
require_once __DIR__ . '/includes/header.php';
?>
<div style="display:grid; grid-template-columns:280px 1fr; gap:32px; align-items:start;">
  <!-- 左側卡片 -->
  <aside class="card text-center" style="position:sticky; top:90px;">
    <div style="width:140px; height:140px; border-radius:50%; margin:0 auto 16px; background:url('<?= img_src($user['avatar'], 'assets/images/default_avatar.svg') ?>') center/cover; border:4px solid var(--gold); box-shadow:var(--shadow);"></div>
    <h2 class="mb-0"><?= e($user['display_name']) ?></h2>
    <p class="text-muted" style="margin-top:4px;">@<?= e($user['username']) ?>
      <?php if ($user['role']==='admin'): ?><span class="badge badge-admin">管理員</span><?php endif; ?>
    </p>
    <p style="font-style:italic; color:var(--ink-soft); min-height:3em;"><?= e($user['bio'] ?: '（這位書友很神秘，沒有留下簡介）') ?></p>

    <div class="book-stat-grid mt-2">
      <div class="book-stat"><span class="num"><?= $bookCount ?></span><span class="lbl">本書</span></div>
      <div class="book-stat"><span class="num"><?= $revCount ?></span><span class="lbl">書評</span></div>
    </div>

    <p class="text-muted" style="font-size:12px; margin-top:16px;">
      加入於 <?= e(date('Y/m/d', strtotime($user['created_at']))) ?>
    </p>
  </aside>

  <!-- 右側內容 -->
  <div>
    <?php if ($isSelf): ?>
      <div class="card mb-3">
        <h2 class="mt-0">編輯我的檔案</h2>
        <?php if ($error): ?>
          <div class="flash flash-danger"><span class="flash-icon">✕</span><span><?= e($error) ?></span></div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
          <?= csrf_field() ?>
          <div class="form-group">
            <label>顯示名稱 <span class="req">*</span></label>
            <input type="text" name="display_name" required value="<?= e($user['display_name']) ?>">
          </div>
          <div class="form-group">
            <label>Email <span class="req">*</span></label>
            <input type="email" name="email" required value="<?= e($user['email']) ?>">
          </div>
          <div class="form-group">
            <label>個人簡介</label>
            <textarea name="bio" rows="3"><?= e($user['bio']) ?></textarea>
          </div>
          <div class="form-group">
            <label>頭像</label>
            <input type="file" name="avatar" accept="image/*" data-preview="#avPrev">
            <img id="avPrev" src="<?= img_src($user['avatar'], 'assets/images/default_avatar.svg') ?>" style="width:80px; height:80px; border-radius:50%; object-fit:cover; margin-top:8px;">
          </div>
          <button class="btn btn-primary">儲存</button>
        </form>
      </div>

      <div class="card">
        <h2 class="mt-0">修改密碼</h2>
        <?php if ($pwError): ?>
          <div class="flash flash-danger"><span class="flash-icon">✕</span><span><?= e($pwError) ?></span></div>
        <?php elseif ($pwOk): ?>
          <div class="flash flash-success"><span class="flash-icon">✓</span><span>密碼已更新</span></div>
        <?php endif; ?>
        <form method="post">
          <?= csrf_field() ?>
          <input type="hidden" name="change_pw" value="1">
          <div class="form-group">
            <label>目前密碼</label>
            <input type="password" name="current_pw" required>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>新密碼</label>
              <input type="password" name="new_pw" required minlength="6">
            </div>
            <div class="form-group">
              <label>確認新密碼</label>
              <input type="password" name="new_pw2" required minlength="6">
            </div>
          </div>
          <button class="btn btn-primary">更新密碼</button>
        </form>
      </div>
    <?php endif; ?>

    <div class="section-title mt-3">
      <h2>擺在書架上的書</h2>
      <span class="more">共 <?= $bookCount ?> 本</span>
    </div>
    <?php if (!$books): ?>
      <div class="empty">
        <div class="empty-icon">📖</div>
        <h3>書架上空空的</h3>
      </div>
    <?php else: ?>
      <div class="book-grid">
        <?php foreach ($books as $b): ?>
          <article class="book-card">
            <a href="book_detail.php?id=<?= (int)$b['id'] ?>" class="book-cover">
              <span class="book-status <?= e($b['status']) ?>"><?= e(status_label($b['status'])) ?></span>
              <img src="<?= img_src($b['cover_image']) ?>" alt="">
            </a>
            <div class="book-body">
              <h3 class="book-title"><a href="book_detail.php?id=<?= (int)$b['id'] ?>"><?= e($b['title']) ?></a></h3>
              <p class="book-author"><?= e($b['author']) ?></p>
              <?= render_stars($b['rating']) ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
