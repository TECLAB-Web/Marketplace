<?php include_once "init.php"; ?>
<?php
$pages=array();
if(isset($_SESSION['userid'])){
$pages['count']=mysql_result(mysql_query("SELECT COUNT(DISTINCT `searches`.`search_id`) FROM `searches` INNER JOIN `favorites` ON `favorites`.`otype`='search' AND `favorites`.`oid`=`searches`.`search_id` AND (`favorites`.`userid`='".$_SESSION['userid']."' OR `favorites`.`sid`='"._F($_COOKIE['PHPSESSID'])."') ;"), 0, 0);
} else {
$pages['count']=mysql_result(mysql_query("SELECT COUNT(DISTINCT `searches`.`search_id`) FROM `searches` INNER JOIN `favorites` ON `favorites`.`otype`='search' AND `favorites`.`oid`=`searches`.`search_id` AND `favorites`.`sid`='"._F($_COOKIE['PHPSESSID'])."' ;"), 0, 0);
}
$pages['per']=(($m)?5:10);
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
$get_searches=mysql_query("SELECT DISTINCT `searches`.* FROM `searches` INNER JOIN `favorites` ON `favorites`.`otype`='search' AND `favorites`.`oid`=`searches`.`search_id` AND (`favorites`.`userid`='".$_SESSION['userid']."' OR `favorites`.`sid`='"._F($_COOKIE['PHPSESSID'])."') ORDER BY `searches`.`time` DESC LIMIT ".($pages['per']*($pages['current']-1)).", ".$pages['per'].";");
} else {
$get_searches=mysql_query("SELECT DISTINCT `searches`.* FROM `searches` INNER JOIN `favorites` ON `favorites`.`otype`='search' AND `favorites`.`oid`=`searches`.`search_id` AND `favorites`.`sid`='"._F($_COOKIE['PHPSESSID'])."' ORDER BY `searches`.`time` DESC LIMIT ".($pages['per']*($pages['current']-1)).", ".$pages['per'].";");
}
?>
<?php
$show_top_tabs=true;
$is_favorites=true;
$current_tab='searches';
$top_tabs_title=l('favorites_searches_title');
$top_tabs_description=l('favorites_searches_description');
$pagetitle=l('favorites_searches_title')." &bull; ".$config['sitename'];
$pagedesc=$config['description'];
?>
<?php
if($m){ include "m-favorites_searches.php"; exit; }
?>
<?php include "includes/header.php"; ?>

