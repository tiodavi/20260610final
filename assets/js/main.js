// 拾光書架 - 前端互動

document.addEventListener('DOMContentLoaded', () => {
  // 手機版選單
  const btn = document.getElementById('navToggle');
  const nav = document.getElementById('siteNav');
  if (btn && nav) {
    btn.addEventListener('click', () => nav.classList.toggle('open'));
  }

  // 圖片預覽 (file input)
  document.querySelectorAll('input[type="file"][data-preview]').forEach(input => {
    input.addEventListener('change', e => {
      const f = e.target.files[0];
      if (!f) return;
      const prev = document.querySelector(input.dataset.preview);
      if (prev) prev.src = URL.createObjectURL(f);
    });
  });

  // 刪除確認
  document.querySelectorAll('form[data-confirm]').forEach(f => {
    f.addEventListener('submit', e => {
      if (!confirm(f.dataset.confirm)) e.preventDefault();
    });
  });

  // Flash 訊息 3 秒淡出
  document.querySelectorAll('.flash').forEach(el => {
    setTimeout(() => { el.style.transition = 'opacity .5s'; el.style.opacity = '0'; }, 4000);
    setTimeout(() => el.remove(), 4700);
  });
});
