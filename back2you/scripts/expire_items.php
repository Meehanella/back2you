<?php
require_once __DIR__ . '/../config/database.php';
$pdo->beginTransaction();
try {
    $items = $pdo->query("SELECT * FROM lost_items WHERE status='Lost' AND created_at <= DATE_SUB(NOW(), INTERVAL 70 DAY)")->fetchAll();
    $archive = $pdo->prepare("INSERT IGNORE INTO expired_items
      (original_item_id,item_name,category,color,date_lost,quantity,has_items_inside,image_path,original_poster_id,original_created_at,expired_at,final_status)
      VALUES (?,?,?,?,?,?,?,?,?,?,NOW(),'Expired')");
    $delete = $pdo->prepare("DELETE FROM lost_items WHERE id=? AND status='Lost'");
    foreach ($items as $item) {
        $archive->execute([$item['id'],$item['item_name'],$item['category'],$item['color'],$item['date_lost'],$item['quantity'],$item['has_items_inside'],$item['image_path'],$item['posted_by'],$item['created_at']]);
        $delete->execute([$item['id']]);
    }
    $pdo->commit();
    echo count($items) . ' eligible item(s) archived.';
} catch (Throwable $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo 'Expiration failed. Check the PHP error log.';
}
