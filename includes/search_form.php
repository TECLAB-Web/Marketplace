<div class="search-form" id="search_form">
<form action="/list/" method="GET" autocomplete="off">
<?php if(intval($search['user_id'])>0){ ?>
<input type="hidden" id="user_id" name="search[user_id]" value="<?php echo intval($search['user_id']); ?>">
<?php } ?>
<div class="search-form-main">
<table>
<td width="35%">
<div class="search-form-query-container">
<a href="javascript:void(0);" tabindex="-1" class="clear-button<?php if(trim($search['query'])==''){ ?> hidden<?php } ?>" title="<?php echo l('list_clear_query'); ?>"><i class="fa fa-times"></i></a>
<input type="text" id="query" name="search[query]" value="<?php echo htmlspecialchars(trim($search['query'])); ?>" placeholder="<?php echo l('search'); ?>" class="form-control search-form-query-input<?php if(trim($search['query'])!=''){ ?> filled-input<?php } ?>">
</div>
</td>
<td width="35%">
<input type="hidden" id="city_id" value="<?php echo intval($city['city_id']); ?>">
<input type="hidden" id="city_url" value="<?php echo $city['url']; ?>">
<div class="search-form-geo-container">
<a href="javascript:void(0);" tabindex="-1" class="clear-button<?php if(intval($city['city_id'])==0){ ?> hidden<?php } ?>" title="<?php echo l('list_city_inall_long'); ?>"><i class="fa fa-times"></i></a>
<input type="text" id="geo" value="<?php if(intval($city['city_id'])>0){ echo $city['geo']; } else { echo l('list_city_inall_short'); } ?>" placeholder="<?php echo l('list_city_start_type'); ?>" data-lang-inall="<?php echo l('list_city_inall_short'); ?>" class="form-control search-form-city-input<?php if(intval($city['city_id'])!=0){ ?> filled-input<?php } ?>">
</div>
</td>
<td width="30%">
<input type="hidden" id="category_id" value="<?php echo intval($current_category); ?>">
<input type="hidden" id="category_url" value="<?php echo getCategoryURL($current_category); ?>">
<div class="search-form-category-container">
<a href="javascript:void(0);" tabindex="-1" class="clear-button<?php if(intval($current_category)==0){ ?> hidden<?php } ?>" title="<?php echo l('list_category_inall_long'); ?>"><i class="fa fa-times"></i></a>
<input type="text" id="category" class="form-control search-form-category-select<?php if(intval($current_category)!=0){ ?> filled-input<?php } ?>" value="<?php if(intval($current_category)!=0){ echo htmlspecialchars(end($selected_cats)['name_'.$config['lang']]); } else { echo l('list_category_inall_short'); } ?>"  onclick="listSearchingCats('<?php echo (($cat_has_subcats)?$search_category:$search_category_parent); ?>', '<?php echo getCategoryURL(($cat_has_subcats)?$search_category:$search_category_parent); ?>'); return false;" data-lang-inall="<?php echo l('list_category_inall_short'); ?>" readonly>
</div>
</td>
</table>
</div>
<div class="search-form-options-container">
<div class="checkbox">
<input type="checkbox" name="search[only_photos]" value="1" id="only_photos" onclick="runSearching();"<?php if(intval($search['only_photos'])==1){ ?> checked<?php } ?>>
<label for="only_photos"><?php echo l('search_only_with_photos'); ?></label>
</div>
</div>
<div class="search-form-category-selector-container hidden">
<div class="search-form-category-selector-arrow"></div>
<div class="search-form-category-selector">
<?php //include "includes/category_selecter.php"; ?>
</div>
</div>
<div class="search-form-filters-container">
<?php include "includes/search_parameters.php"; ?>
</div>
<div class="clear"></div>
<div class="search-form-submit">
<table>
<tr>
<td>
<a href="javascript:void(0);" class="favorite-link" data-id="<?php echo $search_id; ?>" data-type="search">
<span class="add-favorite add-favorite-<?php echo $search_id; ?><?php if($search_favorite){ ?> hidden<?php } ?>"><table><tr><td><i class="fa fa-star-o"></i><i class="fa fa-star"></i></td><td><?php echo l('add_to_favorites'); ?></td></tr></table></span>
<span class="delete-favorite delete-favorite-<?php echo $search_id; ?><?php if(!$search_favorite){ ?> hidden<?php } ?>"><table><tr><td><i class="fa fa-star"></i></td><td><?php echo l('remove_from_favorites'); ?></td></tr></table></span>
</a>
</td>
<td>
<button type="submit" onclick="runSearching(); return false;"><i class="fa fa-search"></i><?php echo l('list_search_submit'); ?></button>
</td>
</tr>
</table>
</div>
<div class="clear"></div>
</form>
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

