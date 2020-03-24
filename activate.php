<?php include "init.php"; ?>
<?php
if(trim($_GET['type'])=='email'){
$check=mysql_query("SELECT * FROM `users` WHERE `new_email_code`='"._F($_GET['code'])."' AND `active`='1';");
} elseif(trim($_GET['type'])=='restore'){
$check=mysql_query("SELECT * FROM `users` WHERE `restore_code`='"._F($_GET['code'])."' AND `active`='1';");
} else {
$check=mysql_query("SELECT * FROM `users` WHERE `register_code`='"._F($_GET['code'])."' AND `active`='0';");
}
if(!mysql_num_rows($check)){
header("HTTP/1.0 404 Not Found");
include "404.php";
exit;
}
unset($_SESSION['social_temp_userid']);
$user=mysql_fetch_assoc($check);
$_SESSION['userid']=$user['userid'];
$my=$user;
if(trim($_GET['type'])=='email'){
$my['email']=trim($user['new_email']);
mysql_query("UPDATE `users` SET `new_email_code`='', `new_email`='', `email`='"._F($user['new_email'])."' WHERE `new_email_code`='"._F($_GET['code'])."';");
} elseif(trim($_GET['type'])=='restore'){
mysql_query("UPDATE `users` SET `restore_code`='', `restore_password`='', `password`='"._F($user['restore_password'])."' WHERE `restore_code`='"._F($_GET['code'])."';");
} else {
mysql_query("UPDATE `users` SET `register_code`='', `register_password`='', `password`='"._F($user['register_password'])."', `notify`='1', `notify_messages`='1', `notify_ads`='1', `active`='1' WHERE `register_code`='"._F($_GET['code'])."';");
}
if(trim($_GET['type'])=='email'){
$pagetitle=l('activate_email_title')." &bull; ".$config['sitename'];
} elseif(trim($_GET['type'])=='restore'){
$pagetitle=l('activate_restore_title')." &bull; ".$config['sitename'];
} else {
if(intval($_GET['aid'])>0){
$pagetitle=l('activate_add_title')." &bull; ".$config['sitename'];	
} else {
$pagetitle=l('activate_register_title')." &bull; ".$config['sitename'];
}
}
$pagedesc=$config['description'];
?>
<?php
if($m){ include "m-activate.php"; exit; }
?>
<?php include "includes/header.php"; ?>

<div class="success-page">
<div class="success-box">
<?php
if(trim($_GET['type'])=='email'){
echo l('activate_email_info_message');
} elseif(trim($_GET['type'])=='restore'){
echo l('activate_restore_info_message');
} else {
if(intval($_GET['aid'])>0){
echo l('activate_add_info_message');	
} else {
echo l('activate_register_info_message');
}
}
?>
</div>
<p><a href="<?php echo $langPrefix; ?>/"><?php echo l('go_to_main_page'); ?></a></p>
<p><a href="<?php echo $langPrefix; ?>/my/"><?php echo l('go_to_my_profile'); ?></a></p>
</div>

<?php include "includes/footer.php"; ?>