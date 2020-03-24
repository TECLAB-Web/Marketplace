<?php include "init.php"; ?>
<?php
if(trim($_GET['ref'])==''){
if(trim($_GET['controller'])=='upgrade'){
$_GET['ref']=$langPrefix.'/my/';
}
if(trim($_GET['controller'])=='wallet'){
$_GET['ref']=$langPrefix.'/my/wallet/';
}
}
?>
<?php
if(!in_array(trim($_GET['controller']), array('wallet', 'upgrade'))){
header("HTTP/1.0 404 Not Found");
include "404.php";
exit;
}
if(trim($_GET['controller'])=='wallet' && !isset($_SESSION['userid'])){
header("Location: ".$langPrefix."/login/?ref=".urlencode($langPrefix.$_SERVER['REQUEST_URI']));
exit;
}
if(trim($_GET['controller'])=='upgrade'){
$ids=array_unique(array_map('intval', explode(',', trim($_GET['ids']))));
$check_ads=mysql_query("SELECT * FROM `ads` WHERE `active` IN(0,1) AND `aid` IN("._F(implode(',', $ids)).") ORDER BY FIND_IN_SET(`aid`, '"._F(implode(',', $ids))."');");
if(mysql_num_rows($check_ads)!=count($ids)){
header("HTTP/1.0 404 Not Found");
include "404.php";
exit;
}
}
?>
<?php
if(trim($_GET['mode'])=='ajax'){
header('Content-Type: application/json; charset=utf-8');
if(trim($_POST['action'])==htmlspecialchars(trim($_GET['controller']))){
if(trim($_GET['controller'])=='wallet'){
$result=array();
$result['errors']=array();
if(intval($_POST['summ'])==0){
$result['errors']['summ']=l('pay_error_summ_empty');
} elseif(intval($_POST['summ'])<0){
$result['errors']['summ']=l('pay_error_summ_negative');
} elseif(intval($_POST['summ'])<5){
$result['errors']['summ']=str_replace('[CURRENCY]', rtrim(reset($currencies), '.'), l('pay_error_summ_small'));
} elseif(intval($_POST['summ'])>100000){
$result['errors']['summ']=str_replace('[CURRENCY]', rtrim(reset($currencies), '.'), l('pay_error_summ_big'));
}
$check_gateway=mysql_query("SELECT * FROM `gateways` WHERE `gid`='".intval($_POST['method'])."';");
if(!mysql_num_rows($check_gateway)){
$result['errors']['form']=l('pay_error_bad_gateway');
} else {
$gateway=mysql_fetch_assoc($check_gateway);
}
if(count($result['errors'])==0){
include "includes/gateways/".$gateway['code'].".php";
$max_order_id=intval(mysql_result(mysql_query("SELECT MAX(`order_id`) FROM `payments`;"), 0, 0));
$order_id=$max_order_id+1;
$payment_id=createPayment($order_id, $gateway['gid'], $_SESSION['userid'], 'wallet', $_SESSION['userid'], '', intval($_POST['summ']), 0);
$initiator=initExternalPayment($order_id, intval($_POST['summ']), reset(array_keys($currencies)));
if($initiator==false){
$result['errors']['form']=l('pay_error_failed_initialization');
$result['status']='error';
} else {
$result['initiator']=$initiator;
$result['status']='success';
}
} else {
$result['status']='error';
}
echo json_encode($result);
}
if(trim($_GET['controller'])=='upgrade'){
$result=array();
$result['errors']=array();
$pre_payments=array();
$total_price=0;
foreach($_POST['services'] as $aid=>$srv){
foreach($srv as $sid=>$senabled){
$check_service=mysql_query("SELECT * FROM `services` WHERE `sid`='".intval($sid)."';");
if(mysql_num_rows($check_service)){
$service=mysql_fetch_assoc($check_service);
$price=intval($service['price']);
$till=0;
$days=explode(',', $service['days']);
if(intval($service['days'])>0){
if(!in_array(intval($_POST['days'][$aid][$sid]), $days)){
$result['errors']['form']=l('pay_error_failed_order');
} else {
$price=$price*intval($_POST['days'][$aid][$sid]);
$till=$time+(60*60*24*intval($_POST['days'][$aid][$sid]));
}
}
$total_price=$total_price+$price;
$pre_payments[]=array('oid'=>$aid, 'summ'=>$price, 'till'=>$till, 'service'=>$service['type']);
}
}
}
$check_gateway=mysql_query("SELECT * FROM `gateways` WHERE `gid`='".intval($_POST['method'])."';");
if(!mysql_num_rows($check_gateway)){
$result['errors']['form']=l('pay_error_bad_gateway');
} else {
$gateway=mysql_fetch_assoc($check_gateway);
if($gateway['code']=='wallet'){
if($total_price>$my['balance']){
$result['errors']['form']=str_replace('[SUMM]', (($total_price-$my['balance']).' '.rtrim(reset($currencies), '.')), l('pay_error_not_enough_funds'));
}
}
}
if(count($result['errors'])==0){
include "includes/gateways/".$gateway['code'].".php";
$max_order_id=intval(mysql_result(mysql_query("SELECT MAX(`order_id`) FROM `payments`;"), 0, 0));
$order_id=$max_order_id+1;
foreach($pre_payments as $pre_payment){
$payment_id=createPayment($order_id, $gateway['gid'], $_SESSION['userid'], 'upgrade', $pre_payment['oid'], $pre_payment['service'], $pre_payment['summ'], $pre_payment['till']);
}
$initiator=initExternalPayment($order_id, $total_price, reset(array_keys($currencies)));
if($initiator==false){
$result['errors']['form']=l('pay_error_failed_initialization');
$result['status']='error';
} else {
$result['initiator']=$initiator;
$result['status']='success';
}
} else {
$result['status']='error';
}
echo json_encode($result);
}
}
exit;
}
?>
<?php
if(trim($_GET['controller'])=='wallet'){
$pagetitle=l('pay_wallet_title')." &bull; ".$config['sitename'];
}
if(trim($_GET['controller'])=='upgrade'){
$pagetitle=l("pay_upgrade_title")." &bull; ".$config['sitename'];
}
$pagedesc=$config['description'];
?>
<?php
if($m){ include "m-pay.php"; exit; }
?>
<?php include "includes/header.php"; ?>

