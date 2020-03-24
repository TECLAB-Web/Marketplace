<?php
$get_cats=mysql_query("SELECT * FROM `categories` WHERE `level`='1' AND `active`='1' ORDER BY `sort` ASC;");
$get_cats_level_2=mysql_query("SELECT * FROM `categories` WHERE `level`='2' AND `active`='1' ORDER BY `sort` ASC;");
?>
<div class="category-chooser">
<div class="category-chooser-main"><div class="category-chooser-main-title"><?php echo l('add_title_category_select'); ?><a href="javascript:void(0);" onclick="$.fancybox.close();"></a></div><div style="padding:10px 10px;"><?php while($cat=mysql_fetch_assoc($get_cats)){ ?><div class="category-chooser-main-item">
<a href="javascript:void(0);" onclick="selectCat('<?php echo $cat['id']; ?>', '<?php echo htmlspecialchars(_F($cat['name_'.$config['lang']])); ?>', '2', '<?php echo $cat['max_photos']; ?>'); return false;">
<img src="/images/cats/<?php echo $cat['id']; ?>.png" id="select_cat_icon_<?php echo $cat['id']; ?>">
<span><?php echo htmlspecialchars($cat['name_'.$config['lang']]); ?></span>
</a>
</div><?php } ?></div></div>
<div class="category-chooser-subselect" style="display:none;">
<table>
<tr>
<td class="category-chooser-level" id="select_cat_level_1">
<div class="category-chooser-level-title"><span><?php echo l('add_title_category'); ?></span></div>
<div class="category-chooser-level-body">
<?php mysql_data_seek($get_cats, 0); ?>
<?php while($cat=mysql_fetch_assoc($get_cats)){ ?>
<?php
$has_children=mysql_num_rows(mysql_query("SELECT * FROM `categories` WHERE `parent_id`='".$cat['id']."' AND `active`='1';"));
?>
<div class="category-chooser-level-item">
<a href="javascript:void(0);" onclick="selectCat('<?php echo $cat['id']; ?>', '<?php echo htmlspecialchars(_F($cat['name_'.$config['lang']])); ?>', '2', '<?php echo $cat['max_photos']; ?>'); return false;" id="select_cat_link_<?php echo $cat['id']; ?>">
<span><?php echo htmlspecialchars($cat['name_'.$config['lang']]); ?><?php if($has_children){ ?> <i class="fa fa-chevron-right"></i><?php } ?></span>
</a>
</div>
<?php } ?>
</div>
</td>
<td class="category-chooser-level" id="select_cat_level_2">
<div class="category-chooser-level-title"><span>&nbsp;</span></div>
<div class="category-chooser-level-body">
<?php mysql_data_seek($get_cats, 0); ?>
<?php while($cat=mysql_fetch_assoc($get_cats)){ ?>
<?php
$get_children=mysql_query("SELECT * FROM `categories` WHERE `parent_id`='".$cat['id']."' AND `active`='1' ORDER BY `sort` ASC;");
?>
<?php if(mysql_num_rows($get_children)){ ?>
<div class="select_cat_items" id="select_cat_<?php echo $cat['id']; ?>" style="display:none;">
<?php while($child=mysql_fetch_assoc($get_children)){ ?>
<?php
$has_children=mysql_num_rows(mysql_query("SELECT * FROM `categories` WHERE `parent_id`='".$child['id']."' AND `active`='1';"));
?>
<div class="category-chooser-level-item">
<a href="javascript:void(0);" onclick="selectCat('<?php echo $child['id']; ?>', '<?php echo htmlspecialchars(_F($child['name_'.$config['lang']])); ?>', '3', '<?php echo $child['max_photos']; ?>'); return false;" id="select_cat_link_<?php echo $child['id']; ?>">
<span><?php echo htmlspecialchars($child['name_'.$config['lang']]); ?><?php if($has_children){ ?> <i class="fa fa-chevron-right"></i><?php } ?></span>
</a>
</div>
<?php } ?>
</div>
<?php } ?>
<?php } ?>
</div>
</td>
<td class="category-chooser-level" id="select_cat_level_3">
<div class="category-chooser-level-title"><span>&nbsp;</span><a href="javascript:void(0);" onclick="$.fancybox.close();"></a></div>
<div class="category-chooser-level-body">
<?php mysql_data_seek($get_cats_level_2, 0); ?>
<?php while($cat=mysql_fetch_assoc($get_cats_level_2)){ ?>
<?php
$get_children=mysql_query("SELECT * FROM `categories` WHERE `parent_id`='".$cat['id']."' AND `active`='1' ORDER BY `sort` ASC;");
?>
<?php if(mysql_num_rows($get_children)){ ?>
<div class="select_cat_items" id="select_cat_<?php echo $cat['id']; ?>" style="display:none;">
<?php while($child=mysql_fetch_assoc($get_children)){ ?>
<div class="category-chooser-level-item">
<a href="javascript:void(0);" onclick="selectCat('<?php echo $child['id']; ?>', '<?php echo htmlspecialchars(_F($child['name_'.$config['lang']])); ?>', '4', '<?php echo $child['max_photos']; ?>'); return false;" id="select_cat_link_<?php echo $child['id']; ?>">
<span><?php echo htmlspecialchars($child['name_'.$config['lang']]); ?></span>
</a>
</div>
<?php } ?>
</div>
<?php } ?>
<?php } ?>
</div>
</td>
</tr>
</table>
</div>
</div>