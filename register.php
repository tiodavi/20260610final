<?php
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) redirect('index.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username  = trim($_POST['username'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $display   = trim($_POST['display_name'] ?? '');
    $pass      = $_POST['password'] ?? '';
    $pass2     = $_POST['password2'] ?? '';
    $bio       = trim($_POST['bio'] ?? '');

    if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username))
        $error = '帳號需為 3-20 字英數字或底線';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $error = 'Email 格式不正確';
    elseif ($display === '')
        $error = '請填寫顯示名稱';
    elseif (mb_strlen($pass) < 6)
        $error = '密碼至少 6 個字元';
    elseif ($pass !== $pass2)
        $error = '兩次密碼不一致';
    else {
        $stmt = db()->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$username, $email]);
        if ($stmt->fetch())
            $error = '帳號或 Email 已被使用';
        else {
            $ins = db()->prepare('INSERT INTO users (username,email,password_hash,display_name,bio) VALUES (?,?,?,?,?)');
            $ins->execute([$username, $email, hash_password($pass), $display, $bio]);
            $newId = db()->lastInsertId();
            $row = db()->prepare('SELECT * FROM users WHERE id = ?');
            $row->execute([$newId]);
            login_user($row->fetch());
            flash_set('success', '註冊成功！歡迎加入拾光書架');
            redirect('index.php');
        }
    }
}

$page_title = '加入書架';
require_once __DIR__ . '/includes/header.php';
?>
<div class="auth-wrap">
  <div class="auth-side">
    <h1>加入<span class="gold">書架</span></h1>
    <p>建立你的專屬閱讀日誌，紀錄每一本走進生命中的書。</p>
    <div class="quote">
      一座書架，就像一個人的自傳。
    </div>
  </div>

  <div class="auth-form">
    <h2>建立帳號</h2>
    <p class="sub">填寫下方資料，開啟你的閱讀旅程。</p>

    <?php if ($error): ?>
      <div class="flash flash-danger"><span class="flash-icon">✕</span><span><?= e($error) ?></span></div>
    <?php endif; ?>

    <form method="post">
      <?= csrf_field() ?>
      <div class="form-group">
        <label>帳號 <span class="req">*</span></label>
        <input type="text" name="username" required pattern="[a-zA-Z0-9_]{3,20}" value="<?= e($_POST['username'] ?? '') ?>">
        <div class="form-hint">3-20 字，英數字或底線</div>
      </div>
      <div class="form-group">
        <label>Email <span class="req">*</span></label>
        <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>顯示名稱 <span class="req">*</span></label>
        <input type="text" name="display_name" required value="<?= e($_POST['display_name'] ?? '') ?>">
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>密碼 <span class="req">*</span></label>
          <input type="password" name="password" required minlength="6">
        </div>
        <div class="form-group">
          <label>確認密碼 <span class="req">*</span></label>
          <input type="password" name="password2" required minlength="6">
        </div>
      </div>
      <div class="form-group">
        <label>個人簡介</label>
        <textarea name="bio" rows="3" placeholder="一句話介紹你的閱讀喜好…"><?= e($_POST['bio'] ?? '') ?></textarea>
      </div>
      <button type="submit" class="btn btn-primary btn-lg btn-block">建立帳號</button>
    </form>

    <p class="auth-switch">
      已經有帳號了？<a href="login.php">立即登入 →</a>
    </p>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
