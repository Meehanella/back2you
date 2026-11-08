<?php
require_once __DIR__ . '/../includes/auth.php';
$u = require_role(['user']);
$total = $pdo->prepare("SELECT COUNT(*) FROM lost_items WHERE posted_by=?"); $total->execute([$u['id']]);
$unread = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE recipient_id=? AND is_read=0"); $unread->execute([$u['id']]);
page_header('User Dashboard');
?>
<h1>Welcome, <?= e($u['full_name']) ?>!</h1><p class="muted">Your BACK2YOU account overview.</p>
<div class="grid"><div class="stat"><strong><?= (int)$total->fetchColumn() ?></strong>My item posts</div><div class="stat"><strong><?= (int)$unread->fetchColumn() ?></strong>Unread notifications</div></div>
<div class="panel"><div class="panel-body"><a class="btn" href="add-item.php">+ Post a Missing Item</a> <a class="btn light" href="missing-items.php">Browse Missing Items</a> <a class="btn light" href="my-items.php">Manage My Posts</a></div></div>
<?php page_footer(); ?>
