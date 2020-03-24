<?php include "init.php"; ?>
<?php
if(trim($_GET['type'])=='edit'){
$pagetitle=l('success_edit_title')." &bull; ".$config['sitename'];
} else {
if(isset($_SESSION['userid'])){
$pagetitle=l('success_add_title')." &bull; ".$config['sitename'];
} else {
$pagetitle=l('success_activate_title')." &bull; ".$config['sitename'];
}
}
$pagedesc=$config['description'];
?>
<?php
if($m){ include "m-success.php"; exit; }
?>
<?php include "includes/header.php"; ?>

<div class="success-page">
<div class="success-box">
<?php
if(trim($_GET['type'])=='edit'){
echo l('success_edit_info_message');
} else {
if(isset($_SESSION['userid'])){
echo l('success_add_info_message');
} else {
echo l('success_activate_info_message');
}
}
?>
</div>
<?php if(trim($_GET['type'])=='add' && !isset($_SESSION['userid'])){ ?>
<p><?php echo l('success_activate_info_message_note'); ?></p>
<?php } ?>
<p><a href="<?php echo $langPrefix; ?>/"><?php echo l('go_to_main_page'); ?></a></p>
<p><a href="<?php echo $langPrefix; ?>/list/"><?php echo l('go_to_ads_list'); ?></a></p>
</div>

<?php include "includes/footer.php"; ?>