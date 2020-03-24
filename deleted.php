<?php include "init.php"; ?>
<?php
$pagetitle=l('deleted_title');
$pagedesc=$config['description'];
?>
<?php
if($m){ include "m-deleted.php"; exit; }
?>
<?php include "includes/header.php"; ?>

<div class="warning-page">
<div class="warning-box"><?php echo l('deleted_info_message') ;?></div>
<p><?php echo l('deleted_info_message_note') ;?></p>
<p><a href="<?php echo $langPrefix; ?>/"><?php echo l('go_to_main_page') ;?></a></p>
</div>

<?php include "includes/footer.php"; ?>