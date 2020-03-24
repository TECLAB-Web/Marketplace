<div class="search-form-category-selector-title">
<div class="pull-left">
<?php if($current_category>0){ ?>
<a href="javascript:void(0);" onclick="listSearchingCats('<?php echo $search_category_parent; ?>', '<?php echo getCategoryURL($search_category_parent); ?>'); return false;"><i class="fa fa-chevron-left"></i><?php echo l('list_select_other_category'); ?></a>
<?php } else { ?>
<?php echo l('list_select_category'); ?>
<?php } ?>
</div>
<?php if($current_category>0){ ?>
<div class="pull-right">
<a href="javascript:void(0);" onclick="selectSearchingCat('<?php echo $current_category; ?>', '<?php echo _F(end($selected_cats)['name_'.$config['lang']]); ?>', '<?php echo getCategoryURL($current_category); ?>'); return false;"><?php echo l('list_all_items_in_category'); ?><i class="fa fa-chevron-right"></i></a>
</div>
<?php } ?>
<div class="clear"></div>
</div>
<div class="search-form-category-selector-body">
<?php
if(count($categories_to_show)>0){
?>
<table>
<?php
$ri=1;
foreach($categories_to_show as $row){
?>
<tr class="">
<?php
$row=array_pad($row, 4, '');
foreach($row as $cell){
$has_subcats=mysql_num_rows(mysql_query("SELECT * FROM `categories` WHERE `parent_id`='".$cell['id']."' AND `active`='1';"));
?>
<td width="25%">
<?php if(is_array($cell)){ ?>
<a href="javascript:void(0);" class="<?php if($cell['id']==$current_category){ ?>active<?php } ?>" onclick="<?php if($has_subcats){ ?>listSearchingCats('<?php echo $cell['id']; ?>', '<?php echo getCategoryURL($cell['id']); ?>');<?php } else { ?>selectSearchingCat('<?php echo $cell['id']; ?>', '<?php echo _F($cell['name_'.$config['lang']]); ?>', '<?php echo getCategoryURL($cell['id']); ?>');<?php } ?> return false;" title="<?php echo htmlspecialchars($cell['name_'.$config['lang']]); ?>"><?php echo htmlspecialchars(cutString($cell['name_'.$config['lang']], 21, '...')); ?></a>
<?php } ?>
</td>
<?php
}
?>
</tr>
<?php
$ri++;
}
?>
</table>
<?php
}
?>
</div>