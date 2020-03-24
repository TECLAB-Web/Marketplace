<?php include_once "init.php"; ?>
<?php
if(trim($_GET['ref'])==''){
$_GET['ref']='/my/';
}
?>
<?php
$edit=false;
if(trim($_GET['controller'])=='edit'){
if(!isset($_SESSION['userid'])){
header("Location: ".$langPrefix."/login/?ref=".urlencode($_SERVER['REQUEST_URI']));
exit;
}
$ad=getAd(intval($_GET['id']));
if(!$ad){
header("HTTP/1.0 404 Not Found");
include "404.php";
exit;
}
//var_dump($ad); exit;
$aid=intval($_GET['id']);
$edit=true;
if(intval($my['admin'])!=1 && $_SESSION['userid']!=$ad['userid']){
include "forbidden.php";
exit;
}
}
?>
<?php
$selectedCategory=0;
$uploadedPhotos=0;
if($edit){
$uploadedPhotos=count($ad['photos']);
}
$maxPhotos=8;
if(intval($_GET['category_id'])>0){
$selectedCategory=intval($_GET['category_id']);
}
if(intval($ad['category_id'])>0){
$selectedCategory=intval($ad['category_id']);
}
if(intval($_POST['category_id'])>0){
$selectedCategory=intval($_POST['category_id']);
}
if($selectedCategory>0){
$selected_cats=array();
$selected_cats_ids=array();
$selected_cats_parents=array();
$main_cat_id=0;
$get_cat=mysql_query("SELECT * FROM `categories` WHERE `id`='".$selectedCategory."' AND `active`='1';");
if(mysql_num_rows($get_cat)){
$cat=mysql_fetch_assoc($get_cat);
$current_cat=$cat;
$selected_cats[]=$cat['name_'.$config['lang']];
$selected_cats_ids[]=$cat['id'];
$selected_cats_parents[]=$cat['parent_id'];
$category_id=$main_cat_id=$cat['id'];
$maxPhotos=$cat['max_photos'];
$get_cat=mysql_query("SELECT * FROM `categories` WHERE `id`='".$cat['parent_id']."' AND `active`='1';");
if(mysql_num_rows($get_cat)){
$cat=mysql_fetch_assoc($get_cat);
$selected_cats[]=$cat['name_'.$config['lang']];
$selected_cats_ids[]=$cat['id'];
$selected_cats_parents[]=$cat['parent_id'];
$parent_category_id=$main_cat_id=$cat['id'];
$get_cat=mysql_query("SELECT * FROM `categories` WHERE `id`='".$cat['parent_id']."' AND `active`='1';");
if(mysql_num_rows($get_cat)){
$cat=mysql_fetch_assoc($get_cat);
$selected_cats[]=$cat['name_'.$config['lang']];
$selected_cats_ids[]=$cat['id'];
$selected_cats_parents[]=0;
$parent_parent_category_id=$main_cat_id=$cat['id'];
}
}
} else {
$selectedCategory=0;
}
} else {
$selected_cats_ids=array(0);
$selected_cats_parents=array(0);
}
?>
<?php
if(trim($_GET['mode'])=='ajax'){
if(trim($_POST['action'])=='parameters'){
if($m){
include "includes/m-parameters.php";
} else {
include "includes/parameters.php";
}
exit;
}
if(trim($_POST['action'])=='categories'){
include "includes/categories.php";
exit;
}
header('Content-Type: application/json; charset=utf-8');
if(trim($_POST['action'])==htmlspecialchars(trim($_GET['controller']))){
$result=array();
$ad_params=array();
$result['errors']=array();
if(trim($_POST['title'])==''){
$result['errors']['title']=l('add_error_title_empty');
} elseif(mb_strlen(trim($_POST['title']))<5){
$result['errors']['title']=l('add_error_title_short');
} elseif(mb_strlen(trim($_POST['title']))>70){
$result['errors']['title']=l('add_error_title_long');
}
if(!mysql_num_rows(mysql_query("SELECT * FROM `categories` WHERE `id`='".$selectedCategory."' AND `active`='1';"))){
$result['errors']['category_id']=l('add_error_category_empty');
}
if((trim($_POST['param_price'])=='price' || trim($_POST['param_price'])=='arranged') && intval($_POST['price'])==0){
$result['errors']['price']=l('add_error_price_empty');
} elseif((trim($_POST['param_price'])=='price' || trim($_POST['param_price'])=='arranged') && intval($_POST['price'])<0){
$result['errors']['price']=l('add_error_price_negative');
}
if(isset($_POST['salary_from']) && isset($_POST['salary_to'])){
if(intval($_POST['salary_from'])==0 && intval($_POST['salary_to'])==0){
$result['errors']['salary_from']=l('add_error_salary_empty');
} elseif(intval($_POST['salary_from'])<0){
$result['errors']['salary_from']=l('add_error_salary_negative');
} elseif(intval($_POST['salary_to'])<0){
$result['errors']['salary_to']=l('add_error_salary_negative');
} elseif(intval($_POST['salary_to'])<intval($_POST['salary_from'])){
$result['errors']['salary_to']=l('add_error_salary_min_more_than_max');
}
}
$get_parameters=mysql_query("SELECT `category_parameter_sort`.`sort`, `category_parameters`.* FROM `category_parameters`, `category_parameter_sort` WHERE `category_parameter_sort`.`key`=`category_parameters`.`key` AND `category_parameter_sort`.`cid`=".$selectedCategory." AND FIND_IN_SET(".$selectedCategory.", `category_parameters`.`cids`) AND `category_parameters`.`type`!='hidden' AND `category_parameters`.`active`='1' ORDER BY `category_parameter_sort`.`sort` ASC;");
while($param=mysql_fetch_assoc($get_parameters)){
$param['validators']=unserialize($param['validators']);
$param['values']=array();
$get_values=mysql_query("SELECT * FROM `category_parameter_values` WHERE (`keys` LIKE '".$param['key'].",%' OR `keys` LIKE '%,".$param['key'].",%' OR `keys` LIKE '%,".$param['key']."' OR `keys`='".$param['key']."') AND FIND_IN_SET(".$selectedCategory.", `cids`) AND `active`='1' ORDER BY `sort` ASC;");
while($value=mysql_fetch_assoc($get_values)){
$param['values'][$value['key']]=$value['value_'.$config['lang']];
}
if(isset($param['validators']['required']) && (($param['type']!='date' && trim($_POST[$param['post_key']])=='') || ($param['type']=='date' && trim(implode('', $_POST[$param['post_key']]))==''))){
if($param['type']=='select'){
$result['errors'][$param['post_key']]=l('add_error_param_select_option');
} else {
$result['errors'][$param['post_key']]=l('add_error_param_input_required').(($param['suffix_'.$config['lang']]!='')?' (в <b>'.$param['suffix_'.$config['lang']].'</b>)':'').'.';
}
} elseif($param['type']=='select' && trim($_POST[$param['post_key']])!='' && !isset($param['values'][trim($_POST[$param['post_key']])])){
$result['errors'][$param['post_key']]=l('add_error_param_select_incorrect_option');
} elseif($param['type']=='input' && isset($param['validators']['digits']) && trim($_POST[$param['post_key']])!='' && !ctype_digit(trim($_POST[$param['post_key']]))){
$result['errors'][$param['post_key']]=l('add_error_param_input_must_be_digital');
} elseif($param['type']=='input' && isset($param['validators']['minlength']) && trim($_POST[$param['post_key']])!='' && mb_strlen(trim($_POST[$param['post_key']]))<intval($param['validators']['minlength'])){
$result['errors'][$param['post_key']]=str_replace('[COUNT]', $param['validators']['minlength'], l('add_error_param_input_too_short'));
} elseif($param['type']=='input' && isset($param['validators']['min']) && trim($_POST[$param['post_key']])!='' && intval($_POST[$param['post_key']])<intval($param['validators']['min'])){
$result['errors'][$param['post_key']]=str_replace('[COUNT]', $param['validators']['min'].''.(($param['suffix_'.$config['lang']]!='')?' '.$param['suffix_'.$config['lang']]:''), l('add_error_param_input_too_small'));
} elseif($param['type']=='input' && isset($param['validators']['maxlength']) && trim($_POST[$param['post_key']])!='' && mb_strlen(trim($_POST[$param['post_key']]))>intval($param['validators']['maxlength'])){
$result['errors'][$param['post_key']]=str_replace('[COUNT]', $param['validators']['maxlength'], l('add_error_param_input_too_long'));
} elseif($param['type']=='input' && isset($param['validators']['max']) && trim($_POST[$param['post_key']])!='' && intval($_POST[$param['post_key']])>intval($param['validators']['max'])){
$result['errors'][$param['post_key']]=str_replace('[COUNT]', $param['validators']['min'].''.(($param['suffix_'.$config['lang']]!='')?' '.$param['suffix_'.$config['lang']]:''), l('add_error_param_input_too_big'));
} elseif($param['type']=='date' && !(checkdate(intval($_POST[$param['post_key']]['month']), intval($_POST[$param['post_key']]['day']), intval($_POST[$param['post_key']]['year'])) && strlen(trim($_POST[$param['post_key']]['year']))==4)){
$result['errors'][$param['post_key']]=l('add_error_param_date_incorrect');
} else {
if(is_array($_POST[$param['post_key']]) && $param['type']=='date'){
$ad_params[$param['name']]=strtotime($_POST[$param['post_key']]['year'].'-'.$_POST[$param['post_key']]['month'].'-'.$_POST[$param['post_key']]['day']);
} elseif(is_array($_POST[$param['post_key']])){
$ad_params[$param['name']]=trim(implode(',', $_POST[$param['post_key']]));
} else {
$ad_params[$param['name']]=trim($_POST[$param['post_key']]);
}
}
}
if($current_cat['private_business']==1 && trim($_POST['private_business'])==''){
$result['errors']['private_business']=l('add_error_param_select_option');
} elseif($current_cat['private_business']==1 && !in_array(trim($_POST['private_business']), array('private', 'business'))){
$result['errors']['private_business']=l('add_error_param_select_incorrect_option');
}
if($current_cat['offer_seek']==1 && trim($_POST['offer_seek'])==''){
$result['errors']['offer_seek']=l('add_error_param_select_option');
} elseif($current_cat['offer_seek']==1 && !in_array(trim($_POST['offer_seek']), array('offer', 'seek'))){
$result['errors']['offer_seek']=l('add_error_param_select_incorrect_option');
}
if(trim($_POST['description'])==''){
$result['errors']['description']=l('add_error_description_empty');
} elseif(mb_strlen(trim($_POST['description']))<10){
$result['errors']['description']=l('add_error_description_short');
} elseif(mb_strlen(trim($_POST['description']))>4096){
$result['errors']['description']=l('add_error_description_long');
}
if($m){
if(!mysql_num_rows(mysql_query("SELECT * FROM `regions` WHERE `region_id`='".intval($_POST['region_id'])."';"))){
$result['errors']['region_id']=l('add_error_geo_empty');
} elseif(!mysql_num_rows(mysql_query("SELECT * FROM `cities` WHERE `city_id`='".intval($_POST['city_id'])."';"))){
$result['errors']['city_id']=l('add_error_geo_empty');
}
} else {
if(!mysql_num_rows(mysql_query("SELECT * FROM `cities` WHERE `city_id`='".intval($_POST['city_id'])."';"))){
$result['errors']['geo']=l('add_error_geo_empty');
}
}
if(trim($_POST['person'])==''){
$result['errors']['person']=l('add_error_person_empty');
} elseif(mb_strlen(trim($_POST['person']))>32){
$result['errors']['person']=l('add_error_person_long');
}
if(isset($_POST['nospam']) && trim($_POST['phone'])=='' && trim($_POST['gg'])=='' && trim($_POST['skype'])==''){
$result['errors']['phone']=l('add_error_if_noemail');
$nospam_error=true;
}
if(trim($_POST['phone'])!='' && (!ctype_digit(ltrim(trim($_POST['phone']), '+')) || mb_strlen(ltrim(trim($_POST['phone']), '+'))<7)){
$result['errors']['phone']=l('add_error_phone_incorrect');
} elseif(mb_strlen(ltrim(trim($_POST['phone']), '+'))>32){
$result['errors']['phone']=l('add_error_phone_long');
}
if(mb_strlen(trim($_POST['gg']))>32){
$result['errors']['gg']=l('add_error_gg_long');
}
if(mb_strlen(trim($_POST['skype']))>32){
$result['errors']['skype']=l('add_error_skype_long');
}
if(!isset($_SESSION['userid'])){
if(trim($_POST['email'])==''){
$result['errors']['email']=l('add_error_email_empty');
if(isset($nospam_error)){ unset($result['errors']['phone']); }
} elseif(!preg_match("/^([-a-zA-Z0-9._]+@[-a-zA-Z0-9.]+(\.[-a-zA-Z0-9]+)+)*$/", trim($_POST['email']))){
$result['errors']['email']=l('add_error_email_invalid');
if(isset($nospam_error)){ unset($result['errors']['phone']); }
} elseif(mysql_num_rows(mysql_query("SELECT * FROM `users` WHERE `email`='"._F($_POST['email'])."';"))){
$result['errors']['email']=l('add_error_email_exists');
if(isset($nospam_error)){ unset($result['errors']['phone']); }
}
if(trim($_POST['password'])==''){
$result['errors']['password']=l('add_error_password_empty');
} elseif(mb_strlen(trim($_POST['password']))<6){
$result['errors']['password']=l('add_error_password_short');
}
if(trim($_POST['cpassword'])==''){
$result['errors']['cpassword']=l('add_error_cpassword_empty');
} elseif(mb_strlen(trim($_POST['password']))>=6 && trim($_POST['cpassword'])!=trim($_POST['password'])){
$result['errors']['cpassword']=l('add_error_cpassword_not_equals');
}
if(!isset($_POST['agree'])){
$result['errors']['agree']=l('add_error_agree_off');
}
}
if(count($result['errors'])==0){
if(isset($_SESSION['userid'])){
$userid=$_SESSION['userid'];
mysql_query("UPDATE `users` SET `city_id`='".intval($_POST['city_id'])."', `person`='"._F($_POST['person'])."', `phone`='"._F($_POST['phone'])."', `gg`='"._F($_POST['gg'])."', `skype`='"._F($_POST['skype'])."', `nospam`='".intval($_POST['nospam'])."' WHERE `userid`='".$_SESSION['userid']."';");
} else {
$code=md5(uniqid('').$time);
$create=mysql_query("INSERT INTO `users` SET `email`='"._F($_POST['email'])."', `register_code`='".$code."', `register_password`='".md5(trim($_POST['password']))."', `city_id`='".intval($_POST['city_id'])."', `person`='"._F($_POST['person'])."', `phone`='"._F($_POST['phone'])."', `gg`='"._F($_POST['gg'])."', `skype`='"._F($_POST['skype'])."', `nospam`='".intval($_POST['nospam'])."', `time`='".$time."', `active`='0';");
$userid=mysql_insert_id();
}
$index=Words2AllForms(trim($_POST['title'])." ".trim($_POST['description']));
$base_price=0;
if(intval($_POST['salary_from'])>0 && intval($_POST['salary_to'])>0){
$base_price=intval($_POST['salary_from'])+(intval($_POST['salary_to'])-intval($_POST['salary_from']))/2;
$base_price=$base_price/$currency_rates[trim($_POST['currency'])];
} elseif(intval($_POST['salary_from'])>0){
$base_price=intval($_POST['salary_from']);
$base_price=$base_price/$currency_rates[trim($_POST['currency'])];
} elseif(intval($_POST['salary_to'])>0){
$base_price=intval($_POST['salary_to']);
$base_price=$base_price/$currency_rates[trim($_POST['currency'])];
} elseif(intval($_POST['price'])>0){
$base_price=intval($_POST['price']);
$base_price=$base_price/$currency_rates[trim($_POST['currency'])];
}
if($edit){
$update=mysql_query("UPDATE `ads` SET `parent_parent_category_id`='".$parent_parent_category_id."', `parent_category_id`='".$parent_category_id."', `category_id`='".$category_id."', `title`='"._F($_POST['title'])."', `description`='"._F($_POST['description'])."', `apid`='"._F($_POST['apid'])."', `city_id`='".intval($_POST['city_id'])."', `person`='"._F($_POST['person'])."', `phone`='"._F($_POST['phone'])."', `gg`='"._F($_POST['gg'])."', `skype`='"._F($_POST['skype'])."', `nospam`='".intval($_POST['nospam'])."', `price_type`='"._F($_POST['param_price'])."', `price`='".intval($_POST['price'])."', `salary_arranged`='"._F($_POST['salary_arranged'])."', `salary_from`='"._F($_POST['salary_from'])."', `salary_to`='"._F($_POST['salary_to'])."', `currency`='"._F($_POST['currency'])."', `base_price`='".$base_price."', `private_business`='"._F($_POST['private_business'])."', `offer_seek`='"._F($_POST['offer_seek'])."', `index`='"._F($index)."', `active`='0' WHERE `aid`='".$aid."';");
if($ad['active']==0 || $ad['active']==1){
mysql_query("UPDATE `ads` SET `time`='".$time."', `time_to`='".($time+60*60*24*$ad['days'])."' WHERE `aid`='".$aid."';");
}
mysql_query("DELETE FROM `ad_parameters` WHERE `aid`='".$aid."'");
} else {
$days=30;
$create=mysql_query("INSERT INTO `ads` SET `userid`='".$userid."', `parent_parent_category_id`='".$parent_parent_category_id."', `parent_category_id`='".$parent_category_id."', `category_id`='".$category_id."', `title`='"._F($_POST['title'])."', `description`='"._F($_POST['description'])."', `apid`='"._F($_POST['apid'])."', `city_id`='".intval($_POST['city_id'])."', `person`='"._F($_POST['person'])."', `phone`='"._F($_POST['phone'])."', `gg`='"._F($_POST['gg'])."', `skype`='"._F($_POST['skype'])."', `nospam`='".intval($_POST['nospam'])."', `price_type`='"._F($_POST['param_price'])."', `price`='".intval($_POST['price'])."', `salary_arranged`='"._F($_POST['salary_arranged'])."', `salary_from`='"._F($_POST['salary_from'])."', `salary_to`='"._F($_POST['salary_to'])."', `currency`='"._F($_POST['currency'])."', `base_price`='".$base_price."', `private_business`='"._F($_POST['private_business'])."', `offer_seek`='"._F($_POST['offer_seek'])."', `index`='"._F($index)."', `days`='".$days."', `time`='".$time."', `time_to`='".($time+60*60*24*$days)."', `active`='0';");
$aid=mysql_insert_id();
}
if(!isset($_SESSION['userid'])){
$mail=mysql_fetch_assoc(mysql_query("SELECT * FROM `mail_templates` WHERE `code`='add_register';"));
$to=trim($_POST['email']);
$mail['title']=$mail['title_'.$config['lang']];
$mail['body']=$mail['body_'.$config['lang']];
$mail['body']=str_replace('[SITE_NAME]', $config['sitename'], $mail['body']);
$mail['body']=str_replace('[SITE_URL]', $config['siteurl'], $mail['body']);
$mail['body']=str_replace('[ACTIVATION_CODE]', $code, $mail['body']);
$mail['body']=str_replace('[AID]', $aid, $mail['body']);
liam($to, $mail['title'], $mail['body'], "noreply@".$config['siteurl']);
}
foreach($ad_params as $apk=>$apv){
mysql_query("INSERT INTO `ad_parameters` SET `aid`='".$aid."', `key`='".$apk."', `values`='"._F($apv)."';");
}
$result['aid']=$aid;
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
if($edit){
$apid=$ad['apid'];
$pagetitle=l('add_title_editing');	
} else {
$apid=uniqid('');
$pagetitle=l('add_title_adding');
}
$pagedesc=$config['description'];
?>
<?php
if($m){ include "m-add.php"; exit; }
?>
<?php include "includes/header.php"; ?>

<h4 class="special-title">
<?php if($edit){ ?>
<?php echo l('add_title_editing'); ?>
<?php } else { ?>
<?php echo l('add_title_adding'); ?>
<?php } ?>
</h4>

<div id="catChoose" style="display:none;"></div>

<script>
$(function(){
$.post(langPrefix+'/ajax/add/', 'action=categories', function(response){
$('#catChoose').html(response);
});
});
</script>

<div id="uploadErrors" style="display:none;">
<div class="window-title"><?php echo l('upload_error'); ?><a href="javascript:void(0);" onclick="$.fancybox.close();"></a></div>
<div class="window-message"></div>
<div class="window-buttons">
<button class="btn btn-primary" onclick="$.fancybox.close();">OK</button>
</div>
</div>

<input type="file" class="hidden" id="uploaderButton" name="file" data-url="<?php echo $langPrefix; ?>/ajax/upload/?apid=<?php echo $apid; ?>" multiple>
<script>
var uploadedPhotos=<?php echo $uploadedPhotos; ?>;
var maxPhotos=<?php echo $maxPhotos; ?>;
var uploadErrors=[];
$(function(){
sortablePhotos($('#apid').val());
$("#photoUploader button").keydown(function(e){
if(e.keyCode===13){
e.preventDefault();
}
});
$('#uploaderButton').fileupload({
dataType: 'json',
change:function (e, data){
$('#photoUploader .add-photo-button:visible').not('.upload-working').slice(0, data.files.length).find('i').removeClass('hidden');
$('#photoUploader .add-photo-button:visible').not('.upload-working').slice(0, data.files.length).find('span').addClass('hidden');
$('#photoUploader .add-photo-button:visible').not('.upload-working').slice(0, data.files.length).addClass('upload-working');
uploadErrors=[];
},
done:function (e, data){
var item=data.result;
if(item.status=='success'){
uploadedPhotos=uploadedPhotos+1;
var new_photo=$('#photoUploaderTemplate').clone();
new_photo.removeAttr('id');
new_photo.removeClass('hidden');
new_photo.find('img').attr('src', item.file);
new_photo.find('.rotate-photo').click(function(){
rotateUploadedPhoto($('#apid').val(), item.key);
});
new_photo.find('.delete-photo').click(function(){
deleteUploadedPhoto($('#apid').val(), item.key);
});
new_photo.attr('id', 'photo_'+item.key);
$('#photoUploader .add-photo-button').eq(0).before(new_photo);
$('#photoUploader .add-photo-button.upload-working:visible').slice(0, 1).addClass('hidden');
} else {
uploadErrors.push('<p><b>'+item.original+':</b><br>'+item.errors.file+'</p>');
}
$('#photoUploader .add-photo-button.upload-working').slice(0, 1).find('i').addClass('hidden');
$('#photoUploader .add-photo-button.upload-working').slice(0, 1).find('span').removeClass('hidden');
$('#photoUploader .add-photo-button.upload-working').slice(0, 1).removeClass('upload-working');
$('#photoUploader .add-photo-button').removeClass('first');
$('#photoUploader .added-photo').removeClass('first');
$('#photoUploader .added-photo').first().addClass('first');
$('#pre_upload_info').addClass('hidden');
$('#post_upload_info').removeClass('hidden');
sortablePhotos($('#apid').val());
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

<form action="/<?php echo htmlspecialchars(trim($_GET['controller'])); ?>/<?php if($edit){ ?>?id=<?php echo $aid; ?><?php } ?>" method="POST" autocomplete="off" class="form-horizontal add-form ajax-form" data-callback="<?php if($edit){ ?>editCallBack<?php } else { ?>addCallBack<?php } ?>">
<input type="hidden" name="action" value="<?php echo htmlspecialchars(trim($_GET['controller'])); ?>">
<hr>
<div class="form-group">
<label class="col-sm-3 control-label"><?php echo l('add_title_title'); ?> <span class="input-required">*</span></label>
<div class="col-sm-7">
<input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($ad['title']); ?>" onfocus="if(!$(this).hasClass('error-input')){ $('#title_tip').removeClass('hidden'); }" onblur="$('#title_tip').addClass('hidden');"<?php if(!$edit){ ?> autofocus<?php } ?>>
<div class="info-tooltip-container hidden" id="title_tip"><div class="info-tooltip-arrow"></div><div class="info-tooltip"><?php echo l('add_title_tooltip'); ?></div></div>
<div class="chars-left" data-maxlength="70"><?php echo str_replace('[COUNT]', 70-intval(mb_strlen($ad['title'])), l('add_title_chars')); ?></div>
</div>
</div>
<div class="form-group">
<label class="col-sm-3 control-label"><?php echo l('add_title_category'); ?> <span class="input-required">*</span></label>
<div class="col-sm-4" id="catChoosed" style="display:<?php if($selectedCategory>0){ ?>block<?php } else { ?>none<?php } ?>;">
<table>
<tr>
<td>
<?php if($selectedCategory>0){ ?>
<img src="/images/cats/<?php echo $main_cat_id; ?>.png">
<?php } else { ?>
<img src="/images/blank.gif">
<?php } ?>
</td>
<td nowrap>
<b><?php if($selectedCategory>0){ ?><?php echo implode(' &raquo; ', array_reverse($selected_cats)); ?><?php } ?></b>
</td>
<td>
<a class="btn btn-primary btn-xs" onclick="showCatChooser(); $(this).blur(); return false;"><?php echo l('add_title_category_change'); ?></a>
</td>
</tr>
</table>
</div>
<div class="col-sm-2" id="catChooseLink" style="display:<?php if($selectedCategory>0){ ?>none<?php } else { ?>block<?php } ?>;">
<input type="hidden" name="category_id" id="category_id" value="<?php echo intval($selectedCategory); ?>">
<a class="btn btn-primary btn-xs" onclick="showCatChooser(); $(this).blur(); return false;"><?php echo l('add_title_category_select'); ?></a>
</div>
</div>
<hr>
<div id="parameters"><?php include "includes/parameters.php"; ?></div>
<div class="form-group">
<label class="col-sm-3 control-label"><?php echo l('add_description_title'); ?> <span class="input-required">*</span></label>
<div class="col-sm-6">
<textarea name="description" class="form-control" rows="7" onfocus="if(!$(this).hasClass('error-input')){ $('#description_tip').removeClass('hidden'); }" onblur="$('#description_tip').addClass('hidden');"><?php echo htmlspecialchars($ad['description']); ?></textarea>
<div class="info-tooltip-container hidden" id="description_tip"><div class="info-tooltip-arrow"></div><div class="info-tooltip"><?php echo l('add_description_tooltip'); ?></div></div>
<div class="chars-left" data-maxlength="4096"><?php echo str_replace('[COUNT]', 4096-intval(mb_strlen($ad['description'])), l('add_description_chars')); ?></div>
</div>
</div>
<div class="form-group" onmouseover="$('#photos_tip').removeClass('hidden');" onmouseout="$('#photos_tip').addClass('hidden');">
<label class="col-sm-3 control-label">
<?php echo l('add_photos_title'); ?>
<span class="label-description"><?php echo l('add_photos_description'); ?></span>
</label>
<div class="col-sm-6">
<div class="info-tooltip-container hidden" id="photos_tip"><div class="info-tooltip-arrow"></div><div class="info-tooltip"><div id="pre_upload_info"<?php if($uploadedPhotos>0){ ?> class="hidden"<?php } ?>><?php echo l('add_photos_tooltip_pre'); ?></div><div id="post_upload_info"<?php if($uploadedPhotos==0){ ?> class="hidden"<?php } ?>><?php echo l('add_photos_tooltip_post'); ?></div></div></div>
<input type="hidden" name="apid" id="apid" value="<?php echo $apid; ?>">
<div class="added-photo hidden" id="photoUploaderTemplate"><div class="rotate-photo" title="<?php echo l('add_photos_rotate'); ?>"><i class="fa fa-repeat"></i></div><div class="delete-photo" title="<?php echo l('add_photos_remove'); ?>"><i class="fa fa-remove"></i></div><img src="/images/blank.gif"></div>
<div id="photoUploader"><?php if(isset($ad['photos'])){ foreach($ad['photos'] as $photo){ ?><div class="added-photo" id="photo_<?php echo $photo['key']; ?>"><div class="rotate-photo" title="<?php echo l('add_photos_rotate'); ?>" onclick="rotateUploadedPhoto('<?php echo $photo['apid']; ?>', '<?php echo $photo['key']; ?>');"><i class="fa fa-repeat"></i></div><div class="delete-photo" title="<?php echo l('add_photos_remove'); ?>" onclick="deleteUploadedPhoto('<?php echo $photo['apid']; ?>', '<?php echo $photo['key']; ?>');"><i class="fa fa-remove"></i></div><img src="/image/92x72/<?php echo $photo['apid']; ?>/<?php echo $photo['key']; ?>.jpg?rev=<?php echo $photo['rev']; ?>"></div><?php } } ?><?php for($pc=1;$pc<=12;$pc++){ ?><div class="add-photo-button<?php if($pc==1){ ?> first<?php } ?><?php if($pc<=$uploadedPhotos || $pc>$maxPhotos){ ?> hidden<?php } ?>" id="add_photo_button_<?php echo $pc; ?>"><button type="button" onclick="$('#uploaderButton').click(); return false;"><span></span><i class="fa fa-spinner fa-spin hidden"></i></button></div><?php } ?><div class="clear"></div></div>
</div>
</div>
<hr>
<div class="form-group">
<label class="col-sm-3 control-label"><?php echo l('add_geo_title'); ?> <span class="input-required">*</span></label>
<div class="col-sm-4">
<input type="hidden" name="city_id" id="city_id" value="<?php if($edit){ echo intval($ad['city_id']); } elseif(isset($_SESSION['userid']) && $my['noprefill']==0){ echo $my['city_id']; } ?>">
<input type="text" name="geo" id="geo" class="form-control" value="<?php if($edit){ echo htmlspecialchars($ad['geo']); } elseif(isset($_SESSION['userid']) && $my['noprefill']==0){ echo $my['geo']; } ?>" onfocus="if(!$(this).hasClass('error-input')){ $('#geo_tip').removeClass('hidden'); }" onblur="$('#geo_tip').addClass('hidden');">
<div class="info-tooltip-container hidden" id="geo_tip"><div class="info-tooltip-arrow"></div><div class="info-tooltip"><?php echo l('add_geo_tooltip'); ?></div></div>
<script type="text/javascript">
$(function(){
$('#geo').typeahead({
ajax:{
url:langPrefix+'/ajax/geo/',
timeout:400,
displayField:'name',
triggerLength:1
}
});
});
</script>
</div>
</div>
<hr>
<div class="form-group">
<label class="col-sm-3 control-label"><?php echo l('add_person_title'); ?> <span class="input-required">*</span></label>
<div class="col-sm-4">
<input type="text" name="person" class="form-control" value="<?php if($edit){ echo htmlspecialchars($ad['person']); } elseif(isset($_SESSION['userid']) && $my['noprefill']==0){ echo htmlspecialchars($my['person']); } ?>" onfocus="if(!$(this).hasClass('error-input')){ $('#person_tip').removeClass('hidden'); }" onblur="$('#person_tip').addClass('hidden');">
<div class="info-tooltip-container hidden" id="person_tip"><div class="info-tooltip-arrow"></div><div class="info-tooltip"><?php echo l('add_person_tooltip'); ?></div></div>
</div>
</div>
<div class="form-group">
<label class="col-sm-3 control-label"><?php echo l('add_email_title'); ?> <span class="input-required">*</span></label>
<div class="col-sm-4">
<input type="text" name="email" class="form-control" value="<?php if(isset($_SESSION['userid'])){ echo $my['email']; } ?>" onfocus="if(!$(this).hasClass('error-input')){ $('#email_tip').removeClass('hidden'); }" onblur="$('#email_tip').addClass('hidden');"<?php if(isset($_SESSION['userid'])){ ?> disabled="disabled"<?php } ?>>
<div class="info-tooltip-container hidden" id="email_tip"><div class="info-tooltip-arrow"></div><div class="info-tooltip"><?php echo l('add_email_tooltip'); ?></div></div>
<div class="checkbox">
<input type="checkbox" name="nospam" id="nospam" value="1"<?php if($edit && $ad['nospam']=='1'){ echo ' checked'; } elseif(isset($_SESSION['userid']) && $my['nospam']=='1'){ echo ' checked'; } ?>>
<label style="color:#999;" for="nospam">
<?php echo l('add_email_disable'); ?>
</label>
</div>
</div>
</div>
<div class="form-group">
<label class="col-sm-3 control-label"><img src="/images/phone-gray.png" style="width:16px;margin-top:-3px;margin-right:9px;"><?php echo l('add_phone_title'); ?></label>
<div class="col-sm-4">
<input type="text" name="phone" class="form-control" value="<?php if($edit){ echo htmlspecialchars($ad['phone']); } elseif(isset($_SESSION['userid']) && $my['noprefill']==0){ echo htmlspecialchars($my['phone']); } ?>" onfocus="if(!$(this).hasClass('error-input')){ $('#phone_tip').removeClass('hidden'); }" onblur="$('#phone_tip').addClass('hidden');">
<div class="info-tooltip-container hidden" id="phone_tip"><div class="info-tooltip-arrow"></div><div class="info-tooltip"><?php echo l('add_phone_tooltip'); ?></div></div>
</div>
</div>
<div class="form-group">
<label class="col-sm-3 control-label"><img src="/images/gg-gray.png" style="width:16px;margin-top:-3px;margin-right:9px;"><?php echo l('add_gg_title'); ?></label>
<div class="col-sm-4">
<input type="text" name="gg" class="form-control" value="<?php if($edit){ echo htmlspecialchars($ad['gg']); } elseif(isset($_SESSION['userid']) && $my['noprefill']==0){ echo htmlspecialchars($my['gg']); } ?>">
</div>
</div>
<div class="form-group">
<label class="col-sm-3 control-label"><img src="/images/skype-gray.png" style="width:16px;margin-top:-3px;margin-right:9px;"><?php echo l('add_skype_title'); ?></label>
<div class="col-sm-4">
<input type="text" name="skype" class="form-control" value="<?php if($edit){ echo htmlspecialchars($ad['skype']); } elseif(isset($_SESSION['userid']) && $my['noprefill']==0){ echo htmlspecialchars($my['skype']); } ?>">
</div>
</div>
<?php if(!isset($_SESSION['userid'])){ ?>
<hr>
<div class="form-group">
<label class="col-sm-3 control-label"><?php echo l('add_password_title'); ?></label>
<div class="col-sm-4">
<input type="password" name="password" class="form-control" onfocus="if(!$(this).hasClass('error-input')){ $('#password_tip').removeClass('hidden'); }" onblur="$('#password_tip').addClass('hidden');">
<div class="info-tooltip-container hidden" id="password_tip"><div class="info-tooltip-arrow"></div><div class="info-tooltip"><?php echo l('add_password_tooltip'); ?></div></div>
</div>
</div>
<div class="form-group">
<label class="col-sm-3 control-label"><?php echo l('add_password_again_title'); ?></label>
<div class="col-sm-4">
<input type="password" name="cpassword" class="form-control">
</div>
</div>
<hr>
<div class="form-group">
<label class="col-sm-3 control-label"></label>
<div class="col-sm-8">
<div class="checkbox" style="padding-top:0;">
<input type="checkbox" name="agree" value="1" id="agree">
<label for="agree">
<?php echo l('add_agree'); ?>
</label>
</div>
</div>
</div>
<?php } ?>
<hr>
<div class="form-group">
<div class="col-sm-3"></div>
<div class="col-sm-7">
<button type="submit" class="btn btn-primary"><?php if($edit){ ?><?php echo l('add_save_changes'); ?><?php } else { ?><?php echo l('add_go'); ?><?php } ?></button>
<?php if($edit){ ?>
<span style="font-size:12px;margin-left:10px;margin-top:12px;display:inline-block;"><?php echo l('add_save_changes_or'); ?> <a href="<?php echo htmlspecialchars(trim($_GET['ref'])); ?>"><?php echo l('add_save_changes_back'); ?></a></span>
<?php } ?>
</div>
</div>
</form>

<?php include "includes/footer.php"; ?>