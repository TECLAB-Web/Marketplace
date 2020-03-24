<?php include "init.php"; ?>
<?php
$check_city=mysql_query("SELECT * FROM `cities` WHERE `city_id`='".intval($_COOKIE['city_id'])."';");
$city=mysql_fetch_assoc($check_city);
$region=mysql_fetch_assoc(mysql_query("SELECT * FROM `regions` WHERE `region_id`='".intval($city['region_id'])."';"));
$city['geo']=$city['title_'.$config['lang']].', '.$region['title_'.$config['lang']];
$city['in_city']=" в ".getCityForms($city['title_'.$config['lang']]);
$get_cats=mysql_query("SELECT * FROM `categories` WHERE `level`='1' AND `active`='1' ORDER BY `sort` ASC;");
?>
<?php
$pagetitle=l('not_found_title');
$pagedesc=$config['description'];
?>
<?php
if($m){ include "m-404.php"; exit; }
?>
<?php include "includes/header.php"; ?>

<div class="empty-list">
<div>
<i class="fa fa-times-circle"></i>
</div>
<b style="font-size:16px;"><?php echo l('not_found_note'); ?></b>
</div>

<hr>

<div class="not-found-categories">
<?php while($cat=mysql_fetch_assoc($get_cats)){ ?>
<div class="not-found-categories-item">
<table>
<tr>
<td>
<a href="<?php echo $langPrefix; ?>/<?php if(intval($city['city_id'])>0){ echo $city['url']; } else { echo 'list'; } ?>/<?php echo $cat['url']; ?>/" data-url="<?php echo $cat['url']; ?>"><?php echo htmlspecialchars($cat['name_'.$config['lang']]); ?></a>
</td>
</tr>
</table>
</div>
<?php } ?>
<div class="clear"></div>
</div>

<?php include "includes/footer.php"; ?>