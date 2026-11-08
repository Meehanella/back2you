<?php
require_once __DIR__ . '/../includes/auth.php';
$count = (int)$pdo->query("SELECT COUNT(*) FROM accounts WHERE role='superadmin'")->fetchColumn();
if ($count > 0) exit('A super administrator already exists. Delete this setup script now.');
$hash = password_hash('1admin', PASSWORD_DEFAULT);
$stmt = $pdo->prepare("INSERT INTO accounts (full_name, username, password_hash, role, must_change_password) VALUES (?, ?, ?, 'superadmin', 1)");
$stmt->execute(['BACK2YOU Super Administrator', 'Ellagariah', $hash]);
echo 'Initial super administrator created. Username: Ellagariah. Default password: 1admin. Log in and change it immediately, then DELETE this setup file.';
