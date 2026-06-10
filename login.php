<?php
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) redirect('index.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';

    if ($u === '' || $p === '') {
        $error = '請輸入帳號與密碼';
    } else {
        $stmt = db()->prepare('SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1');
        $stmt->execute([$u, $u]);
        $row = $stmt->fetch();
        if (!$row || !verify_password($p, $row['password_hash'])) {
            $error = '帳號或密碼錯誤';
        } elseif ($row['status'] === 'banned') {
            $error = '此帳號已被停用，請聯絡管理員';
        } else {
            login_user($row);
            flash_set('success', '歡迎回來，' . $row['display_name']);
            redirect('index.php');
        }
    }
}

$page_title = '會員登入';
require_once __DIR__ . '/includes/header.php';
?>
<div class="auth-wrap">
  <div class="auth-side">
    <h1>拾<span class="gold">光</span>書架</h1>
    <p><?= e(SITE_TAG) ?></p>
    <div class="quote">
      讀書是與偉大的心靈對話，
      每翻一頁，就離自己想成為的樣子更近一點。
    </div>
  </div>

  <div class="auth-form">
    <h2>歡迎回來</h2>
    <p class="sub">登入後繼續管理你的閱讀紀錄。</p>

    <?php if ($error): ?>
      <div class="flash flash-danger"><span class="flash-icon">✕</span><span><?= e($error) ?></span></div>
    <?php endif; ?>

    <form method="post">
      <?= csrf_field() ?>
      <div class="form-group">
        <label>帳號或 Email <span class="req">*</span></label>
        <input type="text" name="username" required autofocus value="<?= e($_POST['username'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>密碼 <span class="req">*</span></label>
        <input type="password" name="password" required>
      </div>
      <button type="submit" class="btn btn-primary btn-lg btn-block">登入書架</button>
    </form>

    <p class="auth-switch">
      還沒有帳號？<a href="register.php">立即加入 →</a>
    </p>
    <p class="auth-switch" style="font-size:12px; color:var(--muted);">
      測試帳號：<code>reader / reader123</code>　|　管理員：<code>admin / admin123</code>
    </p>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
