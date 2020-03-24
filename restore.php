<?php include "init.php"; ?>
<?php
if(trim($_GET['mode'])=='ajax'){
header('Content-Type: application/json; charset=utf-8');
if(trim($_POST['action'])==htmlspecialchars(trim($_GET['controller']))){
$result=array();
$result['errors']=array();
if(trim($_POST['email'])==''){
$result['errors']['email']=l('restore_error_email_empty');
} else {
$check=mysql_query("SELECT * FROM `users` WHERE `email`='"._F($_POST['email'])."';");
if(mysql_num_rows($check)){
$user=mysql_fetch_assoc($check);
} else {
$result['errors']['email']=l('restore_error_email_inexists');
}
}
if($user['active']=='0'){
$result['errors']['form']=l('restore_error_account_inactive');
} elseif($user['active']=='2'){
$result['errors']['form']=l('restore_error_account_removed');
} elseif($user['active']=='3'){
$result['errors']['form']=l('restore_error_account_banned');
}
if(trim($_POST['password'])==''){
$result['errors']['password']=l('restore_error_password_empty');
} elseif(mb_strlen(trim($_POST['password']))<6){
$result['errors']['password']=l('restore_error_password_short');
}
if(trim($_POST['cpassword'])==''){
$result['errors']['cpassword']=l('restore_error_cpassword_empty');
} elseif(mb_strlen(trim($_POST['password']))>=6 && trim($_POST['cpassword'])!=trim($_POST['password'])){
$result['errors']['cpassword']=l('restore_error_cpassword_not_equals');
}
if(count($result['errors'])==0){
$code=md5(uniqid('').$time);
$update=mysql_query("UPDATE `users` SET `restore_code`='".$code."', `restore_password`='".md5(trim($_POST['password']))."' WHERE `email`='"._F($_POST['email'])."';");
$mail=mysql_fetch_assoc(mysql_query("SELECT * FROM `mail_templates` WHERE `code`='restore';"));
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
$pagetitle=l('restore_title')." &bull; ".$config['sitename'];
$pagedesc=$config['description'];
?>
<?php
if($m){ include "m-restore.php"; exit; }
?>
<?php include "includes/header.php"; ?>

<div class="row">
<div class="col-md-3"></div>
<div class="col-md-6">
<h3 class="special-title"><?php echo l('restore_title'); ?></h3>
<div class="auth-box">
<form action="/<?php echo htmlspecialchars(trim($_GET['controller'])); ?>/" method="POST" autocomplete="off" class="ajax-form" data-callback="restoreCallBack">
<input type="hidden" name="action" value="<?php echo htmlspecialchars(trim($_GET['controller'])); ?>">
<div class="form-group">
<label><?php echo l('restore_type_email'); ?></label>
<div>
<input type="text" autocomplete="off" class="form-control" name="email" placeholder="<?php echo l('restore_email'); ?>" autofocus>
</div>
</div>
<div class="form-group">
<label><?php echo l('restore_type_password'); ?></label>
<div>
<input type="password" autocomplete="off" class="form-control" name="password" placeholder="<?php echo l('restore_password'); ?>">
</div>
</div>
<div class="form-group">
<label><?php echo l('restore_retype_password'); ?></label>
<div>
<input type="password" autocomplete="off" class="form-control" name="cpassword" placeholder="<?php echo l('restore_password_again'); ?>">
</div>
</div>
<button type="submit" class="btn btn-primary"><?php echo l('restore_submit'); ?></button>
</form>
</div>
</div>
<div class="col-md-3"></div>
</div>

<?php include "includes/footer.php"; ?>