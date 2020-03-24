<?php
include "includes/mysql.php";
$db=array();
$db['host']='sql301.epizy.com';
$db['base']='epiz_25360613_teclab';
$db['name']='epiz_25360613';
$db['pass']='2APPQwnAvQ';
$link=mysql_connect($db['host'], $db['name'], $db['pass']);
if(!$link){
echo 'Temporary error...';
exit;
}
$db_selected=mysql_select_db($db['base'], $link);
if(!$db_selected){
echo 'Temporary error...';
exit;
}
mysql_query("SET NAMES UTF8");
?>