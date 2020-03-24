<?php include_once "init.php"; ?>
<?php
if(!isset($_SESSION['userid'])){
header("Location: ".$langPrefix."/login/?ref=".urlencode($langPrefix.$_SERVER['REQUEST_URI']));
exit;
}
?>
<?php
if(intval($my['admin'])!=1){
header("HTTP/1.0 404 Not Found");
include "404.php";
exit;
}
?>
<?php
if($m){
header("Location: /");
exit;
}
?>
<?php
if(trim($_GET['action'])=='edit'){
$check_item=mysql_query("SELECT * FROM `gateways` WHERE `gid`='".intval($_GET['gid'])."';");
if(!mysql_num_rows($check_item)){
header("Location: /admin/gateways/");
exit;
}
$item=mysql_fetch_assoc($check_item);
}
?>
<?php
if(trim($_GET['mode'])=='ajax'){
header('Content-Type: application/json; charset=utf-8');
if(trim($_POST['action'])=='add' || trim($_POST['action'])=='edit'){
$result=array();
$result['errors']=array();
if(trim($_POST['code'])==''){
$result['errors']['code']='Укажите ключ способа оплаты.';
} elseif((trim($_POST['action'])=='add' || (trim($_POST['action'])=='edit' && trim($_POST['code'])!=trim($item['code']))) && mysql_num_rows(mysql_query("SELECT * FROM `gateways` WHERE `code`='"._F($_POST['code'])."' AND `active`!='2';"))){
$result['errors']['code']='Способ оплаты с данным ключом уже существует.';
}
foreach($config['langs'] as $l){
if(trim($_POST['title_'.$l])==''){
$result['errors']['title_'.$l]='Заполните это поле.';
}
}
if(count($result['errors'])==0){
$flds=array();
$flds[]="`code`='"._F($_POST['code'])."'";
foreach($config['langs'] as $l){
$flds[]="`title_".$l."`='"._F($_POST['title_'.$l])."'";
}
if(trim($_POST['action'])=='add'){
$flds[]="`sort`='99999'";
}
if(trim($_POST['action'])=='add'){
mysql_query("INSERT INTO `gateways` SET `active`='1', ".implode(', ', $flds).";");
}
if(trim($_POST['action'])=='edit'){
mysql_query("UPDATE `gateways` SET ".implode(', ', $flds)." WHERE `gid`='".intval($_POST['gid'])."';");
if(trim($item['code'])!=trim($_POST['code'])){
copy('images/gateways/'.trim($item['code']).'.png', 'images/gateways/'.trim($_POST['code']).'.png');
}
}
if(trim($_POST['action'])=='add' || (trim($_POST['action'])=='edit' && trim($_POST['icon'])!=trim($_POST['code']).'.png')){
if(trim($_POST['action'])=='edit'){
unlink('images/gateways/'.trim($_POST['code']).'.png');
}
rename('images/gateways/'.trim($_POST['icon']), 'images/gateways/'.trim($_POST['code']).'.png');
}
$result['status']='success';
} else {
$result['status']='error';
}
echo json_encode($result);
}
if(trim($_POST['action'])=='sort'){
$keys=explode(',', trim($_GET['keys']));
if(count($keys)>0){
$result=array();
$order=1;
foreach($keys as $key){
$check=mysql_query("SELECT * FROM `gateways` WHERE `gid`='".intval($key)."';");
if(mysql_num_rows($check)){
mysql_query("UPDATE `gateways` SET `sort`='".$order."' WHERE `gid`='".intval($key)."';");
$order++;
}
}
$result['status']='success';
echo json_encode($result);
}
}
if(trim($_POST['action'])=='delete'){
$result=array();
$result['errors']=array();
if(count($result['errors'])==0){
mysql_query("UPDATE `gateways` SET `active`='2' WHERE `gid`='"._F($_POST['gid'])."';");
$result['status']='success';
} else {
$result['status']='error';
}
echo json_encode($result);
}
if(trim($_POST['action'])=='activate'){
$result=array();
$result['errors']=array();
if(count($result['errors'])==0){
mysql_query("UPDATE `gateways` SET `active`='1' WHERE `gid`='"._F($_POST['gid'])."';");
$result['status']='success';
} else {
$result['status']='error';
}
echo json_encode($result);
}
if(trim($_POST['action'])=='deactivate'){
$result=array();
$result['errors']=array();
if(count($result['errors'])==0){
mysql_query("UPDATE `gateways` SET `active`='0' WHERE `gid`='"._F($_POST['gid'])."';");
$result['status']='success';
} else {
$result['status']='error';
}
echo json_encode($result);
}
if(is_uploaded_file($_FILES['file']['tmp_name'])){
$ext=end((explode(".", $_FILES['file']['name'])));
$result=array();
$result['errors']=array();
$result['original']=$_FILES['file']['name'];
if(!in_array($ext, array('png'))){
$result['errors']['file']=l('attach_errors_bad_extension');
}
if(count($result['errors'])==0){
$key=uniqid('');
mkdir('images/gateways/', 0777);
$filename='images/gateways/'.$key.'.png';
if(move_uploaded_file($_FILES['file']['tmp_name'], $filename)){
$result['key']=$key;
$result['file']=$key.'.png';
$result['status']='success';
} else {
$result['errors']['file']=l('upload_errors_unable');
$result['status']='error';
}
} else {
$result['status']='error';
}
echo json_encode($result);
}
exit;
}
?>
<?php
$get_items=mysql_query("SELECT * FROM `gateways` WHERE `active`='0' OR `active`='1' ORDER BY `sort` ASC;");
?>
<?php
$pagetitle='Способы оплаты';
$pagedesc=$config['description'];
?>
<?php include "includes/header.php"; ?>
<script src="/js/ckeditor/ckeditor.js"></script>

