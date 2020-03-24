<?php include_once "init.php"; ?>
<?php
if(!isset($_SESSION['userid'])){
header("Location: ".$langPrefix."/login/?ref=".urlencode($langPrefix.$_SERVER['REQUEST_URI']));
exit;
}
?>
<?php
$selected_tab=trim($_GET['controller']);
?>
<?php
if(trim($_GET['mode'])=='ajax'){
header('Content-Type: application/json; charset=utf-8');
if(trim($_POST['action'])=='remove'){
$result=array();
$result['errors']=array();
mysql_query("UPDATE `ads` SET `active`='4' WHERE `userid`='".$_SESSION['userid']."' AND `aid`='".intval($_POST['id'])."';");
if(count($result['errors'])==0){
$result['message']=l('my_items_successfully_removed');
$result['status']='success';
} else {
$result['status']='error';
}
echo json_encode($result);
}
if(trim($_POST['action'])=='activate'){
$result=array();
$result['errors']=array();
$ad=getAd(intval($_POST['id']));
if($ad['time_to']>$time){
mysql_query("UPDATE `ads` SET `active`='1' WHERE `userid`='".$_SESSION['userid']."' AND `aid`='".intval($_POST['id'])."';");
} else {
mysql_query("UPDATE `ads` SET `active`='1', `time`='".$time."', `time_to`='".($time+60*60*24*$ad['days'])."' WHERE `userid`='".$_SESSION['userid']."' AND `aid`='".intval($_POST['id'])."';");
}
if(count($result['errors'])==0){
$result['message']=l('my_items_successfully_activated');
$result['status']='success';
} else {
$result['status']='error';
}
echo json_encode($result);
}
if(trim($_POST['action'])=='deactivate'){
$result=array();
$result['errors']=array();
mysql_query("UPDATE `ads` SET `active`='2' WHERE `userid`='".$_SESSION['userid']."' AND `aid`='".intval($_POST['id'])."';");
if(count($result['errors'])==0){
$result['message']=l('my_items_successfully_deactivated');
$result['status']='success';
} else {
$result['status']='error';
}
echo json_encode($result);
}
exit;
}
?>
<?php
$page_tabs=array();
$page_tabs['my']=$lang['my_items_tab_my'];
$page_tabs['waiting']=$lang['my_items_tab_waiting'];
$page_tabs['inactive']=$lang['my_items_tab_inactive'];
$page_tabs['moderated']=$lang['my_items_tab_moderated'];
?>
<?php
if(in_array(trim($_GET['order']), array('asc', 'desc'))){
$order=trim($_GET['order']);
} else {
$order='desc';
}
if(in_array(trim($_GET['orderField']), array('time', 'title'))){
$orderField=trim($_GET['orderField']);
} else {
$orderField='time';
}
if(trim($_GET['q'])!=''){
$sq=" AND (MATCH (`index`, `title`, `description`) AGAINST ('"._F(Words2BaseForm(trim($_GET['q'])))."' IN NATURAL LANGUAGE MODE))";
} else {
$sq='';
}
$count=array();
$count['my']=mysql_result(mysql_query("SELECT COUNT(`aid`) FROM `ads` WHERE `userid`='".$_SESSION['userid']."' AND `active`='1';"), 0, 0);
$count['waiting']=mysql_result(mysql_query("SELECT COUNT(`aid`) FROM `ads` WHERE `userid`='".$_SESSION['userid']."' AND `active`='0';"), 0, 0);
$count['inactive']=mysql_result(mysql_query("SELECT COUNT(`aid`) FROM `ads` WHERE `userid`='".$_SESSION['userid']."' AND `active`='2';"), 0, 0);
$count['moderated']=mysql_result(mysql_query("SELECT COUNT(`aid`) FROM `ads` WHERE `userid`='".$_SESSION['userid']."' AND `active`='3';"), 0, 0);
if(trim($_GET['controller'])=='my'){
$get_found_ads_count=mysql_query("SELECT COUNT(*) FROM `ads` WHERE `userid`='".$_SESSION['userid']."' AND `active`='1'".$sq.";");
}
if(trim($_GET['controller'])=='waiting'){
$get_found_ads_count=mysql_query("SELECT COUNT(*) FROM `ads` WHERE `userid`='".$_SESSION['userid']."' AND `active`='0'".$sq.";");
}
if(trim($_GET['controller'])=='inactive'){
$get_found_ads_count=mysql_query("SELECT COUNT(*) FROM `ads` WHERE `userid`='".$_SESSION['userid']."' AND `active`='2'".$sq.";");
}
if(trim($_GET['controller'])=='moderated'){
$get_found_ads_count=mysql_query("SELECT COUNT(*) FROM `ads` WHERE `userid`='".$_SESSION['userid']."' AND `active`='3'".$sq.";");
}
$pages=array();
$pages['count']=intval(mysql_result($get_found_ads_count, 0, 0));
$pages['per']=(($m)?20:10);
if(intval($_GET['page'])>ceil($pages['count']/$pages['per'])){
$pages['current']=ceil($pages['count']/$pages['per']);
} else {
$pages['current']=((intval($_GET['page'])>1)?intval($_GET['page']):1);
}
$pages['show']=9;
$get_params=$_GET;
$pages['url']=reset(explode('?', $_SERVER['REQUEST_URI']));
unset($get_params['controller']);
unset($get_params['page']);
$get_params['page']='';
if(count($get_params)>0){
$pages['url'].='?'.http_build_query($get_params).'(:page)';
}
$pagingHtml=paginator::getHtml($pages['count'], $pages['current'], $pages['per'], $pages['show'], $pages['url']);
if(trim($_GET['controller'])=='my'){
$get_ads=mysql_query("SELECT * FROM `ads` WHERE `userid`='".$_SESSION['userid']."' AND `active`='1'".$sq." ORDER BY `".$orderField."` ".$order." LIMIT ".($pages['per']*($pages['current']-1)).", ".$pages['per'].";");
}
if(trim($_GET['controller'])=='waiting'){
$get_ads=mysql_query("SELECT * FROM `ads` WHERE `userid`='".$_SESSION['userid']."' AND `active`='0'".$sq." ORDER BY `".$orderField."` ".$order." LIMIT ".($pages['per']*($pages['current']-1)).", ".$pages['per'].";");
}
if(trim($_GET['controller'])=='inactive'){
$get_ads=mysql_query("SELECT * FROM `ads` WHERE `userid`='".$_SESSION['userid']."' AND `active`='2'".$sq." ORDER BY `".$orderField."` ".$order." LIMIT ".($pages['per']*($pages['current']-1)).", ".$pages['per'].";");
}
if(trim($_GET['controller'])=='moderated'){
$get_ads=mysql_query("SELECT * FROM `ads` WHERE `userid`='".$_SESSION['userid']."' AND `active`='3'".$sq." ORDER BY `".$orderField."` ".$order." LIMIT ".($pages['per']*($pages['current']-1)).", ".$pages['per'].";");
}
?>
<?php
$show_top_tabs=true;
$is_cabinet=true;
$current_tab='ads';
$top_tabs_title=l('my_items_title');
$top_tabs_description=l('my_items_description');
$pagetitle=l('my_items_title')." &bull; ".$config['sitename'];
$pagedesc=$config['description'];
?>
<?php
if($m){ include "m-my.php"; exit; }
?>
<?php include "includes/header.php"; ?>

