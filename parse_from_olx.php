<?php
//exit;
include "init.php";
set_time_limit(60*4);
//$json=file_get_contents('http://olx.ua/i2/list/?json=1&search[user_id]=26707277');
$json=file_get_contents('http://olx.ua/i2/list/?json=1&longlist=1');
//$json=file_get_contents('http://olx.ua/i2/list/?json=1&search[user_id]=2937227');
$json=json_decode($json, true);
//var_dump($json); exit;
$ads=$json['ads'];
foreach($ads as $ad){
//var_dump($ad); exit;
if(intval($ad['is_job'])>0){ continue; }
$_POST=array();
$_POST['action']='add';
$_POST['title']=$ad['title'];
$_POST['category_id']=$ad['category_id'];
$_POST['param_price']=$ad['price_type'];
$_POST['currency']=array_search(end(explode(' ', $ad['list_label'])), $currencies);
$_POST['price']=str_replace(array(end(explode(' ', $ad['list_label'])), ' '), '', $ad['list_label']);
$_POST['description']=$ad['description'];
$ad_params=array();
$get_parameters=mysql_query("SELECT * FROM `category_parameters` WHERE FIND_IN_SET(".$ad['category_id'].", `cids`) AND `type`!='hidden' AND `type`!='price' AND `type`!='salary' ORDER BY `sort` ASC;");
while($param=mysql_fetch_assoc($get_parameters)){
$get_values=mysql_query("SELECT * FROM `category_parameter_values` WHERE (`keys` LIKE '".$param['key'].",%' OR `keys` LIKE '%,".$param['key'].",%' OR `keys` LIKE '%,".$param['key']."' OR `keys`='".$param['key']."') AND FIND_IN_SET(".$ad['category_id'].", `cids`) ORDER BY `sort` ASC;");
while($value=mysql_fetch_assoc($get_values)){
$param['values'][$value['key']]=$value['value_'.$config['lang']];
}
foreach($ad['params'] as $aparam){
if(trim($aparam[0])==trim($param['label_ru'])){
if($param['type']=='select'){
foreach($param['values'] as $key=>$value){
if(trim($aparam[1])==trim($value)){
$_POST[$param['post_key']]=trim($key);
}
}
} elseif($param['type']=='checkboxes'){
$_POST[$param['post_key']]=array();
foreach($param['values'] as $key=>$value){
if(mb_strpos(trim($aparam[1]), trim($value))!==false){
$_POST[$param['post_key']][]=trim($key);
}
}
} elseif($param['type']=='input'){
$_POST[$param['post_key']]=str_replace(array(' ', $param['suffix_ru']), '', trim($aparam[1]));
}
}
}
if(is_array($_POST[$param['post_key']]) && $param['type']=='date'){
$ad_params[$param['name']]=strtotime($_POST[$param['post_key']]['year'].'-'.$_POST[$param['post_key']]['month'].'-'.$_POST[$param['post_key']]['day']);
} elseif(is_array($_POST[$param['post_key']])){
$ad_params[$param['name']]=trim(implode(',', $_POST[$param['post_key']]));
} else {
$ad_params[$param['name']]=trim($_POST[$param['post_key']]);
}
}
$_POST['private_business']=((intval($ad['business'])==1)?'business':'private');
$_POST['nospam']='1';
$_POST['person']=trim($ad['person']);
$rjson=file_get_contents('http://olx.ua/i2/ajax/selector/locationregion/?json=1&region_id='.$ad['region_id']);
$rjson=json_decode($rjson, true);
$check_region=mysql_query("SELECT * FROM `regions` WHERE `title_ru`='"._F($rjson['region']['name'])."';");
if(!mysql_num_rows($check_region)){
continue;
}
$region=mysql_fetch_assoc($check_region);
$cjson=file_get_contents('http://olx.ua/i2/ajax/selector/locationcity/?json=1&city_id='.$ad['city_id']);
$cjson=json_decode($cjson, true);
$check_city=mysql_query("SELECT * FROM `cities` WHERE `region_id`='".intval($region['region_id'])."' AND `title_ru`='"._F($cjson['city']['name'])."';");
if(!mysql_num_rows($check_city)){
continue;
}
$city=mysql_fetch_assoc($check_city);
$_POST['city_id']=$city['city_id'];
foreach(array('phone', 'skype', 'gg') as $cm){
$cmjson=file_get_contents('http://olx.ua/i2/ajax/ad/getcontact/?type='.$cm.'&json=1&id='.$ad['id']);
$cmn=str_replace(' ', '', trim(reset(explode('"}', end(explode('"uri":"', $cmjson))))));
$_POST[$cm]=$cmn;
}
if(!mysql_num_rows(mysql_query("SELECT * FROM `ads` WHERE `title`='"._F($_POST['title'])."' AND `phone`='"._F($_POST['phone'])."' AND `skype`='"._F($_POST['skype'])."' AND `gg`='"._F($_POST['gg'])."';"))){
$apid=uniqid('');
$html=file_get_contents(str_replace('olx.ua', 'olx.ua/i2', $ad['url']));
preg_match_all('/https:\/\/olxua-ring([0-9]+)\.akamaized\.net\/images_slandocomua\/([0-9]+)_([0-9]+)_1000x700_([0-9a-zA-Z_-]+)\.jpg/i', $html, $images);
//var_dump($images); exit;
if(count($images)>0){
$images=$images[0];
if(count($images)>0){
foreach($images as $image){
$photo=file_get_contents($image);
if(trim($photo)!=''){
$key=uniqid('');
mkdir('uploads/'.date("Y").'/', 0777);
mkdir('uploads/'.date("Y").'/'.date("m").'/', 0777);
mkdir('uploads/'.date("Y").'/'.date("m").'/'.date("d").'/', 0777);
$filename='uploads/'.date("Y").'/'.date("m").'/'.date("d").'/'.$key.'.jpg';
file_put_contents($filename, $photo);
$photo=null;
$max_order=mysql_result(mysql_query("SELECT MAX(`order`) FROM `ad_photos` WHERE `apid`='"._F($apid)."';"), 0, 0);
mysql_query("INSERT INTO `ad_photos` SET `apid`='"._F($apid)."', `key`='"._F($key)."', `original`='"._F(end(explode('/', $image)))."', `file`='"._F($filename)."', `rev`='".$time."', `order`='".(intval($max_order)+1)."';");
generateThumbnail($apid, $key, 650, 450, 1);
generateThumbnail($apid, $key, 261, 203, 1);
generateThumbnail($apid, $key, 94, 72, 0);
generateThumbnail($apid, $key, 92, 72, 0);
}
}
}
}
$_POST['apid']=$apid;
//var_dump($ad_params); exit;
//var_dump($_POST); exit;
$userid='16';
if(intval($_POST['category_id'])>0){
$selectedCategory=intval($_POST['category_id']);
}
if($selectedCategory>0){
$selected_cats=array();
$main_cat_id=0;
$get_cat=mysql_query("SELECT * FROM `categories` WHERE `id`='".$selectedCategory."' AND `active`='1';");
if(mysql_num_rows($get_cat)){
$cat=mysql_fetch_assoc($get_cat);
$current_cat=$cat;
$selected_cats[]=$cat['name_'.$config['lang']];
$category_id=$main_cat_id=$cat['id'];
$maxPhotos=$cat['max_photos'];
$get_cat=mysql_query("SELECT * FROM `categories` WHERE `id`='".$cat['parent_id']."' AND `active`='1';");
if(mysql_num_rows($get_cat)){
$cat=mysql_fetch_assoc($get_cat);
$selected_cats[]=$cat['name_'.$config['lang']];
$parent_category_id=$main_cat_id=$cat['id'];
$get_cat=mysql_query("SELECT * FROM `categories` WHERE `id`='".$cat['parent_id']."' AND `active`='1';");
if(mysql_num_rows($get_cat)){
$cat=mysql_fetch_assoc($get_cat);
$selected_cats[]=$cat['name_'.$config['lang']];
$parent_parent_category_id=$main_cat_id=$cat['id'];
}
}
} else {
$selectedCategory=0;
}
}
$index=Words2AllForms(trim($_POST['title'])." ".trim($_POST['description']));
$base_price=0;
if(intval($_POST['salary_from'])>0 && intval($_POST['salary_to'])>0){
$base_price=intval($_POST['salary_from'])+(intval($_POST['salary_to'])-intval($_POST['salary_from']))/2;
$base_price=$base_price/$currency_rates[trim($_POST['currency'])];
} elseif(intval($_POST['salary_from'])>0){
$base_price=intval($_POST['salary_from']);
$base_price=$base_price/$currency_rates[trim($_POST['currency'])];
} elseif(intval($_POST['salary_to'])>0){
$base_price=intval($_POST['salary_to']);
$base_price=$base_price/$currency_rates[trim($_POST['currency'])];
} elseif(intval($_POST['price'])>0){
$base_price=intval($_POST['price']);
$base_price=$base_price/$currency_rates[trim($_POST['currency'])];
}
$time=time();
$days=30;
$create=mysql_query("INSERT INTO `ads` SET `userid`='".$userid."', `parent_parent_category_id`='".$parent_parent_category_id."', `parent_category_id`='".$parent_category_id."', `category_id`='".$category_id."', `title`='"._F($_POST['title'])."', `description`='"._F($_POST['description'])."', `apid`='"._F($_POST['apid'])."', `city_id`='".intval($_POST['city_id'])."', `person`='"._F($_POST['person'])."', `phone`='"._F($_POST['phone'])."', `gg`='"._F($_POST['gg'])."', `skype`='"._F($_POST['skype'])."', `nospam`='0', `price_type`='"._F($_POST['param_price'])."', `price`='".intval($_POST['price'])."', `salary_arranged`='"._F($_POST['salary_arranged'])."', `salary_from`='"._F($_POST['salary_from'])."', `salary_to`='"._F($_POST['salary_to'])."', `currency`='"._F($_POST['currency'])."', `base_price`='".$base_price."', `private_business`='"._F($_POST['private_business'])."', `offer_seek`='"._F($_POST['offer_seek'])."', `index`='"._F($index)."', `days`='".$days."', `time`='".$time."', `time_to`='".($time+60*60*24*$days)."', `active`='1';");
$aid=mysql_insert_id();
foreach($ad_params as $apk=>$apv){
mysql_query("INSERT INTO `ad_parameters` SET `aid`='".$aid."', `key`='".$apk."', `values`='"._F($apv)."';");
}
}
//exit;
}
?>