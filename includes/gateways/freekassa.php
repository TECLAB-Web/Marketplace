<?php
function getIP(){
if(isset($_SERVER['HTTP_X_REAL_IP'])) return $_SERVER['HTTP_X_REAL_IP'];
return $_SERVER['REMOTE_ADDR'];
}

function initExternalPayment($order_id, $summ, $currency){
global $config, $currencies, $langPrefix;
$form='';
$form.='<form action="http://www.free-kassa.ru/merchant/cash.php" method="GET">';
$form.='<input type="hidden" name="m" value="35559">';
$form.='<input type="hidden" name="oa" value="'.number_format($summ/0.402, 2).'">';
$form.='<input type="hidden" name="o" value="'.$order_id.'">';
$form.='<input type="hidden" name="s" value="'.md5('35559:'.number_format($summ/0.402, 2).':f0gm0a4i:'.$order_id).'">';
$form.='</form>';
return $form;
}

function successPayCallBack(){
global $gvars, $langPrefix;
foreach($gvars as $name => $value){
global $$name;
}
$check_order=mysql_query("SELECT * FROM `payments` WHERE `order_id`='".intval($_POST['MERCHANT_ORDER_ID'])."' LIMIT 1;");
if(!mysql_num_rows($check_order)){
header("HTTP/1.0 404 Not Found");
include "404.php";
exit;
}
$order=mysql_fetch_assoc($check_order);
$pagetitle=l('payment_success_title')." &bull; ".$config['sitename'];
if($m){
include "includes/m-header.php";
} else {
include "includes/header.php";
}
?>
<div class="success-page">
<div class="success-box">
<?php if($m){ ?>
<div>
<i class="fa fa-check-circle-o"></i>
</div>
<?php } ?>
<?php
if($order['type']=='wallet'){
echo str_replace('[SITENAME]', $config['sitename'], l('payment_success_wallet_info'));
}
if($order['type']=='upgrade'){
echo str_replace('[SITENAME]', $config['sitename'], l('payment_success_upgrade_info'));
}
?>
</div>
<p><a href="<?php echo $langPrefix; ?>/"><?php echo l('go_to_main_page'); ?></a></p>
<?php if($order['type']=='wallet'){ ?>
<p><a href="<?php echo $langPrefix; ?>/my/wallet/"><?php echo l('go_to_wallet'); ?></a></p>
<?php } ?>
<?php if($order['type']=='upgrade'){ ?>
<p><a href="<?php echo $langPrefix; ?>/my/"><?php echo l('go_to_my_profile'); ?></a></p>
<?php } ?>
</div>
<?php
if($m){
include "includes/m-footer.php";
} else {
include "includes/footer.php";
}
}

function cancelPayCallBack(){
global $gvars, $langPrefix;
foreach($gvars as $name => $value){
global $$name;
}
$pagetitle=l('payment_cancel_title')." &bull; ".$config['sitename'];
if($m){
include "includes/m-header.php";
} else {
include "includes/header.php";
}
?>
<div class="warning-page">
<div class="warning-box">
<?php if($m){ ?>
<div>
<i class="fa fa-times"></i>
</div>
<?php } ?>
<?php echo l('payment_cancel_info'); ?>
</div>
<p><a href="<?php echo $langPrefix; ?>/"><?php echo l('go_to_main_page'); ?></a></p>
</div>
<?php
if($m){
include "includes/m-footer.php";
} else {
include "includes/footer.php";
}
}

function failPayCallBack(){
global $gvars, $langPrefix;
foreach($gvars as $name => $value){
global $$name;
}
$pagetitle=l('payment_fail_title')." &bull; ".$config['sitename'];
if($m){
include "includes/m-header.php";
} else {
include "includes/header.php";
}
?>
<div class="warning-page">
<div class="warning-box">
<?php if($m){ ?>
<div>
<i class="fa fa-times"></i>
</div>
<?php } ?>
<?php echo l('payment_fail_info'); ?>
</div>
<p><a href="<?php echo $langPrefix; ?>/"><?php echo l('go_to_main_page'); ?></a></p>
</div>
<?php
if($m){
include "includes/m-footer.php";
} else {
include "includes/footer.php";
}
}

function notifyPayCallBack(){
global $gvars, $langPrefix;
foreach($gvars as $name => $value){
global $$name;
}
if(!in_array(getIP(), array('136.243.38.147', '136.243.38.149', '136.243.38.150', '136.243.38.151', '136.243.38.189'))){
header("HTTP/1.0 404 Not Found");
include "404.php";
exit;
}
if(intval($_POST['MERCHANT_ORDER_ID'])==0){
header("HTTP/1.0 404 Not Found");
include "404.php";
exit;
}
$check_order=mysql_query("SELECT * FROM `payments` WHERE `order_id`='".intval($_POST['MERCHANT_ORDER_ID'])."';");
if(!mysql_num_rows($check_order)){
echo 'FAIL';
exit;
}
echo 'YES';
while($payment=mysql_fetch_assoc($check_order)){
if($payment['status']==0){
processPayment($payment);
}
}
}
?>