<script>
var myItemsCount=<?php echo $pages['count']; ?>;
</script>

<div id="moderatedReason" style="display:none;">
<div class="window-title"><?php echo l('my_items_reason_title'); ?><a href="javascript:void(0);" onclick="$.fancybox.close();"></a></div>
<div class="window-message"><nobr><?php echo l('my_items_reason_text'); ?></nobr></div>
<div class="window-buttons">
<button class="btn btn-primary" onclick="$.fancybox.close();">OK</button>
</div>
</div>

<?php if(mysql_num_rows($get_ads)){ ?>
<div class="my_items_title">
<?php
if(trim($_GET['q'])==''){
echo $page_tabs[trim($_GET['controller'])]['title'];
} else {
echo str_replace('[QUERY]', htmlspecialchars(trim($_GET['q'])), $page_tabs[trim($_GET['controller'])]['search']);
}
?>
</div>
<?php } ?>

<div class="empty-list<?php if(mysql_num_rows($get_ads)){ ?> hidden<?php } ?>">
<div>
<i class="fa fa-bullhorn"></i>
</div>
<?php if(trim($_GET['q'])==''){ ?>
<?php echo l('my_items_empty'); ?>
<?php } else { ?>
<?php echo str_replace('[QUERY]', htmlspecialchars(trim($_GET['q'])), l('my_items_search_empty')); ?>
<?php } ?>
<?php if(trim($_GET['controller'])=='my' && trim($_GET['q'])==''){ ?>
<div class="my-empty-adder">
<a href="<?php echo $langPrefix; ?>/add/"><i class="fa fa-plus"></i><?php echo l('my_empty_add_item'); ?></a>
</div>
<?php } ?>
</div>

