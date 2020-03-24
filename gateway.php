<?php include "init.php"; ?>
<?php
$check_gateway=mysql_query("SELECT * FROM `gateways` WHERE `code`='"._F($_GET['code'])."' AND `active`='1';");
if(!mysql_num_rows($check_gateway)){
header("HTTP/1.0 404 Not Found");
include "404.php";
exit;
}
$gateway=mysql_fetch_assoc($check_gateway);
include "includes/gateways/".$gateway['code'].".php";
$action=trim($_GET['action']).'PayCallBack';
if(!function_exists($action)){
header("HTTP/1.0 404 Not Found");
include "404.php";
exit;
}
$gvars=get_defined_vars();
$action();
?>