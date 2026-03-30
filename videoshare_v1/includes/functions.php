<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function esc(?string $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function base_url(string $path = ''): string {
    return rtrim(SITE_URL, '/') . '/' . ltrim($path, '/');
}

function current_lang(): string {
    if (isset($_GET['lang']) && in_array($_GET['lang'], ['da', 'en'], true)) {
        $_SESSION['lang'] = $_GET['lang'];
    }
    return $_SESSION['lang'] ?? DEFAULT_LANG;
}

function lang(): array {
    static $cache = [];
    $lang = current_lang();
    if (!isset($cache[$lang])) {
        $file = __DIR__ . '/../lang/' . $lang . '.php';
        $cache[$lang] = file_exists($file) ? require $file : require __DIR__ . '/../lang/da.php';
    }
    return $cache[$lang];
}

function t(string $key, string $fallback = ''): string {
    $dict = lang();
    return $dict[$key] ?? ($fallback !== '' ? $fallback : $key);
}

function theme(): string {
    if (isset($_GET['theme']) && in_array($_GET['theme'], ['dark', 'light'], true)) {
        $_SESSION['theme'] = $_GET['theme'];
    }
    return $_SESSION['theme'] ?? 'dark';
}

function is_logged_in(): bool {
    return !empty($_SESSION['user']);
}

function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function is_admin(): bool {
    return is_logged_in() && (int)($_SESSION['user']['is_admin'] ?? 0) === 1;
}

function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

function require_login(): void {
    if (!is_logged_in()) {
        redirect(base_url('login.php'));
    }
}

function require_admin(): void {
    if (!is_admin()) {
        http_response_code(403);
        exit('Forbidden');
    }
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(?string $token): bool {
    return is_string($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function login_user(array $user): void {
    $_SESSION['user'] = [
        'id' => (int)$user['id'],
        'username' => $user['username'],
        'email' => $user['email'],
        'is_admin' => (int)$user['is_admin'],
    ];
}

function logout_user(): void {
    unset($_SESSION['user']);
}

function fetch_settings(): array {
    $stmt = db()->query('SELECT setting_key, setting_value FROM settings');
    $settings = [];
    foreach ($stmt as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    return $settings;
}

function setting(string $key, ?string $default = null): ?string {
    static $settings = null;
    if ($settings === null) {
        $settings = fetch_settings();
    }
    return $settings[$key] ?? $default;
}

function meta_title(?string $title = null): string {
    return $title ? $title . ' - ' . SITE_NAME : SITE_NAME;
}

function meta_description(?string $description = null): string {
    return $description ?: 'VideoShare platform with comments, voting, categories and admin panel.';
}

function categories(): array {
    return ['hardkore', 'paen', 'sjov'];
}

function normalize_category(string $category): string {
    return in_array($category, categories(), true) ? $category : 'sjov';
}

function paginate(int $page, int $perPage): array {
    $page = max(1, $page);
    $offset = ($page - 1) * $perPage;
    return [$page, $offset];
}

function thumbnail_for(array $video): string {
    if (!empty($video['thumbnail']) && file_exists(__DIR__ . '/../' . $video['thumbnail'])) {
        return base_url($video['thumbnail']);
    }
    return 'https://placehold.co/640x360?text=VideoShare';
}

function count_video_comments(int $videoId): int {
    $stmt = db()->prepare('SELECT COUNT(*) FROM comments WHERE video_id = ?');
    $stmt->execute([$videoId]);
    return (int)$stmt->fetchColumn();
}

function comment_tree(int $videoId): array {
    $stmt = db()->prepare('
        SELECT c.*, u.username
        FROM comments c
        JOIN users u ON u.id = c.user_id
        WHERE c.video_id = ?
        ORDER BY c.parent_id ASC, (c.upvotes - c.downvotes) DESC, c.created_at ASC
    ');
    $stmt->execute([$videoId]);
    $rows = $stmt->fetchAll();

    $byParent = [];
    foreach ($rows as $row) {
        $byParent[(int)$row['parent_id']][] = $row;
    }

    $build = function(int $parentId) use (&$build, $byParent): array {
        $items = $byParent[$parentId] ?? [];
        foreach ($items as &$item) {
            $item['children'] = $build((int)$item['id']);
        }
        return $items;
    };

    return $build(0);
}

function attempt_generate_thumbnail(string $videoPath, string $thumbPath): bool {
    $ffmpeg = trim((string)shell_exec('command -v ffmpeg'));
    if ($ffmpeg === '') {
        return false;
    }
    $cmd = escapeshellcmd($ffmpeg) . ' -y -i ' . escapeshellarg($videoPath) . ' -ss 00:00:01 -vframes 1 ' . escapeshellarg($thumbPath) . ' 2>&1';
    shell_exec($cmd);
    return file_exists($thumbPath);
}

function save_uploaded_video(array $file, string $category): ?string {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return null;
    }

    $allowed = ['video/mp4' => 'mp4'];
    $mime = mime_content_type($file['tmp_name']);
    if (!isset($allowed[$mime])) {
        return null;
    }

    $ext = $allowed[$mime];
    $name = uniqid('video_', true) . '.' . $ext;
    $relDir = 'uploads/videos/' . $category;
    $destDir = __DIR__ . '/../' . $relDir;
    if (!is_dir($destDir)) {
        mkdir($destDir, 0775, true);
    }
    $dest = $destDir . '/' . $name;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return null;
    }

    return $relDir . '/' . $name;
}

function save_uploaded_thumbnail(array $file): ?string {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return null;
    }

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = mime_content_type($file['tmp_name']);
    if (!isset($allowed[$mime])) {
        return null;
    }

    $ext = $allowed[$mime];
    $name = uniqid('thumb_', true) . '.' . $ext;
    $relDir = 'uploads/thumbs';
    $destDir = __DIR__ . '/../' . $relDir;
    if (!is_dir($destDir)) {
        mkdir($destDir, 0775, true);
    }
    $dest = $destDir . '/' . $name;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return null;
    }

    return $relDir . '/' . $name;
}
