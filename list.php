<?php include_once "init.php"; ?>
<?php
$query_parts=explode('/', trim($_GET['query']));
unset($_GET['query']);
$_SERVER['REQUEST_URI']=str_replace('/ajax', '', $_SERVER['REQUEST_URI']);
$_SERVER['REQUEST_URI']=str_replace('/'.$config['lang'], '', $_SERVER['REQUEST_URI']);
if($query_parts[0]!='list'){
$php_file=preg_replace("/[^A-Za-z0-9_-]/", '', implode('_', $query_parts)).'.php';
if(file_exists($php_file)){
$_GET['controller']=end($query_parts);
include $php_file;
exit;
}
}
$check_static_page=mysql_query("SELECT * FROM `static_pages` WHERE `url`='"._F($query_parts[0])."';");
if(mysql_num_rows($check_static_page)){
$page=mysql_fetch_assoc($check_static_page);
include 'static_page.php';
exit;
}
if($query_parts[0]!='list'){
$check_city=mysql_query("SELECT * FROM `cities` WHERE `url`='"._F($query_parts[0])."';");
if(!mysql_num_rows($check_city)){
header("HTTP/1.0 404 Not Found");
include "404.php";
exit;
}
$city=mysql_fetch_assoc($check_city);
$region=mysql_fetch_assoc(mysql_query("SELECT * FROM `regions` WHERE `region_id`='".intval($city['region_id'])."';"));
$city['geo']=$city['title_'.$config['lang']].', '.$region['title_'.$config['lang']];
}
$selected_cats=array();
$current_category=0;
$search_category=0;
if(trim($query_parts[1])!=''){
$check_cat=mysql_query("SELECT * FROM `categories` WHERE `url`='"._F($query_parts[1])."' AND `active`='1';");
if(!mysql_num_rows($check_cat)){
header("HTTP/1.0 404 Not Found");
include "404.php";
exit;
}
$selected_cats[0]=mysql_fetch_assoc($check_cat);
$current_category=$selected_cats[0]['id'];
$current_category_search_label=$selected_cats[0]['search_label_'.$config['lang']];
$search_category=$selected_cats[0]['id'];
$search_category_parent=$selected_cats[0]['parent_id'];
}
if(trim($query_parts[2])!=''){
$check_cat=mysql_query("SELECT * FROM `categories` WHERE `parent_id`='".$selected_cats[0]['id']."' AND `url`='"._F($query_parts[2])."' AND `active`='1';");
if(!mysql_num_rows($check_cat)){
header("HTTP/1.0 404 Not Found");
include "404.php";
exit;
}
$selected_cats[1]=mysql_fetch_assoc($check_cat);
$current_category=$selected_cats[1]['id'];
$current_category_search_label=$selected_cats[1]['search_label_'.$config['lang']];
$search_category=$selected_cats[1]['id'];
$search_category_parent=$selected_cats[1]['parent_id'];
}
if(trim($query_parts[3])!=''){
$check_cat=mysql_query("SELECT * FROM `categories` WHERE `parent_id`='".$selected_cats[1]['id']."' AND `url`='"._F($query_parts[3])."' AND `active`='1';");
if(!mysql_num_rows($check_cat)){
header("HTTP/1.0 404 Not Found");
include "404.php";
exit;
}
$selected_cats[2]=mysql_fetch_assoc($check_cat);
$current_category=$selected_cats[2]['id'];
$search_category=$selected_cats[2]['id'];
$search_category_parent=$selected_cats[2]['parent_id'];
}
if(count($selected_cats)>0){
$category=end($selected_cats);
}
?>
<?php
$search=$_GET['search'];
ksort($search);
?>
<?php
if(trim($search['private_business'])=='business'){
$current_tab='business';
} elseif(trim($search['private_business'])=='private'){
$current_tab='private';
} else {
$current_tab='all';
}
if(in_array(trim($search['currency']), array_keys($currencies))){
$selected_currency=$search['currency'];
} else {
$selected_currency=reset(array_keys($currencies));
}
?>
<?php
$page_tabs=array();
$page_tabs['all']=array('tab'=>l('list_all'));
$page_tabs['private']=array('tab'=>l('list_private'));
$page_tabs['business']=array('tab'=>l('list_business'));
$page_orders=array();
$page_orders['time:desc']=array('tab'=>l('list_time_desc'));
$page_orders['price:asc']=array('tab'=>l('list_price_asc'));
$page_orders['price:desc']=array('tab'=>l('list_price_desc'));
?>
<?php
if(isset($page_orders[trim($search['order'])])){
$pre_order=trim($search['order']);
} else {
$pre_order='time:desc';
}
$post_order=explode(':', $pre_order);
$orderField=$post_order[0];
$order=$post_order[1];
$sql_filters=array();
$joins=array();
if(trim($search['query'])!=''){
$joins[]="INNER JOIN `ads` AS `ad_index` ON `ads`.`aid`=`ad_index`.`aid` AND MATCH (`ad_index`.`index`, `ad_index`.`title`, `ad_index`.`description`) AGAINST ('"._F(Words2BaseForm(trim($search['query'])))."' IN NATURAL LANGUAGE MODE)";
}
if(intval($search['only_photos'])==1){
$joins[]="INNER JOIN `ad_photos` AS `ad_photos_index` ON `ads`.`apid`=`ad_photos_index`.`apid`";
}
if(intval($search_category)>0){
$sql_filters['category']="AND (`ads`.`category_id`='".intval($search_category)."' OR `ads`.`parent_category_id`='".intval($search_category)."' OR `ads`.`parent_parent_category_id`='".intval($search_category)."')";	
}
if(intval($city['city_id'])>0){
$sql_filters['city']="AND `ads`.`city_id`='".intval($city['city_id'])."'";
}
if(intval($search['user_id'])>0){
$sql_filters['city']="AND `ads`.`userid`='".intval($search['user_id'])."'";
}
if(trim($search['private_business'])=='business' || trim($search['private_business'])=='private'){
$sql_filters['private_business']="AND `ads`.`private_business`='"._F($search['private_business'])."'";
}
//if(in_array(trim($search['currency']), array_keys($currencies))){
//$sql_filters['currency']="AND `ads`.`currency`='"._F($selected_currency)."'";
//}
//if($orderField=='price'){
//$sql_filters['exclude']="AND `ads`.`salary_from`='0' AND `ads`.`salary_to`='0'";
//}
$get_parameters=mysql_query("SELECT `category_parameter_sort`.`sort`, `category_parameters`.* FROM `category_parameters`, `category_parameter_sort` WHERE `category_parameter_sort`.`key`=`category_parameters`.`key` AND `category_parameter_sort`.`cid`=".intval($search_category)." AND FIND_IN_SET(".intval($search_category).", `category_parameters`.`cids`) AND `category_parameters`.`type`!='hidden' AND `category_parameters`.`type`!='date' AND (`category_parameters`.`type`='price' OR `category_parameters`.`type`='salary' OR `category_parameters`.`type`='input' OR `category_parameters`.`type`='select' OR `category_parameters`.`type`='checkboxes') AND `category_parameters`.`has_searching_form`='1' AND `category_parameters`.`active`='1' ORDER BY FIND_IN_SET(`category_parameters`.`type`, 'price,salary,input,select,checkboxes') ASC, `category_parameter_sort`.`sort` ASC;");
while($param=mysql_fetch_assoc($get_parameters)){
if(isset($search[$param['url_key']])){
$criteria=$search[$param['url_key']];
if(is_array($criteria) && (isset($criteria['from']) || isset($criteria['to']))){
if(trim($criteria['from'])!=''){
if($param['type']=='price'){
$sql_filters['price_from']="AND `ads`.`price`>='".intval($criteria['from'])."'";	
} elseif($param['type']=='salary'){
$sql_filters['salary_from']="AND (`ads`.`salary_from`>='".intval($criteria['from'])."' OR `ads`.`salary_to`>='".intval($criteria['from'])."')";	
} else {
$joins[]="INNER JOIN `ad_parameters` AS `ap_".md5($param['name'].'_from')."` ON `ads`.`aid`=`ap_".md5($param['name'].'_from')."`.`aid` AND `ap_".md5($param['name'].'_from')."`.`key`='".$param['name']."' AND `ap_".md5($param['name'].'_from')."`.`values`+0>='"._F($criteria['from'])."'";
}
}
if(trim($criteria['to'])!=''){
if($param['type']=='price'){
$sql_filters['price_to']="AND `ads`.`price`<='".intval($criteria['to'])."'";	
} elseif($param['type']=='salary'){
$sql_filters['salary_to']="AND (`ads`.`salary_from`<='".intval($criteria['to'])."' OR `ads`.`salary_to`<='".intval($criteria['to'])."')";	
} else {
$joins[]="INNER JOIN `ad_parameters` AS `ap_".md5($param['name'].'_to')."` ON `ads`.`aid`=`ap_".md5($param['name'].'_to')."`.`aid` AND `ap_".md5($param['name'].'_to')."`.`key`='".$param['name']."' AND `ap_".md5($param['name'].'_to')."`.`values`+0<='"._F($criteria['to'])."'";
}
}
} else {
$joins[]="INNER JOIN `ad_parameters` AS `ap_".md5($param['name'])."` ON `ads`.`aid`=`ap_".md5($param['name'])."`.`aid` AND `ap_".md5($param['name'])."`.`key`='".$param['name']."' AND FIND_IN_SET(`ap_".md5($param['name'])."`.`values`, '"._F($criteria)."')";
}
}
}
if(intval($search['user_id'])==0){
$get_top_ads=mysql_query("SELECT SQL_CACHE DISTINCT `ads`.* FROM `ads` ".implode(' ', array_unique($joins))." WHERE `ads`.`active`='1' AND EXISTS(SELECT * FROM `payments` WHERE `payments`.`service`='top' AND `payments`.`oid`=`ads`.`aid` AND `till`>='".$time."')".implode(' ', $sql_filters)." ORDER BY RAND() LIMIT 3;");
}
$pages=array();
$pages['count']=mysql_result(mysql_query("SELECT SQL_CACHE COUNT(DISTINCT `ads`.`aid`) FROM `ads` ".implode(' ', array_unique($joins))." WHERE `ads`.`active`='1' ".implode(' ', $sql_filters).";"), 0, 0);
$pages['per']=(($m)?20:40);
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
$pagingHtml=paginator::getHtml($pages['count'], $pages['current'], $pages['per'], $pages['show'], $pages['url'], true, (($m)?false:true));
$get_ads=mysql_query("SELECT SQL_CACHE DISTINCT `ads`.* FROM `ads` ".implode(' ', array_unique($joins))." WHERE `ads`.`active`='1'".implode(' ', $sql_filters)." ORDER BY `ads`.`".str_replace('price', 'base_price', $orderField)."` ".$order." LIMIT ".($pages['per']*($pages['current']-1)).", ".$pages['per'].";");
$cat_has_subcats=mysql_num_rows(mysql_query("SELECT * FROM `categories` WHERE `parent_id`='".intval($search_category)."' AND `active`='1';"));
$categories_to_show=array();
$get_categories_to_show=mysql_query("SELECT * FROM `categories` WHERE `parent_id`='".intval($search_category)."' AND `active`='1' ORDER BY `sort` ASC;");
$ctsi=1;
$sctsi=1;
while($cat=mysql_fetch_assoc($get_categories_to_show)){
$categories_to_show[$ctsi][$sctsi]=$cat;
if($sctsi==4){
$sctsi=0;
$ctsi++;
}
$sctsi++;
}
if(intval($search['user_id'])>0){
$user=mysql_fetch_assoc(mysql_query("SELECT * FROM `users` WHERE `userid`='".intval($search['user_id'])."';"));
}
?>
<?php
if(trim($_GET['mode'])=='' || (trim($_GET['mode'])=='ajax' && trim($_POST['action'])=='search') || (trim($_GET['mode'])=='ajax' && trim($_POST['action'])=='form')){
$check_search=mysql_query("SELECT `search_id` FROM `searches` WHERE (`userid`='".intval($_SESSION['userid'])."' OR `sid`='"._F($_COOKIE['PHPSESSID'])."') AND `request_hash`='".md5(serialize($search))."' AND `query`='"._F($search['query'])."' AND `city_id`='".intval($city['city_id'])."' AND `category_id`='".intval($search_category)."';");
if(!mysql_num_rows($check_search)){
$search_id=uniqid('');
mysql_query("INSERT INTO `searches` SET `search_id`='".$search_id."', `userid`='".intval($_SESSION['userid'])."', `sid`='"._F($_COOKIE['PHPSESSID'])."', `request`='"._F(serialize($search))."', `request_hash`='".md5(serialize($search))."', `query`='"._F($search['query'])."', `city_id`='".intval($city['city_id'])."', `category_id`='".intval($search_category)."', `time`='".$time."';");
} else {
$search_id=mysql_result($check_search, 0, 0);
}
if(isset($_SESSION['userid'])){
$search_favorite=mysql_num_rows(mysql_query("SELECT * FROM `favorites` WHERE (`userid`='".$_SESSION['userid']."' OR `sid`='"._F($_COOKIE['PHPSESSID'])."') AND `otype`='search' AND `oid`='"._F($search_id)."';"));
} else {
$search_favorite=mysql_num_rows(mysql_query("SELECT * FROM `favorites` WHERE `sid`='"._F($_COOKIE['PHPSESSID'])."' AND `otype`='search' AND `oid`='"._F($search_id)."';"));
}
}
?>
<?php
if(trim($_GET['mode'])=='ajax'){
if(trim($_POST['action'])=='categories'){
include "includes/category_selecter.php";
exit;
}
if(trim($_POST['action'])=='form'){
include "includes/search_form.php";
exit;
}
}
?>
<?php
$show_top_tabs=true;
$is_search=true;
$pagetitle='';
if(trim($search['query'])!=''){
$pagetitle.=htmlspecialchars(trim($search['query'])).' &bull; ';
}
if(intval($current_category)!=0){
$pagetitle.=htmlspecialchars(end($selected_cats)['name_'.$config['lang']]).' &bull; ';
}
if($pagetitle!=''){
$pagetitle.=$config['sitename'];
} else {
$pagetitle.=l('list_title')." &bull; ".$config['sitename'];
}
if(trim(end($selected_cats)['metak_'.$config['lang']])!=''){
$pagekeywords=trim(htmlspecialchars(end($selected_cats)['metak_'.$config['lang']]));
}
if(trim(end($selected_cats)['metad_'.$config['lang']])!=''){
$pagedesc=trim(htmlspecialchars(end($selected_cats)['metad_'.$config['lang']]));
} else {
$pagedesc=$config['description'];
}
?>
<?php
if($m){ include "m-list.php"; exit; }
?>
<?php renderBanner('list_page_top'); ?>
<?php
if(trim($_GET['mode'])!='ajax'){
include "includes/header.php";
?>
<div id="search-page-container">
<?php
} else {
echo '<script>document.title=\''.html_entity_decode($pagetitle).'\';</script>';
}
?>