<?php if(mysql_num_rows($get_ads)){ ?>
<table class="my_items_list">
<tr class="my_items_list_head">
<td>
<div class="checkbox" style="margin-top:0;">
<input type="checkbox" id="select_all">
<label for="select_all"></label>
</div>
</td>
<td>
<div class="my_items_actions_container hidden">
<div class="my_items_actions">
<?php if(trim($_GET['controller'])=='my'){ ?>
<a href="javascript:void(0);" class="global-action deactivate"><?php echo l('my_items_deactivate_selected'); ?></a><span class="separator"></span><a href="javascript:void(0);" class="global-action upgrade"><?php echo l('my_items_upgrade_selected'); ?></a>
<?php } ?>
<?php if(trim($_GET['controller'])=='inactive'){ ?>
<a href="javascript:void(0);" class="global-action activate"><?php echo l('my_items_activate_selected'); ?></a><span class="separator"></span><a href="javascript:void(0);" class="global-action remove-ad"><?php echo l('my_items_remove_selected'); ?></a>
<?php } ?>
<?php if(trim($_GET['controller'])=='waiting'){ ?>
<a href="javascript:void(0);" class="global-action remove-ad"><?php echo l('my_items_cancel_selected'); ?></a>
<?php } ?>
<?php if(trim($_GET['controller'])=='moderated'){ ?>
<a href="javascript:void(0);" class="global-action remove-ad"><?php echo l('my_items_remove_selected'); ?></a>
<?php } ?>
</div>
</div>
<?php
$get_params=$_GET;
$order_link=$langPrefix.reset(explode('?', $_SERVER['REQUEST_URI']));
unset($get_params['controller']);
unset($get_params['page']);
unset($get_params['orderField']);
unset($get_params['order']);
if($order=='desc' && $orderField=='time'){
$get_params['order']='asc';
}
if(count($get_params)>0){
$order_link.='?'.http_build_query($get_params);
}
?>
<a href="<?php echo $order_link; ?>" class="order-link global-action-hide"><?php echo l('my_items_item_date'); ?><?php if($orderField=='time'){ ?><i class="fa fa-chevron-<?php if($order=='desc'){ ?>down<?php } else { ?>up<?php } ?>"></i><?php } ?></a>
</td>
<td></td>
<td>
<?php
$get_params=$_GET;
$order_link=$langPrefix.reset(explode('?', $_SERVER['REQUEST_URI']));
unset($get_params['controller']);
unset($get_params['page']);
unset($get_params['orderField']);
unset($get_params['order']);
$get_params['orderField']='title';
if($order=='desc' && $orderField=='title'){
$get_params['order']='asc';
}
if(count($get_params)>0){
$order_link.='?'.http_build_query($get_params);
}
?>
<a href="<?php echo $order_link; ?>" class="order-link"><?php echo l('my_items_item_title'); ?><?php if($orderField=='title'){ ?><i class="fa fa-chevron-<?php if($order=='desc'){ ?>down<?php } else { ?>up<?php } ?>"></i><?php } ?></a>
</td>
<td nowrap>
<?php echo l('my_items_item_price'); ?>
</td>
<td>
<?php echo l('my_items_item_messages'); ?>
</td>
<td></td>
</tr>
<?php while($ad=mysql_fetch_assoc($get_ads)){ ?>
<?php
$ad=getAd($ad['aid']);
if(trim($_GET['controller'])=='inactive' || trim($_GET['controller'])=='moderated'){
if($ad['time_to']>$time){
$ad['time_to']=$time;
}
}
$all_dialogs_count=mysql_num_rows(mysql_query("SELECT * FROM `dialogs` WHERE `aid`='".$ad['aid']."';"));
$unread_dialogs_count=mysql_num_rows(mysql_query("SELECT DISTINCT `dialogs`.* FROM `dialogs`, `dialog_unread_messages` WHERE `dialogs`.`aid`='".$ad['aid']."' AND `dialogs`.`did`=`dialog_unread_messages`.`did` AND `dialog_unread_messages`.`userid`='".$_SESSION['userid']."';"));
?>
<tr class="ads-list-item" id="ad_<?php echo $ad['aid']; ?>" style="display:table-row !important;">
<td width="20">
<div class="checkbox">
<input type="checkbox" name="selected[<?php echo $ad['aid']; ?>]" value="1" id="selected_<?php echo $ad['aid']; ?>" data-id="<?php echo $ad['aid']; ?>">
<label for="selected_<?php echo $ad['aid']; ?>"></label>
</div>
</td>
<td width="90">
<div class="my-ads-list-item-time">
<nobr><?php echo l('my_items_date_from'); ?> <?php displayDate($ad['time']); ?></nobr><br>
<nobr><?php echo l('my_items_date_to'); ?> <?php displayDate($ad['time_to']); ?></nobr>
</div>
</td>
<td width="50">
<?php if(count($ad['photos'])>0){ ?>
<?php $photo=reset($ad['photos']); ?>
<a href="<?php echo adurl($ad); ?>" target="_blank" title="<?php echo htmlspecialchars($ad['title']); ?>">
<img src="/image/94x72/<?php echo $photo['apid']; ?>/<?php echo $photo['key']; ?>.jpg?rev=<?php echo $photo['rev']; ?>" width="50" height="38">
</a>
<?php } else { ?>
<a href="<?php echo adurl($ad); ?>" target="_blank" title="<?php echo htmlspecialchars($ad['title']); ?>">
<img src="/images/no-photos.png" width="50">
</a>
<?php } ?>
</td>
<td>
<div class="my-ads-list-item-title" title="<?php echo htmlspecialchars($ad['title']); ?>">
<?php echo htmlspecialchars(cutString($ad['title'], 47, '...')); ?>
</div>
<div class="my-ads-list-item-actions">
<?php if(trim($_GET['controller'])=='moderated'){ ?><span>ID: <?php echo $ad['aid']; ?></span><?php } ?>
<a href="<?php echo adurl($ad); ?>" target="_blank"><i class="fa fa-external-link"></i><?php echo l('my_items_view'); ?></a>
<a href="<?php echo $langPrefix; ?>/edit/?id=<?php echo $ad['aid']; ?>&ref=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"><i class="fa fa-pencil"></i><?php echo l('my_items_edit'); ?></a>
<?php if(trim($_GET['controller'])=='my'){ ?>
<a href="javascript:void(0);" class="my-ads-list-item-control deactivate" data-id="<?php echo $ad['aid']; ?>"><i class="fa fa-times"></i><?php echo l('my_items_deactivate'); ?></a>
<?php } ?>
<?php if(trim($_GET['controller'])=='inactive'){ ?>
<a href="javascript:void(0);" class="my-ads-list-item-control remove2" data-id="<?php echo $ad['aid']; ?>"><i class="fa fa-times"></i><?php echo l('my_items_remove'); ?></a>
<?php } ?>
</div>
</td>
<td nowrap>
<div class="my-ads-list-item-price">
<?php echo $ad['display_price']; ?>
</div>
</td>
<td width="50">
<div class="my-ads-list-item-messages<?php if(!$all_dialogs_count){ ?> disabled<?php } ?>">
<?php if(!$all_dialogs_count){ ?>
<span><i class="fa fa-envelope"></i><?php echo $all_dialogs_count; ?></span>
<?php } else { ?>
<?php if($unread_dialogs_count){ ?>
<div class="unread"><?php echo $unread_dialogs_count; ?></div>
<?php } ?>
<a href="<?php echo $langPrefix; ?>/my/messages/<?php echo $ad['aid']; ?>"><i class="fa fa-envelope"></i><?php echo $all_dialogs_count; ?></a>
<?php } ?>
</div>
</td>
<td width="150">
<?php if(trim($_GET['controller'])=='my'){ ?>
<a href="<?php echo $langPrefix; ?>/pay/upgrade/?ids=<?php echo $ad['aid']; ?>&ref=<?php echo urlencode($langPrefix.$_SERVER['REQUEST_URI']); ?>" class="btn btn-success"><?php echo l('my_items_upgrade'); ?></a>
<?php } ?>
<?php if(trim($_GET['controller'])=='inactive'){ ?>
<a href="javascript:void(0);" class="btn btn-primary my-ads-list-item-control activate" data-id="<?php echo $ad['aid']; ?>"><i class="fa fa-refresh"></i><?php echo l('my_items_activate'); ?></a>
<?php } ?>
<?php if(trim($_GET['controller'])=='waiting'){ ?>
<div class="my-ads-list-item-unmoderated">
<?php echo l('my_items_waits_for_moderation'); ?>
</div>
<?php } ?>
<?php if(trim($_GET['controller'])=='moderated'){ ?>
<a href="javascript:void(0);" class="btn btn-danger my-ads-list-item-control remove3" data-id="<?php echo $ad['aid']; ?>"><?php echo l('my_items_remove'); ?></a>
<?php } ?>
</td>
</tr>
<tr class="my-ads-list-item-footer">
<?php if(trim($_GET['controller'])=='moderated'){ ?>
<td colspan="7" class="my-ads-list-item-moderated-reason">
<div>
<i class="fa fa-warning"></i>
<?php echo l('my_items_moderated_reason'); ?>
<a href="javascript:void(0);" class="my-ads-list-item-control reason" data-id="<?php echo $ad['aid']; ?>" data-reason="<?php echo htmlspecialchars($config['complaint_types'][$ad['reason']]); ?>"><?php echo l('my_items_get_reason'); ?></a>
</div>
</td>
<?php } else { ?>
<td colspan="3"></td>
<td colspan="2">
<?php if(trim($_GET['controller'])=='my' || trim($_GET['controller'])=='inactive'){ ?>
<table class="my-ads-list-item-statistics">
<tr>
<td>
<?php echo l('my_items_stats'); ?>
</td>
<td>
<?php echo l('my_items_views'); ?> <?php echo $ad['views']; ?>
</td>
<td title="<?php echo l('my_contact_methods_full', 'phone'); ?>">
<?php echo l('my_contact_methods', 'phone'); ?> <?php echo $ad['phone_views']; ?>
</td>
<td title="<?php echo l('my_contact_methods_full', 'gg'); ?>">
<?php echo l('my_contact_methods', 'gg'); ?> <?php echo $ad['gg_views']; ?>
</td>
<td title="<?php echo l('my_contact_methods_full', 'skype'); ?>">
<?php echo l('my_contact_methods', 'skype'); ?> <?php echo $ad['skype_views']; ?>
</td>
<td title="<?php echo l('my_favorites_full'); ?>">
<?php echo l('my_favorites'); ?> <?php echo $ad['favorites']; ?>
</td>
</tr>
</table>
<?php } ?>
</td>
<td colspan="2" align="right">
<?php if(trim($_GET['controller'])=='waiting'){ ?>
<a href="javascript:void(0);" class="my-ads-list-item-control remove" data-id="<?php echo $ad['aid']; ?>"><i class="fa fa-times"></i><?php echo l('my_items_cancel'); ?></a>
<?php } ?>
<?php if(trim($_GET['controller'])=='my'){ ?>
<a href="https://vk.com/share.php?url=<?php echo urlencode('http://'.$config['siteurl'].adurl($ad)); ?>&title=<?php echo urlencode($ad['title']); ?>&image=<?php if(count($ad['photos'])>0){ echo urlencode('http://'.$config['siteurl'].'/image/94x72/'.$photo['apid'].'/'.$photo['key'].'.jpg?rev='.$photo['rev']); } else { echo urlencode('http://'.$config['siteurl'].'/images/no-photos.png'); }?>" target="_blank"><i class="fa fa-vk"></i></a>
<a href="https://facebook.com/sharer.php?u=<?php echo urlencode('http://'.$config['siteurl'].adurl($ad)); ?>" target="_blank"><i class="fa fa-facebook"></i></a>
<a href="https://twitter.com/home?status=<?php echo urlencode($ad['title'].' http://'.$config['siteurl'].adurl($ad)); ?>" target="_blank"><i class="fa fa-twitter"></i></a>
<?php } ?>
</td>
<?php } ?>
</tr>
<tr class="my-ads-list-item-info-container">
<td colspan="7">
<div class="my-ads-list-item-info hidden"><i class="fa fa-spinner fa-spin"></i><?php echo l('waiting'); ?></div>
</td>
</tr>
<?php } ?>
</table>
<?php } ?>

<?php
if($pages['count']/$pages['per']>1){
echo $pagingHtml;
}
?>

<?php include "includes/footer.php"; ?>