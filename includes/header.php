<?php
require_once __DIR__ . '/functions.php';
$page_title = $page_title ?? SITE_NAME;
$current = basename($_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($page_title) ?> · <?= e(SITE_NAME) ?></title>
<link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Serif+TC:wght@400;500;700;900&family=Noto+Sans+TC:wght@300;400;500;700&family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
</head>
<body>

<header class="site-header">
  <div class="container header-inner">
    <a href="index.php" class="brand">
      <span class="brand-mark">拾</span>
      <span class="brand-text">
        <strong>拾光書架</strong>
        <small>BOOKSHELF OF MEMORIES</small>
      </span>
    </a>

    <button class="nav-toggle" aria-label="選單" id="navToggle">☰</button>

    <nav class="site-nav" id="siteNav">
      <a href="index.php"      class="<?= $current==='index.php'?'active':'' ?>">首頁</a>
      <a href="bookshelf.php"  class="<?= $current==='bookshelf.php'?'active':'' ?>">書架探索</a>
      <?php if (is_logged_in()): ?>
        <a href="my_shelf.php" class="<?= $current==='my_shelf.php'?'active':'' ?>">我的書架</a>
        <a href="add_book.php" class="<?= $current==='add_book.php'?'active':'' ?>">新增書本</a>
      <?php endif; ?>
      <a href="about.php"      class="<?= $current==='about.php'?'active':'' ?>">關於</a>
    </nav>

    <div class="user-area">
      <?php if ($u = current_user()): ?>
        <a href="profile.php" class="user-chip" title="我的檔案">
          <span class="avatar-sm" style="background-image:url('<?= e(asset($u['avatar'] ?: 'assets/images/default_avatar.svg')) ?>')"></span>
          <span class="user-name"><?= e($u['display_name']) ?></span>
          <?php if ($u['role']==='admin'): ?><span class="badge badge-admin">ADMIN</span><?php endif; ?>
        </a>
        <?php if (is_admin()): ?>
          <a href="admin/index.php" class="btn btn-ghost btn-sm">後台</a>
        <?php endif; ?>
        <a href="logout.php" class="btn btn-ghost btn-sm">登出</a>
      <?php else: ?>
        <a href="login.php" class="btn btn-ghost btn-sm">登入</a>
        <a href="register.php" class="btn btn-primary btn-sm">加入書架</a>
      <?php endif; ?>
    </div>
  </div>
</header>

<main class="site-main">
  <div class="container">
    <?= flash_render() ?>