<?php
$qs='';
$sparams=$_GET;
unset($sparams['controller']);
unset($sparams['page']);
unset($sparams['mode']);
unset($sparams['search']['private_business']);
if($current_tab!='all'){
$sparams['search']['private_business']=$current_tab;
}
if(count($sparams)>0 && http_build_query($sparams)!=''){
$qs='?'.str_replace(array('%5B', '%5D'), array('[', ']'), http_build_query($sparams));
}
?>

<div class="breads">
<?php if(intval($search['user_id'])>0){ ?>
<span><?php echo str_replace('[USERNAME]', ((trim($user['person'])!='')?htmlspecialchars($user['person']):reset(explode('@', $user['email']))), l('list_all_of_author')); ?></span>
<?php } else { ?>
<?php if(isset($selected_cats[0])){ ?>
<a href="<?php echo $langPrefix; ?><?php if(intval($city['city_id'])>0){ ?>/<?php echo $city['url']; ?>/<?php } else { ?>/list/<?php } ?>" class="bread" onclick="runSearching($(this).attr('href')); return false;"><?php if(intval($city['city_id'])>0){ ?><?php echo langAllItemsInCity($city['city_id']); ?><?php } else { ?><?php echo l('list_all_short'); ?><?php } ?></a>
<?php } else { ?>
<span><?php if(intval($city['city_id'])>0){ ?><?php echo langAllItemsInCity($city['city_id']); ?><?php } else { ?><?php echo l('list_all_long'); ?><?php } ?><?php if(trim($search['query'])!='' && !isset($selected_cats[0])){ ?> - <?php echo htmlspecialchars(trim($search['query'])); ?><?php } ?></span>
<?php } ?>
<?php if(isset($selected_cats[0])){ ?>
<span class="delimiter"><i class="fa fa-chevron-right"></i></span>
<?php if(isset($selected_cats[1])){ ?>
<a href="<?php echo $langPrefix; ?><?php if(intval($city['city_id'])>0){ ?>/<?php echo $city['url']; ?>/<?php } else { ?>/list/<?php } ?><?php echo $selected_cats[0]['url']; ?>/" class="bread" onclick="runSearching($(this).attr('href')); return false;"><?php echo $selected_cats[0]['name_'.$config['lang']]; ?></a>
<?php } else { ?>
<span><?php echo $selected_cats[0]['name_'.$config['lang']]; ?><?php if(trim($search['query'])!='' && !isset($selected_cats[1])){ ?> - <?php echo htmlspecialchars(trim($search['query'])); ?><?php } ?></span>
<?php } ?>
<?php } ?>
<?php if(isset($selected_cats[1])){ ?>
<span class="delimiter"><i class="fa fa-chevron-right"></i></span>
<?php if(isset($selected_cats[2])){ ?>
<a href="<?php echo $langPrefix; ?><?php if(intval($city['city_id'])>0){ ?>/<?php echo $city['url']; ?>/<?php } else { ?>/list/<?php } ?><?php echo $selected_cats[0]['url']; ?>/<?php echo $selected_cats[1]['url']; ?>/" class="bread" onclick="runSearching($(this).attr('href')); return false;"><?php echo $selected_cats[1]['name_'.$config['lang']]; ?></a>
<?php } else { ?>
<span><?php echo $selected_cats[1]['name_'.$config['lang']]; ?><?php if(trim($search['query'])!='' && !isset($selected_cats[2])){ ?> - <?php echo htmlspecialchars(trim($search['query'])); ?><?php } ?></span>
<?php } ?>
<?php } ?>
<?php if(isset($selected_cats[2])){ ?>
<span class="delimiter"><i class="fa fa-chevron-right"></i></span>
<?php if(isset($selected_cats[3])){ ?>
<a href="<?php echo $langPrefix; ?><?php if(intval($city['city_id'])>0){ ?>/<?php echo $city['url']; ?>/<?php } else { ?>/list/<?php } ?><?php echo $selected_cats[0]['url']; ?>/<?php echo $selected_cats[1]['url']; ?>/<?php echo $selected_cats[2]['url']; ?>/" class="bread" onclick="runSearching($(this).attr('href')); return false;"><?php echo $selected_cats[2]['name_'.$config['lang']]; ?><?php if(trim($search['query'])!=''){ ?> - <?php echo htmlspecialchars(trim($search['query'])); ?><?php } ?></a>
<?php } else { ?>
<span><?php echo $selected_cats[2]['name_'.$config['lang']]; ?><?php if(trim($search['query'])!=''){ ?> - <?php echo htmlspecialchars(trim($search['query'])); ?><?php } ?></span>
<?php } ?>
<?php } ?>
<?php } ?>
<span class="bread-view">
<?php echo l('view_title'); ?>
<a href="javascript:void(0);" onclick="Cookies.set('view', 'list', {expires:365}); runSearching(); return false;" class="<?php if(trim($_COOKIE['view'])!='gallery'){ ?>active<?php } ?>"><?php echo l('view_list'); ?></a>
<a href="javascript:void(0);" onclick="Cookies.set('view', 'gallery', {expires:365}); runSearching(); return false;" class="<?php if(trim($_COOKIE['view'])=='gallery'){ ?>active<?php } ?>"><?php echo l('view_gallery'); ?></a>
</span>
</div>

