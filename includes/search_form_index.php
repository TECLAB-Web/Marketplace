<div style="padding-top:20px;">
<div class="search-form-index">
<div class="search-form" id="search_form_index">
<form action="/<?php if(intval($city['city_id'])>0){ echo $city['url']; } else { echo 'list'; } ?>/" method="GET" autocomplete="off">
<div class="search-form-main">
<table>
<td width="45%">
<div class="search-form-query-container">
<input type="text" id="query" name="search[query]" value="" placeholder="<?php echo str_replace('[COUNT]', mysql_result(mysql_query("SELECT COUNT(*) FROM `ads` WHERE `active`='1';")), l('search_ads')); ?>" class="form-control search-form-query-input" autofocus>
</div>
</td>
<td width="35%">
<input type="hidden" id="city_id" value="<?php echo intval($city['city_id']); ?>">
<input type="hidden" id="city_url" value="<?php echo $city['url']; ?>">
<div class="search-form-geo-container">
<a href="javascript:void(0);" tabindex="-1" class="clear-button<?php if(intval($city['city_id'])==0){ ?> hidden<?php } ?>" title="<?php echo l('list_city_inall_long'); ?>"><i class="fa fa-times"></i></a>
<input type="text" id="geo" value="<?php if(intval($city['city_id'])>0){ echo $city['geo']; } else { echo l('list_city_inall_short'); } ?>" placeholder="<?php echo l('list_city_start_type'); ?>" data-lang-inall="<?php echo l('list_city_inall_short'); ?>" class="form-control search-form-city-input">
</div>
</td>
<td width="20%">
<button class="form-control" type="submit" onclick="runIndexSearching(); return false;"><i class="fa fa-search"></i><?php echo l('list_search_submit'); ?></button>
</td>
</table>
</div>
<div class="clear"></div>
</form>
</div>
</div>
</div>

<script type="text/javascript">
$(function(){
$('#geo').typeahead({
ajax:{
url:langPrefix+'/ajax/geo/',
timeout:400,
displayField:'name',
triggerLength:1
}
});
});
</script>

<?php
$sfic=0;
$sficq=0;
?>

<script type="text/javascript">
active_cat=0;
</script>

<div class="index-categories">
<?php while($cat=mysql_fetch_assoc($get_cats)){ ?>
<?php
$categories_to_show=array();
$get_categories_to_show=mysql_query("SELECT * FROM `categories` WHERE `parent_id`='".intval($cat['id'])."' AND `active`='1' ORDER BY `sort` ASC;");
$ctsi=1;
$sctsi=1;
while($catshow=mysql_fetch_assoc($get_categories_to_show)){
$categories_to_show[$ctsi][$sctsi]=$catshow;
if($sctsi==4){
$sctsi=0;
$ctsi++;
}
$sctsi++;
}
?>
<div class="index-categories-item">
<table>
<tr>
<td>
<div class="index-categories-item-icon">
<a href="<?php echo $langPrefix; ?>/<?php if(intval($city['city_id'])>0){ echo $city['url']; } else { echo 'list'; } ?>/<?php echo $cat['url']; ?>/" data-url="<?php echo $cat['url']; ?>"<?php if(count($categories_to_show)>0){ ?> onclick="if(active_cat==<?php echo $cat['id']; ?>){ $('.index-subcats').html(''); active_cat=0; } else { $('.index-subcats').html(''); $(this).closest('.index-categories-item').nextAll('.index-subcats').first().html($('#subcats_<?php echo $cat['id']; ?>').html()); active_cat=<?php echo $cat['id']; ?>; } $(this).blur(); return false;"<?php } ?>><img src="/images/cats/<?php echo $cat['id']; ?>.png"></a>
</div>
</td>
<td>
<a href="<?php echo $langPrefix; ?>/<?php if(intval($city['city_id'])>0){ echo $city['url']; } else { echo 'list'; } ?>/<?php echo $cat['url']; ?>/" data-url="<?php echo $cat['url']; ?>"<?php if(count($categories_to_show)>0){ ?> onclick="if(active_cat==<?php echo $cat['id']; ?>){ $('.index-subcats').html(''); active_cat=0; } else { $('.index-subcats').html(''); $(this).closest('.index-categories-item').nextAll('.index-subcats').first().html($('#subcats_<?php echo $cat['id']; ?>').html()); active_cat=<?php echo $cat['id']; ?>; } $(this).blur(); return false;"<?php } ?>><?php echo htmlspecialchars($cat['name_'.$config['lang']]); ?></a>
</td>
</tr>
</table>
</div>
<?php
$sfic++;
$sficq++;
if($sfic==0){
$sficq=1;
}
if($sfic%4==1){
$sficq=1;
}
?>
<div id="subcats_<?php echo $cat['id']; ?>" style="width:100%;" class="pull-left hidden">
<div class="index-category-selector">
<div class="index-category-selector-arrow" style="left:<?php echo 14+222*($sficq-1); ?>px;"></div>
<div class="index-category-selector-title">
<div class="pull-left">
<a data-caturl="<?php echo $cat['url']; ?>" href="<?php echo $langPrefix; ?>/<?php if(intval($city['city_id'])>0){ echo $city['url']; } else { echo 'list'; } ?>/<?php echo $cat['url']; ?>/"><?php echo l('list_all_items_in_category'); ?><i class="fa fa-chevron-right"></i></a>
</div>
<div class="clear"></div>
</div>
<div class="index-category-selector-body">
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
?>
<td width="25%">
<?php if(is_array($cell)){ ?>
<a href="<?php echo $langPrefix; ?>/<?php if(intval($city['city_id'])>0){ echo $city['url']; } else { echo 'list'; } ?>/<?php echo $cat['url']; ?>/<?php echo $cell['url']; ?>/" data-caturl="<?php echo $cat['url']; ?>/<?php echo $cell['url']; ?>" title="<?php echo htmlspecialchars($cell['name_'.$config['lang']]); ?>"><?php echo htmlspecialchars(cutString($cell['name_'.$config['lang']], 24, '...')); ?></a>
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
</div>
</div>
<?php if($sfic%4==0 || mysql_num_rows($get_cats)==$sfic){ ?>
<div  style="width:100%;" class="pull-left index-subcats"></div>
<?php } ?>
<?php } ?>
<div class="clear"></div>
</div>