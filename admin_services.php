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
$check_item=mysql_query("SELECT * FROM `services` WHERE `sid`='".intval($_GET['sid'])."';");
if(!mysql_num_rows($check_item)){
header("Location: /admin/services/");
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
foreach($config['langs'] as $l){
if(trim($_POST['title_'.$l])==''){
$result['errors']['title_'.$l]='Заполните это поле.';
}
if(trim($_POST['action_title_'.$l])==''){
$result['errors']['action_title_'.$l]='Заполните это поле.';
}
if(trim($_POST['description_'.$l])==''){
$result['errors']['description_'.$l]='Заполните это поле.';
}
if(trim($_POST['features_'.$l])==''){
$result['errors']['features_'.$l]='Заполните это поле.';
}
}
if(trim($_POST['price'])!='' && intval($_POST['price'])<0){
$result['errors']['price']='Некорректная цена.';
}
if(trim($_POST['type'])==''){
$result['errors']['type']='Выберите тип платной услуги.';
}
if(trim($_POST['days'])!='' && !preg_match("/^\d+(?:,\d+)*$/", trim($_POST['days']))){
$result['errors']['days']='Допускаются только числа и запятые.';
} elseif(trim($_POST['days'])!='' && !in_array(trim($_POST['default_day']), explode(',', trim($_POST['days'])))){
$result['errors']['default_day']='Выбран несуществующий период.';
}
if(trim($_POST['icon'])==''){
$result['errors']['icon']='Загрузите иконку.';
}
if(count($result['errors'])==0){
$flds=array();
$flds[]="`type`='"._F($_POST['type'])."'";
$flds[]="`price`='".intval($_POST['price'])."'";
$flds[]="`days`='"._F($_POST['days'])."'";
$flds[]="`default_day`='".intval($_POST['default_day'])."'";
foreach($config['langs'] as $l){
$flds[]="`title_".$l."`='"._F($_POST['title_'.$l])."'";
$flds[]="`action_title_".$l."`='"._F($_POST['action_title_'.$l])."'";
$flds[]="`description_".$l."`='"._F($_POST['description_'.$l])."'";
$flds[]="`features_".$l."`='"._F($_POST['features_'.$l])."'";
}
if(trim($_POST['action'])=='add'){
$flds[]="`sort`='99999'";
}
if(trim($_POST['action'])=='add'){
mysql_query("INSERT INTO `services` SET `active`='1', ".implode(', ', $flds).";");
$_POST['sid']=mysql_insert_id();
}
if(trim($_POST['action'])=='edit'){
mysql_query("UPDATE `services` SET ".implode(', ', $flds)." WHERE `sid`='".intval($_POST['sid'])."';");
}
if(trim($_POST['action'])=='add' || (trim($_POST['action'])=='edit' && trim($_POST['icon'])!=$item['sid'].'.png')){
if(trim($_POST['action'])=='edit'){
unlink('images/services/'.intval($_POST['sid']).'.png');
}
rename('images/services/'.trim($_POST['icon']), 'images/services/'.intval($_POST['sid']).'.png');
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
$check=mysql_query("SELECT * FROM `services` WHERE `sid`='".intval($key)."';");
if(mysql_num_rows($check)){
mysql_query("UPDATE `services` SET `sort`='".$order."' WHERE `sid`='".intval($key)."';");
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
mysql_query("UPDATE `services` SET `active`='2' WHERE `sid`='".intval($_POST['sid'])."';");
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
mysql_query("UPDATE `services` SET `active`='1' WHERE `sid`='".intval($_POST['sid'])."';");
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
mysql_query("UPDATE `services` SET `active`='0' WHERE `sid`='".intval($_POST['sid'])."';");
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
mkdir('images/services/', 0777);
$filename='images/services/'.$key.'.png';
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
$get_items=mysql_query("SELECT * FROM `services` WHERE `active`='0' OR `active`='1' ORDER BY `sort` ASC;");
?>
<?php
$pagetitle='Настройка платных услуг';
$pagedesc=$config['description'];
?>
<?php include "includes/header.php"; ?>

<h4 class="special-title"><a href="/admin/">Администрирование</a><i class="fa fa-chevron-right"></i><?php if(trim($_GET['action'])!=''){ ?><a href="/admin/services/">Настройка платных услуг</a><?php } else { ?>Настройка платных услуг<?php } ?><?php if(trim($_GET['action'])=='add'){ ?><i class="fa fa-chevron-right"></i>Добавление<?php } ?><?php if(trim($_GET['action'])=='edit'){ ?><i class="fa fa-chevron-right"></i>Редактирование<?php } ?></h4>

<hr>

<?php if(trim($_GET['action'])=='add' || trim($_GET['action'])=='edit'){ ?>

<div id="uploadErrors" style="display:none;">
<div class="window-title"><?php echo l('upload_error'); ?><a href="javascript:void(0);" onclick="$.fancybox.close();"></a></div>
<div class="window-message"></div>
<div class="window-buttons">
<button class="btn btn-primary" onclick="$.fancybox.close();">OK</button>
</div>
</div>

<input type="file" class="hidden" id="uploaderButton" name="file" data-url="/ajax/admin/services/">
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
$('#icon_uploaded').html('<img src="/images/services/'+item.file+'" style="width:32px;margin-bottom:20px;">');
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

<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="POST" autocomplete="off" class="form-horizontal add-form ajax-form" data-callback="adminServicesCallBack">
<?php if(trim($_GET['action'])=='edit'){ ?>
<input type="hidden" name="sid" value="<?php echo $item['sid']; ?>">
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
<?php if(count($config['langs'])>1){ ?><hr><?php } ?>
<?php foreach($config['langs'] as $l){ ?>
<div class="form-group">
<label class="col-sm-3 control-label">Призыв к действию (<?php echo mb_strtoupper($l); ?>) <span class="input-required">*</span></label>
<div class="col-sm-7">
<input type="text" autocomplete="off" class="form-control" name="action_title_<?php echo $l; ?>" value="<?php echo htmlspecialchars($item['action_title_'.$l]); ?>">
</div>
</div>
<?php } ?>
<?php if(count($config['langs'])>1){ ?><hr><?php } ?>
<?php foreach($config['langs'] as $l){ ?>
<div class="form-group">
<label class="col-sm-3 control-label">Что даёт (<?php echo mb_strtoupper($l); ?>) <span class="input-required">*</span></label>
<div class="col-sm-7">
<textarea name="description_<?php echo $l; ?>" class="form-control" rows="3"><?php echo htmlspecialchars($item['description_'.$l]); ?></textarea>
</div>
</div>
<?php } ?>
<?php if(count($config['langs'])>1){ ?><hr><?php } ?>
<?php foreach($config['langs'] as $l){ ?>
<div class="form-group">
<label class="col-sm-3 control-label">Преимущества (<?php echo mb_strtoupper($l); ?>) <span class="input-required">*</span></label>
<div class="col-sm-7">
<textarea name="features_<?php echo $l; ?>" class="form-control" rows="3"><?php echo htmlspecialchars($item['features_'.$l]); ?></textarea>
</div>
</div>
<?php } ?>
<hr>
<div class="form-group">
<label class="col-sm-3 control-label">
Цена (<?php echo reset($currencies); ?>)
</label>
<div class="col-sm-2">
<input type="text" autocomplete="off" class="form-control" name="price" value="<?php if($item['price']>0){ echo htmlspecialchars($item['price']); } ?>">
</div>
</div>
<div class="form-group">
<label class="col-sm-3 control-label">Тип платной услуги <span class="input-required">*</span></label>
<div class="col-sm-4">
<select size="1" name="type" class="form-control">
<option value="">Выберите...</option>
<option value="top"<?php if($item['type']=='top'){ ?> selected<?php } ?>>Размещение над списком</option>
<option value="highlight"<?php if($item['type']=='highlight'){ ?> selected<?php } ?>>Выделение цветом</option>
<option value="urgent"<?php if($item['type']=='urgent'){ ?> selected<?php } ?>>Пометка &laquo;Срочно&raquo;</option>
<option value="up"<?php if($item['type']=='up'){ ?> selected<?php } ?>>Обновление даты и времени</option>
</select>
</div>
</div>
<div class="form-group">
<label class="col-sm-3 control-label">
Периоды оплаты в днях
<span class="label-description">Если услуга должна действовать всё время размещения объявления - не заполняйте это поле</span>
</label>
<div class="col-sm-7">
<input type="text" autocomplete="off" class="form-control" name="days" value="<?php echo htmlspecialchars($item['days']); ?>">
<div style="color:#666;font-size:12px;margin-top:5px;">Например, &laquo;<b>3,7,10</b>&raquo; означает, что пользователь сможет выбрать, на <b>3</b>, <b>7</b> или <b>10 дней</b> покупать услугу. Периоды необходимо разделять запятыми без пробелов.</div>
</div>
</div>
<div class="form-group">
<label class="col-sm-3 control-label">
Период по умолчанию
<span class="label-description">Если периодов оплаты несколько, задайте период, который должен отображаться по умолчанию</span>
</label>
<div class="col-sm-2">
<input type="text" autocomplete="off" class="form-control" name="default_day" value="<?php if($item['default_day']>0){ echo htmlspecialchars($item['default_day']); } ?>">
</div>
</div>
<hr>
<div class="form-group">
<label class="col-sm-3 control-label" style="padding-top:2px;">PNG-иконка услуги <span class="input-required">*</span></label>
<div class="col-sm-2">
<input type="hidden" name="icon" id="icon" value="<?php if(intval($item['sid'])>0){ echo $item['sid'].'.png'; } ?>">
<div id="icon_uploaded">
<?php if(intval($item['sid'])>0){ ?>
<img src="/images/services/<?php echo $item['sid']; ?>.png?<?php echo rand(1,9999); ?>" style="width:32px;margin-bottom:20px;">
<?php } ?>
</div>
<a href="javascript:void(0);" onclick="$('#uploaderButton').click(); return false;" style="font-size:12px;font-weight:bold;">Загрузить файл</a>
</div>
</div>
<hr>
<div class="form-group">
<div class="col-sm-3"></div>
<div class="col-sm-7">
<button type="submit" class="btn btn-primary"><?php if(trim($_GET['action'])=='edit'){ ?>Сохранить платную услугу<?php } else { ?>Добавить платную услугу<?php } ?></button>
</div>
</div>
</form>

<?php } else { ?>

<a class="btn btn-primary btn-sm" href="/admin/services/?action=add">Добавить платную услугу</a>

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
Название платной услуги (<?php echo mb_strtoupper($config['lang']); ?>)
</th>
<th></th>
</tr>
</thead>
<tbody id="servicesList">
<?php while($item=mysql_fetch_assoc($get_items)){ ?>
<tr id="service_<?php echo $item['sid']; ?>">
<td width="40">
<i class="fa fa-bars" style="color:#666;cursor:move;" title="Тащите для изменения порядка элементов"></i>
</td>
<td>
<?php echo $item['sid']; ?>
</td>
<td width="32" align="center">
<img src="/images/services/<?php echo $item['sid']; ?>.png?<?php echo rand(1,9999); ?>" style="max-width:16px;height:16px;">
</td>
<td style="<?php if($item['active']=='0'){ ?>text-decoration:line-through;<?php } ?>">
<?php echo htmlspecialchars($item['title_'.$config['lang']]); ?>
</td>
<td align="right">
<a title="Редактировать" class="btn btn-primary btn-xs" href="/admin/services/?action=edit&sid=<?php echo $item['sid']; ?>"><i class="fa fa-pencil"></i></a>
<?php if($item['active']=='0'){ ?>
<button title="Активировать" class="btn btn-success btn-xs" onclick="$.post('/ajax<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>', 'sid=<?php echo $item['sid']; ?>&action=activate', function(){ location.reload(); }); return false;"><i class="fa fa-check"></i></button>
<?php } ?>
<?php if($item['active']=='1'){ ?>
<button title="Отключить" class="btn btn-warning btn-xs" onclick="$.post('/ajax<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>', 'sid=<?php echo $item['sid']; ?>&action=deactivate', function(){ location.reload(); }); return false;"><i class="fa fa-ban"></i></button>
<?php } ?>
<button title="Удалить" class="btn btn-danger btn-xs" onclick="if(confirm('Вы уверены?')){ $.post('/ajax<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>', 'sid=<?php echo $item['sid']; ?>&action=delete', function(){ location.reload(); }); } return false;"><i class="fa fa-remove"></i></button>
</td>
</tr>
<?php } ?>
</tbody>
</table>

<script type="text/javascript">
$(function(){
var list=document.getElementById("servicesList");
Sortable.create(list, {
handle:'i.fa-bars',
draggable:'tr',
onEnd:function(e){
var serviceIDs=[];
$('#servicesList tr').each(function(){
var service_id=($(this).attr('id')).replace('service_', '');
serviceIDs.push(service_id);
});
var keys=serviceIDs.join(',');
$.post(langPrefix+'/ajax/admin/services/?keys='+keys, 'action=sort');
}
});
});
</script>
<?php } else { ?>
Здесь пока ничего нет.
<?php } ?>

<?php } ?>

<?php include "includes/footer.php"; ?>