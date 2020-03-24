<?php include "init.php"; ?>
<?php
if(!isset($_POST['order_id']) || !isset($_POST['title']) || !isset($_POST['summ']) || !isset($_POST['currency']) || !isset($_POST['cancel_url']) || !isset($_POST['fail_url']) || !isset($_POST['success_url']) || !isset($_POST['notify_url'])){
header("HTTP/1.0 404 Not Found");
include "404.php";
exit;
}
?>
<?php
if(trim($_GET['mode'])=='ajax'){
header('Content-Type: application/json; charset=utf-8');
if(trim($_POST['action'])==htmlspecialchars(trim($_GET['controller']))){
$result=array();
$result['errors']=array();
if(count($result['errors'])==0){
$postdata=http_build_query(array('order_id'=>intval($_POST['order_id'])));
$opts=array('http'=>array('method'=>'POST', 'header'=>'Content-type: application/x-www-form-urlencoded', 'content'=>$postdata));
$context=stream_context_create($opts);
$response=file_get_contents(trim($_POST['notify_url']), false, $context);
$result['response']=trim($response);
$result['status']='success';
} else {
$result['status']='error';
}
echo json_encode($result);
}
exit;
}
?>
<?php
$pagetitle=l('tg_title')." &bull; ".$config['sitename'];
$pagedesc=$config['description'];
?>
<?php include "includes/header.php"; ?>

<script type="text/javascript">
var success_url='<?php echo htmlspecialchars($_POST['success_url']); ?>?order_id=<?php echo intval($_POST['order_id']); ?>';
var fail_url='<?php echo htmlspecialchars($_POST['fail_url']); ?>';
</script>

<div class="row">
<div class="col-md-2"></div>
<div class="col-md-8">
<h3 class="special-title"><?php echo l('tg_title'); ?></h3>
<hr>
<form action="/<?php echo htmlspecialchars(trim($_GET['controller'])); ?>/" method="POST" autocomplete="off" class="ajax-form" data-callback="testGatewayCallBack">
<input type="hidden" name="action" value="<?php echo htmlspecialchars(trim($_GET['controller'])); ?>">
<?php foreach($_POST as $k=>$v){ ?>
<input type="hidden" name="<?php echo $k; ?>" value="<?php echo htmlspecialchars(trim($v)); ?>">
<?php } ?>
<table class="table table-bordered" width="100%">
<tr>
<td width="40%" align="right">
<?php echo l('tg_payment_title'); ?>
&nbsp;&nbsp;
</td>
<td>
&nbsp;&nbsp;
<b><?php echo htmlspecialchars($_POST['title']); ?></b>
</td>
</tr>
<tr>
<td width="40%" align="right">
<?php echo l('tg_payment_number'); ?>
&nbsp;&nbsp;
</td>
<td>
&nbsp;&nbsp;
<b><?php echo intval($_POST['order_id']); ?></b>
</td>
</tr>
<tr>
<td width="40%" align="right">
<?php echo l('tg_payment_summ'); ?>
&nbsp;&nbsp;
</td>
<td>
&nbsp;&nbsp;
<b><?php echo intval($_POST['summ']).' '.$currencies[$_POST['currency']]; ?></b>
</td>
</tr>
</table>
<hr>
<table width="100%">
<tr>
<td width="50%">
<a class="btn btn-danger btn-sm" href="<?php echo htmlspecialchars($_POST['cancel_url']); ?>"><?php echo l('tg_cancel'); ?></a>
</td>
<td width="50%" align="right">
<button type="submit" class="btn btn-primary btn-sm"><?php echo l('tg_submit'); ?></button>
</td>
</tr>
</table>
</form>
</div>
<div class="col-md-2"></div>
</div>

<?php include "includes/footer.php"; ?>