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
$check_item=mysql_query("SELECT * FROM `category_parameter_values` WHERE `id`='".intval($_GET['id'])."';");
if(!mysql_num_rows($check_item)){
header("Location: /admin/parameter-values/");
exit;
}
$item=mysql_fetch_assoc($check_item);
$item['validators']=unserialize($item['validators']);
if($item['parent_id']!=intval($_GET['parent_id'])){
header("Location: /admin/parameter-values/");
exit;
}
}
?>
<?php
if(trim($_GET['mode'])=='ajax'){
header('Content-Type: application/json; charset=utf-8');
if(trim($_POST['action'])=='add' || trim($_POST['action'])=='edit'){
$result=array();
$result['errors']=array();
if(trim($_POST['key'])==''){
$result['errors']['key']='Укажите системный ключ.';
}
foreach($config['langs'] as $l){
if(trim($_POST['value_'.$l])==''){
$result['errors']['value_'.$l]='Заполните это поле.';
}
}
if(trim(implode('', $_POST['cids']))==''){
$result['errors']['cids']='Выберите что-нибудь.';
}
if(count($result['errors'])==0){
$flds=array();
$flds[]="`key`='"._F($_POST['key'])."'";
$flds[]="`cids`='"._F(implode(',', $_POST['cids']))."'";
$flds[]="`keys`='"._F(implode(',', $_POST['keys']))."'";
foreach($config['langs'] as $l){
$flds[]="`value_".$l."`='"._F($_POST['value_'.$l])."'";
}
if(trim($_POST['action'])=='add'){
$flds[]="`sort`='99999'";
}
if(trim($_POST['action'])=='add'){
mysql_query("INSERT INTO `category_parameter_values` SET `active`='1', ".implode(', ', $flds).";");
$_POST['id']=mysql_insert_id();
}
if(trim($_POST['action'])=='edit'){
mysql_query("UPDATE `category_parameter_values` SET ".implode(', ', $flds)." WHERE `id`='".intval($_POST['id'])."';");
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
$check=mysql_query("SELECT * FROM `category_parameter_values` WHERE `id`='"._F($key)."';");
if(mysql_num_rows($check)){
mysql_query("UPDATE `category_parameter_values` SET `sort`='".$order."' WHERE `id`='"._F($key)."';");
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
mysql_query("UPDATE `category_parameter_values` SET `active`='2' WHERE `id`='".intval($_POST['id'])."';");
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
mysql_query("UPDATE `category_parameter_values` SET `active`='1' WHERE `id`='".intval($_POST['id'])."';");
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
mysql_query("UPDATE `category_parameter_values` SET `active`='0' WHERE `id`='".intval($_POST['id'])."';");
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
//ini_set('display_errors', '1');
$get_items=mysql_query("SELECT * FROM `category_parameter_values` WHERE (`active`='0' OR `active`='1') ORDER BY `value_".$config['lang']."` ASC, `key` ASC;");
$get_parameters=mysql_query("SELECT * FROM `category_parameters` WHERE (`active`='0' OR `active`='1') ORDER BY `label_".$config['lang']."` ASC, `key` ASC;");
?>
<?php
$pagetitle='Управление значениями параметров';
$pagedesc=$config['description'];
?>
<?php include "includes/header.php"; ?>

<h4 class="special-title"><a href="/admin/">Администрирование</a><i class="fa fa-chevron-right"></i><?php if(trim($_GET['action'])!='' || intval($_GET['parent_id'])>0){ ?><a href="/admin/parameter-values/">Управление значениями параметров</a><?php } else { ?>Управление значениями параметров<?php } ?><?php if(intval($_GET['parent_id'])>0){ ?><i class="fa fa-chevron-right"></i><?php if(trim($_GET['action'])!=''){ ?><a href="/admin/parameter-values/?parent_id=<?php echo intval($_GET['parent_id']); ?>"><?php echo htmlspecialchars($parent['name_'.$config['lang']]); ?></a><?php } else { ?><?php echo htmlspecialchars($parent['name_'.$config['lang']]); ?><?php } ?><?php } ?><?php if(trim($_GET['action'])=='add'){ ?><i class="fa fa-chevron-right"></i>Добавление<?php } ?><?php if(trim($_GET['action'])=='edit'){ ?><i class="fa fa-chevron-right"></i>Редактирование<?php } ?></h4>

<hr>

<?php if(trim($_GET['action'])=='add' || trim($_GET['action'])=='edit'){ ?>

<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="POST" autocomplete="off" class="form-horizontal add-form ajax-form" data-callback="adminParameterValuesCallBack">
<?php if(trim($_GET['action'])=='edit'){ ?>
<input type="hidden" name="id" value="<?php echo $item['id']; ?>">
<?php } ?>
<input type="hidden" name="action" value="<?php echo htmlspecialchars(trim($_GET['action'])); ?>">
<?php foreach($config['langs'] as $l){ ?>
<div class="form-group">
<label class="col-sm-3 control-label">Значение (<?php echo mb_strtoupper($l); ?>) <span class="input-required">*</span></label>
<div class="col-sm-5">
<input type="text" autocomplete="off" class="form-control" name="value_<?php echo $l; ?>" value="<?php echo htmlspecialchars($item['value_'.$l]); ?>">
</div>
</div>
<?php } ?>
<hr>
<div class="form-group">
<label class="col-sm-3 control-label">Системный ключ <span class="input-required">*</span></label>
<div class="col-sm-5">
<input type="text" autocomplete="off" class="form-control" name="key" value="<?php echo htmlspecialchars($item['key']); ?>">
</div>
</div>
<hr>
<div class="form-group">
<label class="col-sm-3 control-label">Категории <span class="input-required">*</span></label>
<div class="col-sm-5">
<div style="margin-top:-5px;">
<div style="padding-left:0px;padding-top:5px;">
<div class="checkbox" style="padding-top:0;min-height:auto;">
<input type="checkbox" name="cids[]" value="0" id="cid_0"<?php if(in_array('0', explode(',', $item['cids']))){ ?> checked<?php } ?>>
<label for="cid_0">
Все категории (в поисковой форме)
</label>
</div>
</div>
<?php adminEditParameterCategories(0, 0); ?>
</div>
</div>
</div>
<hr>
<div class="form-group">
<label class="col-sm-3 control-label">Параметры <span class="input-required">*</span></label>
<div class="col-sm-5">
<div style="margin-top:-5px;">
<?php
$rpar='';
$rpari=1;
?>
<?php while($param=mysql_fetch_assoc($get_parameters)){ ?>
<div style="padding-left:0px;padding-top:5px;">
<div class="checkbox" style="padding-top:0;min-height:auto;">
<input type="checkbox" name="keys[]" value="<?php echo htmlspecialchars($param['key']); ?>" id="key_<?php echo htmlspecialchars($param['key']); ?>"<?php if(in_array($param['key'], explode(',', $item['keys']))){ ?> checked<?php } ?>>
<label for="key_<?php echo htmlspecialchars($param['key']); ?>">
<?php echo htmlspecialchars($param['label_'.$config['lang']]); ?>
<?php if($rpar==$param['label_'.$config['lang']]){ ?>
<?php
$rpari++;
?>
<b style="color:#999;">&nbsp;#<?php echo $rpari; ?></b>
<?php } else { ?>
<?php
$rpari=1;
?>
<?php } ?>
</label>
</div>
</div>
<?php
$rpar=$param['label_'.$config['lang']];
?>
<?php } ?>
</div>
</div>
</div>
<hr>
<div class="form-group">
<div class="col-sm-3"></div>
<div class="col-sm-5">
<button type="submit" class="btn btn-primary"><?php if(trim($_GET['action'])=='edit'){ ?>Сохранить значение<?php } else { ?>Добавить значение<?php } ?></button>
</div>
</div>
</form>

<?php } else { ?>

<a class="btn btn-primary btn-sm" href="/admin/parameter-values/?action=add">Добавить значение</a>

<hr>
<?php if(mysql_num_rows($get_items)){ ?>
<table class="table">
<thead>
<tr>
<th>
#
</th>
<th>
Ключ
</th>
<th>
Значение (<?php echo mb_strtoupper($config['lang']); ?>)
</th>
<th></th>
</tr>
</thead>
<tbody id="paramsList">
<?php
$rpar='';
$rpari=1;
?>
<?php while($item=mysql_fetch_assoc($get_items)){ ?>
<tr id="value_<?php echo $item['key']; ?>">
<td width="50">
<?php echo $item['id']; ?>
</td>
<td width="50">
<?php echo $item['key']; ?>
</td>
<td>
<?php echo htmlspecialchars($item['value_'.$config['lang']]); ?>
<?php if($rpar==$item['value_'.$config['lang']]){ ?>
<?php
$rpari++;
?>
<b style="color:#999;">&nbsp;#<?php echo $rpari; ?></b>
<?php } else { ?>
<?php
$rpari=1;
?>
<?php } ?>
</td>
<td align="right">
<a title="Редактировать" class="btn btn-primary btn-xs" href="/admin/parameter-values/?action=edit&id=<?php echo $item['id']; ?>"><i class="fa fa-pencil"></i></a>
<?php if($item['active']=='0'){ ?>
<button title="Активировать" class="btn btn-success btn-xs" onclick="$.post('/ajax<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>', 'id=<?php echo $item['id']; ?>&action=activate', function(){ location.reload(); }); return false;"><i class="fa fa-check"></i></button>
<?php } ?>
<?php if($item['active']=='1'){ ?>
<button title="Отключить" class="btn btn-warning btn-xs" onclick="$.post('/ajax<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>', 'id=<?php echo $item['id']; ?>&action=deactivate', function(){ location.reload(); }); return false;"><i class="fa fa-ban"></i></button>
<?php } ?>
<button title="Удалить" class="btn btn-danger btn-xs" onclick="if(confirm('Вы уверены?')){ $.post('/ajax<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>', 'id=<?php echo $item['id']; ?>&action=delete', function(){ location.reload(); }); } return false;"><i class="fa fa-remove"></i></button>
</td>
</tr>
<?php
$rpar=$item['value_'.$config['lang']];
?>
<?php } ?>
</tbody>
</table>
<?php } else { ?>
Здесь пока ничего нет.
<?php } ?>

<?php } ?>

<?php include "includes/footer.php"; ?>