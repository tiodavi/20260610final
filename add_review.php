<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$bookId = (int)($_GET['book_id'] ?? $_POST['book_id'] ?? 0);
$book = db()->prepare('SELECT * FROM books WHERE id = ?');
$book->execute([$bookId]);
$book = $book->fetch();
if (!$book) { flash_set('danger', '找不到書'); redirect('bookshelf.php'); }

$me = current_user();
$error = '';

// 找出我對這本書的書評 (限定一則)
$mine = db()->prepare('SELECT * FROM reviews WHERE book_id = ? AND user_id = ?');
$mine->execute([$bookId, $me['id']]);
$mine = $mine->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $content = trim($_POST['content'] ?? '');
    $rating  = (int)($_POST['rating'] ?? 0);

    if ($content === '')  $error = '書評內容不可為空';
    elseif ($rating < 1) $error = '請選擇評分';

    if (!$error) {
        if ($mine) {
            db()->prepare('UPDATE reviews SET content=?, rating=? WHERE id=?')
                ->execute([$content, $rating, $mine['id']]);
        } else {
            db()->prepare('INSERT INTO reviews (book_id,user_id,content,rating) VALUES (?,?,?,?)')
                ->execute([$bookId, $me['id'], $content, $rating]);
        }
        // 同步書的評分
        $avg = (int)round((float)db()->query("SELECT AVG(rating) FROM reviews WHERE book_id=" . (int)$bookId)->fetchColumn());
        db()->prepare('UPDATE books SET rating=? WHERE id=?')->execute([$avg, $bookId]);
        flash_set('success', $mine ? '書評已更新' : '書評已發表');
        redirect('book_detail.php?id=' . $bookId);
    }
}

$page_title = ($mine?'編輯':'撰寫') . '書評';
require_once __DIR__ . '/includes/header.php';
?>
<div class="card" style="max-width:760px; margin:0 auto;">
  <h2><?= $mine?'編輯':'撰寫' ?>書評</h2>
  <p class="text-muted">《<?= e($book['title']) ?>》— <?= e($book['author']) ?></p>

  <?php if ($error): ?>
    <div class="flash flash-danger"><span class="flash-icon">✕</span><span><?= e($error) ?></span></div>
  <?php endif; ?>

  <form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="book_id" value="<?= (int)$bookId ?>">
    <div class="form-group">
      <label>你的評分 <span class="req">*</span></label>
      <div class="rate">
        <?php for ($i=5;$i>=1;$i--): ?>
          <input type="radio" id="r<?= $i ?>" name="rating" value="<?= $i ?>" <?= (int)($mine['rating'] ?? 0)===$i?'checked':'' ?>>
          <label for="r<?= $i ?>" title="<?= $i ?> 顆星"></label>
        <?php endfor; ?>
      </div>
    </div>
    <div class="form-group">
      <label>書評內容 <span class="req">*</span></label>
      <textarea name="content" rows="8" required placeholder="說說你對這本書的想法..."><?= e($mine['content'] ?? '') ?></textarea>
    </div>
    <div class="flex gap-2">
      <button class="btn btn-primary btn-lg"><?= $mine?'更新書評':'發表書評' ?></button>
      <a href="book_detail.php?id=<?= (int)$bookId ?>" class="btn btn-ghost btn-lg">取消</a>
    </div>
  </form>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