<h4 class="special-title"><a href="/admin/">Администрирование</a><i class="fa fa-chevron-right"></i><?php if(trim($_GET['action'])!=''){ ?><a href="/admin/gateways/">Способы оплаты</a><?php } else { ?>Способы оплаты<?php } ?><?php if(trim($_GET['action'])=='add'){ ?><i class="fa fa-chevron-right"></i>Добавление<?php } ?><?php if(trim($_GET['action'])=='edit'){ ?><i class="fa fa-chevron-right"></i>Редактирование<?php } ?></h4>

<hr>

<?php if(trim($_GET['action'])=='add' || trim($_GET['action'])=='edit'){ ?>

<div id="uploadErrors" style="display:none;">
<div class="window-title"><?php echo l('upload_error'); ?><a href="javascript:void(0);" onclick="$.fancybox.close();"></a></div>
<div class="window-message"></div>
<div class="window-buttons">
<button class="btn btn-primary" onclick="$.fancybox.close();">OK</button>
</div>
</div>

<input type="file" class="hidden" id="uploaderButton" name="file" data-url="/ajax/admin/gateways/">
<script>
var uploadErrors=[];
$(function(){
$('#uploaderButton').fileupload({
dataType: 'json',
change:function (e, data){
uploadErrors=[];
},
done:function (e, data){
var item=data.result;
if(item.status=='success'){
$('#icon_uploaded').html('<img src="/images/gateways/'+item.file+'" style="width:64px;margin-bottom:20px;">');
$('#icon').val(item.file);
} else {
uploadErrors.push('<p><b>'+item.original+':</b><br>'+item.errors.file+'</p>');
}
},
stop:function (e, data){
if(uploadErrors.length>0){
$('#uploadErrors .window-message').html(uploadErrors.join(''));
$.fancybox({'type':'inline', 'href':'#uploadErrors', 'closeBtn':false, helpers:{overlay:{locked:false}}});
}
}
});
});
</script>

<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="POST" autocomplete="off" class="form-horizontal add-form ajax-form" data-callback="adminGatewaysCallBack">
<?php if(trim($_GET['action'])=='edit'){ ?>
<input type="hidden" name="gid" value="<?php echo $item['gid']; ?>">
<?php } ?>
<input type="hidden" name="action" value="<?php echo htmlspecialchars(trim($_GET['action'])); ?>">
<?php foreach($config['langs'] as $l){ ?>
<div class="form-group">
<label class="col-sm-3 control-label">Название (<?php echo mb_strtoupper($l); ?>) <span class="input-required">*</span></label>
<div class="col-sm-7">
<input type="text" autocomplete="off" class="form-control" name="title_<?php echo $l; ?>" value="<?php echo htmlspecialchars($item['title_'.$l]); ?>">
</div>
</div>
<?php } ?>
<hr>
<div class="form-group">
<label class="col-sm-3 control-label">Ключ способа <span class="input-required">*</span></label>
<div class="col-sm-7">
<input type="text" autocomplete="off" class="form-control" name="code" value="<?php echo htmlspecialchars($item['code']); ?>">
</div>
</div>
<hr>
<div class="form-group">
<label class="col-sm-3 control-label" style="padding-top:2px;">PNG-иконка способа оплаты <span class="input-required">*</span></label>
<div class="col-sm-2">
<input type="hidden" name="icon" id="icon" value="<?php if(trim($item['code'])!=''){ echo $item['code'].'.png'; } ?>">
<div id="icon_uploaded">
<?php if(trim($item['code'])!=''){ ?>
<img src="/images/gateways/<?php echo $item['code']; ?>.png?<?php echo rand(1,9999); ?>" style="width:64px;margin-bottom:20px;">
<?php } ?>
</div>
<a href="javascript:void(0);" onclick="$('#uploaderButton').click(); return false;" style="font-size:12px;font-weight:bold;">Загрузить файл</a>
</div>
</div>
<hr>
<div class="form-group">
<div class="col-sm-3"></div>
<div class="col-sm-7">
<button type="submit" class="btn btn-primary"><?php if(trim($_GET['action'])=='edit'){ ?>Сохранить способ оплаты<?php } else { ?>Добавить способ оплаты<?php } ?></button>
</div>
</div>
</form>

<?php } else { ?>

<a class="btn btn-primary btn-sm" href="/admin/gateways/?action=add">Добавить способ оплаты</a>

<hr>
<?php if(mysql_num_rows($get_items)){ ?>
<table class="table">
<thead>
<tr>
<th></th>
<th>
#
</th>
<th></th>
<th>
Название способа оплаты (<?php echo mb_strtoupper($config['lang']); ?>)
</th>
<th></th>
</tr>
</thead>
<tbody id="gatewaysList">
<?php while($item=mysql_fetch_assoc($get_items)){ ?>
<tr id="gateway_<?php echo $item['gid']; ?>">
<td width="40">
<i class="fa fa-bars" style="color:#666;cursor:move;" title="Тащите для изменения порядка элементов"></i>
</td>
<td>
<?php echo $item['gid']; ?>
</td>
<td width="32" align="center">
<img src="/images/gateways/<?php echo $item['code']; ?>.png?<?php echo rand(1,9999); ?>" style="max-width:16px;height:16px;">
</td>
<td style="<?php if($item['active']=='0'){ ?>text-decoration:line-through;<?php } ?>">
<?php echo htmlspecialchars($item['title_'.$config['lang']]); ?>
</td>
<td align="right">
<a title="Редактировать" class="btn btn-primary btn-xs" href="/admin/gateways/?action=edit&gid=<?php echo $item['gid']; ?>"><i class="fa fa-pencil"></i></a>
<?php if($item['active']=='0'){ ?>
<button title="Активировать" class="btn btn-success btn-xs" onclick="$.post('/ajax<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>', 'gid=<?php echo $item['gid']; ?>&action=activate', function(){ location.reload(); }); return false;"><i class="fa fa-check"></i></button>
<?php } ?>
<?php if($item['active']=='1'){ ?>
<button title="Отключить" class="btn btn-warning btn-xs" onclick="$.post('/ajax<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>', 'gid=<?php echo $item['gid']; ?>&action=deactivate', function(){ location.reload(); }); return false;"><i class="fa fa-ban"></i></button>
<?php } ?>
<button title="Удалить" class="btn btn-danger btn-xs" onclick="if(confirm('Вы уверены?')){ $.post('/ajax<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>', 'gid=<?php echo $item['gid']; ?>&action=delete', function(){ location.reload(); }); } return false;"><i class="fa fa-remove"></i></button>
</td>
</tr>
<?php } ?>
</tbody>
</table>

<script type="text/javascript">
$(function(){
var list=document.getElementById("gatewaysList");
Sortable.create(list, {
handle:'i.fa-bars',
draggable:'tr',
onEnd:function(e){
var gatewaysIDs=[];
$('#gatewaysList tr').each(function(){
var gateway_id=($(this).attr('id')).replace('gateway_', '');
gatewaysIDs.push(gateway_id);
});
var keys=gatewaysIDs.join(',');
$.post(langPrefix+'/ajax/admin/gateways/?keys='+keys, 'action=sort');
}
});
});
</script>
<?php } else { ?>
Здесь пока ничего нет.
<?php } ?>

<?php } ?>

<?php include "includes/footer.php"; ?>