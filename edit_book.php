<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM books WHERE id = ?');
$stmt->execute([$id]);
$book = $stmt->fetch();
if (!$book) { flash_set('danger', '找不到這本書'); redirect('bookshelf.php'); }

$me = current_user();
if ($book['user_id'] != $me['id'] && !is_admin()) {
    flash_set('danger', '只能編輯自己新增的書');
    redirect('book_detail.php?id=' . $id);
}

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
        $cover = $book['cover_image'];
        if (!empty($_FILES['cover_image']['name'])) {
            $new = handle_upload($_FILES['cover_image'], 'covers', $err);
            if ($new) { delete_upload($cover); $cover = $new; }
            else $error = $err ?? '封面圖上傳失敗';
        }
        // 刪除封面
        if (isset($_POST['remove_cover']) && $_POST['remove_cover']==='1') {
            delete_upload($cover); $cover = null;
        }

        if (!$error) {
            db()->prepare("UPDATE books SET
              title=?, author=?, isbn=?, cover_image=?, description=?, category_id=?, status=?, rating=?, page_count=?, published_year=?, publisher=?
              WHERE id=?")->execute([$title,$author,$isbn,$cover,$desc,$cat,$status,$rating,$pages,$year,$pub,$id]);
            flash_set('success', '已更新');
            redirect('book_detail.php?id=' . $id);
        }
    }
    // 重新讀取
    $stmt = db()->prepare('SELECT * FROM books WHERE id = ?');
    $stmt->execute([$id]);
    $book = $stmt->fetch();
}

$page_title = '編輯《' . $book['title'] . '》';
require_once __DIR__ . '/includes/header.php';
?>
<div class="card" style="max-width:760px; margin:0 auto;">
  <h2>編輯書本</h2>

  <?php if ($error): ?>
    <div class="flash flash-danger"><span class="flash-icon">✕</span><span><?= e($error) ?></span></div>
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="form-group">
      <label>書名 <span class="req">*</span></label>
      <input type="text" name="title" required value="<?= e($book['title']) ?>">
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>作者 <span class="req">*</span></label>
        <input type="text" name="author" required value="<?= e($book['author']) ?>">
      </div>
      <div class="form-group">
        <label>ISBN</label>
        <input type="text" name="isbn" value="<?= e($book['isbn']) ?>">
      </div>
    </div>
    <div class="form-group">
      <label>封面圖片</label>
      <input type="file" name="cover_image" accept="image/*" data-preview="#coverPrev">
      <div class="form-hint">留空表示不變更</div>
      <img id="coverPrev" src="<?= img_src($book['cover_image']) ?>" alt="預覽" style="max-width:160px; margin-top:10px; border-radius:4px; box-shadow:var(--shadow);">
      <?php if ($book['cover_image']): ?>
        <label style="margin-top:8px; font-weight:400; font-size:13px;">
          <input type="checkbox" name="remove_cover" value="1"> 移除目前封面
        </label>
      <?php endif; ?>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>分類</label>
        <select name="category_id">
          <option value="0">未分類</option>
          <?php foreach ($cats as $c): ?>
            <option value="<?= (int)$c['id'] ?>" <?= (int)$book['category_id']===(int)$c['id']?'selected':'' ?>><?= e($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>閱讀狀態</label>
        <select name="status">
          <?php foreach (['want'=>'想讀','reading'=>'閱讀中','finished'=>'已讀完'] as $k=>$v): ?>
            <option value="<?= $k ?>" <?= $book['status']===$k?'selected':'' ?>><?= $v ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>評分</label>
        <select name="rating">
          <option value="">未評分</option>
          <?php for ($i=5;$i>=1;$i--): ?>
            <option value="<?= $i ?>" <?= (int)$book['rating']===$i?'selected':'' ?>><?= str_repeat('★',$i) ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="form-group">
        <label>頁數</label>
        <input type="number" name="page_count" min="0" value="<?= e($book['page_count']) ?>">
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>出版社</label>
        <input type="text" name="publisher" value="<?= e($book['publisher']) ?>">
      </div>
      <div class="form-group">
        <label>出版年</label>
        <input type="number" name="published_year" min="0" max="2100" value="<?= e($book['published_year']) ?>">
      </div>
    </div>
    <div class="form-group">
      <label>簡介</label>
      <textarea name="description" rows="5"><?= e($book['description']) ?></textarea>
    </div>
    <div class="flex gap-2">
      <button class="btn btn-primary btn-lg">儲存變更</button>
      <a href="book_detail.php?id=<?= (int)$book['id'] ?>" class="btn btn-ghost btn-lg">取消</a>
    </div>
  </form>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
