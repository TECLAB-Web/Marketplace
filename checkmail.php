<?php include "init.php"; ?>
<?php
if(trim($_GET['type'])=='email'){
$pagetitle=l('checkmail_email_title')." &bull; ".$config['sitename'];
} elseif(trim($_GET['type'])=='restore'){
$pagetitle=l('checkmail_restore_title')." &bull; ".$config['sitename'];
} else {
$pagetitle=l('checkmail_register_title')." &bull; ".$config['sitename'];
}
$pagedesc=$config['description'];
?>
<?php
if($m){ include "m-checkmail.php"; exit; }
?>
<?php include "includes/header.php"; ?>

<div class="success-page">
<div class="success-box">
<?php
if(trim($_GET['type'])=='email'){
echo l('checkmail_email_info_message');
} elseif(trim($_GET['type'])=='restore'){
echo l('checkmail_restore_info_message');
} else {
echo l('checkmail_register_info_message');
}
?>
</div>
<p><?php echo l('checkmail_info_message_note'); ?></p>
<p><a href="<?php echo $langPrefix; ?>/"><?php echo l('go_to_main_page'); ?></a></p>
<p><a href="<?php echo $langPrefix; ?>/list/"><?php echo l('go_to_ads_list'); ?></a></p>
</div>

<?php include "includes/footer.php"; ?>