<?php if(mysql_num_rows($get_searches)){ ?>
<div class="favorites-control" style="margin-bottom:20px;">
<span class="favorites-clearall">
<a href="javascript:void(0);" onclick="clearFavorites('searches'); return false;"><?php echo l('favorites_searches_clear_all'); ?></a>
</span>
<div class="clear"></div>
</div>
<div class="searches-list">
<?php while($search=mysql_fetch_assoc($get_searches)){ ?>
<?php
$search['city']=mysql_fetch_assoc(mysql_query("SELECT * FROM `cities` WHERE `city_id`='".intval($search['city_id'])."';"));
$search['region']=mysql_fetch_assoc(mysql_query("SELECT * FROM `regions` WHERE `region_id`='".intval($search['city']['region_id'])."';"));
if(intval($search['city_id'])>0){
$search['geo']=$search['city']['title_'.$config['lang']].', '.$search['region']['title_'.$config['lang']];
} else {
$search['geo']='';
}
$search['params']=unserialize($search['request']);
if(intval($search['params']['user_id'])>0){
$search['user']=mysql_fetch_assoc(mysql_query("SELECT * FROM `users` WHERE `userid`='".intval($search['params']['user_id'])."';"));
}
$search_params['search']=$search['params'];
$get_parameters=mysql_query("SELECT * FROM `category_parameters` WHERE FIND_IN_SET(".intval($search['category_id']).", `cids`) AND `type`!='hidden' AND `type`!='date' AND (`type`='price' OR `type`='salary' OR `type`='input' OR `type`='select' OR `type`='checkboxes') AND `has_searching_form`='1' ORDER BY FIND_IN_SET(`type`, 'price,salary,input,select,checkboxes') ASC, `sort` ASC;");
?>
<div class="searches-list-item">
<div class="searches-list-item-body">
<?php if($search['query']=='' && $search['geo']=='' && $search['category_id']==0 && (count($search['params'])==0 || (count($search['params'])==1 && isset($search['params']['currency'])))){ ?>
<div class="col-md-6">
<div class="searches-list-item-row">
<div class="searches-list-item-row-title">
<?php echo l('favorites_searches_area'); ?>
</div>
<?php echo l('favorites_searches_all_items'); ?>
</div>
</div>
<?php } ?>
<?php if($search['params']['user_id']>0){ ?>
<div class="col-md-6">
<div class="searches-list-item-row">
<div class="searches-list-item-row-title">
<?php echo l('favorites_searches_author'); ?>
</div>
<?php echo htmlspecialchars($search['user']['person']); ?> (ID: <?php echo intval($search['params']['user_id']); ?>)
</div>
</div>
<?php } ?>
<?php if($search['geo']!=''){ ?>
<div class="col-md-6">
<div class="searches-list-item-row">
<div class="searches-list-item-row-title">
<?php echo l('favorites_searches_geo'); ?>
</div>
<?php echo htmlspecialchars($search['geo']); ?>
</div>
</div>
<?php } ?>
<?php if($search['category_id']>0){ ?>
<div class="col-md-6">
<div class="searches-list-item-row">
<div class="searches-list-item-row-title">
<?php echo l('favorites_searches_category'); ?>
</div>
<?php echo getCategoryFullTitle($search['category_id']); ?>
</div>
</div>
<?php } ?>
<?php if($search['query']!=''){ ?>
<div class="col-md-6">
<div class="searches-list-item-row">
<div class="searches-list-item-row-title">
<?php echo l('favorites_searches_query'); ?>
</div>
<?php echo htmlspecialchars($search['query']); ?>
</div>
</div>
<?php } ?>
<?php if(isset($search['params']['currency'])){ ?>
<div class="col-md-6">
<div class="searches-list-item-row">
<div class="searches-list-item-row-title">
<?php echo l('favorites_searches_currency'); ?>
</div>
<?php echo $currencies[$search['params']['currency']]; ?>
</div>
</div>
<?php } ?>
<?php
while($param=mysql_fetch_assoc($get_parameters)){
if(!isset($search['params'][$param['url_key']])){ continue; }
if(in_array(trim($search['params']['currency']), array_keys($currencies))){
$selected_currency=$search['currency'];
} else {
$selected_currency=reset(array_keys($currencies));
}
$param['values']=array();
$get_values=mysql_query("SELECT * FROM `category_parameter_values` WHERE (`keys` LIKE '".$param['key'].",%' OR `keys` LIKE '%,".$param['key'].",%' OR `keys` LIKE '%,".$param['key']."' OR `keys`='".$param['key']."') AND FIND_IN_SET(".$search['category_id'].", `cids`) ORDER BY `sort` ASC;");
while($value=mysql_fetch_assoc($get_values)){
$param['values'][$value['key']]=$value['value_'.$config['lang']];
}
if($param['type']=='price' || $param['type']=='salary'){
$param['suffix_'.$config['lang']]=$currencies[$selected_currency];
}
?>
<div class="col-md-6">
<div class="searches-list-item-row">
<div class="searches-list-item-row-title">
<?php echo htmlspecialchars($param['label_'.$config['lang']]); ?>:
</div>
<?php if($param['type']=='select' || $param['type']=='checkboxes'){ ?>
<?php echo implode(', ', array_intersect_key($param['values'], array_flip(explode(',', $search['params'][$param['url_key']])))); ?>
<?php } ?>
<?php if($param['type']=='input' || $param['type']=='price' || $param['type']=='salary'){ ?>
<?php if(trim($search['params'][$param['url_key']]['from'])!=''){ ?><?php echo l('favorites_searches_from'); ?> <?php echo htmlspecialchars(trim($search['params'][$param['url_key']]['from'])); ?> <?php } ?>
<?php if(trim($search['params'][$param['url_key']]['to'])!=''){ ?><?php echo l('favorites_searches_to'); ?> <?php echo htmlspecialchars(trim($search['params'][$param['url_key']]['to'])); ?> <?php } ?>
<?php echo $param['suffix_'.$config['lang']]; ?>
<?php } ?>
</div>
</div>
<?php
}
?>
<div class="clear"></div>
</div>
<div class="searches-list-item-footer">
<div class="col-md-6">
<a href="<?php echo $langPrefix; ?>/<?php if($search['city_id']>0){ echo $search['city']['url'].'/'; } else { echo 'list/'; } ?><?php if($search['category_id']>0){ echo getCategoryURL($search['category_id']).'/'; } ?><?php if(count($search_params['search'])>0){ echo '?'.str_replace(array('%5B', '%5D'), array('[', ']'), http_build_query($search_params)); } ?>"><b><?php echo l('favorites_searches_go_to_ads_list'); ?></b></a>
</div>
<div class="col-md-6" style="text-align:right;">
<a href="javascript:void(0);" class="favorite-link" data-id="<?php echo $search['search_id']; ?>" data-type="search">
<span class="add-favorite-<?php echo $search['search_id']; ?> hidden"><?php echo l('return_to_favorites'); ?></span>
<span class="delete-favorite-<?php echo $search['search_id']; ?>"><?php echo l('remove_from_favorites'); ?></span>
</a>
</div>
<div class="clear"></div>
</div>
</div>
<?php } ?>
<div class="clear"></div>
</div>
<?php } else { ?>
<div class="empty-list">
<div>
<i class="fa fa-search"></i>
</div>
<?php echo l('favorites_searches_empty'); ?>
</div>
<?php } ?>

<?php
if($pages['count']/$pages['per']>1){
echo $pagingHtml;
}
?>

<?php include "includes/footer.php"; ?>