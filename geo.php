<?php include "init.php"; ?>
<?php
if(trim($_GET['mode'])=='ajax'){
header('Content-Type: application/json; charset=utf-8');
$result=array();
$_GET['text']=reset(explode(',', trim($_GET['text'])));
if(mb_strlen(trim($_GET['text']))>=1){
$qlim=array();
foreach($config['langs'] as $lang){
$qlim[]="(`regions`.`title_".$lang."`!='' AND `cities`.`title_".$lang."` LIKE '"._F($_GET['text'])."%')";
}
$check=mysql_query("SELECT `regions`.`title_".$config['lang']."` as `region_".$config['lang']."`, `cities`.`title_".$config['lang']."`, `cities`.`city_id`, `cities`.`url` FROM `cities`, `regions` WHERE `regions`.`region_id`=`cities`.`region_id` AND `cities`.`country_id`='".$config['country']."' AND (".implode(' OR ', $qlim).") ORDER BY `cities`.`important` DESC, `regions`.`title_".$config['lang']."` ASC, `cities`.`title_".$config['lang']."` ASC LIMIT 10;");
if(mysql_num_rows($check)){
while($city=mysql_fetch_assoc($check)){
$result[]=array('id'=>$city['city_id'], 'url'=>$city['url'], 'name'=>$city['title_'.$config['lang']], 'region'=>$city['region_'.$config['lang']]);
}
}
}
echo json_encode($result);
exit;
}
?>