<form action="/pay/<?php echo htmlspecialchars(trim($_GET['controller'])); ?>/<?php if(trim($_GET['controller'])=='upgrade'){ ?>?ids=<?php echo implode(',', $ids); ?><?php } ?>" method="POST" autocomplete="off" class="ajax-form" data-callback="payCallBack">
<input type="hidden" name="action" value="<?php echo htmlspecialchars(trim($_GET['controller'])); ?>">

<h4 class="special-title pay-page-title">
<span>1</span>
<?php
if(trim($_GET['controller'])=='wallet'){
echo l('pay_please_add_funds');
}
if(trim($_GET['controller'])=='upgrade'){
if(count($ids)==1){
echo l('pay_please_upgrade_one_ad');
} else {
echo l('pay_please_upgrade_multiple_ads');
}
}
?>
</h4>

<div class="clear"></div>

<?php if(trim($_GET['controller'])=='wallet'){ ?>
<div class="pay-items-list">
<div class="pay-title">
<?php echo l('pay_how_many_funds'); ?>
</div>
<div class="pay-item pay-item-selected">
<table width="100%">
<tr>
<td width="120" nowrap>
<div>
<input type="text" name="summ" class="form-control input-sm" value="" onkeyup="recountWalletSumm(this.value);" autofocus>
</div>
</td>
<td width="15" nowrap></td>
<td nowrap>
<?php echo reset($currencies); ?>
</td>
</tr>
</table>
</div>
</div>
<?php } ?>
<?php if(trim($_GET['controller'])=='upgrade'){ ?>

<?php $ai=1; ?>
<?php while($ad=mysql_fetch_assoc($check_ads)){ ?>
<?php
if(intval($_GET['selected'])>0){
$get_services=mysql_query("(SELECT * FROM `services` WHERE `sid`='".intval($_GET['selected'])."' AND `active`='1') UNION ALL (SELECT * FROM `services` WHERE `sid`!='".intval($_GET['selected'])."' AND `active`='1' ORDER BY `sort` ASC);");
} else {
$get_services=mysql_query("SELECT * FROM `services` WHERE `active`='1' ORDER BY `sort` ASC;");
}
?>
<div class="pay-items-list" id="ad_<?php echo $ad['aid']; ?>" style="display:block !important;">
<div class="pay-title">
<?php
echo htmlspecialchars($ad['title']);
?>:
</div>
<?php while($service=mysql_fetch_assoc($get_services)){ ?>
<?php
$days=explode(',', $service['days']);
$first_day=reset($days);
if(checkActiveService($ad['aid'], $service['type'], 'upgrade')){
$already=true;
} else {
$already=false;
}
?>
<div class="pay-item" id="service_<?php echo $service['sid']; ?>"<?php if(intval($_GET['selected'])>0 && $service['sid']!=intval($_GET['selected'])){ ?> style="display:none;"<?php } ?>>
<table width="100%">
<tr>
<td width="14" nowrap>
<div class="checkbox" style="display:inline-block;"<?php if($already){ ?> title="<?php echo l('pay_service_already_active'); ?>"<?php } ?>>
<input type="checkbox" name="services[<?php echo $ad['aid']; ?>][<?php echo $service['sid']; ?>]" class="pay-service-<?php echo $service['sid']; ?>" id="check_service_<?php echo $ad['aid']; ?>_<?php echo $service['sid']; ?>" value="1" onclick="if(this.checked){ $(this).closest('.pay-item').addClass('pay-item-selected'); } else { $(this).closest('.pay-item').removeClass('pay-item-selected'); } recountPayedServices();"<?php if($already){ ?> disabled<?php } ?>>
<label for="check_service_<?php echo $ad['aid']; ?>_<?php echo $service['sid']; ?>">&nbsp;</label>
</div>
</td>
<td width="70" align="center" nowrap>
<label for="check_service_<?php echo $ad['aid']; ?>_<?php echo $service['sid']; ?>" style="margin:auto;cursor:pointer;">
<div class="pay-item-icon" style="background-image:url('/images/services/<?php echo $service['sid']; ?>.png');"></div>
</label>
</td>
<td nowrap>
<label for="check_service_<?php echo $ad['aid']; ?>_<?php echo $service['sid']; ?>" style="margin:auto;font-weight:normal;cursor:pointer;">
<?php echo $service['title_'.$config['lang']]; ?>
</label>
<div class="pay-item-info" onmouseover="$('#service_tip_<?php echo $ad['aid']; ?>_<?php echo $service['sid']; ?>').removeClass('hidden');" onmouseout="$('#service_tip_<?php echo $ad['aid']; ?>_<?php echo $service['sid']; ?>').addClass('hidden');">
<span>i</span>
<div class="info-tooltip-container hidden" id="service_tip_<?php echo $ad['aid']; ?>_<?php echo $service['sid']; ?>"><div class="info-tooltip-arrow"></div><div class="info-tooltip"><?php echo nl2br($service['description_'.$config['lang']]); ?></div></div>
</div>
</td>
<td align="right" width="140" nowrap>
<?php if(intval($service['days'])>0){ ?>
<div class="pay-item-days">
<?php if(count($days)>1){ ?>
<select name="days[<?php echo $ad['aid']; ?>][<?php echo $service['sid']; ?>]" size="1" class="form-control" onchange="$('#service_price_<?php echo $ad['aid']; ?>_<?php echo $service['sid']; ?>').html(this.value*1*<?php echo $service['price']; ?>); recountPayedServices();">
<?php foreach($days as $day){ ?>
<option value="<?php echo $day; ?>"<?php if($day==$service['default_day']){ ?> selected<?php } ?>><?php echo langPayServiceFor_X_Days($day); ?></option>
<?php } ?>
</select>
<?php } else { ?>
<input type="hidden" name="days[<?php echo $ad['aid']; ?>][<?php echo $service['sid']; ?>]" value="<?php echo $first_day; ?>">
<?php echo langPayServiceFor_X_Days($first_day); ?>
<?php } ?>
</div>
<?php } ?>
</td>
<td align="right" width="100" nowrap>
<div class="pay-item-price">
<span id="service_price_<?php echo $ad['aid']; ?>_<?php echo $service['sid']; ?>"><?php if(intval($service['days'])==0){ echo $service['price']; } else { echo $service['price']*$service['default_day']; } ?></span>
<?php echo reset($currencies); ?>
</div>
</td>
</tr>
</table>
</div>
<?php } ?>
<?php if(intval($_GET['selected'])>0 && mysql_num_rows($get_services)>1){ ?>
<div class="pay-from-top-ad">
<a href="javascript:void(0);" onclick="$(this).closest('.pay-items-list').find('.pay-from-top-ad').hide(); $(this).closest('.pay-items-list').find('.pay-item').fadeIn('fast');"><i class="fa fa-plus"></i><?php echo l('pay_add_services'); ?></a>
</div>
<?php } ?>
</div>
<?php $ai++; ?>
<?php } ?>

<?php } ?>

