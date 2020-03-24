<?php
if($selectedCategory>0){
?>
<?php if($current_cat['offer_seek']==1){ ?>
<div class="form-group">
<label class="col-sm-3 control-label"><?php echo l('add_offer_seek'); ?> <span class="input-required">*</span></label>
<div class="col-sm-4" style="width:auto;max-width:33%;">
<select size="1" name="offer_seek" class="form-control" style="width:auto;max-width:100%;" onchange="if(this.value=='offer' || this.value==''){ $('.only_offer').removeClass('hidden'); } else { $('.only_offer').addClass('hidden'); }">
<option value=""><?php echo l('add_select_param_value'); ?></option>
<option value="offer"<?php if($ad['offer_seek']=='offer'){ ?> selected<?php } ?>><?php echo $current_cat['offer_name_adding_'.$config['lang']]; ?></option>
<option value="seek"<?php if($ad['offer_seek']=='seek'){ ?> selected<?php } ?>><?php echo $current_cat['seek_name_adding_'.$config['lang']]; ?></option>
</select>
</div>
</div>
<?php } ?>
<?php
$get_parameters=mysql_query("SELECT `category_parameter_sort`.`sort`, `category_parameters`.* FROM `category_parameters`, `category_parameter_sort` WHERE `category_parameter_sort`.`key`=`category_parameters`.`key` AND `category_parameter_sort`.`cid`=".$selectedCategory." AND FIND_IN_SET(".$selectedCategory.", `category_parameters`.`cids`) AND `category_parameters`.`type`!='hidden' AND `category_parameters`.`active`='1' ORDER BY `category_parameter_sort`.`sort` ASC;");
while($param=mysql_fetch_assoc($get_parameters)){
$param['validators']=unserialize($param['validators']);
$param['values']=array();
$get_values=mysql_query("SELECT * FROM `category_parameter_values` WHERE (`keys` LIKE '".$param['key'].",%' OR `keys` LIKE '%,".$param['key'].",%' OR `keys` LIKE '%,".$param['key']."' OR `keys`='".$param['key']."') AND FIND_IN_SET(".$selectedCategory.", `cids`) AND `active`='1' ORDER BY `sort` ASC;");
while($value=mysql_fetch_assoc($get_values)){
$param['values'][$value['key']]=$value['value_'.$config['lang']];
}
//var_dump($param);
?>
<div class="form-group<?php if($param['offer_seek']=='offer'){ ?> only_offer<?php } ?>">
<label class="col-sm-3 control-label"><?php echo htmlspecialchars($param['label_'.$config['lang']]); ?><?php if(intval($param['validators']['required'])==1){ ?> <span class="input-required">*</span><?php } ?></label>
<?php if($param['type']=='salary'){ ?>
<div class="col-sm-8" id="salaryBlock">
<div style="display:inline-block;">
<input type="text" name="salary_from" value="<?php if(intval($ad['salary_from'])>0){ echo $ad['salary_from']; } ?>" placeholder="<?php echo l('add_salary_from'); ?>" class="form-control salary_from pull-left">
<div class="pull-left salary_delimiter">-</div>
<input type="text" name="salary_to" value="<?php if(intval($ad['salary_to'])>0){ echo $ad['salary_to']; } ?>" placeholder="<?php echo l('add_salary_to'); ?>" class="form-control salary_to pull-left">
<?php if(count($currencies)>1){ ?>
<select size="1" name="currency" class="form-control currency pull-left" style="margin-top:0;">
<?php foreach($currencies as $key=>$val){ ?>
<option value="<?php echo $key; ?>"<?php if(trim($ad['currency'])==$key){ ?> selected<?php } ?>><?php echo $val; ?></option>
<?php } ?>
</select>
<?php } else { ?>
<div class="pull-left" style="margin-top:6px;margin-left:7px;font-size:13px;font-weight:bold;">
<?php echo reset($currencies); ?>
</div>
<?php } ?>
<div class="checkbox pull-left arranged" style="margin-top:-1px;">
<input type="checkbox" name="salary_arranged" value="arranged" id="checkbox_salary_arranged"<?php if(trim($ad['salary_arranged'])=='arranged'){ ?> checked<?php } ?>>
<label for="checkbox_salary_arranged"><?php echo l('price_arranged'); ?></label>
</div>
<div class="clear"></div>
</div>
</div>
<?php } ?>
<?php if($param['type']=='price'){ ?>
<div class="col-sm-8" id="summBlock">
<?php foreach($param['values'] as $key=>$val){ ?>
<?php if($key!='arranged'){ ?>
<div>
<div style="display:inline-block;">
<?php if(!(count($param['values'])==2 && isset($param['values']['arranged']) && isset($param['values']['price'])) || count($param['values'])>2){ ?>
<div class="radio price-type pull-left">
<input type="radio" class="pull-left" name="<?php echo $param['post_key']; ?>" value="<?php echo $key; ?>" id="radio_<?php echo $param['post_key']; ?>_<?php echo $key; ?>" onclick="<?php if($key=='price'){ ?>$('.form-control.price').removeAttr('disabled'); $('.form-control.currency').removeAttr('disabled'); $('#checkbox_price_arranged').removeAttr('disabled'); $('.form-control.price').focus();<?php } else { ?>$('.form-control.price').val('').attr('disabled', 'disabled'); $('.form-control.currency').attr('disabled', 'disabled'); $('#checkbox_price_arranged').removeAttr('checked').attr('disabled', 'disabled');<?php } ?>"<?php if(trim($ad['price_type'])==$key || trim($ad['price_type'])=='arranged' || (trim($ad['price_type'])=='' && $key=='price')){ ?> checked<?php } ?>>
<label for="radio_<?php echo $param['post_key']; ?>_<?php echo $key; ?>" class="pull-left">
<?php echo $val; ?>
</label>
</div>
<?php } ?>
<?php if($key=='price'){ ?>
<?php if((count($param['values'])==2 && isset($param['values']['arranged']) && isset($param['values']['price'])) || (count($param['values'])==1 && isset($param['values']['price']))){ ?>
<input type="hidden" name="<?php echo $param['post_key']; ?>" value="<?php echo $key; ?>">
<?php } ?>
<input type="text" name="price" value="<?php if(intval($ad['price'])>0){ echo $ad['price']; } ?>" class="form-control price pull-left"<?php if((count($param['values'])==2 && isset($param['values']['arranged']) && isset($param['values']['price'])) || (count($param['values'])==1 && isset($param['values']['price']))){ ?> style="margin-left:0;"<?php } ?><?php if($edit && trim($ad['price_type'])!=$key && trim($ad['price_type'])!='arranged'){ ?> disabled="disabled"<?php } ?>>
<?php if(count($currencies)>1){ ?>
<select size="1" name="currency" class="form-control currency pull-left"<?php if($edit && trim($ad['price_type'])!=$key && trim($ad['price_type'])!='arranged'){ ?> disabled="disabled"<?php } ?>>
<?php foreach($currencies as $ckey=>$val){ ?>
<option value="<?php echo $ckey; ?>"<?php if(trim($ad['currency'])==$ckey){ ?> selected<?php } ?>><?php echo $val; ?></option>
<?php } ?>
</select>
<?php } else { ?>
<div class="pull-left" style="margin-left:7px;font-size:13px;font-weight:bold;">
<?php echo reset($currencies); ?>
</div>
<input type="hidden" name="currency" value="<?php echo reset(array_keys($currencies)); ?>">
<?php } ?>
<?php if(isset($param['values']['arranged'])){ ?>
<div class="checkbox pull-left arranged">
<input type="checkbox" name="<?php echo $param['post_key']; ?>" value="arranged" id="checkbox_price_arranged"<?php if(trim($ad['price_type'])=='arranged'){ ?> checked<?php } ?><?php if($edit && trim($ad['price_type'])!=$key && trim($ad['price_type'])!='arranged'){ ?> disabled="disabled"<?php } ?>>
<label for="checkbox_price_arranged"><?php echo l('price_arranged'); ?></label>
</div>
<?php } ?>
<?php } ?>
<div class="clear"></div>
</div>
</div>
<?php } ?>
<?php } ?>
</div>
<?php } ?>
<?php if($param['type']=='date'){ ?>
<div class="col-sm-8">
<div>
<div style="display:inline-block;float:left;">
<input type="text" name="<?php echo $param['post_key']; ?>[day]" value="<?php if(intval($ad['params'][$param['name']])>0){ echo intval(date("d", intval($ad['params'][$param['name']]))); } ?>" placeholder="<?php echo l('add_day'); ?>" class="form-control day pull-left">
<select size="1" name="<?php echo $param['post_key']; ?>[month]" class="form-control month pull-left">
<option value=""><?php echo l('add_month'); ?></option>
<?php foreach($months_of as $key=>$val){ ?>
<?php if($key>0){ ?>
<option value="<?php echo $key; ?>"<?php if(intval($ad['params'][$param['name']])>0 && intval(date("m", intval($ad['params'][$param['name']])))==$key){ ?> selected<?php } ?>><?php echo $val; ?></option>
<?php } ?>
<?php } ?>
</select>
<input type="text" name="<?php echo $param['post_key']; ?>[year]" value="<?php if(intval($ad['params'][$param['name']])>0){ echo intval(date("Y", intval($ad['params'][$param['name']]))); } ?>" placeholder="<?php echo l('add_year'); ?>" class="form-control year pull-left">
</div>
</div>
</div>
<?php } ?>
<?php if($param['type']=='select'){ ?>
<div class="col-sm-4" style="width:auto;max-width:33%;">
<select size="1" name="<?php echo $param['post_key']; ?>" class="form-control" style="width:auto;max-width:100%;">
<option value=""><?php echo l('add_select_param_value'); ?></option>
<?php foreach($param['values'] as $key=>$val){ ?>
<option value="<?php echo $key; ?>"<?php if(trim($ad['params'][$param['name']])==$key){ ?> selected<?php } ?>><?php echo $val; ?></option>
<?php } ?>
</select>
</div>
<?php } ?>
<?php if($param['type']=='input'){ ?>
<div class="col-sm-3">
<input type="text" name="<?php echo $param['post_key']; ?>" class="form-control" value="<?php echo trim($ad['params'][$param['name']]); ?>">
</div>
<div class="col-sm-2 suffix-container">
<span class="suffix"><?php if($param['suffix_'.$config['lang']]!=''){ ?><?php echo $param['suffix_'.$config['lang']]; ?><?php } ?></span>
</div>
<?php } ?>
<?php if($param['type']=='checkboxes'){ ?>
<div class="col-sm-7"><?php foreach($param['values'] as $key=>$val){ ?><div class="checkbox" style="display:inline-block;width:33%;">
<input type="checkbox" name="<?php echo $param['post_key']; ?>[]" value="<?php echo $key; ?>" id="checkbox_<?php echo $param['post_key']; ?>_<?php echo $key; ?>"<?php if(in_array($key, explode(',', trim($ad['params'][$param['name']])))){ ?> checked<?php } ?>>
<label for="checkbox_<?php echo $param['post_key']; ?>_<?php echo $key; ?>"><?php echo $val; ?></label>
</div><?php } ?></div>
<?php } ?>
</div>
<?php
}
?>
<?php if($current_cat['private_business']==1){ ?>
<div class="form-group">
<label class="col-sm-3 control-label"><?php echo $current_cat['private_name_'.$config['lang']]; ?> / <?php echo $current_cat['business_name_'.$config['lang']]; ?> <span class="input-required">*</span></label>
<div class="col-sm-4" style="width:auto;max-width:33%;">
<select size="1" name="private_business" class="form-control" style="width:auto;max-width:100%;">
<option value=""><?php echo l('add_select_param_value'); ?></option>
<option value="private"<?php if($ad['private_business']=='private'){ ?> selected<?php } ?>><?php echo $current_cat['private_name_'.$config['lang']]; ?></option>
<option value="business"<?php if($ad['private_business']=='business'){ ?> selected<?php } ?>><?php echo $current_cat['business_name_'.$config['lang']]; ?></option>
</select>
</div>
</div>
<?php } ?>
<?php
}
?>