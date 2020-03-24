<?php include_once "init.php"; ?>
<?php
if(!isset($_SESSION['userid'])){
header("Location: ".$langPrefix."/login/?ref=".urlencode($langPrefix.$_SERVER['REQUEST_URI']));
exit;
}
?>
<?php
$show_top_tabs=true;
$is_cabinet=true;
$current_tab='wallet';
$top_tabs_title=l('my_wallet_title');
$top_tabs_description=l('my_wallet_description');
$pagetitle=l('my_wallet_title')." &bull; ".$config['sitename'];
$pagedesc=$config['description'];
?>
<?php
if($m){ include "m-my_wallet.php"; exit; }
?>
<?php include "includes/header.php"; ?>

<table width="100%" style="margin-bottom:40px;">
<tr>
<td>
<div class="wallet-balance"><?php echo l('my_wallet_balance_is'); ?> <b><?php echo number_format($my['balance'], 0, '', ' ').' '.reset($currencies); ?></b></div>
</td>
<td align="right">
<a class="btn btn-success" href="<?php echo $langPrefix; ?>/pay/wallet/"><?php echo l('my_wallet_add_funds'); ?></a>
</td>
</tr>
</table>

<?php
$pages=array();
$pages['count']=mysql_result(mysql_query("SELECT COUNT(*) FROM `payments` WHERE `userid`='".$_SESSION['userid']."' AND `status`='1';"), 0, 0);
$pages['per']=10;
if(intval($_GET['page'])>ceil($pages['count']/$pages['per'])){
$pages['current']=ceil($pages['count']/$pages['per']);
} else {
$pages['current']=((intval($_GET['page'])>1)?intval($_GET['page']):1);
}
$pages['show']=9;
$pages['url']='/my/wallet/?page=(:page)';
$pagingHtml=paginator::getHtml($pages['count'], $pages['current'], $pages['per'], $pages['show'], $pages['url']);
$get_payments=mysql_query("SELECT * FROM `payments` WHERE `userid`='".$_SESSION['userid']."' AND `status`='1' ORDER BY `pid` DESC LIMIT ".($pages['per']*($pages['current']-1)).", ".$pages['per'].";");
?>
<?php if(!mysql_num_rows($get_payments)){ ?>
<div class="empty-list">
<div>
<i class="fa fa-credit-card"></i>
</div>
<?php echo l('my_wallet_empty'); ?>
</div>
<?php } else { ?>
<div class="my_items_title">
<?php echo l('my_wallet_history'); ?>
</div>
<table class="my_items_list">
<tbody>
<tr class="my_items_list_head">
<td align="right" nowrap>
<?php echo l('my_wallet_table_number'); ?>
</td>
<td align="right" nowrap>
<?php echo l('my_wallet_table_summ'); ?>, <?php echo reset($currencies); ?>
</td>
<td width="100%">
<?php echo l('my_wallet_table_description'); ?>
</td>
<td nowrap>
<?php echo l('my_wallet_table_method'); ?>
</td>
<td align="right" nowrap>
<?php echo l('my_wallet_table_time'); ?>
</td>
</tr>
<?php
while($payment=mysql_fetch_assoc($get_payments)){
$gateway=mysql_fetch_assoc(mysql_query("SELECT * FROM `gateways` WHERE `gid`='".$payment['gid']."';"));
if($payment['type']=='upgrade'){
$ad=mysql_fetch_assoc(mysql_query("SELECT * FROM `ads` WHERE `aid`='".$payment['oid']."';"));
}
?>
<tr class="payments-list-item">
<td align="right" nowrap>
<?php echo $payment['pid']; ?>
</td>
<td align="right" nowrap>
<?php
if($payment['gid']==0 && $payment['type']!='wallet'){
echo '<span class="text-danger">-'.intval($payment['summ']).'</span>';
} elseif($payment['gid']!=0 && $payment['type']=='wallet'){
echo '<span class="text-success">+'.intval($payment['summ']).'</span>';
} else {
echo '<i class="text-muted">'.intval($payment['summ']).'</i>';
}
?>
</td>
<td width="100%">
<?php
if($payment['type']=='wallet'){
echo l('my_wallet_payment_type_wallet');
}
if($payment['type']=='upgrade'){
echo str_replace('[TITLE]', htmlspecialchars($ad['title']), str_replace('[URL]', adurl($ad), l('my_wallet_payment_type_upgrade')));
}
?>
</td>
<td nowrap>
<?php echo htmlspecialchars($gateway['title_'.$config['lang']]); ?>
</td>
<td align="right" nowrap>
<?php displayTime($payment['time']); ?>
</td>
</tr>
<?php
}
?>
</tbody>
</table>
<?php } ?>

<?php
if($pages['count']/$pages['per']>1){
echo $pagingHtml;
}
?>

<?php include "includes/footer.php"; ?>