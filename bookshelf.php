<?php
require_once __DIR__ . '/includes/functions.php';

// 篩選條件
$where = []; $params = [];
$q      = trim($_GET['q'] ?? '');
$cat    = (int)($_GET['cat'] ?? 0);
$status = $_GET['status'] ?? '';
$sort   = $_GET['sort'] ?? 'new';

if ($q !== '')      { $where[] = '(b.title LIKE ? OR b.author LIKE ?)'; $params[] = "%$q%"; $params[] = "%$q%"; }
if ($cat > 0)       { $where[] = 'b.category_id = ?'; $params[] = $cat; }
if (in_array($status, ['want','reading','finished'], true)) { $where[] = 'b.status = ?'; $params[] = $status; }

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$orderBy = match ($sort) {
  'popular'  => 'rcnt DESC, b.rating DESC, b.id DESC',
  'rating'   => 'b.rating DESC, b.id DESC',
  'title'    => 'b.title ASC',
  default    => 'b.created_at DESC',
};

$perPage = 12;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

$total = (int)db()->prepare("
    SELECT COUNT(DISTINCT b.id) FROM books b LEFT JOIN users u ON u.id=b.user_id
    LEFT JOIN reviews r ON r.book_id=b.id $whereSql
")->execute($params) ?: null;
$stmt = db()->prepare("SELECT COUNT(DISTINCT b.id) FROM books b $whereSql");
$stmt->execute($params);
$total = (int)$stmt->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));

$sql = "
  SELECT b.*, u.display_name, c.name AS cat_name,
         (SELECT COUNT(*) FROM reviews r WHERE r.book_id=b.id) AS rcnt
    FROM books b
    JOIN users u ON u.id = b.user_id
LEFT JOIN categories c ON c.id = b.category_id
    $whereSql
GROUP BY b.id
ORDER BY $orderBy
   LIMIT $perPage OFFSET $offset
";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll();

$cats = db()->query("SELECT * FROM categories ORDER BY id")->fetchAll();

$page_title = '書架探索';
require_once __DIR__ . '/includes/header.php';
?>

<div class="section-title">
  <h2>書架探索</h2>
  <span class="more">共 <?= $total ?> 本書</span>
</div>

<form method="get" class="filter-bar">
  <input type="search" name="q" class="search-input" placeholder="🔍 搜尋書名或作者..." value="<?= e($q) ?>">
  <select name="cat" onchange="this.form.submit()">
    <option value="0">全部分類</option>
    <?php foreach ($cats as $c): ?>
      <option value="<?= (int)$c['id'] ?>" <?= $cat===(int)$c['id']?'selected':'' ?>><?= e($c['name']) ?></option>
    <?php endforeach; ?>
  </select>
  <select name="status" onchange="this.form.submit()">
    <option value="">所有狀態</option>
    <option value="want"     <?= $status==='want'?'selected':'' ?>>想讀</option>
    <option value="reading"  <?= $status==='reading'?'selected':'' ?>>閱讀中</option>
    <option value="finished" <?= $status==='finished'?'selected':'' ?>>已讀完</option>
  </select>
  <select name="sort" onchange="this.form.submit()">
    <option value="new"     <?= $sort==='new'?'selected':'' ?>>最新</option>
    <option value="popular" <?= $sort==='popular'?'selected':'' ?>>最熱門</option>
    <option value="rating"  <?= $sort==='rating'?'selected':'' ?>>最高評分</option>
    <option value="title"   <?= $sort==='title'?'selected':'' ?>>書名 A-Z</option>
  </select>
  <button class="btn btn-primary btn-sm" type="submit">搜尋</button>
  <?php if ($q || $cat || $status || $sort!=='new'): ?>
    <a href="bookshelf.php" class="btn-link">清除</a>
  <?php endif; ?>
</form>

<?php if (!$books): ?>
  <div class="empty">
    <div class="empty-icon">📚</div>
    <h3>找不到符合條件的書</h3>
    <p>試試別的關鍵字或條件吧。</p>
  </div>
<?php else: ?>
  <div class="book-grid">
    <?php foreach ($books as $b): ?>
      <article class="book-card">
        <a href="book_detail.php?id=<?= (int)$b['id'] ?>" class="book-cover">
          <span class="book-status <?= e($b['status']) ?>"><?= e(status_label($b['status'])) ?></span>
          <?php if ($b['cover_image']): ?>
            <img src="<?= img_src($b['cover_image']) ?>" alt="<?= e($b['title']) ?>">
          <?php else: ?>
            <img src="<?= asset('assets/images/default_cover.svg') ?>" alt="預設封面">
          <?php endif; ?>
        </a>
        <div class="book-body">
          <h3 class="book-title"><a href="book_detail.php?id=<?= (int)$b['id'] ?>"><?= e($b['title']) ?></a></h3>
          <p class="book-author"><?= e($b['author']) ?></p>
          <?= render_stars($b['rating']) ?>
          <div class="book-meta">
            <span><?= e($b['cat_name'] ?? '未分類') ?></span>
            <span><?= e($b['display_name']) ?></span>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>

  <?php if ($totalPages > 1): ?>
    <div class="filter-bar" style="justify-content:center; margin-top:30px;">
      <?php
        $base = http_build_query(array_filter(['q'=>$q,'cat'=>$cat?:null,'status'=>$status,'sort'=>$sort!=='new'?$sort:null]));
        $link = 'bookshelf.php?' . $base . ($base ? '&' : '') . 'page=';
        for ($p = 1; $p <= $totalPages; $p++):
      ?>
        <a class="chip <?= $p===$page?'active':'' ?>" href="<?= e($link . $p) ?>"><?= $p ?></a>
      <?php endfor; ?>
    </div>
  <?php endif; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
