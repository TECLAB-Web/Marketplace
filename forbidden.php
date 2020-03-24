<?php include_once "init.php"; ?>
<?php
$pagetitle=l('forbidden_title');
$pagedesc=$config['description'];
?>
<?php
if($m){ include "m-forbidden.php"; exit; }
?>
<?php include "includes/header.php"; ?>

<div class="warning-page">
<div class="warning-box">
<?php echo l('forbidden_info_message'); ?>
</div>
<?php if(intval($aid)>0){ ?>
<p><a href="<?php echo adurl($ad); ?>"><?php echo l('forbidden_return'); ?></a></p>
<?php } ?>
<p><a href="<?php echo $langPrefix; ?>/"><?php echo l('go_to_main_page'); ?></a></p>
</div>

<?php include "includes/footer.php"; ?>