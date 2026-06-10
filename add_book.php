<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$cats = db()->query("SELECT * FROM categories ORDER BY id")->fetchAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $title    = trim($_POST['title'] ?? '');
    $author   = trim($_POST['author'] ?? '');
    $desc     = trim($_POST['description'] ?? '');
    $cat      = (int)($_POST['category_id'] ?? 0) ?: null;
    $status   = $_POST['status'] ?? 'want';
    $rating   = $_POST['rating'] !== '' ? max(1, min(5, (int)$_POST['rating'])) : null;
    $pages    = (int)($_POST['page_count'] ?? 0) ?: null;
    $year     = (int)($_POST['published_year'] ?? 0) ?: null;
    $pub      = trim($_POST['publisher'] ?? '');
    $isbn     = trim($_POST['isbn'] ?? '');

    if ($title === '' || $author === '') {
        $error = '書名與作者必填';
    } else {
        $cover = null;
        if (!empty($_FILES['cover_image']['name'])) {
            $cover = handle_upload($_FILES['cover_image'], 'covers', $err);
            if (!$cover) $error = $err ?? '封面圖上傳失敗';
        }
        if (!$error) {
            $stmt = db()->prepare("INSERT INTO books
              (user_id,title,author,isbn,cover_image,description,category_id,status,rating,page_count,published_year,publisher)
              VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([current_user()['id'],$title,$author,$isbn,$cover,$desc,$cat,$status,$rating,$pages,$year,$pub]);
            $newId = db()->lastInsertId();
            flash_set('success', '《' . $title . '》已加入書架');
            redirect('book_detail.php?id=' . $newId);
        }
    }
}

$page_title = '新增書本';
require_once __DIR__ . '/includes/header.php';
?>

<div class="card" style="max-width:760px; margin:0 auto;">
  <h2>新增一本書</h2>
  <p class="text-muted">填寫書本資訊，並可選擇上傳封面圖片。</p>

  <?php if ($error): ?>
    <div class="flash flash-danger"><span class="flash-icon">✕</span><span><?= e($error) ?></span></div>
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="form-group">
      <label>書名 <span class="req">*</span></label>
      <input type="text" name="title" required value="<?= e($_POST['title'] ?? '') ?>">
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>作者 <span class="req">*</span></label>
        <input type="text" name="author" required value="<?= e($_POST['author'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>ISBN</label>
        <input type="text" name="isbn" value="<?= e($_POST['isbn'] ?? '') ?>">
      </div>
    </div>
    <div class="form-group">
      <label>封面圖片</label>
      <input type="file" name="cover_image" accept="image/*" data-preview="#coverPrev">
      <div class="form-hint">支援 JPG / PNG / GIF / WebP，最大 <?= MAX_UPLOAD_MB ?> MB</div>
      <img id="coverPrev" src="<?= asset('assets/images/default_cover.svg') ?>" alt="預覽" style="max-width:160px; margin-top:10px; border-radius:4px; box-shadow:var(--shadow);">
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>分類</label>
        <select name="category_id">
          <option value="0">未分類</option>
          <?php foreach ($cats as $c): ?>
            <option value="<?= (int)$c['id'] ?>" <?= ((int)($_POST['category_id'] ?? 0))===(int)$c['id']?'selected':'' ?>><?= e($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>閱讀狀態</label>
        <select name="status">
          <option value="want"     <?= ($_POST['status'] ?? '')==='want'?'selected':'' ?>>想讀</option>
          <option value="reading"  <?= ($_POST['status'] ?? '')==='reading'?'selected':'' ?>>閱讀中</option>
          <option value="finished" <?= ($_POST['status'] ?? '')==='finished'?'selected':'' ?>>已讀完</option>
        </select>
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>評分</label>
        <select name="rating">
          <option value="">未評分</option>
          <?php for ($i=5;$i>=1;$i--): ?>
            <option value="<?= $i ?>" <?= ((int)($_POST['rating'] ?? 0))===$i?'selected':'' ?>><?= str_repeat('★',$i) ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="form-group">
        <label>頁數</label>
        <input type="number" name="page_count" min="0" value="<?= e($_POST['page_count'] ?? '') ?>">
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>出版社</label>
        <input type="text" name="publisher" value="<?= e($_POST['publisher'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>出版年</label>
        <input type="number" name="published_year" min="0" max="2100" value="<?= e($_POST['published_year'] ?? '') ?>">
      </div>
    </div>
    <div class="form-group">
      <label>簡介</label>
      <textarea name="description" rows="5" placeholder="簡單介紹這本書..."><?= e($_POST['description'] ?? '') ?></textarea>
    </div>
    <div class="flex gap-2">
      <button type="submit" class="btn btn-primary btn-lg">新增到書架</button>
      <a href="bookshelf.php" class="btn btn-ghost btn-lg">取消</a>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
