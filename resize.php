<?php
include "includes/db.php";
$check=mysql_query("SELECT * FROM `ad_photos` WHERE `apid`='".mysql_real_escape_string(trim($_GET['apid']))."' AND `key`='".mysql_real_escape_string(trim($_GET['key']))."';");
if(!mysql_num_rows($check)){
header("HTTP/1.0 404 Not Found");
include "404.php";
exit;
}
$photo=mysql_fetch_assoc($check);
if(intval($_GET['w'])==0 && intval($_GET['h'])==0){
$pdata=getimagesize($photo['file']);
header('Content-Type: '.$pdata['mime']);
readfile($photo['file']);
exit;
}
$_GET['src']=$photo['file'];
//echo $_GET['src']; exit;
$resized=str_replace('.jpg', '_'.(intval($_GET['w']).'x'.intval($_GET['h'])).'_'.intval($_GET['contain']).'.jpg', $photo['file']);
//echo $resized; exit;
if(file_exists($resized)){
$pdata=getimagesize($resized);
header('Content-Type: '.$pdata['mime']);
readfile($resized);
exit;
}
?>