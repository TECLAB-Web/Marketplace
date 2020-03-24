<?php include_once "init.php"; ?>
<?php
$ad=getAd(intval($_GET['id']));
if(!$ad){
header("HTTP/1.0 404 Not Found");
include "404.php";
exit;
}
if(trim($_GET['mode'])=='print'){
if(strpos($_SERVER['HTTP_REFERER'], adurl($ad))===false || !($ad['active']=='1' || $_SESSION['userid']==$ad['userid'])){
unset($_GET['mode']);
header("HTTP/1.0 404 Not Found");
include "404.php";
exit;
}
} elseif(trim($_GET['mode'])==''){
if(strpos($langPrefix.$_SERVER['REQUEST_URI'], adurl($ad))===false){
header("Location: ".adurl($ad));
exit;
}
}
$aid=intval($_GET['id']);
?>
<?php
$selectedCategory=0;
$uploadedPhotos=0;
if($edit){
$uploadedPhotos=count($ad['photos']);
}
$maxPhotos=8;
if(intval($ad['category_id'])>0){
$selectedCategory=intval($ad['category_id']);
}
if($selectedCategory>0){
$selected_cats=array();
$selected_cats_urls=array();
$main_cat_id=0;
$get_cat=mysql_query("SELECT * FROM `categories` WHERE `id`='".$selectedCategory."';");
if(mysql_num_rows($get_cat)){
$cat=mysql_fetch_assoc($get_cat);
$current_cat=$cat;
$selected_cats[]=$cat['name_'.$config['lang']];
$selected_cats_urls[]=$cat['url'];
$category_id=$main_cat_id=$cat['id'];
$maxPhotos=$cat['max_photos'];
$get_cat=mysql_query("SELECT * FROM `categories` WHERE `id`='".$cat['parent_id']."';");
if(mysql_num_rows($get_cat)){
$cat=mysql_fetch_assoc($get_cat);
$selected_cats[]=$cat['name_'.$config['lang']];
$selected_cats_urls[]=$cat['url'];
$parent_category_id=$main_cat_id=$cat['id'];
$get_cat=mysql_query("SELECT * FROM `categories` WHERE `id`='".$cat['parent_id']."';");
if(mysql_num_rows($get_cat)){
$cat=mysql_fetch_assoc($get_cat);
$selected_cats[]=$cat['name_'.$config['lang']];
$selected_cats_urls[]=$cat['url'];
$parent_parent_category_id=$main_cat_id=$cat['id'];
}
}
} else {
$selectedCategory=0;
}
}
?>
<?php
if(trim($_GET['mode'])=='ajax'){
header('Content-Type: application/json; charset=utf-8');
if(isset($_SESSION['userid'])){
if(trim($_POST['action'])=='message'){
$result=array();
$result['errors']=array();
if($_SESSION['userid']==$ad['userid']){
$result['errors']['form']=l('item_pm_error_myself');
} elseif(trim($_POST['text'])==''){
$result['errors']['text']=l('item_pm_error_empty');
} elseif(mb_strlen(trim($_POST['text']))>4096){
$result['errors']['text']=l('item_pm_error_long');
}
if(count($result['errors'])==0){
$check_dialog_member=mysql_query("SELECT `dialogs`.`did` FROM `dialogs`, `dialog_members` WHERE `dialogs`.`aid`='".$ad['aid']."' AND `dialog_members`.`did`=`dialogs`.`did` AND `dialog_members`.`userid`='".$_SESSION['userid']."';");
if(mysql_num_rows($check_dialog_member)){
$did=mysql_result($check_dialog_member, 0, 0);
} else {
mysql_query("INSERT INTO `dialogs` SET `aid`='".$ad['aid']."', `time`='".$time."';");
$did=mysql_insert_id();
mysql_query("INSERT INTO `dialog_members` SET `did`='".$did."', `userid`='".$_SESSION['userid']."', `time`='".$time."', `active`='1';");
mysql_query("INSERT INTO `dialog_members` SET `did`='".$did."', `userid`='".$ad['userid']."', `time`='".$time."', `active`='1';");
}
$index=Words2AllForms(trim($_POST['text']));
mysql_query("INSERT INTO `dialog_messages` SET `did`='".$did."', `userid`='".$_SESSION['userid']."', `text`='"._F($_POST['text'])."', `index`='"._F($index)."', `dmuid`='"._F($_POST['dmuid'])."', `time`='".$time."';");
$dmid=mysql_insert_id();
mysql_query("INSERT INTO `dialog_unread_messages` SET `did`='".$did."', `userid`='".$ad['userid']."', `dmid`='".$dmid."';");
if($ad['user']['notify_messages']>0){
$mail=mysql_fetch_assoc(mysql_query("SELECT * FROM `mail_templates` WHERE `code`='message';"));
$to=trim($ad['user']['email']);
$mail['title']=$mail['title_'.$ad['user']['lang']];
$mail['body']=$mail['body_'.$ad['user']['lang']];
$mail['body']=str_replace('[SITE_NAME]', $config['sitename'], $mail['body']);
$mail['body']=str_replace('[SITE_URL]', $config['siteurl'], $mail['body']);
$mail['body']=str_replace('[AD_TITLE]', htmlspecialchars($ad['title']), $mail['body']);
$mail['body']=str_replace('[DID]', $did, $mail['body']);
liam($to, $mail['title'], $mail['body'], "noreply@".$config['siteurl']);
}
$result['did']=$did;
$result['status']='success';
} else {
$result['status']='error';
}
echo json_encode($result);
}
}
if(trim($_POST['action'])=='complaint'){
$result=array();
$result['errors']=array();
if(!isset($config['complaint_types'][intval($_POST['ctid'])])){
$result['errors']['ctid']=l('item_c_error_category');
}
if(trim($_POST['description'])==''){
$result['errors']['description']=l('item_c_error_empty');
} elseif(mb_strlen(trim($_POST['description']))>500){
$result['errors']['description']=l('item_c_error_long');
}
if(count($result['errors'])==0){
mysql_query("INSERT INTO `complaints` SET `userid`='".$_SESSION['userid']."', `aid`='".$ad['aid']."', `ctid`='".intval($_POST['ctid'])."', `description`='"._F($_POST['description'])."', `time`='".$time."';");
$result['message']=l('item_c_thanks');
$result['status']='success';
} else {
$result['status']='error';
}
echo json_encode($result);
}
if(trim($_POST['action'])=='contact'){
if(isset($contact_methods[trim($_POST['type'])])){
$result=array();
mysql_query("INSERT IGNORE INTO `contact_viewers` SET `ip`='".$_SERVER['REMOTE_ADDR']."', `views`='0', `last_view`='0';");
mysql_query("UPDATE `contact_viewers` SET `views`='1' WHERE `ip`='".$_SERVER['REMOTE_ADDR']."' AND `views`>'0' AND `last_view`<'".($time-60*5)."';");
$viewer=mysql_fetch_assoc(mysql_query("SELECT * FROM `contact_viewers` WHERE `ip`='".$_SERVER['REMOTE_ADDR']."';"));
if(!($ad['active']=='1' || $_SESSION['userid']==$ad['userid'])){
$result['inactive']=true;
$result['inactive_label']=l('item_contact_inactive');
} elseif($viewer['views']>=$config['max_contact_views'] && (trim($_POST['captcha'])=='' || trim($_SESSION['captcha'])!=trim($_POST['captcha']))){
$result['captcha']=true;
$result['captcha_message']=l('item_contact_error_too_many');
unset($_SESSION['captcha']);
} else {
$result['value']=htmlspecialchars($ad[trim($_POST['type'])]);
if(trim($_POST['type'])=='skype'){
$result['call_label']=l('item_contact_call');
}
mysql_query("UPDATE `ads` SET `"._F($_POST['type'])."_views`=`"._F($_POST['type'])."_views`+1 WHERE `aid`='".$ad['aid']."';");
if($viewer['views']>=$config['max_contact_views']){
$views=1;
} else {
$views=$viewer['views']+1;
}
mysql_query("UPDATE `contact_viewers` SET `views`='".$views."', `last_view`='".$time."' WHERE `ip`='".$_SERVER['REMOTE_ADDR']."';");
unset($_SESSION['captcha']);
}
echo json_encode($result);
}
}
exit;
}
?>
<?php
mysql_query("UPDATE `ads` SET `views`=`views`+1 WHERE `aid`='".$ad['aid']."';");
if($ad['user']['hidesimilar']==0){
$get_other_ads=mysql_query("SELECT * FROM `ads` WHERE `aid`!='".$ad['aid']."' AND `userid`='".$ad['userid']."' AND `active`='1' ORDER BY RAND() LIMIT 4;");
}
?>
<?php
//if(trim($_GET['json'])!=''){
//echo json_encode($ad, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT );
//exit;
//}
?>
<?php
$dmuid=uniqid('');
$is_ad_page=true;
$pagetitle=htmlspecialchars($ad['title'])." - ".$ad['geo']." &bull; ".$config['sitename'];
$pagedesc=htmlspecialchars(cutString(str_replace("\n", ' ', str_replace("\r\n", ' ', $ad['description'])), 600, '...'));
?>
<?php
if($m){ include "m-item.php"; exit; }
?>
<?php include "includes/header.php"; ?>
<?php if(trim($_GET['mode'])!='print'){ ?>
<?php
$bread_cats=array_reverse($selected_cats);
$bread_cats_urls=array_reverse($selected_cats_urls);
?>

<div class="breads">
<a href="javascript:void(0);" onclick="history.go(-1);" class="bread-back"><i class="fa fa-arrow-left"></i><?php echo l('item_go_back'); ?></a>
<a href="<?php echo $langPrefix; ?>/<?php echo $ad['city']['url']; ?>/" class="bread"><?php echo langAllItemsInCity($ad['city_id']); ?></a>
<span class="delimiter"><i class="fa fa-chevron-right"></i></span>
<a href="<?php echo $langPrefix; ?>/<?php echo $ad['city']['url']; ?>/<?php echo $bread_cats_urls[0]; ?>/" class="bread"><?php echo $bread_cats[0]; ?></a>
<?php if(isset($bread_cats[1])){ ?>
<span class="delimiter"><i class="fa fa-chevron-right"></i></span>
<a href="<?php echo $langPrefix; ?>/<?php echo $ad['city']['url']; ?>/<?php echo $bread_cats_urls[0]; ?>/<?php echo $bread_cats_urls[1]; ?>/" class="bread"><?php echo $bread_cats[1]; ?></a>
<?php } ?>
<?php if(isset($bread_cats[2])){ ?>
<span class="delimiter"><i class="fa fa-chevron-right"></i></span>
<a href="<?php echo $langPrefix; ?>/<?php echo $ad['city']['url']; ?>/<?php echo $bread_cats_urls[0]; ?>/<?php echo $bread_cats_urls[1]; ?>/<?php echo $bread_cats_urls[2]; ?>/" class="bread"><?php echo $bread_cats[2]; ?></a>
<?php } ?>
<!-- <a href="javascript:void(0);" class="bread-next"><?php echo l('item_next'); ?><i class="fa fa-arrow-right"></i></a> -->
</div>

<?php renderBanner('item_top_title'); ?>

<div itemscope itemtype="http://schema.org/Product">

<div class="item-overlay-background" style="display:none;" onclick="hideOverlay(); return false;"></div>
<div class="item-overlay" style="display:none;">
<table class="item-overlay-table">
<tr>
<td>
<div class="item-title">
<?php echo htmlspecialchars($ad['title']); ?>
</div>
<div class="sub-title">
<span class="icon markerloc gray vmiddle inlblk"></span>
<?php if($ad['coordinates']){ ?>
<a href="javascript:void(0);" onclick="showMapDialog(); $(this).blur(); return false;" title="<?php echo l('item_show_map'); ?>"><?php echo htmlspecialchars($ad['geo']); ?></a>
<?php } else { ?>
<b><?php echo htmlspecialchars($ad['geo']); ?></b>
<?php } ?>
<span>|</span>
<span><?php echo l('item_added_at'); ?> <?php displayTime($ad['time']); ?></span>
<span>|</span>
<span><?php echo l('item_number'); ?> <?php echo $ad['aid']; ?></span>
<span>|</span>
<span><?php echo l('my_items_views'); ?> <?php echo $ad['views']; ?></span>
</div>
</td>
<td align="right" valign="top">
<i class="fa fa-times item-overlay-close" onclick="hideOverlay(); return false;"></i>
</td>
</tr>
<tr>
<td class="item-overlay-table-photo-container">
<a href="javascript:void(0);" target="_blank" class="overlay-gallery-fullsize">
<span><?php echo l('item_photo_fullsize'); ?></span>
</a>
<?php if($ad['active']=='1' || $_SESSION['userid']==$ad['userid']){ ?>
<a href="javascript:void(0);" class="overlay-gallery-favorite<?php if($ad['favorite']){ ?> overlay-gallery-favorite-active<?php } ?> favorite-link" data-id="<?php echo $ad['aid']; ?>">
<span class="add-favorite-<?php echo $ad['aid']; ?><?php if($ad['favorite']){ ?> hidden<?php } ?>"><?php echo l('item_add_to_favorites_short'); ?></span>
<span class="delete-favorite-<?php echo $ad['aid']; ?><?php if(!$ad['favorite']){ ?> hidden<?php } ?>"><?php echo l('item_remove_from_favorites_short'); ?></span>
</a>
<?php } ?>
<?php if(count($ad['photos'])>1){ ?><i class="fa fa-chevron-left prev"></i><?php } ?>
<?php
$pi=1;
foreach($ad['photos'] as $photo){
?><div class="overlay-gallery-item<?php if($pi==1){ ?> current-photo<?php } ?>" id="overlay-gallery-item-<?php echo $photo['key']; ?>" style="max-width:<?php echo $photo['size'][0]; ?>px;max-height:<?php echo $photo['size'][1]; ?>px;background-image:url('/image/0x0/<?php echo $photo['apid']; ?>/<?php echo $photo['key']; ?>.jpg?rev=<?php echo $photo['rev']; ?>');" data-src="/image/0x0/<?php echo $photo['apid']; ?>/<?php echo $photo['key']; ?>.jpg?rev=<?php echo $photo['rev']; ?>" alt="<?php echo htmlspecialchars($ad['title'])." - ".$ad['geo']; ?>" title="<?php echo htmlspecialchars($ad['title'])." - ".$ad['geo']; ?>"<?php if(count($ad['photos'])>1){ ?> onclick="$('.item-overlay-table-photo-container .next').click(); return false;"<?php } ?>></div><?php
$pi++;
}
?>
<?php if(count($ad['photos'])>1){ ?><i class="fa fa-chevron-right next"></i><?php } ?>
</td>
<td class="item-overlay-table-sidebar">
<?php if(trim($ad['display_price'])!='&mdash;'){ ?>
<div class="ad-price-container">
<div class="ad-price">
<div class="ad-price-text<?php if(isset($ad['arranged_price'])){ ?> arranged<?php } ?>"><?php echo $ad['display_price']; ?></div>
<?php if(isset($ad['arranged_price'])){ ?>
<div class="ad-price-arranged"><?php echo l('price_arranged'); ?></div>
<?php } ?>
</div>
</div>
<?php } ?>
<div style="padding-left:10px;">
<?php if($ad['active']=='1' || $_SESSION['userid']==$ad['userid']){ ?>
<?php if($ad['nospam']=='0'){ ?>
<a href="javascript:void(0);" onclick="hideOverlay(); $(window).scrollTop($('#post-message-anchor').offset().top-$(window).height()+20);<?php if(isset($_SESSION['userid'])){ ?> $('#post-message-text').focus();<?php } ?>" class="write-link">
<div class="item-contact-button contact-email">
<span><?php echo l('item_contact_author'); ?></span>
</div>
</a>
<?php } ?>
<?php foreach($contact_methods as $k=>$v){ ?>
<?php if(trim($ad[$k])!=''){ ?>
<div class="item-contact-button contact-<?php echo $k; ?>" data-type="<?php echo $k; ?>" title="<?php echo $v; ?>">
<span><?php echo htmlspecialchars(substr_replace(trim($ad[$k]), str_repeat('x', strlen(trim($ad[$k]))-2), 2, strlen(trim($ad[$k]))-2)); ?></span>
<a href="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>"><?php echo l('item_show_contact_info'); ?></a>
</div>
<?php } ?>
<?php } ?>
<?php if($ad['coordinates']){ ?>
<div class="item-map-link">
<div class="item-map-link-text">
<?php echo $ad['geo']; ?>
<div><a href="javascript:void(0);" onclick="showMapDialog(); $(this).blur(); return false;"><?php echo l('item_show_map'); ?></a></div>
</div>
</div>
<?php } ?>
<?php } ?>
<div class="item-user">
<div class="author-name"><?php echo htmlspecialchars($ad['person']); ?></div>
<div class="author-since"><?php echo str_replace('[DATE]', $months_of[intval(date("m", $ad['user']['time']))]." ".intval(date("Y", $ad['user']['time'])), l('item_author_registered_at')); ?></div>
<?php if($ad['active']=='1' || $_SESSION['userid']==$ad['userid']){ ?>
<?php if($ad['user']['hidesimilar']==0){ ?>
<div class="author-all"><a href="<?php echo $langPrefix; ?>/list/?search[user_id]=<?php echo $ad['userid']; ?>"><?php echo l('item_other_items'); ?></a></div>
<?php } ?>
<?php } ?>
</div>
</div>
</td>
</tr>
<tr>
<td class="item-overlay-table-footer thumbs-container">
<script type="text/javascript">
var thumb_photos='<?php
if(count($ad['photos'])>1){
$pi=1;
foreach($ad['photos'] as $photo){
?><div class="overlay-thumb<?php if($pi==1){ ?> current-thumb<?php } ?>" id="overlay-thumb-<?php echo $photo['key']; ?>" onclick="showOverlayGalleryItem(\'<?php echo $photo['key']; ?>\'); return false;"><img src="/image/94x72/<?php echo $photo['apid']; ?>/<?php echo $photo['key']; ?>.jpg?rev=<?php echo $photo['rev']; ?>"></div><?php
$pi++;
}
}
?>';
</script>
</td>
<td class="item-overlay-table-footer"></td>
</tr>
</table>
</div>

<div class="hidden" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
<meta itemprop="price" content="<?php echo $ad['price']; ?>">
<meta itemprop="priceCurrency" content="<?php echo $ad['currency']; ?>">
<link itemprop="availability" href="http://schema.org/InStock">
</div>

<div class="row">
<div class="item-side-content">
<div id="item_side_bar">
<?php if(trim($ad['display_price'])!='&mdash;'){ ?>
<div class="ad-price-container">
<div class="ad-price">
<div class="ad-price-text<?php if(isset($ad['arranged_price'])){ ?> arranged<?php } ?>"><?php echo $ad['display_price']; ?></div>
<?php if(isset($ad['arranged_price'])){ ?>
<div class="ad-price-arranged"><?php echo l('price_arranged'); ?></div>
<?php } ?>
</div>
</div>
<?php } ?>
<?php if($ad['active']=='1' || $_SESSION['userid']==$ad['userid']){ ?>
<?php if($ad['nospam']=='0'){ ?>
<a href="javascript:void(0);" onclick="$(window).scrollTop($('#post-message-anchor').offset().top-$(window).height()+20);<?php if(isset($_SESSION['userid'])){ ?> $('#post-message-text').focus();<?php } ?>" class="write-link">
<div class="item-contact-button contact-email">
<span><?php echo l('item_contact_author'); ?></span>
</div>
</a>
<?php } ?>
<?php foreach($contact_methods as $k=>$v){ ?>
<?php if(trim($ad[$k])!=''){ ?>
<div class="item-contact-button contact-<?php echo $k; ?>" data-type="<?php echo $k; ?>" title="<?php echo $v; ?>">
<span><?php echo htmlspecialchars(substr_replace(trim($ad[$k]), str_repeat('x', strlen(trim($ad[$k]))-2), 2, strlen(trim($ad[$k]))-2)); ?></span>
<a href="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>"><?php echo l('item_show_contact_info'); ?></a>
</div>
<?php } ?>
<?php } ?>
<?php if($ad['coordinates']){ ?>
<div class="item-map-link">
<div class="item-map-link-text">
<?php echo $ad['geo']; ?>
<div><a href="javascript:void(0);" onclick="showMapDialog(); $(this).blur(); return false;"><?php echo l('item_show_map'); ?></a></div>
</div>
</div>
<?php } ?>
<?php } ?>
<div class="item-user">
<div class="author-name"><?php echo htmlspecialchars($ad['person']); ?></div>
<div class="author-since"><?php echo str_replace('[DATE]', $months_of[intval(date("m", $ad['user']['time']))]." ".intval(date("Y", $ad['user']['time'])), l('item_author_registered_at')); ?></div>
<?php if($ad['active']=='1' || $_SESSION['userid']==$ad['userid']){ ?>
<?php if($ad['user']['hidesimilar']==0){ ?>
<div class="author-all"><a href="<?php echo $langPrefix; ?>/list/?search[user_id]=<?php echo $ad['userid']; ?>"><?php echo l('item_other_items'); ?></a></div>
<?php } ?>
<?php } ?>
</div>
<div class="item-service-links">
<?php if($ad['active']=='1' || $_SESSION['userid']==$ad['userid']){ ?>
<div>
<a href="javascript:void(0);" class="item-service-link favorite-link" data-id="<?php echo $ad['aid']; ?>">
<span class="add-favorite-<?php echo $ad['aid']; ?><?php if($ad['favorite']){ ?> hidden<?php } ?>"><?php echo l('item_add_to_favorites'); ?></span>
<span class="delete-favorite-<?php echo $ad['aid']; ?><?php if(!$ad['favorite']){ ?> hidden<?php } ?>"><?php echo l('item_remove_from_favorites'); ?></span>
</a>
</div>
<div>
<a href="<?php echo adurl($ad, 'print/'); ?>" target="_blank" class="item-service-link"><?php echo l('item_print'); ?></a>
</div>
<?php } ?>
<div>
<a href="<?php echo $langPrefix; ?>/edit/?id=<?php echo $ad['aid']; ?>&ref=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="item-service-link"><?php echo l('item_edit'); ?></a>
</div>
<div>
<a href="javascript:void(0);" onclick="showComplaintDialog(); $(this).blur(); return false;" class="item-service-link" style="color:#FB6565;"><?php echo l('item_complaint'); ?></a>
</div>
</div>
<?php renderBanner('item_right'); ?>
</div>
</div>
<div class="item-main-content">
<?php if($ad['active']!='1'){ ?>
<div class="ad-alert"><?php echo l('item_alert_'.$ad['active']); ?></div>
<?php } ?>
<div itemprop="name" class="item-title">
<?php if(checkActiveService($ad['aid'], 'urgent', 'upgrade')){ ?>
<div class="ads-list-item-urgent">Срочно</div>
<?php } ?>
<?php echo htmlspecialchars($ad['title']); ?>
</div>
<div class="sub-title">
<?php if($ad['coordinates']){ ?>
<a href="javascript:void(0);" onclick="showMapDialog(); $(this).blur(); return false;" title="<?php echo l('item_show_map'); ?>"><?php echo htmlspecialchars($ad['geo']); ?></a>
<?php } else { ?>
<b><?php echo htmlspecialchars($ad['geo']); ?></b>
<?php } ?>
<span>|</span>
<div class="item-time"><?php echo l('item_added_at'); ?> <?php displayTime($ad['time']); ?></div>
<span>|</span>
<div class="item-time"><?php echo l('my_items_views'); ?> <?php echo $ad['views']; ?></div>
</div>
<div class="item-id"><?php echo l('item_number'); ?> <?php echo $ad['aid']; ?></div>
<?php if($ad['active']=='1' || $_SESSION['userid']==$ad['userid']){ ?>
<?php $get_services=mysql_query("SELECT * FROM `services` WHERE `active`='1' ORDER BY `sort` ASC;"); ?>
<?php if(mysql_num_rows($get_services)){ ?>
<div class="item-services">
<?php while($service=mysql_fetch_assoc($get_services)){ ?>
<a class="item-service" href="<?php echo $langPrefix; ?>/pay/upgrade/?ids=<?php echo $ad['aid']; ?>&selected=<?php echo $service['sid']; ?>&ref=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">
<img src="/images/services/<?php echo $service['sid']; ?>.png">
<?php echo $service['action_title_'.$config['lang']]; ?>
</a>
<?php } ?>
</div>
<?php } ?>
<?php } ?>
<div class="photo-gallery unselectable">
<?php if($ad['active']=='1' || $_SESSION['userid']==$ad['userid']){ ?>
<a href="javascript:void(0);" class="photo-gallery-favorite<?php if($ad['favorite']){ ?> photo-gallery-favorite-active<?php } ?> favorite-link" data-id="<?php echo $ad['aid']; ?>">
<span class="add-favorite-<?php echo $ad['aid']; ?><?php if($ad['favorite']){ ?> hidden<?php } ?>"><?php echo l('item_add_to_favorites_short'); ?></span>
<span class="delete-favorite-<?php echo $ad['aid']; ?><?php if(!$ad['favorite']){ ?> hidden<?php } ?>"><?php echo l('item_remove_from_favorites_short'); ?></span>
</a>
<?php } ?>
<?php if(count($ad['photos'])>0){ ?>
<?php
$photo=reset($ad['photos']);
?>
<a href="javascript:void(0);" target="_blank" class="photo-gallery-opener" onclick="showOverlayGalleryItem('<?php echo $photo['key']; ?>'); return false;">
<i class="fa fa-search-plus"></i>
<span><?php echo l('item_photo_gallery_opener'); ?></span>
</a>
<div class="photo-gallery-item current-photo">
<img itemprop="image" src="/image/650x450/<?php echo $photo['apid']; ?>/<?php echo $photo['key']; ?>.jpg?rev=<?php echo $photo['rev']; ?>&contain=1" alt="<?php echo htmlspecialchars($ad['title'])." - ".$ad['geo']; ?>" title="<?php echo htmlspecialchars($ad['title'])." - ".$ad['geo']; ?>" onclick="showOverlayGalleryItem('<?php echo $photo['key']; ?>'); return false;" onload="setTimeout(function(){ $('.other-photos').html(other_photos); $('.thumbs-container').html(thumb_photos); }, 1000);">
</div>
<?php } else { ?>
<div class="photo-gallery-item current-photo">
<img itemprop="image" src="/images/no-photos.png" style="margin:40px 0;cursor:default;" alt="<?php echo l('item_no_photos'); ?>" title="<?php echo l('item_no_photos'); ?>">
</div>
<?php } ?>
</div>
<div class="row item-params">
<?php if($current_cat['private_business']==1){ ?>
<div class="col-md-6 item-param">
<span><?php echo l('item_private_business'); ?></span>
<a href="<?php echo $langPrefix; ?>/<?php echo $ad['city']['url']; ?>/<?php echo implode('/', array_reverse($selected_cats_urls)); ?>/?search[private_business]=<?php echo $ad['private_business']; ?>"><?php echo $current_cat[$ad['private_business'].'_name_'.$config['lang']]; ?></a>
</div>
<?php } ?>
<?php if($current_cat['offer_seek']==1){ ?>
<div class="col-md-6 item-param">
<span><?php echo l('item_offer_seek'); ?></span>
<a href="<?php echo $langPrefix; ?>/<?php echo $ad['city']['url']; ?>/<?php echo implode('/', array_reverse($selected_cats_urls)); ?>/?search[offer_seek]=<?php echo $ad['offer_seek']; ?>"><?php echo $current_cat[$ad['offer_seek'].'_name_adding_'.$config['lang']]; ?></a>
</div>
<?php } ?>
<?php
$get_parameters=mysql_query("SELECT `category_parameter_sort`.`sort`, `category_parameters`.* FROM `category_parameters`, `category_parameter_sort` WHERE `category_parameter_sort`.`key`=`category_parameters`.`key` AND `category_parameter_sort`.`cid`=".$ad['category_id']." AND FIND_IN_SET(".$ad['category_id'].", `category_parameters`.`cids`) AND `category_parameters`.`type`!='hidden' AND `category_parameters`.`active`='1' AND `category_parameters`.`key` NOT LIKE '%price%' ORDER BY `category_parameter_sort`.`sort` ASC;");
while($param=mysql_fetch_assoc($get_parameters)){
if(trim($ad['params'][$param['name']])!=''){
$param['values']=array();
$get_values=mysql_query("SELECT * FROM `category_parameter_values` WHERE (`keys` LIKE '".$param['key'].",%' OR `keys` LIKE '%,".$param['key'].",%' OR `keys` LIKE '%,".$param['key']."' OR `keys`='".$param['key']."') AND FIND_IN_SET(".$ad['category_id'].", `cids`) ORDER BY `sort` ASC;");
while($value=mysql_fetch_assoc($get_values)){
$param['values'][$value['key']]=$value['value_'.$config['lang']];
}
?>
<div class="col-md-6 item-param">
<span><?php echo $param['label_'.$config['lang']]; ?>:</span>
<?php if($param['type']=='date'){ ?>
<?php displayDate($ad['params'][$param['name']]); ?>
<?php } ?>
<?php if($param['type']=='input'){ ?>
<?php echo ((strpos($param['name'], 'year')===false)?number_format($ad['params'][$param['name']], 0, '.', ' '):$ad['params'][$param['name']]); ?><?php if($param['suffix_'.$config['lang']]!=''){ echo " ".$param['suffix_'.$config['lang']]; } ?>
<?php } ?>
<?php if($param['type']=='select'){ ?>
<?php if($param['has_searching_form']=='0'){ ?>
<?php echo $param['values'][$ad['params'][$param['name']]]; ?>
<?php } else { ?>
<a href="<?php echo $langPrefix; ?>/<?php echo $ad['city']['url']; ?>/<?php echo implode('/', array_reverse($selected_cats_urls)); ?>/?search[<?php echo $param['url_key']; ?>]=<?php echo _F(htmlspecialchars($ad['params'][$param['name']])); ?>"><?php echo $param['values'][$ad['params'][$param['name']]]; ?></a>
<?php } ?>
<?php } ?>
<?php if($param['type']=='checkboxes'){ ?>
<?php
$cbi=1;
$cbs=explode(',', $ad['params'][$param['name']]);
foreach($cbs as $cbval){
?>
<?php if($param['has_searching_form']=='0'){ ?>
<?php echo $param['values'][$cbval]; ?><?php if(count($cbs)>$cbi){ echo ', '; } ?>
<?php } else { ?>
<a href="<?php echo $langPrefix; ?>/<?php echo $ad['city']['url']; ?>/<?php echo implode('/', array_reverse($selected_cats_urls)); ?>/?search[<?php echo $param['url_key']; ?>]=<?php echo htmlspecialchars($cbval); ?>"><?php echo $param['values'][$cbval]; ?></a><?php if(count($cbs)>$cbi){ echo ', '; } ?>
<?php } ?>
<?php
$cbi++;
}
?>
<?php } ?>
</div>
<?php
}
}
?>
</div>
<div class="suka"></div>
<div class="item-description" itemprop="description">
<?php echo nl2p(htmlspecialchars($ad['description'])); ?>
</div>
<?php if(count($ad['photos'])>1){ ?>
<div class="other-photos">
<div class="other-photos-loader">
<i class="fa fa-circle-o-notch fa-spin"></i>
</div>
<script type="text/javascript">
var other_photos='<?php
unset($ad['photos'][reset(array_keys($ad['photos']))]);
foreach($ad['photos'] as $photo){?><div class="photo-gallery unselectable"><div class="photo-gallery-item current-photo"><img src="/image/650x450/<?php echo $photo['apid']; ?>/<?php echo $photo['key']; ?>.jpg?rev=<?php echo $photo['rev']; ?>&contain=1" alt="<?php echo _F(htmlspecialchars($ad['title'])." - ".$ad['geo']); ?>" title="<?php echo _F(htmlspecialchars($ad['title'])." - ".$ad['geo']); ?>" onclick="showOverlayGalleryItem(\'<?php echo $photo['key']; ?>\'); return false;"></div></div><?php
}
?>';
</script>
</div>
<?php } ?>
<hr>
<script src="//yastatic.net/es5-shims/0.0.2/es5-shims.min.js"></script>
<script src="//yastatic.net/share2/share.js"></script>
<div class="ya-share2" data-services="vkontakte,facebook,odnoklassniki,moimir,gplus" data-counter=""></div>
<hr>
<?php if($ad['active']=='1' || $_SESSION['userid']==$ad['userid']){ ?>
<?php if($ad['nospam']=='0'){ ?>
<div id="uploadErrors" style="display:none;">
<div class="window-title"><?php echo l('upload_error'); ?><a href="javascript:void(0);" onclick="$.fancybox.close();"></a></div>
<div class="window-message"></div>
<div class="window-buttons">
<button class="btn btn-primary" onclick="$.fancybox.close();">OK</button>
</div>
</div>
<input type="file" class="hidden" id="uploaderButton" name="file" data-url="<?php echo $langPrefix; ?>/ajax/attach/?dmuid=<?php echo $dmuid; ?>">
<script>
var uploadedAttachments=0;
var maxAttachments=5;
var uploadErrors=[];
$(function(){
$("#attachmentUploader button").keydown(function(e){
if(e.keyCode===13){
e.preventDefault();
}
});
$('#uploaderButton').fileupload({
dataType: 'json',
change:function (e, data){
$('.add-attachment-button').find('i.fa-spinner').removeClass('hidden');
$('.add-attachment-button').find('i.fa-cloud-upload').addClass('hidden');
$('.add-attachment-button').addClass('upload-working');
uploadErrors=[];
},
done:function (e, data){
var item=data.result;
if(item.status=='success'){
uploadedAttachments=uploadedAttachments+1;
var new_attachment=$('#attachmentUploaderTemplate').clone();
new_attachment.removeAttr('id');
new_attachment.removeClass('hidden');
new_attachment.find('.attachment-title').html('<b>'+item.original+'</b>, '+item.size);
new_attachment.find('.file-icon').attr('data-type', item.type);
new_attachment.find('.delete-attachment').click(function(){
deleteUploadedAttachment($('#dmuid').val(), item.key);
});
new_attachment.attr('id', 'attachment_'+item.key);
var oldh=$(document).outerHeight();
$('#attachmentUploader').append(new_attachment);
var newh=$(document).outerHeight();
$(window).scrollTop($(window).scrollTop()+(newh-oldh));
if(uploadedAttachments==5){
$('.add-attachment-button button').prop('disabled', true);
}
} else {
uploadErrors.push('<p><b>'+item.original+':</b><br>'+item.errors.file+'</p>');
}
$('.add-attachment-button').find('i.fa-spinner').addClass('hidden');
$('.add-attachment-button').find('i.fa-cloud-upload').removeClass('hidden');
$('.add-attachment-button').removeClass('upload-working');
},
stop:function (e, data){
if(uploadErrors.length>0){
$('#uploadErrors .window-message').html(uploadErrors.join(''));
$.fancybox({'type':'inline', 'href':'#uploadErrors', 'closeBtn':false, helpers:{overlay:{locked:false}}});
}
}
});
});
</script>
<div class="item-post-message-title"><?php echo l('item_contact_author_title'); ?></div>
<div class="item-post-message">
<?php if(!isset($_SESSION['userid'])){ ?>
<?php echo str_replace('[REF]', urlencode($_SERVER['REQUEST_URI']), l('item_contact_login')); ?>
<?php } else { ?>
<form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="POST" autocomplete="off" class="ajax-form" data-callback="messageCallBack">
<input type="hidden" name="action" value="message">
<input type="hidden" name="dmuid" id="dmuid" value="<?php echo $dmuid; ?>">
<div>
<?php foreach($contact_methods as $k=>$v){ ?>
<?php if(trim($ad[$k])!=''){ ?>
<div class="form-group contact-method">
<div class="item-contact-button contact-<?php echo $k; ?> contact-gray-<?php echo $k; ?>" data-type="<?php echo $k; ?>" title="<?php echo $v; ?>">
<span><?php echo htmlspecialchars(substr_replace(trim($ad[$k]), str_repeat('x', strlen(trim($ad[$k]))-2), 2, strlen(trim($ad[$k]))-2)); ?></span>
<a href="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>"><?php echo l('item_show_contact_info'); ?></a>
</div>
</div>
<?php } ?>
<?php } ?>
</div>
<div class="form-group">
<textarea name="text" class="form-control" rows="5" placeholder="<?php echo l('item_contact_text'); ?>" id="post-message-text"></textarea>
</div>
<div class="form-group">
<div class="added-attachment hidden" id="attachmentUploaderTemplate"><div class="file-icon"></div><div class="attachment-title"></div><a href="javascript:void(0);" class="delete-attachment"><?php echo l('remove_attachment'); ?></a></div>
<div id="attachmentUploader"></div>
</div>
<div class="form-group" style="margin-bottom:0;">
<table width="100%">
<tr>
<td valign="top">
<div>
<div class="add-attachment-button" id="add_attachment_button" title="<?php echo l('add_attachment'); ?>">
<button type="button" onclick="$('#uploaderButton').click(); return false;"><i class="fa fa-cloud-upload"></i><i class="fa fa-spinner fa-spin hidden"></i></button>
</div>
</div>
</td>
<td valign="top" width="40%" align="right">
<button type="submit" class="btn btn-primary" style="margin:0;"><?php echo l('item_contact_submit'); ?></button>
</td>
</tr>
</table>
</div>
</form>
<?php } ?>
</div>
<div id="post-message-anchor"></div>
<?php } ?>
<?php } ?>
<?php if($ad['active']=='1' || $_SESSION['userid']==$ad['userid']){ ?>
<?php if($ad['user']['hidesimilar']==0 && mysql_num_rows($get_other_ads)){ ?>
<div class="other-ads">
<div class="other-title">
<?php echo str_replace('[USERNAME]', htmlspecialchars($ad['person']), l('item_other_items_of_author')); ?>
<div class="other-all"><a href="<?php echo $langPrefix; ?>/list/?search[user_id]=<?php echo $ad['userid']; ?>"><?php echo l('item_all_items_of_author'); ?></a></div>
</div>
<div class="other-list">
<?php while($oad=mysql_fetch_assoc($get_other_ads)){ ?>
<?php
$oad=getAd($oad['aid']);
$get_o_cat=mysql_query("SELECT * FROM `categories` WHERE `id`='".$oad['category_id']."';");
if(mysql_num_rows($get_o_cat)){
$ocat=mysql_fetch_assoc($get_o_cat);
}
?>
<div class="other-list-item">
<table>
<tr>
<td width="100"align="center" class="other-list-item-photo-container">
<?php if(count($oad['photos'])>0){ ?>
<?php $photo=reset($oad['photos']); ?>
<a href="<?php echo adurl($oad); ?>">
<img src="/image/261x203/<?php echo $photo['apid']; ?>/<?php echo $photo['key']; ?>.jpg?rev=<?php echo $photo['rev']; ?>&contain=1">
</a>
<?php } else { ?>
<a href="<?php echo adurl($oad); ?>">
<img src="/images/no-photos.png">
</a>
<?php } ?>
</td>
<td width="10"></td>
<td>
<a class="other-list-item-title" href="<?php echo adurl($oad); ?>"><?php echo htmlspecialchars($oad['title']); ?></a>
<div class="other-list-item-category"><?php echo htmlspecialchars($ocat['name_'.$config['lang']]); ?></div>
<div class="other-list-item-time"><?php displayTime($oad['time']); ?></div>
</td>
<td width="10"></td>
<td align="right" style="max-width:100px;">
<div class="other-list-item-price"><nobr><?php echo $oad['display_price']; ?></nobr></div>
</td>
</tr>
</table>
</div>
<?php } ?>
</div>
</div>
<?php } ?>
<?php } ?>
<!--
<hr>
<center>
<a href="<?php echo $langPrefix; ?>/add/?category_id=<?php echo $ad['category_id']; ?>" class="btn btn-large btn-success">Подать объявление в категории <?php echo htmlspecialchars(reset($selected_cats)); ?></a>
</center>
-->
<?php renderBanner('item_bottom'); ?>
</div>
</div>

</div>

<div id="complaintWindow" style="display:none;">
<div class="window-title"><?php echo l('item_complaint_title'); ?><a href="javascript:void(0);" onclick="$.fancybox.close();"></a></div>
<div class="window-message" style="width:500px;">
<form action="<?php echo adurl($ad); ?>" method="POST" autocomplete="off" class="form ajax-form ajax-form-no-scrolling" id="complaintForm" data-callback="complaintCallBack">
<input type="hidden" name="action" value="complaint">
<?php foreach($config['complaint_types'] as $ctid=>$ctitle){ ?>
<div class="form-group">
<div class="radio" style="display:inline;width:auto;">
<input type="radio" name="ctid" id="complaint_type_<?php echo $ctid; ?>" value="<?php echo $ctid; ?>" onclick="$('#complaintTextarea').focus();">
<label for="complaint_type_<?php echo $ctid; ?>"><?php echo htmlspecialchars($ctitle); ?></label>
</div>
</div>
<?php } ?>
<div class="form-group">
<textarea name="description" class="form-control" rows="3" placeholder="<?php echo l('item_complaint_text'); ?>" id="complaintTextarea"></textarea>
<div class="chars-left" data-maxlength="500"><?php echo str_replace('[COUNT]', '500', l('item_complaint_chars')); ?></div>
</div>
</form>
</div>
<div class="window-buttons">
<button class="btn btn-primary" onclick="$('#complaintForm').submit();"><?php echo l('item_complaint_submit'); ?></button>
</div>
</div>

<?php if($ad['coordinates']){ ?>
<div id="mapWindow" style="display:none;">
<div class="window-title"><?php echo l('item_map_title'); ?><a href="javascript:void(0);" onclick="$.fancybox.close();"></a></div>
<div id="item-map"></div>
</div>
<script type="text/javascript">
function initMap(){
var map=new google.maps.Map(document.getElementById('item-map'), {
center:{lat:<?php echo $ad['coordinates']['lat']; ?>, lng:<?php echo $ad['coordinates']['lng']; ?>},
scrollwheel:false,
zoom:11
});
}
</script>
<?php } ?>

<script type="text/javascript">
$(function(){
$("#item_side_bar").parent().stick_in_parent({offset_top:10});
});
</script>

<?php } else { ?>
<?php
$photos=array_values($ad['photos']);
?>
<img src="/images/logo.png" style="width:200px;">
<div class="item-title" style="margin-top:20px;"><?php echo htmlspecialchars($ad['title']); ?></div>
<div style="font-size:12px;color:#666;margin-top:5px;"><b><?php echo htmlspecialchars($ad['geo']); ?></b> | <?php echo l('item_added_at'); ?> <?php displayTime($ad['time']); ?></div>
<div style="font-size:11px;color:#666;margin-top:5px;"><?php echo l('item_number'); ?> <?php echo $ad['aid']; ?></div>
<?php if(isset($photos[0])){ ?>
<div style="text-align:center;background-color:#f7f6f6;margin-top:10px;">
<img src="/image/650x450/<?php echo $photos[0]['apid']; ?>/<?php echo $photos[0]['key']; ?>.jpg?rev=<?php echo $photos[0]['rev']; ?>&contain=1" style="max-width:100%;">
</div>
<?php } ?>
<div style="margin-top:20px;font-size:12px;">
<table style="max-width:100%;">
<tr>
<td style="padding:5px 10px;background:#eee;white-space:nowrap;color:#666;" align="right"><b><?php echo l('item_contact_person'); ?></b></td>
<td style="padding:5px 10px;"><?php echo htmlspecialchars($ad['person']); ?></td>
</tr>
<?php foreach($contact_methods as $k=>$v){ ?>
<?php if(trim($ad[$k])!=''){ ?>
<tr>
<td style="padding:5px 10px;background:#eee;white-space:nowrap;color:#666;" align="right"><b><?php echo $v; ?>:</b></td>
<td style="padding:5px 10px;"><?php echo htmlspecialchars($ad[$k]); ?></td>
</tr>
<?php } ?>
<?php } ?>
<tr>
<td colspan="2"><br></td>
</tr>
<tr>
<td style="padding:5px 10px;background:#eee;white-space:nowrap;color:#666;" align="right"><b><?php echo l('item_category'); ?></b></td>
<td style="padding:5px 10px;"><?php echo implode(' &raquo; ', array_reverse($selected_cats)); ?></td>
</tr>
<?php if($current_cat['private_business']==1){ ?>
<tr>
<td style="padding:5px 10px;background:#eee;white-space:nowrap;color:#666;" align="right"><b><?php echo l('item_private_business'); ?></b></td>
<td style="padding:5px 10px;"><?php echo $current_cat[$ad['private_business'].'_name_'.$config['lang']]; ?></td>
</tr>
<?php } ?>
<?php if($current_cat['offer_seek']==1){ ?>
<tr>
<td style="padding:5px 10px;background:#eee;white-space:nowrap;color:#666;" align="right"><b><?php echo l('item_offer_seek'); ?></b></td>
<td style="padding:5px 10px;"><?php echo $current_cat[$ad['offer_seek'].'_name_adding_'.$config['lang']]; ?></td>
</tr>
<?php } ?>
<?php
$get_parameters=mysql_query("SELECT `category_parameter_sort`.`sort`, `category_parameters`.* FROM `category_parameters`, `category_parameter_sort` WHERE `category_parameter_sort`.`key`=`category_parameters`.`key` AND `category_parameter_sort`.`cid`=".$ad['category_id']." AND FIND_IN_SET(".$ad['category_id'].", `category_parameters`.`cids`) AND `category_parameters`.`type`!='hidden' AND `category_parameters`.`active`='1' AND `category_parameters`.`key` NOT LIKE '%price%' ORDER BY `category_parameter_sort`.`sort` ASC;");
while($param=mysql_fetch_assoc($get_parameters)){
if(trim($ad['params'][$param['name']])!=''){
$param['values']=array();
$get_values=mysql_query("SELECT * FROM `category_parameter_values` WHERE (`keys` LIKE '".$param['key'].",%' OR `keys` LIKE '%,".$param['key'].",%' OR `keys` LIKE '%,".$param['key']."' OR `keys`='".$param['key']."') AND FIND_IN_SET(".$ad['category_id'].", `cids`) ORDER BY `sort` ASC;");
while($value=mysql_fetch_assoc($get_values)){
$param['values'][$value['key']]=$value['value_'.$config['lang']];
}
?>
<tr>
<td style="padding:5px 10px;background:#eee;white-space:nowrap;color:#666;" align="right"><b><?php echo $param['label_'.$config['lang']]; ?>:</b></td>
<td style="padding:5px 10px;">
<?php if($param['type']=='date'){ ?>
<?php displayDate($ad['params'][$param['name']]); ?>
<?php } ?>
<?php if($param['type']=='input'){ ?>
<?php echo ((strpos($param['name'], 'year')===false)?number_format($ad['params'][$param['name']], 0, '.', ' '):$ad['params'][$param['name']]); ?><?php if($param['suffix_'.$config['lang']]!=''){ echo " ".$param['suffix_'.$config['lang']]; } ?>
<?php } ?>
<?php if($param['type']=='select'){ ?>
<?php echo $param['values'][$ad['params'][$param['name']]]; ?>
<?php } ?>
<?php if($param['type']=='checkboxes'){ ?>
<?php
$cbi=1;
$cbs=explode(',', $ad['params'][$param['name']]);
foreach($cbs as $cbval){
?>
<?php echo $param['values'][$cbval]; ?><?php if(count($cbs)>$cbi){ echo ', '; } ?>
<?php
$cbi++;
}
?>
<?php } ?>
</td>
</tr>
<?php
}
}
?>
<tr>
<td colspan="2"><br></td>
</tr>
<tr>
<td style="padding:5px 10px;background:#eee;white-space:nowrap;color:#666;" align="right"><b><?php if($ad['salary_from']>0 || $ad['salary_to']>0){ ?><?php echo l('salary'); ?><?php } else { ?><?php echo l('price'); ?><?php } ?>:</b></td>
<td style="padding:5px 10px;"><?php echo $ad['display_price']; ?><?php if(isset($ad['arranged_price'])){ ?> (<?php echo mb_strtolower(l('price_arranged')); ?>)<?php } ?></td>
</tr>
</table>
</div>
<div style="margin-top:20px;margin-bottom:20px;">
<?php echo nl2p(htmlspecialchars($ad['description'])); ?>
</div>
<?php
unset($photos[0]);
foreach($photos as $photo){?>
<div style="text-align:center;background-color:#f7f6f6;margin-top:10px;">
<img src="/image/650x450/<?php echo $photo['apid']; ?>/<?php echo $photo['key']; ?>.jpg?rev=<?php echo $photo['rev']; ?>&contain=1" style="max-width:100%;">
</div>
<?php
}
?>
<br>
<script type="text/javascript">
window.print();
</script>
<?php } ?>

<?php include "includes/footer.php"; ?>