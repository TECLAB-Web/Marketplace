<?php include_once "init.php"; ?>
<?php
if(trim($_GET['mode'])=='ajax'){
header('Content-Type: application/json; charset=utf-8');
if(trim($_POST['action'])==htmlspecialchars(trim($_GET['controller']))){
$result=array();
if(mysql_num_rows(mysql_query("SELECT * FROM `ads` WHERE `aid`='".intval($_POST['id'])."';"))){
if(isset($_SESSION['userid'])){
$check=mysql_query("SELECT * FROM `favorites` WHERE (`userid`='".$_SESSION['userid']."' OR `sid`='"._F($_COOKIE['PHPSESSID'])."') AND `otype`='ad' AND `oid`='".intval($_POST['id'])."';");
} else {
$check=mysql_query("SELECT * FROM `favorites` WHERE `sid`='"._F($_COOKIE['PHPSESSID'])."' AND `otype`='ad' AND `oid`='".intval($_POST['id'])."';");
}
if(mysql_num_rows($check)){
if(isset($_SESSION['userid'])){
mysql_query("DELETE FROM `favorites` WHERE (`userid`='".$_SESSION['userid']."' OR `sid`='"._F($_COOKIE['PHPSESSID'])."') AND `otype`='ad' AND `oid`='".intval($_POST['id'])."';");
} else {
mysql_query("DELETE FROM `favorites` WHERE `sid`='"._F($_COOKIE['PHPSESSID'])."' AND `otype`='ad' AND `oid`='".intval($_POST['id'])."';");
}
$result['status']='deleted';
} else {
mysql_query("INSERT INTO `favorites` SET `userid`='".intval($_SESSION['userid'])."', `sid`='"._F($_COOKIE['PHPSESSID'])."', `otype`='ad', `oid`='".intval($_POST['id'])."', `time`='".$time."';");
$result['status']='added';
}
echo json_encode($result);
}
exit;
}
if(trim($_POST['action'])==htmlspecialchars(trim($_GET['controller'])).'_search'){
$result=array();
if(mysql_num_rows(mysql_query("SELECT * FROM `searches` WHERE `search_id`='"._F($_POST['id'])."';"))){
if(isset($_SESSION['userid'])){
$check=mysql_query("SELECT * FROM `favorites` WHERE (`userid`='".$_SESSION['userid']."' OR `sid`='"._F($_COOKIE['PHPSESSID'])."') AND `otype`='search' AND `oid`='"._F($_POST['id'])."';");
} else {
$check=mysql_query("SELECT * FROM `favorites` WHERE `sid`='"._F($_COOKIE['PHPSESSID'])."' AND `otype`='search' AND `oid`='"._F($_POST['id'])."';");
}
if(mysql_num_rows($check)){
if(isset($_SESSION['userid'])){
mysql_query("DELETE FROM `favorites` WHERE (`userid`='".$_SESSION['userid']."' OR `sid`='"._F($_COOKIE['PHPSESSID'])."') AND `otype`='search' AND `oid`='"._F($_POST['id'])."';");
} else {
mysql_query("DELETE FROM `favorites` WHERE `sid`='"._F($_COOKIE['PHPSESSID'])."' AND `otype`='search' AND `oid`='"._F($_POST['id'])."';");
}
$result['status']='deleted';
} else {
mysql_query("INSERT INTO `favorites` SET `userid`='".intval($_SESSION['userid'])."', `sid`='"._F($_COOKIE['PHPSESSID'])."', `otype`='search', `oid`='"._F($_POST['id'])."', `time`='".$time."';");
$result['status']='added';
}
echo json_encode($result);
}
exit;
}
if(trim($_POST['action'])=='clearallads'){
$result=array();
if(isset($_SESSION['userid'])){
mysql_query("DELETE FROM `favorites` WHERE (`userid`='".$_SESSION['userid']."' OR `sid`='"._F($_COOKIE['PHPSESSID'])."') AND `otype`='ad';");
} else {
mysql_query("DELETE FROM `favorites` WHERE `sid`='"._F($_COOKIE['PHPSESSID'])."' AND `otype`='ad';");
}
$result['status']='deleted';
echo json_encode($result);
exit;
}
if(trim($_POST['action'])=='clearallsearches'){
$result=array();
if(isset($_SESSION['userid'])){
mysql_query("DELETE FROM `favorites` WHERE (`userid`='".$_SESSION['userid']."' OR `sid`='"._F($_COOKIE['PHPSESSID'])."') AND `otype`='search';");
} else {
mysql_query("DELETE FROM `favorites` WHERE `sid`='"._F($_COOKIE['PHPSESSID'])."' AND `otype`='search';");
}
$result['status']='deleted';
echo json_encode($result);
exit;
}
}
?>
<?php
if(trim($_GET['from'])=='header'){
if($favorite_searches_count>0 && $favorite_ads_count==0){
header("Location: ".$langPrefix."/favorites/searches/");
exit;
}
}
?>
<?php
$pages=array();
if(isset($_SESSION['userid'])){
$pages['count']=mysql_result(mysql_query("SELECT COUNT(DISTINCT `ads`.`aid`) FROM `ads` INNER JOIN `favorites` ON `favorites`.`otype`='ad' AND `favorites`.`oid`=`ads`.`aid` AND (`favorites`.`userid`='".$_SESSION['userid']."' OR `favorites`.`sid`='"._F($_COOKIE['PHPSESSID'])."') WHERE `active`='1';"), 0, 0);
} else {
$pages['count']=mysql_result(mysql_query("SELECT COUNT(DISTINCT `ads`.`aid`) FROM `ads` INNER JOIN `favorites` ON `favorites`.`otype`='ad' AND `favorites`.`oid`=`ads`.`aid` AND `favorites`.`sid`='"._F($_COOKIE['PHPSESSID'])."' WHERE `active`='1';"), 0, 0);
}
$pages['per']=(($m)?20:30);
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
unset($get_params['mode']);
$get_params['page']='';
if(count($get_params)>0){
$pages['url'].='?'.str_replace(array('%5B', '%5D'), array('[', ']'), http_build_query($get_params)).'(:page)';
}
$pagingHtml=paginator::getHtml($pages['count'], $pages['current'], $pages['per'], $pages['show'], $pages['url']);
if(isset($_SESSION['userid'])){
$get_ads=mysql_query("SELECT DISTINCT `ads`.* FROM `ads` INNER JOIN `favorites` ON `favorites`.`otype`='ad' AND `favorites`.`oid`=`ads`.`aid` AND (`favorites`.`userid`='".$_SESSION['userid']."' OR `favorites`.`sid`='"._F($_COOKIE['PHPSESSID'])."') WHERE `active`='1' ORDER BY `ads`.`time` DESC LIMIT ".($pages['per']*($pages['current']-1)).", ".$pages['per'].";");
} else {
$get_ads=mysql_query("SELECT DISTINCT `ads`.* FROM `ads` INNER JOIN `favorites` ON `favorites`.`otype`='ad' AND `favorites`.`oid`=`ads`.`aid` AND `favorites`.`sid`='"._F($_COOKIE['PHPSESSID'])."' WHERE `active`='1' ORDER BY `ads`.`time` DESC LIMIT ".($pages['per']*($pages['current']-1)).", ".$pages['per'].";");
}
?>
<?php
$show_top_tabs=true;
$is_favorites=true;
$current_tab='ads';
$top_tabs_title=l('favorites_title');
$top_tabs_description=l('favorites_description');
$pagetitle=l('favorites_title')." &bull; ".$config['sitename'];
$pagedesc=$config['description'];
?>
<?php
if($m){ include "m-favorites.php"; exit; }
?>
<?php include "includes/header.php"; ?>

