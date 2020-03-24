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
if(trim($_GET['mode'])=='ajax'){
header('Content-Type: application/json; charset=utf-8');
if(trim($_POST['action'])=='complaint_decline'){
$ad=getAd(intval($_POST['aid']));
$result=array();
$result['errors']=array();
if(count($result['errors'])==0){
mysql_query("DELETE FROM `complaints` WHERE `aid`='".intval($ad['aid'])."';");
$result['status']='success';
$result['message']=l('moder_complaint_declined');
} else {
$result['status']='error';
}
echo json_encode($result);
}
if(trim($_POST['action'])=='complaint_accept'){
$ad=getAd(intval($_POST['aid']));
$result=array();
$result['errors']=array();
if(isset($_POST['reason']) && !isset($config['complaint_types'][intval($_POST['reason'])])){
$result['errors']['form']='Выберите причину отклонения объявления.';
}
if(count($result['errors'])==0){
$index=Words2AllForms(trim($_POST['title'])." ".trim($_POST['description']));
$update=mysql_query("UPDATE `ads` SET `moderator`='".$_SESSION['userid']."', `reason`='".intval($_POST['reason'])."', `index`='"._F($index)."', `active`='3' WHERE `aid`='".intval($ad['aid'])."';");
mysql_query("DELETE FROM `complaints` WHERE `aid`='".intval($ad['aid'])."';");
$mail=mysql_fetch_assoc(mysql_query("SELECT * FROM `mail_templates` WHERE `code`='ad_rejected';"));
$to=trim($ad['user']['email']);
$mail['title']=$mail['title_'.$config['lang']];
$mail['body']=$mail['body_'.$config['lang']];
$mail['body']=str_replace('[SITE_NAME]', $config['sitename'], $mail['body']);
$mail['body']=str_replace('[SITE_URL]', $config['siteurl'], $mail['body']);
$mail['body']=str_replace('[AD_TITLE]', htmlspecialchars(trim($_POST['title'])), $mail['body']);
liam($to, $mail['title'], $mail['body'], "noreply@".$config['siteurl']);
$result['status']='success';
$result['message']=l('moder_complaint_accepted');
} else {
$result['status']='error';
}
echo json_encode($result);
}
if(trim($_POST['action'])=='reject'){
$ad=getAd(intval($_POST['aid']));
$result=array();
$result['errors']=array();
if(isset($_POST['reason']) && !isset($config['complaint_types'][intval($_POST['reason'])])){
$result['errors']['form']='Выберите причину отклонения объявления.';
}
if(count($result['errors'])==0){
$index=Words2AllForms(trim($_POST['title'])." ".trim($_POST['description']));
$update=mysql_query("UPDATE `ads` SET `moderator`='".$_SESSION['userid']."', `reason`='".intval($_POST['reason'])."', `index`='"._F($index)."', `active`='3' WHERE `aid`='".intval($ad['aid'])."';");
$mail=mysql_fetch_assoc(mysql_query("SELECT * FROM `mail_templates` WHERE `code`='ad_rejected';"));
$to=trim($ad['user']['email']);
$mail['title']=$mail['title_'.$config['lang']];
$mail['body']=$mail['body_'.$config['lang']];
$mail['body']=str_replace('[SITE_NAME]', $config['sitename'], $mail['body']);
$mail['body']=str_replace('[SITE_URL]', $config['siteurl'], $mail['body']);
$mail['body']=str_replace('[AD_TITLE]', htmlspecialchars(trim($_POST['title'])), $mail['body']);
liam($to, $mail['title'], $mail['body'], "noreply@".$config['siteurl']);
$result['status']='success';
$result['message']=l('moder_rejected');
} else {
$result['status']='error';
}
echo json_encode($result);
}
if(trim($_POST['action'])=='publish'){
$ad=getAd(intval($_POST['aid']));
if(!isset($_POST['title'])){
$_POST['title']=$ad['title'];
}
if(!isset($_POST['description'])){
$_POST['description']=$ad['description'];
}
$result=array();
$result['errors']=array();
if(trim($_POST['title'])==''){
$result['errors']['title']=l('add_error_title_empty');
} elseif(mb_strlen(trim($_POST['title']))<5){
$result['errors']['title']=l('add_error_title_short');
} elseif(mb_strlen(trim($_POST['title']))>70){
$result['errors']['title']=l('add_error_title_long');
}
if(trim($_POST['description'])==''){
$result['errors']['description']=l('add_error_description_empty');
} elseif(mb_strlen(trim($_POST['description']))<10){
$result['errors']['description']=l('add_error_description_short');
} elseif(mb_strlen(trim($_POST['description']))>4096){
$result['errors']['description']=l('add_error_description_long');
}
if(count($result['errors'])==0){
$index=Words2AllForms(trim($_POST['title'])." ".trim($_POST['description']));
$update=mysql_query("UPDATE `ads` SET `title`='"._F($_POST['title'])."', `description`='"._F($_POST['description'])."', `index`='"._F($index)."', `active`='1' WHERE `aid`='".intval($ad['aid'])."';");
mysql_query("UPDATE `ads` SET `time`='".$time."', `time_to`='".($time+60*60*24*$ad['days'])."' WHERE `aid`='".intval($ad['aid'])."';");
foreach($_POST['delete_photo'] as $key=>$value){
if(trim($value)=='1'){
mysql_query("DELETE FROM `ad_photos` WHERE `key`='"._F($key)."'");
}
}
$mail=mysql_fetch_assoc(mysql_query("SELECT * FROM `mail_templates` WHERE `code`='ad_published';"));
$to=trim($ad['user']['email']);
$mail['title']=$mail['title_'.$config['lang']];
$mail['body']=$mail['body_'.$config['lang']];
$mail['body']=str_replace('[SITE_NAME]', $config['sitename'], $mail['body']);
$mail['body']=str_replace('[SITE_URL]', $config['siteurl'], $mail['body']);
$mail['body']=str_replace('[AD_TITLE]', htmlspecialchars(trim($_POST['title'])), $mail['body']);
$mail['body']=str_replace('[AD_URL]', adurl($ad), $mail['body']);
liam($to, $mail['title'], $mail['body'], "noreply@".$config['siteurl']);
$result['status']='success';
$result['message']=l('moder_published');
} else {
$result['status']='error';
}
echo json_encode($result);
}
if(trim($_POST['action'])=='remove'){
$result=array();
$result['errors']=array();
mysql_query("UPDATE `ads` SET `active`='4' WHERE `aid`='".intval($_POST['aid'])."';");
if(count($result['errors'])==0){
$result['message']=l('my_items_successfully_removed');
$result['status']='success';
} else {
$result['status']='error';
}
echo json_encode($result);
}
if(trim($_POST['action'])=='deactivate'){
$result=array();
$result['errors']=array();
mysql_query("UPDATE `ads` SET `active`='2' WHERE `aid`='".intval($_POST['aid'])."';");
if(count($result['errors'])==0){
$result['message']=l('my_items_successfully_deactivated');
$result['status']='success';
} else {
$result['status']='error';
}
echo json_encode($result);
}
if(trim($_POST['action'])=='activate'){
$result=array();
$result['errors']=array();
$ad=getAd(intval($_POST['aid']));
if($ad['time_to']>$time){
mysql_query("UPDATE `ads` SET `active`='1' WHERE `aid`='".intval($_POST['aid'])."';");
} else {
mysql_query("UPDATE `ads` SET `active`='1', `time`='".$time."', `time_to`='".($time+60*60*24*$ad['days'])."' WHERE `aid`='".intval($_POST['aid'])."';");
}
if(count($result['errors'])==0){
$result['message']=l('my_items_successfully_activated');
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
//$get_ads=mysql_query("SELECT * FROM `ads` WHERE `active`='0' ORDER BY `time` DESC LIMIT 20;");
$get_first_unmoderated_ad=mysql_query("SELECT * FROM `ads` WHERE `active`='0' ORDER BY `time` ASC LIMIT 1;");
?>
<?php
$show_top_tabs=true;
$is_moderation=true;
$current_tab='master';
$top_tabs_title='Мастер модерации новых объявлений';
$top_tabs_description='Здесь можно осуществлять модерацию в порядке очереди путём нажатия кнопок &laquo;Опубликовать&raquo; или &laquo;Отклонить&raquo; с последующим переходом к следующему объявлению.';
$pagetitle='Мастер модерации новых объявлений';
$pagedesc=$config['description'];
?>
<?php include "includes/header.php"; ?>

<?php if(mysql_num_rows($get_first_unmoderated_ad)){ ?>
<?php
$ad=mysql_fetch_assoc($get_first_unmoderated_ad);
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
<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="POST" autocomplete="off" class="form add-form ajax-form" data-callback="moderationMasterCallBack">
<input type="hidden" name="aid" value="<?php echo $ad['aid']; ?>">
<input type="hidden" name="action" id="action" value="publish">
<div class="moder-ad">
<table class="moder-ad-controls">
<tr>
<td>
<select name="reason" size="1">
<option value="0">Выберите причину...</option>
<?php foreach($config['complaint_types'] as $ctid=>$ctitle){ ?>
<option value="<?php echo $ctid; ?>"><?php echo htmlspecialchars($ctitle); ?></option>
<?php } ?>
</select>
<button class="btn btn-sm btn-danger" onclick="document.getElementById('action').value='reject';">Отклонить</button>
</td>
<td align="right">
<button class="btn btn-sm btn-success" id="save_public" onclick="document.getElementById('action').value='publish';">Опубликовать</button>
</td>
</tr>
</table>
<hr>
<div class="form-group">
<label style="font-size:14px;">Рубрика:</label>
<div>
<?php echo implode(' &raquo; ', array_reverse($selected_cats)); ?>
</div>
</div>
<div class="form-group">
<label style="font-size:14px;">Заголовок объявления<span style="font-weight:normal;"> (откорректируйте, если не соответствует правилам сайта и законодательству)</span>:</label>
<div>
<input type="text" autocomplete="off" class="form-control" name="title" value="<?php echo htmlspecialchars($ad['title']); ?>" onkeyup="onMasterFormChangeCheck();">
</div>
</div>
<div class="form-group">
<label style="font-size:14px;">Описание объявления<span style="font-weight:normal;"> (откорректируйте, если не соответствует правилам сайта и законодательству)</span>:</label>
<div>
<textarea autocomplete="off" class="form-control" name="description" onkeyup="onMasterFormChangeCheck();"><?php echo htmlspecialchars($ad['description']); ?></textarea>
</div>
</div>
<div class="form-group">
<label style="font-size:14px;">Фотографии<span style="font-weight:normal;"> (кликните по фотографии, чтобы выставить её на удаление, если это необходимо)</span>:</label>
<div>
<div class="moder-ad-photos">
<?php if(count($ad['photos'])>0){ ?>
<?php
$pi=1;
foreach($ad['photos'] as $photo){
?>
<div class="moder-ad-photos-item" id="photo_<?php echo $photo['key']; ?>">
<input type="hidden" name="delete_photo[<?php echo $photo['key']; ?>]" value="0" id="delete_photo_<?php echo $photo['key']; ?>">
<img src="/image/261x203/<?php echo $photo['apid']; ?>/<?php echo $photo['key']; ?>.jpg?rev=<?php echo $photo['rev']; ?>&contain=1" onclick="if($('#photo_<?php echo $photo['key']; ?>').hasClass('del')){ $('#photo_<?php echo $photo['key']; ?>').removeClass('del'); $('#delete_photo_<?php echo $photo['key']; ?>').val('0'); } else { $('#photo_<?php echo $photo['key']; ?>').addClass('del'); $('#delete_photo_<?php echo $photo['key']; ?>').val('1'); } onMasterFormChangeCheck();">
<i class="fa fa-times"></i>
</div>
<?php
$pi++;
}
?>
<?php } else { ?>
<div class="main-form-error" style="margin-top:5px;">
К данному объявлению фотографии не прикреплены.
</div>
<?php } ?>
<div class="clear"></div>
</div>
</div>
</div>
</div>
</form>

<script type="text/javascript">
function onMasterFormChangeCheck(){
if($.trim($('input[name=title]').val())=="<?php echo str_replace("\r\n", "\\n", str_replace('"', '\\"', $ad['title'])); ?>" && $.trim($('textarea[name=description]').val())=="<?php echo str_replace("\r\n", "\\n", str_replace('"', '\\"', $ad['description'])); ?>" && $('.moder-ad-photos-item.del').length==0){
$('#save_public').html('Опубликовать');
} else {
$('#save_public').html('Сохранить корректировки и опубликовать');
}
}
</script>
<?php } else { ?>
<div class="empty-list">
<div>
<i class="fa fa-list"></i>
</div>
В настоящее время объявлений, ожидающих модерации, нет.
</div>
<?php } ?>

<?php include "includes/footer.php"; ?>