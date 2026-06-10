  </div>
</main>

<footer class="site-footer">
  <div class="container footer-grid">
    <div>
      <div class="brand brand-footer">
        <span class="brand-mark">拾</span>
        <span class="brand-text">
          <strong>拾光書架</strong>
          <small>BOOKSHELF OF MEMORIES</small>
        </span>
      </div>
      <p class="footer-tag"><?= e(SITE_TAG) ?></p>
    </div>

    <div>
      <h4>關於本站</h4>
      <ul>
        <li><a href="about.php">理念</a></li>
        <li><a href="bookshelf.php">探索書架</a></li>
        <li><a href="register.php">加入會員</a></li>
      </ul>
    </div>

    <div>
      <h4>會員專區</h4>
      <ul>
        <?php if (is_logged_in()): ?>
          <li><a href="my_shelf.php">我的書架</a></li>
          <li><a href="profile.php">編輯檔案</a></li>
          <li><a href="add_book.php">新增書本</a></li>
        <?php else: ?>
          <li><a href="login.php">會員登入</a></li>
        <?php endif; ?>
      </ul>
    </div>

    <div>
      <h4>聯絡</h4>
      <ul>
        <li>Email  hello@bookshelf.local</li>
        <li>© <?= date('Y') ?> 拾光書架</li>
        <li>課堂專題作品 · <?= e(SITE_NAME) ?></li>
      </ul>
    </div>
  </div>
</footer>

<script src="assets/js/main.js"></script>
</body>
</html>