<?php if(mysql_num_rows($get_ads)){ ?>
<div class="favorites-control">
<span class="favorites-view">
<?php echo l('view_title'); ?>
<a href="javascript:void(0);" onclick="Cookies.set('favorites_view', 'list', {expires:365}); location.reload(); return false;" class="<?php if(trim($_COOKIE['favorites_view'])!='gallery'){ ?>active<?php } ?>"><?php echo l('view_list'); ?></a>
<a href="javascript:void(0);" onclick="Cookies.set('favorites_view', 'gallery', {expires:365}); location.reload(); return false;" class="<?php if(trim($_COOKIE['favorites_view'])=='gallery'){ ?>active<?php } ?>"><?php echo l('view_gallery'); ?></a>
</span>
<span class="favorites-clearall">
<a href="javascript:void(0);" onclick="clearFavorites('ads'); return false;"><?php echo l('favorites_clear_all'); ?></a>
</span>
<div class="clear"></div>
</div>
<div class="ads-list ads-list-standard">
<?php while($ad=mysql_fetch_assoc($get_ads)){ ?>
<?php
// IMPORTANT!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// IMPORTANT!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// IMPORTANT!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// IMPORTANT!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// IMPORTANT!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// IMPORTANT!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// IMPORTANT!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// DO NOT FORGET ABOUT "list" AND "favorites"!
// IMPORTANT!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// IMPORTANT!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// IMPORTANT!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// IMPORTANT!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// IMPORTANT!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// IMPORTANT!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// IMPORTANT!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
?>
<?php
$ad=getAd($ad['aid']);
$selected_cats=array();
$get_cat=mysql_query("SELECT * FROM `categories` WHERE `id`='".$ad['category_id']."';");
if(mysql_num_rows($get_cat)){
$cat=mysql_fetch_assoc($get_cat);
$current_cat=$cat;
$selected_cats[]=$cat['name_'.$config['lang']];
$get_cat=mysql_query("SELECT * FROM `categories` WHERE `id`='".$cat['parent_id']."';");
if(mysql_num_rows($get_cat)){
$cat=mysql_fetch_assoc($get_cat);
$selected_cats[]=$cat['name_'.$config['lang']];
$get_cat=mysql_query("SELECT * FROM `categories` WHERE `id`='".$cat['parent_id']."';");
if(mysql_num_rows($get_cat)){
$cat=mysql_fetch_assoc($get_cat);
$selected_cats[]=$cat['name_'.$config['lang']];
}
}
}
?>
<?php if(trim($_COOKIE['favorites_view'])=='gallery'){ ?>
<div class="ads-gallery-item">
<div class="ads-gallery-item-photo-container" title="<?php echo htmlspecialchars($ad['title']); ?>">
<?php if(count($ad['photos'])>0){ ?>
<?php $photo=reset($ad['photos']); ?>
<a href="<?php echo adurl($ad); ?>">
<img src="/image/261x203/<?php echo $photo['apid']; ?>/<?php echo $photo['key']; ?>.jpg?rev=<?php echo $photo['rev']; ?>&contain=1" class="ads-gallery-item-photo">
</a>
<?php } else { ?>
<a href="<?php echo adurl($ad); ?>">
<img src="/images/no-photos.png" width="100%">
</a>
<?php } ?>
</div>
<div class="ads-gallery-item-info">
<a class="ads-gallery-item-link" href="<?php echo adurl($ad); ?>" title="<?php echo htmlspecialchars($ad['title']); ?>"><?php echo htmlspecialchars($ad['title']); ?></a>
<table>
<tr>
<td>
<div class="ads-gallery-item-price<?php if($ad['salary_from']>0 || $ad['salary_to']>0){ ?> salary<?php } ?>">
<?php echo $ad['display_price']; ?>
</div>
</td>
<td width="20" align="right">
<a href="javascript:void(0);" class="favorite-link" data-id="<?php echo $ad['aid']; ?>">
<span class="add-favorite add-favorite-<?php echo $ad['aid']; ?><?php if($ad['favorite']){ ?> hidden<?php } ?>" title="<?php echo l('return_to_favorites'); ?>"><i class="fa fa-star-o"></i><i class="fa fa-star"></i></span>
<span class="delete-favorite delete-favorite-<?php echo $ad['aid']; ?><?php if(!$ad['favorite']){ ?> hidden<?php } ?>" title="<?php echo l('remove_from_favorites'); ?>"><i class="fa fa-star"></i></span>
</a>
</td>
</tr>
</table>
</div>
</div>
<?php } else { ?>
<div class="ads-list-item">
<table>
<tr>
<td rowspan="2" class="ads-list-item-photo-container">
<?php if(count($ad['photos'])>0){ ?>
<?php $photo=reset($ad['photos']); ?>
<a href="<?php echo adurl($ad); ?>">
<img src="/image/261x203/<?php echo $photo['apid']; ?>/<?php echo $photo['key']; ?>.jpg?rev=<?php echo $photo['rev']; ?>&contain=1" class="ads-list-item-photo">
</a>
<?php } else { ?>
<a href="<?php echo adurl($ad); ?>">
<img src="/images/no-photos.png" width="100%">
</a>
<?php } ?>
</td>
<td rowspan="2" width="10"></td>
<td class="ads-list-item-top">
<a class="ads-list-item-title" href="<?php echo adurl($ad); ?>">
<?php echo htmlspecialchars($ad['title']); ?>
</a>
<div class="ads-list-item-category"><?php echo implode(' &raquo; ', array_reverse($selected_cats)); ?></div>
</td>
<td rowspan="2" width="10"></td>
<td class="ads-list-item-top" align="right" nowrap>
<div class="ads-list-item-price"><?php echo $ad['display_price']; ?></div>
<?php if(isset($ad['arranged_price'])){ ?>
<div class="ads-list-item-arranged"><?php echo l('price_arranged'); ?></div>
<?php } ?>
</td>
</tr>
<tr>
<td class="ads-list-item-bottom">
<div class="ads-list-item-geo"><?php echo htmlspecialchars($ad['geo_short']); ?></div>
<div class="ads-list-item-time"><?php displayTime($ad['time']); ?></div>
</td>
<td class="ads-list-item-bottom" align="right" nowrap>
<a href="javascript:void(0);" class="favorite-link" data-id="<?php echo $ad['aid']; ?>">
<span class="add-favorite add-favorite-<?php echo $ad['aid']; ?><?php if($ad['favorite']){ ?> hidden<?php } ?>"><table><tr><td><?php echo l('return_to_favorites'); ?></td><td><i class="fa fa-star-o"></i><i class="fa fa-star"></i></td></tr></table></span>
<span class="delete-favorite delete-favorite-<?php echo $ad['aid']; ?><?php if(!$ad['favorite']){ ?> hidden<?php } ?>"><table><tr><td><?php echo l('remove_from_favorites'); ?></td><td><i class="fa fa-star"></i></td></tr></table></span>
</a>
</td>
</tr>
</table>
</div>
<?php } ?>
<?php } ?>
<div class="clear"></div>
</div>
<?php } else { ?>
<div class="empty-list">
<div>
<i class="fa fa-star-o"></i>
</div>
<?php echo l('favorites_empty'); ?>
</div>
<?php } ?>

<?php
if($pages['count']/$pages['per']>1){
echo $pagingHtml;
}
?>

<?php include "includes/footer.php"; ?>