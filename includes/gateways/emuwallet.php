<?php
function initExternalPayment($order_id, $summ, $currency){
global $config, $currencies, $langPrefix;
$form='';
$form.='<form action="'.$langPrefix.'/test-gateway/" method="POST">';
$form.='<input type="hidden" name="order_id" value="'.$order_id.'">';
$form.='<input type="hidden" name="summ" value="'.$summ.'">';
$form.='<input type="hidden" name="currency" value="'.$currency.'">';
$form.='<input type="hidden" name="title" value="ORDER #'.$order_id.' on '.$config['sitename'].'">';
$form.='<input type="hidden" name="cancel_url" value="http://'.$config['siteurl'].$langPrefix.'/pay/emuwallet/cancel/">';
$form.='<input type="hidden" name="fail_url" value="http://'.$config['siteurl'].$langPrefix.'/pay/emuwallet/fail/">';
$form.='<input type="hidden" name="notify_url" value="http://'.$config['siteurl'].$langPrefix.'/pay/emuwallet/notify/">';
$form.='<input type="hidden" name="success_url" value="http://'.$config['siteurl'].$langPrefix.'/pay/emuwallet/success/">';
$form.='</form>';
return $form;
}

function successPayCallBack(){
global $gvars, $langPrefix;
foreach($gvars as $name => $value){
global $$name;
}
$check_order=mysql_query("SELECT * FROM `payments` WHERE `order_id`='".intval($_GET['order_id'])."' LIMIT 1;");
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
if(intval($_POST['order_id'])==0){
header("HTTP/1.0 404 Not Found");
include "404.php";
exit;
}
$check_order=mysql_query("SELECT * FROM `payments` WHERE `order_id`='".intval($_POST['order_id'])."';");
if(!mysql_num_rows($check_order)){
echo 'FAIL';
exit;
}
echo 'OK';
while($payment=mysql_fetch_assoc($check_order)){
if($payment['status']==0){
processPayment($payment);
}
}
}
?>