<?php include_once "init.php"; ?>
<?php
if(!isset($_SESSION['userid'])){
header("Location: ".$langPrefix."/login/?ref=".urlencode($langPrefix.$_SERVER['REQUEST_URI']));
exit;
}
?>
<?php
if(intval($my['admin'])!=1){
header("HTTP/1.0 404 Not Found");
include "404.php";
exit;
}
?>
<?php
if($m){
header("Location: /");
exit;
}
?>
<?php
$selected_tab=trim($_GET['controller']);
?>
<?php
$page_tabs=array();
$page_tabs['list']=$lang['my_items_tab_my'];
$page_tabs['waiting']=$lang['my_items_tab_waiting'];
$page_tabs['complaints']=$lang['my_items_tab_complaints'];
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
$count['list']=mysql_result(mysql_query("SELECT COUNT(`aid`) FROM `ads` WHERE `active`='1';"), 0, 0);
$count['waiting']=mysql_result(mysql_query("SELECT COUNT(`aid`) FROM `ads` WHERE `active`='0';"), 0, 0);
$count['complaints']=mysql_result(mysql_query("SELECT COUNT(`aid`) FROM `ads` WHERE `active`='1' AND EXISTS(SELECT * FROM `complaints` WHERE `complaints`.`aid`=`ads`.`aid`);"), 0, 0);
$count['inactive']=mysql_result(mysql_query("SELECT COUNT(`aid`) FROM `ads` WHERE `active`='2';"), 0, 0);
$count['moderated']=mysql_result(mysql_query("SELECT COUNT(`aid`) FROM `ads` WHERE `active`='3';"), 0, 0);
if(trim($_GET['controller'])=='list'){
$get_found_ads_count=mysql_query("SELECT COUNT(*) FROM `ads` WHERE `active`='1'".$sq.";");
}
if(trim($_GET['controller'])=='waiting'){
$get_found_ads_count=mysql_query("SELECT COUNT(*) FROM `ads` WHERE `active`='0'".$sq.";");
}
if(trim($_GET['controller'])=='complaints'){
$get_found_ads_count=mysql_query("SELECT COUNT(*) FROM `ads` WHERE `active`='1' AND EXISTS(SELECT * FROM `complaints` WHERE `complaints`.`aid`=`ads`.`aid`)".$sq.";");
}
if(trim($_GET['controller'])=='inactive'){
$get_found_ads_count=mysql_query("SELECT COUNT(*) FROM `ads` WHERE `active`='2'".$sq.";");
}
if(trim($_GET['controller'])=='moderated'){
$get_found_ads_count=mysql_query("SELECT COUNT(*) FROM `ads` WHERE `active`='3'".$sq.";");
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
if(trim($_GET['controller'])=='list'){
$get_ads=mysql_query("SELECT * FROM `ads` WHERE `active`='1'".$sq." ORDER BY `".$orderField."` ".$order." LIMIT ".($pages['per']*($pages['current']-1)).", ".$pages['per'].";");
}
if(trim($_GET['controller'])=='waiting'){
$get_ads=mysql_query("SELECT * FROM `ads` WHERE `active`='0'".$sq." ORDER BY `".$orderField."` ".$order." LIMIT ".($pages['per']*($pages['current']-1)).", ".$pages['per'].";");
}
if(trim($_GET['controller'])=='complaints'){
$get_ads=mysql_query("SELECT * FROM `ads` WHERE `active`='1' AND EXISTS(SELECT * FROM `complaints` WHERE `complaints`.`aid`=`ads`.`aid`)".$sq." ORDER BY `".$orderField."` ".$order." LIMIT ".($pages['per']*($pages['current']-1)).", ".$pages['per'].";");
}
if(trim($_GET['controller'])=='inactive'){
$get_ads=mysql_query("SELECT * FROM `ads` WHERE `active`='2'".$sq." ORDER BY `".$orderField."` ".$order." LIMIT ".($pages['per']*($pages['current']-1)).", ".$pages['per'].";");
}
if(trim($_GET['controller'])=='moderated'){
$get_ads=mysql_query("SELECT * FROM `ads` WHERE `active`='3'".$sq." ORDER BY `".$orderField."` ".$order." LIMIT ".($pages['per']*($pages['current']-1)).", ".$pages['per'].";");
}
?>
<?php
$show_top_tabs=true;
$is_moderation=true;
$current_tab='list';
$top_tabs_title='Расширенное управление объявлениями';
$top_tabs_description='Здесь можно осуществлять управление размещёнными на сайте объявлениями.';
$pagetitle='Расширенное управление объявлениями';
$pagedesc=$config['description'];
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
<?php if(trim($_GET['controller'])=='list'){ ?>
<a href="javascript:void(0);" class="global-action moder_deactivate"><?php echo l('my_items_deactivate_selected'); ?></a><span class="separator"></span><a href="javascript:void(0);" class="global-action remove-ad"><?php echo l('my_items_remove_selected'); ?></a>
<?php } ?>
<?php if(trim($_GET['controller'])=='inactive'){ ?>
<a href="javascript:void(0);" class="global-action moder_activate"><?php echo l('my_items_activate_selected'); ?></a><span class="separator"></span><a href="javascript:void(0);" class="global-action remove-ad"><?php echo l('my_items_remove_selected'); ?></a>
<?php } ?>
<?php if(trim($_GET['controller'])=='waiting'){ ?>
<a href="javascript:void(0);" class="global-action moder_publish"><?php echo l('moder_publish_selected'); ?></a><span class="separator"></span><a href="javascript:void(0);" class="global-action moder_reject"><?php echo l('moder_reject_selected'); ?></a>
<?php } ?>
<?php if(trim($_GET['controller'])=='moderated'){ ?>
<a href="javascript:void(0);" class="global-action moder_publish"><?php echo l('moder_publish_selected'); ?></a><span class="separator"></span><a href="javascript:void(0);" class="global-action remove-ad"><?php echo l('my_items_remove_selected'); ?></a>
<?php } ?>
<?php if(trim($_GET['controller'])=='complaints'){ ?>
<a href="javascript:void(0);" class="global-action moder_complaint_accept"><?php echo l('moder_complaint_accept_selected'); ?></a><span class="separator"></span><a href="javascript:void(0);" class="global-action moder_complaint_decline"><?php echo l('moder_complaint_decline_selected'); ?></a>
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
<span>ID: <?php echo $ad['aid']; ?></span>
<a href="<?php echo adurl($ad); ?>" target="_blank"><i class="fa fa-external-link"></i><?php echo l('my_items_view'); ?></a>
<a href="<?php echo $langPrefix; ?>/edit/?id=<?php echo $ad['aid']; ?>&ref=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"><i class="fa fa-pencil"></i><?php echo l('my_items_edit'); ?></a>
<?php if(trim($_GET['controller'])=='waiting'){ ?>
<a href="javascript:void(0);" class="my-ads-list-item-control moder_reject" data-id="<?php echo $ad['aid']; ?>"><i class="fa fa-times"></i><?php echo l('moder_reject'); ?></a>
<?php } ?>
<?php if(trim($_GET['controller'])=='list' || trim($_GET['controller'])=='inactive'){ ?>
<a href="javascript:void(0);" class="my-ads-list-item-control moder_remove" data-id="<?php echo $ad['aid']; ?>"><i class="fa fa-times"></i><?php echo l('my_items_remove'); ?></a>
<?php } ?>
<?php if(trim($_GET['controller'])=='moderated'){ ?>
<a href="javascript:void(0);" class="my-ads-list-item-control moder_publish" data-id="<?php echo $ad['aid']; ?>"><i class="fa fa-check"></i><?php echo l('moder_publish'); ?></a>
<?php } ?>
<?php if(trim($_GET['controller'])=='complaints'){ ?>
<a href="javascript:void(0);" class="my-ads-list-item-control moder_complaint_decline" data-id="<?php echo $ad['aid']; ?>"><i class="fa fa-times"></i><?php echo l('moder_complaint_decline'); ?></a>
<?php } ?>
</div>
</td>
<td nowrap>
<div class="my-ads-list-item-price">
<?php echo $ad['display_price']; ?>
</div>
</td>
<td width="<?php if(trim($_GET['controller'])=='waiting'){ ?>150<?php } else { ?>150<?php } ?>">
<?php if(trim($_GET['controller'])=='list'){ ?>
<a href="javascript:void(0);" class="btn btn-danger my-ads-list-item-control moder_deactivate" data-id="<?php echo $ad['aid']; ?>"><?php echo l('my_items_deactivate'); ?></a>
<?php } ?>
<?php if(trim($_GET['controller'])=='inactive'){ ?>
<a href="javascript:void(0);" class="btn btn-success my-ads-list-item-control moder_activate" data-id="<?php echo $ad['aid']; ?>"><?php echo l('my_items_activate'); ?></a>
<?php } ?>
<?php if(trim($_GET['controller'])=='waiting'){ ?>
<a href="javascript:void(0);" class="btn btn-success my-ads-list-item-control moder_publish" data-id="<?php echo $ad['aid']; ?>"><?php echo l('moder_publish'); ?></a>
<?php } ?>
<?php if(trim($_GET['controller'])=='moderated'){ ?>
<a href="javascript:void(0);" class="btn btn-danger my-ads-list-item-control moder_remove" data-id="<?php echo $ad['aid']; ?>"><?php echo l('my_items_remove'); ?></a>
<?php } ?>
<?php if(trim($_GET['controller'])=='complaints'){ ?>
<a href="javascript:void(0);" class="btn btn-success my-ads-list-item-control moder_complaint_accept" data-id="<?php echo $ad['aid']; ?>"><?php echo l('moder_complaint_accept'); ?></a>
<?php } ?>
</td>
</tr>
<tr class="my-ads-list-item-footer">
<?php if(trim($_GET['controller'])=='complaints'){ ?>
<td colspan="7" class="my-ads-list-item-moderated-reason">
<div>
<i class="fa fa-warning"></i>
<?php echo l('moder_complaint_reason'); ?> <b><?php echo mysql_result(mysql_query("SELECT LOWER(GROUP_CONCAT(`title_".$config['lang']."` SEPARATOR ', ')) FROM `complaint_types` WHERE `ctid` IN(SELECT `ctid` FROM `complaints` WHERE `aid`='".$ad['aid']."');"), 0, 0); ?></b>
</div>
</td>
<?php } elseif(trim($_GET['controller'])=='moderated'){ ?>
<td colspan="7" class="my-ads-list-item-moderated-reason">
<div>
<i class="fa fa-warning"></i>
<?php echo l('my_items_moderated_reason'); ?>
<?php if($ad['reason']>0){ ?>
<a href="javascript:void(0);" class="my-ads-list-item-control reason" data-id="<?php echo $ad['aid']; ?>" data-reason="<?php echo htmlspecialchars($config['complaint_types'][$ad['reason']]); ?>"><?php echo l('my_items_get_reason'); ?></a>
<?php } ?>
</div>
</td>
<?php } else { ?>
<td colspan="3"></td>
<td colspan="2">
<?php if(trim($_GET['controller'])=='list' || trim($_GET['controller'])=='inactive' || trim($_GET['controller'])=='waiting'){ ?>
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
<td colspan="1" align="right">

</td>
<?php } ?>
</tr>
<tr class="my-ads-list-item-info-container">
<td colspan="6">
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