<?php renderBanner('search_page_top'); ?>

<?php if(intval($search['user_id'])==0){ ?>
<?php
if(count($categories_to_show)>0){
?>
<div class="search-list-categories-container">
<table class="search-list-categories">
<?php
$ri=1;
foreach($categories_to_show as $row){
?>
<tr class="<?php if($ri>3){ ?>hidden<?php } ?>">
<?php
$row=array_pad($row, 4, '');
foreach($row as $cell){
$_sql_filters=$sql_filters;
unset($_sql_filters['category']);
unset($_sql_filters['filters']);
$_sql_filters['category']="AND (`ads`.`category_id`='".intval($cell['id'])."' OR `ads`.`parent_category_id`='".intval($cell['id'])."' OR `ads`.`parent_parent_category_id`='".intval($cell['id'])."')";	
$ads_count=mysql_result(mysql_query("SELECT SQL_CACHE COUNT(*) FROM `ads` ".implode(' ', array_unique($joins))." WHERE `ads`.`active`='1' ".implode(' ', $_sql_filters).";"), 0, 0);
?>
<td>
<?php if(is_array($cell)){ ?>
<a href="<?php echo $langPrefix; ?><?php echo reset(explode('?', $_SERVER['REQUEST_URI'])).$cell['url'].'/'; ?><?php echo $qs; ?>" title="<?php echo htmlspecialchars($cell['name_'.$config['lang']]); ?>" onclick="runSearching($(this).attr('href')); return false;"><?php echo htmlspecialchars(cutString($cell['name_'.$config['lang']], 25, '...')); ?></a>
<span class=""><?php echo $ads_count; ?></span>
<?php } ?>
</td>
<?php
}
?>
</tr>
<?php
$ri++;
}
?>
</table>
<div class="show-all-categories">
<?php
if(count($categories_to_show)>3){
?>
<a href="javascript:void(0);" onclick="$(this).hide(); $('.search-list-categories tr.hidden').removeClass('hidden');"><?php echo l('list_show_all_subcategories'); ?><i class="fa fa-angle-down"></i></a>
<?php
}
?>
</div>
</div>
<?php
}
?>
<?php } ?>

