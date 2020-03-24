<?php include_once "init.php"; ?>
<?php
if(!isset($_SESSION['userid'])){
header("Location: ".$langPrefix."/login/?ref=".urlencode($langPrefix.$_SERVER['REQUEST_URI']));
exit;
}
?>
<?php
if(trim($_GET['mode'])=='ajax'){
header('Content-Type: application/json; charset=utf-8');
if(trim($_POST['action'])==htmlspecialchars(trim($_GET['controller']))){
$result=array();
$result['errors']=array();
if(intval($_POST['city_id'])!=0 && !mysql_num_rows(mysql_query("SELECT * FROM `cities` WHERE `city_id`='".intval($_POST['city_id'])."';"))){
$result['errors']['geo']=l('my_settings_contacts_error_geo_invalid');
}
if(trim($_POST['person'])!='' && mb_strlen(trim($_POST['person']))>32){
$result['errors']['person']=l('my_settings_contacts_error_person_long');
}
if(trim($_POST['phone'])!='' && (!ctype_digit(ltrim(trim($_POST['phone']), '+')) || mb_strlen(ltrim(trim($_POST['phone']), '+'))<7)){
$result['errors']['phone']=l('my_settings_contacts_error_phone_invalid');
} elseif(mb_strlen(ltrim(trim($_POST['phone']), '+'))>32){
$result['errors']['phone']=l('my_settings_contacts_error_phone_long');
}
if(trim($_POST['gg'])!='' && mb_strlen(trim($_POST['gg']))>32){
$result['errors']['gg']=l('my_settings_contacts_error_gg_long');
}
if(trim($_POST['skype'])!='' && mb_strlen(trim($_POST['skype']))>32){
$result['errors']['skype']=l('my_settings_contacts_error_skype_long');
}
if(count($result['errors'])==0){
mysql_query("UPDATE `users` SET `city_id`='".intval($_POST['city_id'])."', `person`='"._F($_POST['person'])."', `phone`='"._F($_POST['phone'])."', `gg`='"._F($_POST['gg'])."', `skype`='"._F($_POST['skype'])."', `noprefill`='".intval($_POST['noprefill'])."', `hidesimilar`='".intval($_POST['hidesimilar'])."' WHERE `userid`='".$_SESSION['userid']."';");
$result['message']=l('my_settings_contacts_success');
$result['status']='success';
} else {
$result['status']='error';
}
echo json_encode($result);
}
if(trim($_POST['action'])=='password'){
$result=array();
$result['errors']=array();
if(trim($_POST['npassword'])==''){
$result['errors']['npassword']=l('my_settings_password_error_password_empty');
} elseif(mb_strlen(trim($_POST['npassword']))<6){
$result['errors']['npassword']=l('my_settings_password_error_password_short');
}
if(trim($_POST['cnpassword'])==''){
$result['errors']['cnpassword']=l('my_settings_password_error_cpassword_empty');
} elseif(mb_strlen(trim($_POST['npassword']))>=6 && trim($_POST['cnpassword'])!=trim($_POST['npassword'])){
$result['errors']['cnpassword']=l('my_settings_password_error_cpassword_not_equals');
}
if(count($result['errors'])==0){
mysql_query("UPDATE `users` SET `password`='".md5(trim($_POST['npassword']))."' WHERE `userid`='".$_SESSION['userid']."';");
$result['message']=l('my_settings_password_success');
$result['status']='success';
} else {
$result['status']='error';
}
echo json_encode($result);
}
if(trim($_POST['action'])=='email'){
$result=array();
$result['errors']=array();
if(trim($_POST['email'])==''){
$result['errors']['email']=l('my_settings_email_error_email_empty');
} elseif(!preg_match("/^([-a-zA-Z0-9._]+@[-a-zA-Z0-9.]+(\.[-a-zA-Z0-9]+)+)*$/", trim($_POST['email']))){
$result['errors']['email']=l('my_settings_email_error_email_invalid');
} elseif(mysql_num_rows(mysql_query("SELECT * FROM `users` WHERE `email`='"._F($_POST['email'])."';"))){
$result['errors']['email']=l('my_settings_email_error_email_exists');
}
if(count($result['errors'])==0){
$code=md5(uniqid('').$time);
$update=mysql_query("UPDATE `users` SET `new_email_code`='".$code."', `new_email`='"._F($_POST['email'])."' WHERE `userid`='".$_SESSION['userid']."';");
$mail=mysql_fetch_assoc(mysql_query("SELECT * FROM `mail_templates` WHERE `code`='email';"));
$to=trim($_POST['email']);
$mail['title']=$mail['title_'.$config['lang']];
$mail['body']=$mail['body_'.$config['lang']];
$mail['body']=str_replace('[SITE_NAME]', $config['sitename'], $mail['body']);
$mail['body']=str_replace('[SITE_URL]', $config['siteurl'], $mail['body']);
$mail['body']=str_replace('[ACTIVATION_CODE]', $code, $mail['body']);
liam($to, $mail['title'], $mail['body'], "noreply@".$config['siteurl']);
$result['status']='success';
} else {
$result['status']='error';
}
echo json_encode($result);
}
if(trim($_POST['action'])=='notify'){
$result=array();
$result['errors']=array();
if(count($result['errors'])==0){
mysql_query("UPDATE `users` SET `notify`='".intval($_POST['notify'])."', `notify_messages`='".intval($_POST['notify_messages'])."', `notify_ads`='".intval($_POST['notify_ads'])."' WHERE `userid`='".$_SESSION['userid']."';");
$result['message']=l('my_settings_notify_success');
$result['status']='success';
} else {
$result['status']='error';
}
echo json_encode($result);
}
if(trim($_POST['action'])=='delete'){
$result=array();
$result['errors']=array();
if(count($result['errors'])==0){
mysql_query("UPDATE `users` SET `active`='2' WHERE `userid`='".$_SESSION['userid']."';");
mysql_query("UPDATE `ads` SET `active`='2' WHERE `userid`='".$_SESSION['userid']."';");
unset($_SESSION['userid']);
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
$show_top_tabs=true;
$is_cabinet=true;
$current_tab='settings';
$top_tabs_title=l('my_settings_title');
$top_tabs_description=l('my_settings_description');
$pagetitle=l('my_settings_title')." &bull; ".$config['sitename'];
$pagedesc=$config['description'];
?>
<?php
if($m){ include "m-my_settings.php"; exit; }
?>
<?php include "includes/header.php"; ?>

<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
<div class="panel panel-default">
<div class="panel-heading" role="tab" id="headingContacts">
<h4 class="panel-title">
<a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseContacts" aria-expanded="true" aria-controls="collapseContacts"><?php echo l('my_settings_contacts_title'); ?></a>
</h4>
</div>
<div id="collapseContacts" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingContacts">
<div class="panel-body">
<form action="/my/<?php echo htmlspecialchars(trim($_GET['controller'])); ?>/" method="POST" autocomplete="off" class="settings-form ajax-form" data-callback="settingsCallBack">
<input type="hidden" name="action" value="<?php echo htmlspecialchars(trim($_GET['controller'])); ?>">
<div class="form-group" style="width:250px;">
<label><?php echo l('my_settings_contacts_geo'); ?></label>
<div>
<input type="hidden" name="city_id" id="city_id" value="<?php echo $my['city_id']; ?>">
<input type="text" name="geo" id="geo" class="form-control" value="<?php echo $my['geo']; ?>">
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
<div class="form-group" style="width:250px;">
<label><?php echo l('my_settings_contacts_person'); ?></label>
<div>
<input type="text" name="person" class="form-control" value="<?php echo htmlspecialchars($my['person']); ?>">
</div>
</div>
<div class="form-group" style="width:250px;">
<label><?php echo l('my_settings_contacts_phone'); ?></label>
<div>
<input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($my['phone']); ?>">
</div>
</div>
<div class="form-group" style="width:250px;">
<label><?php echo l('my_settings_contacts_gg'); ?></label>
<div>
<input type="text" name="gg" class="form-control" value="<?php echo htmlspecialchars($my['gg']); ?>">
</div>
</div>
<div class="form-group" style="width:250px;">
<label><?php echo l('my_settings_contacts_skype'); ?></label>
<div>
<input type="text" name="skype" class="form-control" value="<?php echo htmlspecialchars($my['skype']); ?>">
</div>
</div>
<div class="form-group">
<div class="checkbox">
<input type="checkbox" name="noprefill" value="1" id="noprefill"<?php if($my['noprefill']==1){ ?> checked<?php } ?>>
<label for="noprefill"><?php echo l('my_settings_contacts_noprefill'); ?></label>
</div>
</div>
<div class="form-group">
<div class="checkbox">
<input type="checkbox" name="hidesimilar" value="1" id="hidesimilar"<?php if($my['hidesimilar']==1){ ?> checked<?php } ?>>
<label for="hidesimilar"><?php echo l('my_settings_contacts_hidesimilar'); ?></label>
</div>
</div>
<button type="submit" class="btn btn-primary"><?php echo l('my_settings_contacts_submit'); ?></button>
<div class="form-success-message" id="settings-success-message"></div>
</form>
</div>
</div>
</div>
<div class="panel panel-default">
<div class="panel-heading" role="tab" id="headingPassword">
<h4 class="panel-title">
<a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapsePassword" aria-expanded="false" aria-controls="collapsePassword"><?php echo l('my_settings_password_title'); ?></a>
</h4>
</div>
<div id="collapsePassword" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingPassword">
<div class="panel-body">
<form action="/my/<?php echo htmlspecialchars(trim($_GET['controller'])); ?>/" method="POST" autocomplete="off" class="settings-form ajax-form" data-callback="settingsPasswordCallBack">
<input type="hidden" name="action" value="password">
<div class="form-group" style="width:250px;">
<label><?php echo l('my_settings_password_new'); ?></label>
<div>
<input type="password" name="npassword" class="form-control" value="">
</div>
</div>
<div class="form-group" style="width:250px;">
<label><?php echo l('my_settings_password_confirm'); ?></label>
<div>
<input type="password" name="cnpassword" class="form-control" value="">
</div>
</div>
<button type="submit" class="btn btn-primary"><?php echo l('my_settings_password_submit'); ?></button>
<div class="form-success-message" id="settings-password-success-message"></div>
</form>
</div>
</div>
</div>
<div class="panel panel-default">
<div class="panel-heading" role="tab" id="headingEMail">
<h4 class="panel-title">
<a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseEMail" aria-expanded="false" aria-controls="collapseEMail"><?php echo l('my_settings_email_title'); ?></a>
</h4>
</div>
<div id="collapseEMail" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingEMail">
<div class="panel-body">
<form action="/my/<?php echo htmlspecialchars(trim($_GET['controller'])); ?>/" method="POST" autocomplete="off" class="settings-form ajax-form" data-callback="settingsEMailCallBack">
<input type="hidden" name="action" value="email">
<div class="form-group" style="width:250px;">
<label><?php echo l('my_settings_email_new'); ?></label>
<div>
<input type="text" name="email" class="form-control" value="">
</div>
</div>
<button type="submit" class="btn btn-primary"><?php echo l('my_settings_email_submit'); ?></button>
<div class="form-success-message" id="settings-email-success-message"></div>
</form>
</div>
</div>
</div>
<div class="panel panel-default">
<div class="panel-heading" role="tab" id="headingNotify">
<h4 class="panel-title">
<a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseNotify" aria-expanded="false" aria-controls="collapseNotify"><?php echo l('my_settings_notify_title'); ?></a>
</h4>
</div>
<div id="collapseNotify" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingNotify">
<div class="panel-body">
<form action="/my/<?php echo htmlspecialchars(trim($_GET['controller'])); ?>/" method="POST" autocomplete="off" class="settings-form ajax-form" data-callback="settingsNotifyCallBack">
<input type="hidden" name="action" value="notify">
<div class="form-group">
<div class="checkbox">
<input type="checkbox" name="notify" value="1" id="notify"<?php if($my['notify']==1){ ?> checked<?php } ?>>
<label for="notify"><?php echo l('my_settings_notify_yes_1'); ?></label>
</div>
</div>
<div class="form-group">
<div class="checkbox">
<input type="checkbox" name="notify_messages" value="1" id="notify_messages"<?php if($my['notify_messages']==1){ ?> checked<?php } ?>>
<label for="notify_messages"><?php echo l('my_settings_notify_yes_2'); ?></label>
</div>
</div>
<div class="form-group">
<div class="checkbox">
<input type="checkbox" name="notify_ads" value="1" id="notify_ads"<?php if($my['notify_ads']==1){ ?> checked<?php } ?>>
<label for="notify_ads"><?php echo l('my_settings_notify_yes_3'); ?></label>
</div>
</div>
<button type="submit" class="btn btn-primary"><?php echo l('my_settings_notify_submit'); ?></button>
<div class="form-success-message" id="settings-notify-success-message"></div>
</form>
</div>
</div>
</div>
<div class="panel panel-default">
<div class="panel-heading" role="tab" id="headingDelete">
<h4 class="panel-title">
<a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseDelete" aria-expanded="false" aria-controls="collapseDelete"><?php echo l('my_settings_delete_title'); ?></a>
</h4>
</div>
<div id="collapseDelete" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingDelete">
<div class="panel-body">
<form action="/my/<?php echo htmlspecialchars(trim($_GET['controller'])); ?>/" method="POST" autocomplete="off" class="settings-form ajax-form" data-callback="settingsDeleteCallBack">
<input type="hidden" name="action" value="delete">
<div class="form-group">
<div class="checkbox">
<input type="checkbox" name="confirm" value="1" id="confirm" onclick="if(this.checked){ $('#delete-submit').removeAttr('disabled'); } else { $('#delete-submit').attr('disabled', 'disabled'); }">
<label for="confirm"><?php echo l('my_settings_delete_confirm'); ?></label>
</div>
</div>
<button type="submit" class="btn btn-danger" id="delete-submit" disabled="disabled"><?php echo l('my_settings_delete_submit'); ?></button>
</form>
</div>
</div>
</div>
</div>

<?php include "includes/footer.php"; ?>