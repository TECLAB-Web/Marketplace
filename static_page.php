<?php include_once "init.php"; ?>
<?php
$pagetitle=htmlspecialchars($page['title_'.$config['lang']])." &bull; ".$config['sitename'];
$pagedesc=$config['description'];
?>
<?php
if($m){ include "m-static_page.php"; exit; }
?>
<?php include "includes/header.php"; ?>

<h4 class="special-title"><?php echo htmlspecialchars($page['title_'.$config['lang']]); ?></h4>

<hr>

<?php echo $page['text_'.$config['lang']]; ?>

<?php include "includes/footer.php"; ?>