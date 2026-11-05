<?php
require_once __DIR__ . '/config/session.php';
if (!empty($_SESSION['account'])) {
    $role = $_SESSION['account']['role'];
    if ($role === 'user') header('Location: user/dashboard.php');
    elseif ($role === 'superadmin') header('Location: superadmin/dashboard.php');
    else header('Location: admin/dashboard.php');
    exit;
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>BACK2YOU</title><link rel="stylesheet" href="assets/css/style.css"></head>
<body><section class="hero"><div><h1>Welcome to BACK2YOU</h1><p>Where we find lost items and bring it back to you!</p><div class="hero-actions"><a class="btn" href="login.php?type=user">USER</a><a class="btn gold" href="login.php?type=admin">ADMIN</a></div></div></section></body></html>
