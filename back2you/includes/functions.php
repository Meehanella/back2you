<?php
require_once __DIR__ . '/../config/session.php';

function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
function redirect(string $url): never {
    header('Location: ' . $url);
    exit;
}
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}
function verify_csrf(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!is_string($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(419);
            exit('Invalid form token. Go back, refresh the page, and try again.');
        }
    }
}
function current_user(): ?array {
    return $_SESSION['account'] ?? null;
}
function require_login(): array {
    $user = current_user();
    if (!$user) redirect('/back2you/login.php?type=user');
    return $user;
}
function require_role(array $roles): array {
    $user = require_login();
    if (!in_array($user['role'], $roles, true)) {
        http_response_code(403);
        exit('Access denied. You do not have permission to view this page.');
    }
    return $user;
}
function log_audit(PDO $pdo, string $action, string $targetType = '', ?int $targetId = null, string $details = ''): void {
    $u = current_user();
    $stmt = $pdo->prepare("INSERT INTO audit_logs (actor_id, action, target_type, target_id, details) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$u['id'] ?? null, $action, $targetType ?: null, $targetId, $details]);
}
function notify_user(PDO $pdo, int $recipient, string $type, string $message, ?int $itemId = null): void {
    $u = current_user();
    $stmt = $pdo->prepare("INSERT INTO notifications (sender_id, recipient_id, item_id, notification_type, message) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$u['id'] ?? null, $recipient, $itemId, $type, $message]);
}
function upload_item_image(string $field = 'image'): ?string {
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) return null;
    $f = $_FILES[$field];
    if ($f['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Image upload failed.');
    if ($f['size'] > 3 * 1024 * 1024) throw new RuntimeException('Image must be 3 MB or smaller.');
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($f['tmp_name']);
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
    if (!isset($allowed[$mime]) || getimagesize($f['tmp_name']) === false) {
        throw new RuntimeException('Only valid JPG, PNG, GIF, or WEBP images are allowed.');
    }
    $name = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    $dir = __DIR__ . '/../uploads/items/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    if (!move_uploaded_file($f['tmp_name'], $dir . $name)) throw new RuntimeException('Could not save uploaded image.');
    return 'uploads/items/' . $name;
}
function item_statuses(): array {
    return ['Lost', 'Not Found', 'Recovered', 'Found', 'Cancelled'];
}
function page_header(string $title): void {
    $u = current_user();
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>' . e($title) . ' | BACK2YOU</title><link rel="stylesheet" href="/back2you/assets/css/style.css"></head><body>';
    echo '<header class="topbar"><a class="brand" href="/back2you/">BACK2YOU</a><nav>';
    if ($u) {
        if ($u['role'] === 'user') echo '<a href="/back2you/user/dashboard.php">Dashboard</a><a href="/back2you/user/missing-items.php">Missing Items</a><a href="/back2you/user/my-items.php">My Items</a><a href="/back2you/user/notifications.php">Notifications</a>';
        else echo '<a href="/back2you/' . ($u['role'] === 'superadmin' ? 'superadmin' : 'admin') . '/dashboard.php">Dashboard</a><a href="/back2you/admin/missing-items.php">Items</a><a href="/back2you/admin/found-items.php">Found Items</a><a href="/back2you/admin/recovered-items.php">Recovered</a><a href="/back2you/admin/notifications.php">Notifications</a>';
        if ($u['role'] === 'superadmin') echo '<a href="/back2you/superadmin/accounts.php">Accounts</a><a href="/back2you/superadmin/audit-logs.php">Audit Logs</a>';
        echo '<a href="/back2you/logout.php">Logout</a>';
    } else echo '<a href="/back2you/login.php?type=user">User Login</a><a href="/back2you/login.php?type=admin">Admin Login</a>';
    echo '</nav></header><main class="container">';
}
function page_footer(): void {
    echo '</main><footer>BACK2YOU · Philippine Women’s College of Davao · Lost and Found Management System</footer><script src="/back2you/assets/js/script.js"></script></body></html>';
}
