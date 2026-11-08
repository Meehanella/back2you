<?php
require_once __DIR__ . '/../includes/auth.php'; $u=require_role(['user']);
$q=trim($_GET['q']??''); $cat=trim($_GET['category']??''); $color=trim($_GET['color']??'');
$sql="SELECT * FROM lost_items WHERE status='Lost'"; $args=[];
if($q!==''){ $sql.=" AND item_name LIKE ?"; $args[]="%$q%"; }
if($cat!==''){ $sql.=" AND category=?"; $args[]=$cat; }
if($color!==''){ $sql.=" AND color LIKE ?"; $args[]="%$color%"; }
$sql.=" ORDER BY created_at DESC LIMIT 100";
$s=$pdo->prepare($sql);$s->execute($args);$items=$s->fetchAll();
if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();$id=(int)($_POST['item_id']??0);$st=$pdo->prepare("SELECT posted_by,item_name FROM lost_items WHERE id=? AND status='Lost'");$st->execute([$id]);$item=$st->fetch();if($item && (int)$item['posted_by']!==$u['id']){notify_user($pdo,(int)$item['posted_by'],'Possible matching item',$u['full_name'].' thinks your post "'.$item['item_name'].'" may match an item they are looking for.',$id);$msg='The poster has been notified.';}else $msg='You cannot notify yourself or this item is unavailable.';}
page_header('Missing Items');?><h1>Currently Missing Items</h1><?php if(!empty($msg)):?><div class="alert success"><?=e($msg)?></div><?php endif;?>
<form class="toolbar" method="get"><input name="q" placeholder="Search item name" value="<?=e($q)?>"><input name="category" placeholder="Category" value="<?=e($cat)?>"><input name="color" placeholder="Color" value="<?=e($color)?>"><button>Search</button></form>
<div class="table-wrap"><table><tr><th>Picture</th><th>Item</th><th>Category</th><th>Color</th><th>Date Lost</th><th>Qty</th><th>Inside?</th><th>Status</th><th>Date Posted</th><th>Notify</th></tr>
<?php foreach($items as $it):?><tr><td><?php if($it['image_path']):?><img class="thumb" src="../<?=e($it['image_path'])?>"><?php else:?>—<?php endif;?></td><td><?=e($it['item_name'])?></td><td><?=e($it['category'])?></td><td><?=e($it['color'])?></td><td><?=e($it['date_lost'])?></td><td><?=e($it['quantity'])?></td><td><?=$it['has_items_inside']?'Yes':'No'?></td><td><span class="badge"><?=e($it['status'])?></span></td><td><?=e($it['created_at'])?></td><td><form method="post"><?=csrf_field()?><input type="hidden" name="item_id" value="<?=$it['id']?>"><button class="small">Notify</button></form></td></tr><?php endforeach;?>
<?php if(!$items):?><tr><td colspan="10">No active missing items found.</td></tr><?php endif;?></table></div><?php page_footer();?>
