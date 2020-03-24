<?php include "init.php"; ?>
<?php
if(trim($_GET['mode'])=='ajax'){
header('Content-Type: application/json; charset=utf-8');
if(trim($_POST['action'])==htmlspecialchars(trim($_GET['controller']))){
$result=array();
$result['errors']=array();
if(trim($_POST['email'])==''){
$result['errors']['email']=l('register_error_email_empty');
} elseif(!preg_match("/^([-a-zA-Z0-9._]+@[-a-zA-Z0-9.]+(\.[-a-zA-Z0-9]+)+)*$/", trim($_POST['email']))){
$result['errors']['email']=l('register_error_email_invalid');
} elseif(mysql_num_rows(mysql_query("SELECT * FROM `users` WHERE `email`='"._F($_POST['email'])."';"))){
$result['errors']['email']=l('register_error_email_exists');
}
if(trim($_POST['password'])==''){
$result['errors']['password']=l('register_error_password_empty');
} elseif(mb_strlen(trim($_POST['password']))<6){
$result['errors']['password']=l('register_error_password_short');
}
if(trim($_POST['cpassword'])==''){
$result['errors']['cpassword']=l('register_error_cpassword_empty');
} elseif(mb_strlen(trim($_POST['password']))>=6 && trim($_POST['cpassword'])!=trim($_POST['password'])){
$result['errors']['cpassword']=l('register_error_cpassword_not_equals');
}
if(!isset($_POST['agree'])){
$result['errors']['agree']=l('register_error_agree_off');
}
if(count($result['errors'])==0){
$code=md5(uniqid('').$time);
$create=mysql_query("INSERT INTO `users` SET `email`='"._F($_POST['email'])."', `register_code`='".$code."', `register_password`='".md5(trim($_POST['password']))."', `time`='".$time."', `active`='0';");
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
$pagetitle=l('register_title')." &bull; ".$config['sitename'];
$pagedesc=$config['description'];
?>
<?php
if($m){ include "m-register.php"; exit; }
?>
<?php include "includes/header.php"; ?>

<div class="row">
<div class="col-md-5">
<h3 class="special-title"><?php echo l('register_title'); ?></h3>
<div class="auth-box">
<form action="/<?php echo htmlspecialchars(trim($_GET['controller'])); ?>/" method="POST" autocomplete="off" class="ajax-form" data-callback="registerCallBack">
<input type="hidden" name="action" value="<?php echo htmlspecialchars(trim($_GET['controller'])); ?>">
<div class="form-group">
<label><?php echo l('register_type_email'); ?></label>
<div>
<input type="text" autocomplete="off" class="form-control" name="email" placeholder="<?php echo l('register_email'); ?>" autofocus>
</div>
</div>
<div class="form-group">
<label><?php echo l('register_type_password'); ?></label>
<div>
<input type="password" autocomplete="off" class="form-control" name="password" placeholder="<?php echo l('register_password'); ?>">
</div>
</div>
<div class="form-group">
<label><?php echo l('register_retype_password'); ?></label>
<div>
<input type="password" autocomplete="off" class="form-control" name="cpassword" placeholder="<?php echo l('register_password_again'); ?>">
</div>
</div>
<div class="form-group">
<div class="checkbox">
<input type="checkbox" name="agree" value="1" id="agree">
<label for="agree"><?php echo l('register_agree'); ?></label>
</div>
</div>
<button type="submit" class="btn btn-primary"><?php echo l('register_submit'); ?></button>
<div class="already-registered">
<a href="<?php echo $langPrefix; ?>/login/"><?php echo l('register_already_registered'); ?></a>
</div>
</form>
</div>
</div>
<div class="col-md-7">
<h3 class="special-title">&nbsp;</h3>
<div class="register-features">
<p><?php echo l('register_features'); ?></p>
<p><i class="fa fa-long-arrow-right"></i> <?php echo l('register_feature_1'); ?></p>
<p><i class="fa fa-long-arrow-right"></i> <?php echo l('register_feature_2'); ?></p>
<p><i class="fa fa-long-arrow-right"></i> <?php echo l('register_feature_3'); ?></p>
<p><?php echo l('register_features_end'); ?></p>
</div>
</div>
</div>

<?php include "includes/footer.php"; ?>