<div class="empty-list<?php if(mysql_num_rows($get_ads) || mysql_num_rows($get_top_ads)){ ?> hidden<?php } ?>">
<div>
<i class="fa fa-search"></i>
</div>
<?php echo l('list_empty'); ?>
</div>

<?php if(mysql_num_rows($get_ads) || mysql_num_rows($get_top_ads)){ ?>
<?php foreach(array('top', 'standard') as $list_type){ ?>
<?php if(mysql_num_rows(($list_type=='top')?$get_top_ads:$get_ads)){ ?>
<div class="ads-list-title">
<?php if($list_type=='top'){ ?>
<?php echo l('list_type_top'); ?>
<?php } else { ?>
<?php if(mysql_num_rows($get_top_ads)){ ?>
<?php echo l('list_type_standard'); ?>
<?php } else { ?>
<?php echo langListFound_X_Items($pages['count']); ?>:
<?php } ?>
<?php } ?>
</div>
<?php if($list_type=='standard' && mysql_num_rows($get_top_ads)){ ?><div class="ads-list-count"><?php echo langListFound_X_Items($pages['count']); ?></div><?php } ?>
<div class="ads-list ads-list-<?php echo $list_type; ?>">
<?php while($ad=mysql_fetch_assoc(($list_type=='top')?$get_top_ads:$get_ads)){ ?>
<?php
$ad=getAd($ad['aid'], $selected_currency);
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
<?php if(trim($_COOKIE['view'])=='gallery'){ ?>
<div class="ads-gallery-item<?php if(checkActiveService($ad['aid'], 'highlight', 'upgrade')){ ?> highlighted<?php } ?>">
<div class="ads-gallery-item-photo-container" title="<?php echo htmlspecialchars($ad['title']); ?>">
<?php if(checkActiveService($ad['aid'], 'urgent', 'upgrade')){ ?>
<div class="ads-gallery-item-urgent">Срочно</div>
<?php } ?>
<?php if(count($ad['photos'])>0){ ?>
<?php $photo=reset($ad['photos']); ?>
<a href="<?php echo adurl($ad); ?>#<?php echo $search_id; ?>">
<img src="/image/261x203/<?php echo $photo['apid']; ?>/<?php echo $photo['key']; ?>.jpg?rev=<?php echo $photo['rev']; ?>&contain=1" class="ads-gallery-item-photo">
</a>
<?php } else { ?>
<a href="<?php echo adurl($ad); ?>#<?php echo $search_id; ?>">
<img src="/images/no-photos.png" width="100%">
</a>
<?php } ?>
</div>
<div class="ads-gallery-item-info">
<a class="ads-gallery-item-link" href="<?php echo adurl($ad); ?>#<?php echo $search_id; ?>" title="<?php echo htmlspecialchars($ad['title']); ?>"><?php echo htmlspecialchars($ad['title']); ?></a>
<table>
<tr>
<td>
<div class="ads-gallery-item-price<?php if($ad['salary_from']>0 || $ad['salary_to']>0){ ?> salary<?php } ?>">
<?php echo $ad['display_price']; ?>
</div>
</td>
<td width="20" align="right">
<a href="javascript:void(0);" class="favorite-link" data-id="<?php echo $ad['aid']; ?>">
<span class="add-favorite add-favorite-<?php echo $ad['aid']; ?><?php if($ad['favorite']){ ?> hidden<?php } ?>" title="В избранные"><i class="fa fa-star-o"></i><i class="fa fa-star"></i></span>
<span class="delete-favorite delete-favorite-<?php echo $ad['aid']; ?><?php if(!$ad['favorite']){ ?> hidden<?php } ?>" title="Удалить из избранных"><i class="fa fa-star"></i></span>
</a>
</td>
</tr>
</table>
</div>
</div>
<?php } else { ?>
<div class="ads-list-item<?php if(checkActiveService($ad['aid'], 'highlight', 'upgrade')){ ?> highlighted<?php } ?>">
<table>
<tr>
<td rowspan="2" class="ads-list-item-photo-container">


<?php if(count($ad['photos'])>0){ ?>
<?php $photo=reset($ad['photos']); ?>

<a href="<?php echo adurl($ad); ?>#<?php echo $search_id; ?>">
<?php foreach($ad['photos'] as $photo){ ?>
<img src="/image/261x203/<?php echo $photo['apid']; ?>/<?php echo $photo['key']; ?>.jpg?rev=<?php echo $photo['rev']; ?>&contain=1" class="ads-list-item-photo">
<?php if(checkActiveService($ad['aid'],'top', 'upgrade')){ ?>
<div class="sr-page__list__item_img_top"></div>
<?php } ?>
</a>

<?php } } else { ?>
<a href="<?php echo adurl($ad); ?>#<?php echo $search_id; ?>">
<img src="/images/no-photos.png" width="100%">
<?php if(checkActiveService($ad['aid'],'top', 'upgrade')){ ?>
<div class="sr-page__list__item_img_top"></div>
<?php } ?>
</a>
<?php } ?>
</td>



<td rowspan="2" width="10"></td>
<td class="ads-list-item-top">
<a class="ads-list-item-title" href="<?php echo adurl($ad); ?>#<?php echo $search_id; ?>">
<?php if(checkActiveService($ad['aid'],'urgent', 'upgrade')){ ?>
<div class="ads-list-item-urgent"><?php echo l('urgent'); ?></div>
<?php } ?>
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
<span class="add-favorite add-favorite-<?php echo $ad['aid']; ?><?php if($ad['favorite']){ ?> hidden<?php } ?>"><table><tr><td><?php echo l('add_to_favorites'); ?></td><td><i class="fa fa-star-o"></i><i class="fa fa-star"></i></td></tr></table></span>
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
<?php } ?>
<?php } ?>
<?php } ?>

<?php
if($pages['count']/$pages['per']>1){
echo $pagingHtml;
}
?>

<?php
if(trim($_GET['mode'])!='ajax'){
?>
</div>
<?php renderBanner('list_page_buttom'); ?>
<script>

		$(document).ready(function() {
			$(".ads-list-item-photo-container").brazzersCarousel();
		});

	</script>
<?php
include "includes/footer.php";
}
?>