<?php
include "init.php";
if(trim($_GET['provider'])=='email'){
if(!isset($_SESSION['social_temp_userid'])){
header("HTTP/1.0 404 Not Found");
include "404.php";
exit;
}
?>
<?php
if(trim($_GET['mode'])=='ajax'){
header('Content-Type: application/json; charset=utf-8');
if(trim($_POST['action'])=='email'){
$result=array();
$result['errors']=array();
if(trim($_POST['email'])==''){
$result['errors']['email']=l('email_error_email_empty');
} elseif(!preg_match("/^([-a-zA-Z0-9._]+@[-a-zA-Z0-9.]+(\.[-a-zA-Z0-9]+)+)*$/", trim($_POST['email']))){
$result['errors']['email']=l('email_error_email_invalid');
} elseif(mysql_num_rows(mysql_query("SELECT * FROM `users` WHERE `email`='"._F($_POST['email'])."';"))){
$result['errors']['email']=l('email_error_email_exists');
}
if(count($result['errors'])==0){
$code=md5(uniqid('').$time);
$update=mysql_query("UPDATE `users` SET `email`='"._F($_POST['email'])."', `register_code`='".$code."', `register_password`='' WHERE `userid`='".$_SESSION['social_temp_userid']."';");
$mail=mysql_fetch_assoc(mysql_query("SELECT * FROM `mail_templates` WHERE `code`='register';"));
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
exit;
}
?>
<?php
$pagetitle=l('email_title')." &bull; ".$config['sitename'];
$pagedesc=$config['description'];
?>
<?php include "includes/header.php"; ?>

<div class="row">
<div class="col-md-3"></div>
<div class="col-md-6">
<h3 class="special-title"><?php echo l('email_title'); ?></h3>
<div class="auth-box">
<form action="/s-login/email/" method="POST" autocomplete="off" class="ajax-form" data-callback="registerCallBack">
<input type="hidden" name="action" value="email">
<div class="form-group">
<label><?php echo l('email_type_email'); ?></label>
<div>
<input type="text" autocomplete="off" class="form-control" name="email" placeholder="<?php echo l('email_email'); ?>" value="<?php echo htmlspecialchars($_SESSION['social_temp_email']); ?>" autofocus>
</div>
</div>
<button type="submit" class="btn btn-primary"><?php echo l('email_submit'); ?></button>
</form>
</div>
</div>
<div class="col-md-3"></div>
</div>

<?php include "includes/footer.php"; ?>
<?php
} else {
if(trim($_GET['provider'])=='start'){
require_once("includes/hybridauth/Hybrid/Auth.php");
require_once("includes/hybridauth/Hybrid/Endpoint.php");
Hybrid_Endpoint::process();
} else {
$haconfig=dirname(__FILE__).'/includes/hybridauth/config.php';
require_once("includes/hybridauth/Hybrid/Auth.php");
try{
$hybridauth=new Hybrid_Auth($haconfig);
$social=$hybridauth->authenticate(trim($_GET['provider']));
$social_user_profile=$social->getUserProfile();
$social_user_profile=(array)$social_user_profile;
$social->logout();
$check=mysql_query("SELECT * FROM `users` WHERE `email`='"._F($social_user_profile['email'])."';");
if(trim($social_user_profile['email'])!='' && mysql_num_rows($check)){
$user=mysql_fetch_assoc($check);
if($user['active']!='1'){
header("HTTP/1.0 404 Not Found");
include "404.php";
exit;
}
$_SESSION['userid']=$user['userid'];
mysql_query("UPDATE `users` SET `social_type`='"._F($_GET['provider'])."', `social_id`='"._F($social_user_profile['identifier'])."', `person`='"._F($social_user_profile['firstName'])."' WHERE `userid`='".$user['userid']."';");
if(trim($_GET['ref'])!=''){
header("Location: ".trim($_GET['ref']));
} else {
header("Location: ".$langPrefix."/my/");
}
exit;
} else {
$_SESSION['social_temp_email']=trim($social_user_profile['email']);
$check=mysql_query("SELECT * FROM `users` WHERE `social_type`='"._F($_GET['provider'])."' AND `social_id`='"._F($social_user_profile['identifier'])."';");
if(mysql_num_rows($check)){
$user=mysql_fetch_assoc($check);
if($user['active']=='0'){
$_SESSION['social_temp_userid']=$user['userid'];
header("Location: ".$langPrefix."/s-login/email/");
exit;
}
if($user['active']!='1'){
header("HTTP/1.0 404 Not Found");
include "404.php";
exit;
}
$_SESSION['userid']=$user['userid'];
if(trim($_GET['ref'])!=''){
header("Location: ".trim($_GET['ref']));
} else {
header("Location: ".$langPrefix."/my/");
}
exit;
} else {
mysql_query("INSERT INTO `users` SET `social_type`='"._F($_GET['provider'])."', `social_id`='"._F($social_user_profile['identifier'])."', `person`='"._F($social_user_profile['firstName'])."', `time`='".$time."', `active`='0';");
$_SESSION['social_temp_userid']=mysql_insert_id();
header("Location: ".$langPrefix."/s-login/email/");
exit;
}
}
} catch(Exception $e){
header("HTTP/1.0 404 Not Found");
include "404.php";
exit;
}
}
}
?>