<div id="select-payment-method-2" style="display:<?php if(trim($_GET['controller'])=='upgrade'){ ?>block<?php } else { ?>block<?php } ?>;">

<h4 class="special-title pay-page-title">
<span>2</span>
<?php echo l('pay_select_payment_method'); ?>
</h4>

<div class="clear"></div>

<?php
$get_gateways=mysql_query("SELECT * FROM `gateways` WHERE `active`='1' ORDER BY `sort` ASC;");
?>

<div id="pay-methods"><?php $pgi=1; ?><?php while($gateway=mysql_fetch_assoc($get_gateways)){ ?><?php if((trim($_GET['controller'])=='wallet' && $gateway['code']=='wallet') || ($gateway['code']=='wallet'&& !isset($_SESSION['userid']))){ continue; } ?><div class="pay-method<?php if($pgi==1){ ?> pay-method-selected<?php } ?>" title="<?php echo str_replace('[SITENAME]', $config['sitename'], htmlspecialchars($gateway['description'])); ?>">
<div class="pay-method-icon" style="background-image:url('/images/gateways/<?php echo $gateway['code']; ?>.png');" onclick="$('#radio_gateway_<?php echo $gateway['gid']; ?>').click(); $('.pay-method').removeClass('pay-method-selected'); $(this).closest('.pay-method').addClass('pay-method-selected');"></div>
<div class="pay-method-title">
<div class="radio">
<input type="radio" name="method" value="<?php echo $gateway['gid']; ?>" id="radio_gateway_<?php echo $gateway['gid']; ?>"<?php if($pgi==1){ ?> checked<?php } ?> onclick="$('.pay-method').removeClass('pay-method-selected'); $(this).closest('.pay-method').addClass('pay-method-selected');">
<label for="radio_gateway_<?php echo $gateway['gid']; ?>" style="font-size:12px;"><?php echo htmlspecialchars($gateway['title_'.$config['lang']]); ?></label>
</div>
</div>
</div><?php $pgi++; ?><?php } ?></div>

<hr>

</div>

<table style="margin-top:20px;" width="100%">
<tr>
<td>
<a href="<?php echo htmlspecialchars(trim($_GET['ref'])); ?>" class="btn btn-primary"><?php echo l('pay_go_back'); ?></a>
</td>
<td align="right">
<span id="pay-summ" style="display:none;"><?php echo l('pay_total'); ?> <span>0</span> <?php echo reset($currencies); ?></span>
<button type="submit" class="btn btn-success" id="pay-submit-button" disabled="disabled"><?php echo l('pay_go_next'); ?></button>
</td>
</tr>
</table>

</form>

<div id="external-payment-form"></div>

<?php if(intval($_GET['selected'])){ ?>
<script type="text/javascript">
$(function(){
$('#service_<?php echo intval($_GET['selected']); ?> input[type=checkbox]').click();
});
</script>
<?php } ?>

<?php include "includes/footer.php"; ?>