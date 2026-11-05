<?php
require_once __DIR__ . '/includes/auth.php';
if (current_user()) redirect('index.php');
$type = ($_GET['type'] ?? 'user') === 'admin' ? 'admin' : 'user';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare("SELECT * FROM accounts WHERE username = ? AND account_status = 'active' LIMIT 1");
    $stmt->execute([$username]);
    $account = $stmt->fetch();
    if ($account && password_verify($password, $account['password_hash'])) {
        if (($type === 'user' && $account['role'] !== 'user') || ($type === 'admin' && !in_array($account['role'], ['admin','superadmin'], true))) {
            $error = 'This account cannot use this login page.';
        } else {
            session_regenerate_id(true);
            $_SESSION['account'] = [
                'id'=>(int)$account['id'], 'full_name'=>$account['full_name'],
                'username'=>$account['username'], 'role'=>$account['role'],
                'must_change_password'=>(int)$account['must_change_password']
            ];
            $up = $pdo->prepare("UPDATE accounts SET last_login_at = NOW() WHERE id = ?");
            $up->execute([$account['id']]);
            log_audit($pdo, 'LOGIN', 'account', (int)$account['id'], 'Successful login');
            if ($account['must_change_password']) redirect('superadmin/settings.php?first=1');
            if ($account['role'] === 'user') redirect('user/dashboard.php');
            if ($account['role'] === 'superadmin') redirect('superadmin/dashboard.php');
            redirect('admin/dashboard.php');
        }
    } else $error = 'Incorrect username or password.';
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= $type==='admin'?'Admin':'User' ?> Login | BACK2YOU</title><link rel="stylesheet" href="assets/css/style.css"></head>
<body><div class="login-wrap"><section class="login-box"><div class="login-head"><div class="crest">PWC</div><div>BACK2YOU<br><?= $type==='admin'?'ADMINISTRATOR':'USER' ?> LOGIN</div></div><div class="login-body">
<?php if($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
<form method="post"><?= csrf_field() ?><div class="field"><input name="username" placeholder="Username" required autocomplete="username"></div><div class="field"><input type="password" name="password" placeholder="Password" required autocomplete="current-password"></div><div class="login-actions"><button type="submit">LOG IN</button><a href="register-info.php">New user? Register</a></div></form>
<p class="muted" style="margin-top:18px"><a href="index.php">← Back to homepage</a></p></div></section></div></body></html>
