<?php
require_once __DIR__ . '/db.php';

/* ============================================================
 *   認證 / Session
 * ============================================================ */

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return !empty($_SESSION['user']);
}

function is_admin(): bool
{
    return is_logged_in() && ($_SESSION['user']['role'] ?? '') === 'admin';
}

function require_login(): void
{
    if (!is_logged_in()) {
        $_SESSION['flash'] = ['type' => 'warning', 'msg' => '請先登入'];
        redirect('login.php');
    }
}

function require_admin(): void
{
    require_login();
    if (!is_admin()) {
        $_SESSION['flash'] = ['type' => 'danger', 'msg' => '需要管理員權限'];
        redirect('index.php');
    }
}

function login_user(array $user): void
{
    // 避免儲存敏感欄位
    unset($user['password_hash']);
    $_SESSION['user'] = $user;
}

function logout_user(): void
{
    unset($_SESSION['user']);
    session_regenerate_id(true);
}

/* ============================================================
 *   雜湊 / 驗證
 * ============================================================ */

function hash_password(string $plain): string
{
    return password_hash($plain, PASSWORD_BCRYPT);
}

function verify_password(string $plain, string $hash): bool
{
    return password_verify($plain, $hash);
}

/* ============================================================
 *   Flash 訊息
 * ============================================================ */

function flash_set(string $type, string $msg): void
{
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function flash_get(): ?array
{
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

function flash_render(): string
{
    $f = flash_get();
    if (!$f) return '';
    $icon = ['success'=>'✓','danger'=>'✕','warning'=>'!','info'=>'ℹ'][$f['type']] ?? '•';
    return '<div class="flash flash-' . htmlspecialchars($f['type']) . '">'
         .   '<span class="flash-icon">' . $icon . '</span>'
         .   '<span>' . htmlspecialchars($f['msg']) . '</span>'
         . '</div>';
}

/* ============================================================
 *   輔助
 * ============================================================ */

function redirect(string $url): void
{
    // 若不是 http(s) 開頭也不是根目錄開頭，加上根目錄前綴
    if (!preg_match('#^(https?:|/)#i', $url)) {
        // 取目前的 basename，反推根目錄
        $sn = $_SERVER['SCRIPT_NAME'] ?? '';
        // 假設 project 根 = SCRIPT_NAME 移除 basename
        $base = rtrim(str_replace('\\', '/', dirname($sn)), '/');
        // 若目前在 admin/ 子目錄，要回到上一層
        if (substr_count($base, '/') >= 2) {
            $base = preg_replace('#/[^/]+$#', '', $base);
        }
        $url = $base . '/' . ltrim($url, '/');
    }
    header('Location: ' . $url);
    exit;
}

function e(?string $s): string
{
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    // 簡化版：以當前目錄相對位置產生連結
    return $path;
}

function status_label(string $s): string
{
    return ['want'=>'想讀','reading'=>'閱讀中','finished'=>'已讀完'][$s] ?? $s;
}

function render_stars(?int $rating): string
{
    $rating = max(0, min(5, (int)$rating));
    $out = '<span class="stars" aria-label="' . $rating . ' 顆星">';
    for ($i = 1; $i <= 5; $i++) {
        $out .= $i <= $rating ? '★' : '☆';
    }
    $out .= '</span>';
    return $out;
}

function time_ago(?string $datetime): string
{
    if (!$datetime) return '';
    $t = strtotime($datetime);
    $diff = time() - $t;
    if ($diff < 60)      return '剛剛';
    if ($diff < 3600)    return floor($diff/60) . ' 分鐘前';
    if ($diff < 86400)   return floor($diff/3600) . ' 小時前';
    if ($diff < 86400*7) return floor($diff/86400) . ' 天前';
    return date('Y-m-d', $t);
}

/* ============================================================
 *   圖片上傳
 * ============================================================ */

/**
 * 處理單張圖片上傳，回傳儲存的相對路徑 (例如 uploads/covers/abc.jpg)
 * 失敗回傳 null；錯誤訊息可由 $error 取得
 */
function handle_upload(array $file, string $subdir, ?string &$error = null): ?string
{
    $error = null;

    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        $error = '未選擇檔案';
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = '上傳失敗 (錯誤代碼 ' . $file['error'] . ')';
        return null;
    }
    if ($file['size'] > MAX_UPLOAD_MB * 1024 * 1024) {
        $error = '檔案太大 (上限 ' . MAX_UPLOAD_MB . ' MB)';
        return null;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_IMG, true)) {
        $error = '不支援的圖片格式';
        return null;
    }

    // 確認是否為真實圖片
    $info = @getimagesize($file['tmp_name']);
    if (!$info) {
        $error = '檔案不是有效的圖片';
        return null;
    }

    $dir = rtrim(UPLOAD_DIR, '/') . '/' . trim($subdir, '/') . '/';
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
        $error = '無法建立上傳目錄';
        return null;
    }

    $name = bin2hex(random_bytes(8)) . '_' . time() . '.' . $ext;
    $path = $dir . $name;
    if (!move_uploaded_file($file['tmp_name'], $path)) {
        $error = '移動檔案失敗';
        return null;
    }

    // 縮圖 (寬度限制)
    if ($info[0] > MAX_IMG_WIDTH) {
        resize_image($path, MAX_IMG_WIDTH, $info[2]);
    }

    return UPLOAD_URL . trim($subdir, '/') . '/' . $name;
}

function resize_image(string $path, int $maxW, int $type)
{
    [$w, $h] = getimagesize($path);
    $newW = $maxW;
    $newH = (int)round($h * ($maxW / $w));

    $src = null; $dst = null;
    switch ($type) {
        case IMAGETYPE_JPEG: $src = @imagecreatefromjpeg($path); break;
        case IMAGETYPE_PNG:  $src = @imagecreatefrompng($path);  break;
        case IMAGETYPE_GIF:  $src = @imagecreatefromgif($path);  break;
        case IMAGETYPE_WEBP: $src = @imagecreatefromwebp($path); break;
    }
    if (!$src) return;

    $dst = imagecreatetruecolor($newW, $newH);
    if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_WEBP) {
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
    }
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $w, $h);

    switch ($type) {
        case IMAGETYPE_JPEG: imagejpeg($dst, $path, 85); break;
        case IMAGETYPE_PNG:  imagepng($dst, $path, 6);   break;
        case IMAGETYPE_GIF:  imagegif($dst, $path);       break;
        case IMAGETYPE_WEBP: imagewebp($dst, $path, 85);  break;
    }
    imagedestroy($src); imagedestroy($dst);
}

function delete_upload(?string $relPath): void
{
    if (!$relPath) return;
    $abs = __DIR__ . '/../' . ltrim($relPath, '/');
    if (is_file($abs)) @unlink($abs);
}

/* ============================================================
 *   CSRF
 * ============================================================ */

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

function csrf_check(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
    $t = $_POST['csrf'] ?? '';
    if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $t)) {
        http_response_code(400);
        die('CSRF 驗證失敗，請重新操作');
    }
}
