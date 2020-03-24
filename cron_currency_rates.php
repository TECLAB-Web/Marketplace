<?php
include "init.php";
set_time_limit(3600);
$url='http://www.apilayer.net/api/live?access_key=a181703b6cc02d3fd788454b3915efad&format=1';
$file=file_get_contents($url);
$data=json_decode($file, true);
if(count($data['quotes'])>0){
mysql_query("DELETE FROM `currency_rates`;");
foreach($currencies as $key=>$val){
$data['quotes']['USDBYR']=$data['quotes']['USDBYR']/10000;
mysql_query("INSERT INTO `currency_rates` SET `currency`='".$key."', `rate`='".floatval($data['quotes']['USD'.$key])."', `updated`='".$time."';");
}
$currency_rates=array();
$get_currency_rates=mysql_query("SELECT * FROM `currency_rates`;");
while($crate=mysql_fetch_assoc($get_currency_rates)){
$currency_rates[$crate['currency']]=$crate['rate'];
}
$get_ads=mysql_query("SELECT * FROM `ads` WHERE `price`>'0' OR `salary_from`>0 OR `salary_to`>0 AND `active` IN(1,2) ORDER BY `time` DESC LIMIT 10000;");
$ads_corrected=0;
while($ad=mysql_fetch_assoc($get_ads)){
$base_price=0;
if(intval($ad['salary_from'])>0 && intval($ad['salary_to'])>0){
$base_price=intval($ad['salary_from'])+(intval($ad['salary_to'])-intval($ad['salary_from']))/2;
$base_price=$base_price/$currency_rates[trim($ad['currency'])];
} elseif(intval($ad['salary_from'])>0){
$base_price=intval($ad['salary_from']);
$base_price=$base_price/$currency_rates[trim($ad['currency'])];
} elseif(intval($ad['salary_to'])>0){
$base_price=intval($ad['salary_to']);
$base_price=$base_price/$currency_rates[trim($ad['currency'])];
} elseif(intval($ad['price'])>0){
$base_price=intval($ad['price']);
$base_price=$base_price/$currency_rates[trim($ad['currency'])];
}
mysql_query("UPDATE `ads` SET `base_price`='".$base_price."' WHERE `aid`='".$ad['aid']."';");
$ads_corrected=$ads_corrected+1;
}
echo 'Updated: '.$ads_corrected;
} else {
echo 'Failed.';
}
?>