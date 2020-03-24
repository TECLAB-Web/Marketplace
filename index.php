<?php include "init.php"; ?>
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
$pre_order='time:desc';
$current_tab='all';
$selected_currency=reset(array_keys($currencies));
?>
<?php
$check_city=mysql_query("SELECT * FROM `cities` WHERE `city_id`='".((intval($_GET['city_id'])>0)?intval($_GET['city_id']):intval($_COOKIE['city_id']))."';");
$city=mysql_fetch_assoc($check_city);
$region=mysql_fetch_assoc(mysql_query("SELECT * FROM `regions` WHERE `region_id`='".intval($city['region_id'])."';"));
$city['geo']=$city['title_'.$config['lang']].', '.$region['title_'.$config['lang']];
if($m && intval($_GET['category_id'])>0){
$check_category=mysql_query("SELECT * FROM `categories` WHERE `id`='".intval($_GET['category_id'])."';");
$category=mysql_fetch_assoc($check_category);
$search_category=$category['id'];
$get_cats=mysql_query("SELECT * FROM `categories` WHERE `parent_id`='".intval($_GET['category_id'])."' AND `active`='1' ORDER BY `sort` ASC;");
} else {
$get_cats=mysql_query("SELECT * FROM `categories` WHERE `level`='1' AND `active`='1' ORDER BY `sort` ASC;");
}
if($m){
$get_regions=mysql_query("SELECT * FROM `regions` WHERE `country_id`='".$config['country']."' ORDER BY `title_".$config['lang']."` ASC;");
$get_cities=mysql_query("SELECT * FROM `cities` WHERE `country_id`='".$config['country']."' AND `region_id`='".intval($_GET['region_id'])."' ORDER BY `important` DESC, `title_".$config['lang']."` ASC;");
$get_parameters=mysql_query("SELECT `category_parameter_sort`.`sort`, `category_parameters`.* FROM `category_parameters`, `category_parameter_sort` WHERE `category_parameter_sort`.`key`=`category_parameters`.`key` AND `category_parameter_sort`.`cid`=".intval($_GET['category_id'])." AND FIND_IN_SET(".intval($_GET['category_id']).", `category_parameters`.`cids`) AND `category_parameters`.`type`!='hidden' AND `category_parameters`.`type`!='date' AND (`category_parameters`.`type`='price' OR `category_parameters`.`type`='salary' OR `category_parameters`.`type`='input' OR `category_parameters`.`type`='select' OR `category_parameters`.`type`='checkboxes') AND `category_parameters`.`has_searching_form`='1' AND `category_parameters`.`active`='1' ORDER BY FIND_IN_SET(`category_parameters`.`type`, 'price,salary,input,select,checkboxes') ASC, `category_parameter_sort`.`sort` ASC;");
}
if(intval($city['city_id'])>0){
$get_ads=mysql_query("SELECT `ads`.* FROM `ads` WHERE `ads`.`city_id`='".intval($city['city_id'])."' AND `ads`.`active`='1' AND EXISTS(SELECT * FROM `ad_photos` WHERE `ad_photos`.`apid`=`ads`.`apid`) ORDER BY `ads`.`time` DESC LIMIT 25;");
} else {
$get_ads=mysql_query("SELECT `ads`.* FROM `ads` WHERE `ads`.`active`='1' AND EXISTS(SELECT * FROM `ad_photos` WHERE `ad_photos`.`apid`=`ads`.`apid`) ORDER BY `ads`.`time` DESC LIMIT 25;");
}
?>
<?php
$show_top_tabs=true;
$is_main=true;
$pagetitle=$config['welcome'];
$pagedesc=$config['description'];
?>
<?php
if($m){ include "m-index.php"; exit; }
?>
<?php
if(trim($_GET['mode'])!='ajax'){
include "includes/header.php";
?>
<div id="index-page-container">
<?php
}
?>
<?php renderBanner('global_centre'); ?>
<?php if(mysql_num_rows($get_ads)){ ?>
<div class="ads-list-title">
<?php if(intval($city['city_id'])>0){ ?>
<?php echo langNewItemsInCity($city['city_id']); ?>
<?php } else { ?>
<?php echo l('index_new_items'); ?>
<?php }?>
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
<div class="ads-gallery-item<?php if(checkActiveService($ad['aid'], 'highlight', 'upgrade')){ ?> highlighted<?php } ?>">
<div class="ads-gallery-item-photo-container" title="<?php echo htmlspecialchars($ad['title']); ?>">
<?php if(checkActiveService($ad['aid'], 'urgent', 'upgrade')){ ?>
<div class="ads-gallery-item-urgent"><?php echo l('urgent'); ?></div>
<?php } ?>
<?php if(count($ad['photos'])>0){ ?>
<?php $photo=reset($ad['photos']); ?>
<a href="<?php echo adurl($ad); ?>">
<img src="/image/261x203/<?php echo $photo['apid']; ?>/<?php echo $photo['key']; ?>.jpg?rev=<?php echo $photo['rev']; ?>&contain=1" class="ads-gallery-item-photo butterfly">
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
<span class="add-favorite add-favorite-<?php echo $ad['aid']; ?><?php if($ad['favorite']){ ?> hidden<?php } ?>" title="<?php echo l('add_to_favorites'); ?>"><i class="fa fa-star-o"></i><i class="fa fa-star"></i></span>
<span class="delete-favorite delete-favorite-<?php echo $ad['aid']; ?><?php if(!$ad['favorite']){ ?> hidden<?php } ?>" title="<?php echo l('remove_from_favorites'); ?>"><i class="fa fa-star"></i></span>
</a>
</td>
</tr>
</table>
</div>
</div>
<?php } ?>
<div class="clear"></div>
</div>
<div class="ads-list-see-all">
<a href="<?php echo $langPrefix; ?>/<?php if(intval($city['city_id'])>0){ echo $city['url']; } else { echo 'list'; } ?>/"><?php echo l('index_see_all'); ?></a>
</div>
<?php } else { ?>
<div class="empty-list">
<div>
<i class="fa fa-list"></i>
</div>
<?php echo l('index_empty'); ?>
</div>
<?php } ?>

<?php
if(trim($_GET['mode'])!='ajax'){
?>
</div>
<?php
include "includes/footer.php";
}
?>