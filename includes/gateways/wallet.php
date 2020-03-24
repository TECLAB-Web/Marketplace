<?php
function initExternalPayment($order_id, $summ, $currency){
global $config, $currencies, $langPrefix;
$check_order=mysql_query("SELECT * FROM `payments` WHERE `order_id`='".$order_id."';");
while($payment=mysql_fetch_assoc($check_order)){
if($payment['status']==0){
processPayment($payment);
}
}
$form='';
$form.='<form action="'.$langPrefix.'/pay/wallet/success/" method="GET">';
$form.='<input type="hidden" name="order_id" value="'.$order_id.'">';
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
?>