<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('bookshelf.php');
csrf_check();

$id = (int)($_POST['id'] ?? 0);
$bookId = (int)($_POST['book_id'] ?? 0);

$r = db()->prepare('SELECT * FROM reviews WHERE id = ?');
$r->execute([$id]);
$r = $r->fetch();
if (!$r) { flash_set('danger', '找不到書評'); redirect('book_detail.php?id=' . $bookId); }

$me = current_user();
if ($r['user_id'] != $me['id'] && !is_admin()) {
    flash_set('danger', '沒有權限'); redirect('book_detail.php?id=' . $bookId);
}

db()->prepare('DELETE FROM reviews WHERE id = ?')->execute([$id]);

// 重新計算書的評分
$avg = (int)round((float)db()->query("SELECT AVG(rating) FROM reviews WHERE book_id=" . (int)$r['book_id'])->fetchColumn());
db()->prepare('UPDATE books SET rating=? WHERE id=?')->execute([$avg, $r['book_id']]);

flash_set('success', '書評已刪除');
redirect('book_detail.php?id=' . $r['book_id']);