<div class="top-tabs-ul">
<ul class="nav nav-tabs">
<?php if(intval($search['user_id'])==0){ ?>
<?php foreach($page_tabs as $key=>$val){ ?>
<?php
$qs='';
$sparams=$_GET;
unset($sparams['controller']);
unset($sparams['page']);
unset($sparams['mode']);
unset($sparams['search']['private_business']);
if($key!='all'){
$sparams['search']['private_business']=$key;
}
if(count($sparams)>0 && http_build_query($sparams)!=''){
$qs='?'.str_replace(array('%5B', '%5D'), array('[', ']'), http_build_query($sparams));
}
?>
<li class="<?php if($current_tab==$key){ ?>active<?php } else { ?>not-active<?php } ?>"><a href="<?php echo $langPrefix; ?><?php echo reset(explode('?', $_SERVER['REQUEST_URI'])); ?><?php echo $qs; ?>" onclick="runSearching($(this).attr('href')); return false;"><?php echo $val['tab']; ?></a></li>
<?php } ?>
<?php } ?>
</ul>
<div class="top-tabs-additionals">
<?php if(count($currencies)>1){ ?>
<div class="top-tabs-additional">
<?php echo l('list_currency'); ?>
<?php
$qs='';
$sparams=$_GET;
unset($sparams['controller']);
unset($sparams['page']);
unset($sparams['mode']);
unset($sparams['search']['currency']);
if(count($sparams)>0 && http_build_query($sparams)!=''){
$qs='?'.str_replace(array('%5B', '%5D'), array('[', ']'), http_build_query($sparams));
}
?>
<?php foreach($currencies as $key=>$val){ ?>
<?php
$qs='';
$sparams=$_GET;
unset($sparams['controller']);
unset($sparams['page']);
unset($sparams['mode']);
unset($sparams['search']['currency']);
if($key!=reset(array_keys($currencies))){
$sparams['search']['currency']=$key;
}
if(count($sparams)>0 && http_build_query($sparams)!=''){
$qs='?'.str_replace(array('%5B', '%5D'), array('[', ']'), http_build_query($sparams));
}
?>
<a href="<?php echo $langPrefix; ?><?php echo reset(explode('?', $_SERVER['REQUEST_URI'])); ?><?php echo $qs; ?>" class="<?php if(trim($selected_currency)==$key){ ?>active<?php } else { ?>not-active<?php } ?>" onclick="runSearching($(this).attr('href')); return false;"><?php echo $val; ?></a>
<?php } ?>
</div>
<?php } ?>
<div class="top-tabs-additional">
<?php echo l('list_sort'); ?>
<?php foreach($page_orders as $key=>$val){ ?>
<?php
$qs='';
$sparams=$_GET;
unset($sparams['controller']);
unset($sparams['page']);
unset($sparams['mode']);
unset($sparams['search']['order']);
if($key!='time:desc'){
$sparams['search']['order']=$key;
}
if(count($sparams)>0 && http_build_query($sparams)!=''){
$qs='?'.str_replace(array('%5B', '%5D'), array('[', ']'), http_build_query($sparams));
}
?>
<a href="<?php echo $langPrefix; ?><?php echo reset(explode('?', $_SERVER['REQUEST_URI'])); ?><?php echo $qs; ?>" class="<?php if($pre_order==$key){ ?>active<?php } else { ?>not-active<?php } ?>" onclick="runSearching($(this).attr('href')); return false;"><?php echo $val['tab']; ?></a>
<?php } ?>
</div>
</div>
</div>