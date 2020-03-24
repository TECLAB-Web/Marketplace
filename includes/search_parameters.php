<?php
if(mysql_num_rows($get_parameters)>0){
?>
<div class="search-form-filters">
<?php
mysql_data_seek($get_parameters, 0);
$params_count=1;
$param_pairs=0;
$params_columns=1;
while($param=mysql_fetch_assoc($get_parameters)){
$param['validators']=unserialize($param['validators']);
$param['values']=array();
$get_values=mysql_query("SELECT * FROM `category_parameter_values` WHERE (`keys` LIKE '".$param['key'].",%' OR `keys` LIKE '%,".$param['key'].",%' OR `keys` LIKE '%,".$param['key']."' OR `keys`='".$param['key']."') AND FIND_IN_SET(".$search_category.", `cids`) AND `active`='1' ORDER BY `sort` ASC;");
while($value=mysql_fetch_assoc($get_values)){
$param['values'][$value['key']]=$value['value_'.$config['lang']];
}
if($param['type']=='price' || $param['type']=='salary'){
$param['suffix_'.$config['lang']]=$currencies[$selected_currency];
}
?>
<?php if($param['type']=='select' || $param['type']=='checkboxes'){ ?>
<?php if($params_columns>5 || $param_pairs%2==0){ ?>
<div class="search-form-filter" data-type="<?php echo $param['type']; ?>">
<?php } ?>
<div class="search-form-filter-input">
<input type="text" name="search[<?php echo $param['url_key']; ?>]" class="form-control<?php if(trim($search[$param['url_key']])!=''){ ?> filled-input<?php } ?>" value="<?php echo htmlspecialchars(trim($search[$param['url_key']])); ?>" placeholder="<?php echo $param['label_'.$config['lang']]; ?>" readonly>
<i class="fa fa-chevron-down"></i>
<div class="filled-label<?php if(trim($search[$param['url_key']])==''){ ?> hidden<?php } ?>"><span><?php echo implode(', ', array_intersect_key($param['values'], array_flip(explode(',', $search[$param['url_key']])))); ?></span><i class="fa fa-times" title="<?php echo l('list_param_clear'); ?>"></i></div>
<?php if(count($param['values'])>0){ ?>
<ul class="dropdown-menu hidden">
<?php foreach($param['values'] as $key=>$label){ ?>
<?php
$is_selected=((in_array($key, explode(',', trim($search[$param['url_key']]))))?true:false);
?>
<li data-value="<?php echo $key; ?>" data-label="<?php echo $label; ?>" data-selected="<?php echo (($is_selected)?'true':'false'); ?>">
<a href="javascript:void(0);"><i class="fa fa-square-o<?php if($is_selected){ ?> hidden<?php } ?>"></i><i class="fa fa-check-square-o<?php if(!$is_selected){ ?> hidden<?php } ?>"></i><?php echo $label; ?></a>
</li>
<?php } ?>
</ul>
<?php } ?>
</div>
<?php if($params_columns>5 || !($param_pairs%2==0) || $params_count==mysql_num_rows($get_parameters)){ ?>
</div>
<?php } ?>
<?php $param_pairs=$param_pairs+1; ?>
<?php $params_count++; ?>
<?php if($param_pairs%2==0){ $params_columns++; } ?>
<?php } ?>
<?php if($param['type']=='input' || $param['type']=='price' || $param['type']=='salary'){ ?>
<div class="search-form-filter" data-type="<?php echo $param['type']; ?>">
<div class="search-form-filter-input" data-prefix="<?php echo l('list_prefix_from'); ?>" data-suffix="<?php echo $param['suffix_'.$config['lang']]; ?>">
<input type="text" name="search[<?php echo $param['url_key']; ?>][from]" class="form-control<?php if(trim($search[$param['url_key']]['from'])!=''){ ?> filled-input<?php } ?>" value="<?php echo htmlspecialchars(trim($search[$param['url_key']]['from'])); ?>" placeholder="<?php echo $param['label_'.$config['lang']]; ?><?php if($param['suffix_'.$config['lang']]!=''){ ?> (<?php echo $param['suffix_'.$config['lang']]; ?>)<?php } ?>, <?php echo l('list_prefix_from'); ?>">
<div class="filled-label<?php if(trim($search[$param['url_key']]['from'])==''){ ?> hidden<?php } ?>"><span><?php echo l('list_prefix_from'); ?> <?php echo htmlspecialchars(trim($search[$param['url_key']]['from'])); ?> <?php echo $param['suffix_'.$config['lang']]; ?></span><i class="fa fa-times" title="<?php echo l('list_param_clear'); ?>"></i></div>
</div>
<div class="search-form-filter-input" data-prefix="<?php echo l('list_prefix_to'); ?>" data-suffix="<?php echo $param['suffix_'.$config['lang']]; ?>">
<div class="bracket"></div>
<input type="text" name="search[<?php echo $param['url_key']; ?>][to]" class="form-control<?php if(trim($search[$param['url_key']]['to'])!=''){ ?> filled-input<?php } ?>" value="<?php echo htmlspecialchars(trim($search[$param['url_key']]['to'])); ?>" placeholder="<?php echo $param['label_'.$config['lang']]; ?><?php if($param['suffix_'.$config['lang']]!=''){ ?> (<?php echo $param['suffix_'.$config['lang']]; ?>)<?php } ?>, <?php echo l('list_prefix_to'); ?>">
<div class="filled-label<?php if(trim($search[$param['url_key']]['to'])==''){ ?> hidden<?php } ?>"><span><?php echo l('list_prefix_to'); ?> <?php echo htmlspecialchars(trim($search[$param['url_key']]['to'])); ?> <?php echo $param['suffix_'.$config['lang']]; ?></span><i class="fa fa-times" title="<?php echo l('list_param_clear'); ?>"></i></div>
</div>
</div>
<?php $param_pairs=$param_pairs+2; ?>
<?php $params_count++; ?>
<?php $params_columns++; ?>
<?php } ?>
<?php
}
?>
</div>
<?php
}
?>