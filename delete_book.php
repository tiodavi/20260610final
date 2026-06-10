<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('bookshelf.php');
csrf_check();

$id = (int)($_POST['id'] ?? 0);
$book = db()->prepare('SELECT * FROM books WHERE id = ?');
$book->execute([$id]);
$book = $book->fetch();
if (!$book) { flash_set('danger', '找不到書'); redirect('bookshelf.php'); }

$me = current_user();
if ($book['user_id'] != $me['id'] && !is_admin()) {
    flash_set('danger', '沒有權限刪除'); redirect('book_detail.php?id=' . $id);
}

delete_upload($book['cover_image']);
db()->prepare('DELETE FROM books WHERE id = ?')->execute([$id]);
flash_set('success', '已刪除《' . $book['title'] . '》');
redirect('bookshelf.php');
