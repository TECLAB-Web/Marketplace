<?php
include "init.php";
if(!in_array(trim($_GET['l']), $config['langs'])){
header("HTTP/1.0 404 Not Found");
include "404.php";
exit;
}
$_GET['ref']=str_replace($langPrefix, '', str_replace('https://'.$config['siteurl'], '', str_replace('http://'.$config['siteurl'], '', $_SERVER['HTTP_REFERER'])));
$_SESSION['lang']=trim($_GET['l']);
if(trim($_GET['l'])==reset($config['langs'])){
$url=$_GET['ref'];
} else {
$url='/'.trim($_GET['l']).$_GET['ref'];
}
header("Location: ".$url);
exit;
?>