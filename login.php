<?php include "init.php"; ?>
<?php
if(trim($_GET['mode'])=='ajax'){
header('Content-Type: application/json; charset=utf-8');
if(trim($_POST['action'])==htmlspecialchars(trim($_GET['controller']))){
$result=array();
$result['errors']=array();
if(trim($_POST['email'])==''){
$result['errors']['email']=l('login_error_email_empty');
} else {
$check=mysql_query("SELECT * FROM `users` WHERE `email`='"._F($_POST['email'])."';");
if(mysql_num_rows($check)){
$user=mysql_fetch_assoc($check);
} else {
$result['errors']['email']=l('login_error_email_inexists');
}
}
if(trim($_POST['password'])==''){
$result['errors']['password']=l('login_error_password_empty');
} elseif(mysql_num_rows($check) && $user['password']!=md5(trim($_POST['password']))){
$result['errors']['password']=l('login_error_password_incorrect');
} elseif($user['active']=='0'){
$result['errors']['form']=l('login_error_account_inactive');
} elseif($user['active']=='2'){
$result['errors']['form']=l('login_error_account_removed');
} elseif($user['active']=='3'){
$result['errors']['form']=l('login_error_account_banned');
}
if(count($result['errors'])==0){
$_SESSION['userid']=$user['userid'];
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
$pagetitle=l('login_title')." &bull; ".$config['sitename'];
$pagedesc=$config['description'];
?>
<?php
if($m){ include "m-login.php"; exit; }
?>
<?php include "includes/header.php"; ?>

<div class="row">
<div class="col-md-3"></div>
<div class="col-md-6">
<h3 class="special-title"><?php echo l('login_title'); ?></h3>
<div class="auth-box">
<form action="/<?php echo htmlspecialchars(trim($_GET['controller'])); ?>/" method="POST" autocomplete="off" class="ajax-form" data-callback="loginCallBack">
<input type="hidden" name="action" value="<?php echo htmlspecialchars(trim($_GET['controller'])); ?>">
<div class="form-group">
<label><?php echo l('login_type_email'); ?></label>
<div>
<input type="text" autocomplete="off" class="form-control" name="email" placeholder="<?php echo l('login_email'); ?>" autofocus>
</div>
</div>
<div class="form-group">
<label><?php echo l('login_type_password'); ?></label>
<div>
<input type="password" autocomplete="off" class="form-control" name="password" placeholder="<?php echo l('login_password'); ?>">
</div>
</div>
<button type="submit" class="btn btn-primary"><?php echo l('login_submit'); ?></button>
<div class="cannot-login">
<a href="<?php echo $langPrefix; ?>/restore/" class="pull-left"><?php echo l('login_cant_login'); ?></a>
<a href="<?php echo $langPrefix; ?>/register/" class="pull-right"><?php echo l('login_to_register'); ?></a>
<div class="clear"></div>
</div>
</form>
</div>
</div>
<div class="col-md-3"></div>
</div>

<div class="social-login-title">
<?php echo l('login_using_social_network'); ?>
</div>

<div class="social-login"><a href="/s-login/vkontakte/<?php if(isset($_GET['ref'])){ ?>?ref=<?php echo urlencode($_GET['ref']); ?><?php } ?>" class="vkontakte"><i class="fa fa-vk"></i></a><a href="/s-login/odnoklassniki/<?php if(isset($_GET['ref'])){ ?>?ref=<?php echo urlencode($_GET['ref']); ?><?php } ?>" class="odnoklassniki"><i class="fa fa-odnoklassniki"></i></a><a href="/s-login/facebook/<?php if(isset($_GET['ref'])){ ?>?ref=<?php echo urlencode($_GET['ref']); ?><?php } ?>" class="facebook"><i class="fa fa-facebook"></i></a><a href="/s-login/google/<?php if(isset($_GET['ref'])){ ?>?ref=<?php echo urlencode($_GET['ref']); ?><?php } ?>" class="google"><i class="fa fa-google"></i></a></div>

<?php include "includes/footer.php"; ?>