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
$check_item=mysql_query("SELECT * FROM `category_parameters` WHERE `id`='".intval($_GET['id'])."';");
if(!mysql_num_rows($check_item)){
header("Location: /admin/parameters/");
exit;
}
$item=mysql_fetch_assoc($check_item);
$item['validators']=unserialize($item['validators']);
if($item['parent_id']!=intval($_GET['parent_id'])){
header("Location: /admin/parameters/");
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
} elseif((trim($_POST['action'])=='add' || (trim($_POST['action'])=='edit' && trim($_POST['key'])!=trim($item['key']))) && mysql_num_rows(mysql_query("SELECT * FROM `category_parameters` WHERE `key`='"._F($_POST['key'])."' AND (`active`='0' OR `active`='1');"))){
$result['errors']['key']='Параметр с данным ключом уже существует.';
}
foreach($config['langs'] as $l){
if(trim($_POST['label_'.$l])==''){
$result['errors']['label_'.$l]='Заполните это поле.';
}
}
if(trim($_POST['type'])==''){
$result['errors']['type']='Выберите что-нибудь.';
}
if(trim(implode('', $_POST['cids']))==''){
$result['errors']['cids']='Выберите что-нибудь.';
}
if(count($result['errors'])==0){
$_POST['validators']['min']=intval(trim($_POST['validators']['min']));
$_POST['validators']['max']=intval(trim($_POST['validators']['max']));
$_POST['validators']['minlength']=intval(trim($_POST['validators']['minlength']));
$_POST['validators']['maxlength']=intval(trim($_POST['validators']['maxlength']));
$validators=serialize(array_filter($_POST['validators']));
$flds=array();
$flds[]="`name`='"._F($_POST['name'])."'";
$flds[]="`url_key`='"._F($_POST['url_key'])."'";
$flds[]="`post_key`='"._F($_POST['post_key'])."'";
$flds[]="`type`='"._F($_POST['type'])."'";
$flds[]="`key`='"._F($_POST['key'])."'";
$flds[]="`validators`='"._F($validators)."'";
$flds[]="`cids`='"._F(implode(',', $_POST['cids']))."'";
$flds[]="`has_searching_form`='".intval($_POST['has_searching_form'])."'";
$flds[]="`has_adding_form`='1'";
$flds[]="`multi_select`='1'";
$flds[]="`offer_seek`='".$item['offer_seek']."'";
$flds[]="`is_numeric`='".((intval($_POST['validators']['digits'])==1)?'1':'0')."'";
foreach($config['langs'] as $l){
$flds[]="`label_".$l."`='"._F($_POST['label_'.$l])."'";
$flds[]="`suffix_".$l."`='"._F($_POST['suffix_'.$l])."'";
}
if(trim($_POST['action'])=='add'){
$flds[]="`sort`='99999'";
}
if(trim($_POST['action'])=='add'){
mysql_query("INSERT INTO `category_parameters` SET `active`='1', ".implode(', ', $flds).";");
$_POST['id']=mysql_insert_id();
}
if(trim($_POST['action'])=='add'){
foreach($_POST['cids'] as $cid){
mysql_query("INSERT INTO `category_parameter_sort` SET `key`='"._F($_POST['key'])."', `cid`='"._F($cid)."', `sort`='99999';");
}
}
if(trim($_POST['action'])=='edit'){
mysql_query("UPDATE `category_parameters` SET ".implode(', ', $flds)." WHERE `id`='".intval($_POST['id'])."';");
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
$check=mysql_query("SELECT * FROM `category_parameter_sort` WHERE `key`='"._F($key)."' AND `cid`='".intval($_GET['cid'])."';");
if(mysql_num_rows($check)){
mysql_query("UPDATE `category_parameter_sort` SET `sort`='".$order."' WHERE `key`='"._F($key)."' AND `cid`='".intval($_GET['cid'])."';");
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
mysql_query("UPDATE `category_parameters` SET `active`='2' WHERE `id`='".intval($_POST['id'])."';");
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
mysql_query("UPDATE `category_parameters` SET `active`='1' WHERE `id`='".intval($_POST['id'])."';");
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
mysql_query("UPDATE `category_parameters` SET `active`='0' WHERE `id`='".intval($_POST['id'])."';");
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
$get_items=mysql_query("SELECT * FROM `category_parameters` WHERE (`active`='0' OR `active`='1') ORDER BY `label_".$config['lang']."` ASC, `key` ASC;");
?>
<?php
$pagetitle='Управление параметрами объявлений';
$pagedesc=$config['description'];
?>
<?php include "includes/header.php"; ?>

<h4 class="special-title"><a href="/admin/">Администрирование</a><i class="fa fa-chevron-right"></i><?php if(trim($_GET['action'])!='' || intval($_GET['parent_id'])>0){ ?><a href="/admin/parameters/">Управление параметрами объявлений</a><?php } else { ?>Управление параметрами объявлений<?php } ?><?php if(intval($_GET['parent_id'])>0){ ?><i class="fa fa-chevron-right"></i><?php if(trim($_GET['action'])!=''){ ?><a href="/admin/parameters/?parent_id=<?php echo intval($_GET['parent_id']); ?>"><?php echo htmlspecialchars($parent['name_'.$config['lang']]); ?></a><?php } else { ?><?php echo htmlspecialchars($parent['name_'.$config['lang']]); ?><?php } ?><?php } ?><?php if(trim($_GET['action'])=='add'){ ?><i class="fa fa-chevron-right"></i>Добавление<?php } ?><?php if(trim($_GET['action'])=='edit'){ ?><i class="fa fa-chevron-right"></i>Редактирование<?php } ?></h4>

<hr>

<?php if(trim($_GET['action'])=='add' || trim($_GET['action'])=='edit'){ ?>

<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="POST" autocomplete="off" class="form-horizontal add-form ajax-form" data-callback="adminParametersCallBack">
<?php if(trim($_GET['action'])=='edit'){ ?>
<input type="hidden" name="id" value="<?php echo $item['id']; ?>">
<?php } ?>
<input type="hidden" name="action" value="<?php echo htmlspecialchars(trim($_GET['action'])); ?>">
<input type="hidden" name="name" value="<?php echo htmlspecialchars($item['name']); ?>">
<input type="hidden" name="url_key" value="<?php echo htmlspecialchars($item['url_key']); ?>">
<input type="hidden" name="post_key" value="<?php echo htmlspecialchars($item['post_key']); ?>">
<?php foreach($config['langs'] as $l){ ?>
<div class="form-group">
<label class="col-sm-3 control-label">Название (<?php echo mb_strtoupper($l); ?>) <span class="input-required">*</span></label>
<div class="col-sm-5">
<input type="text" autocomplete="off" class="form-control" name="label_<?php echo $l; ?>" value="<?php echo htmlspecialchars($item['label_'.$l]); ?>">
</div>
</div>
<?php } ?>
<?php if(count($config['langs'])>1){ ?><hr><?php } ?>
<?php foreach($config['langs'] as $l){ ?>
<div class="form-group">
<label class="col-sm-3 control-label">Суффикс (<?php echo mb_strtoupper($l); ?>)</label>
<div class="col-sm-2">
<input type="text" autocomplete="off" class="form-control" name="suffix_<?php echo $l; ?>" value="<?php echo htmlspecialchars($item['suffix_'.$l]); ?>">
</div>
</div>
<?php } ?>
<hr>
<div class="form-group">
<label class="col-sm-3 control-label">Тип поля <span class="input-required">*</span></label>
<div class="col-sm-3">
<select size="1" name="type" class="form-control" onchange="generateKeys();">
<option value="">Выберите...</option>
<option value="input"<?php if($item['type']=='input'){ ?> selected<?php } ?>>Текстовое поле</option>
<option value="select"<?php if($item['type']=='select'){ ?> selected<?php } ?>>Выпадающий список</option>
<option value="checkboxes"<?php if($item['type']=='checkboxes'){ ?> selected<?php } ?>>Переключатели</option>
<option value="date"<?php if($item['type']=='date'){ ?> selected<?php } ?>>Дата</option>
<option value="price"<?php if($item['type']=='price'){ ?> selected<?php } ?>>Цена</option>
<option value="salary"<?php if($item['type']=='salary'){ ?> selected<?php } ?>>Зарплата</option>
</select>
</div>
</div>
<div class="form-group">
<label class="col-sm-3 control-label">Системный ключ <span class="input-required">*</span></label>
<div class="col-sm-5">
<input type="text" autocomplete="off" class="form-control" name="key" value="<?php echo htmlspecialchars($item['key']); ?>" onkeyup="generateKeys();">
</div>
</div>
<div class="form-group">
<label class="col-sm-3 control-label"></label>
<div class="col-sm-5">
<div class="checkbox" style="padding-top:0;min-height:auto;">
<input type="checkbox" name="has_searching_form" value="1" id="has_searching_form"<?php if(intval($item['has_searching_form'])==1){ ?> checked<?php } ?>>
<label for="has_searching_form">
Отображать в поисковой форме
</label>
</div>
</div>
</div>
<div class="form-group">
<label class="col-sm-3 control-label"></label>
<div class="col-sm-5">
<div class="checkbox" style="padding-top:0;min-height:auto;">
<input type="checkbox" name="validators[digits]" value="1" id="digits" onchange="if(this.checked){ $('#digits_options').removeClass('hidden'); } else { $('#digits_options').addClass('hidden'); }"<?php if(intval($item['validators']['digits'])==1){ ?> checked<?php } ?>>
<label for="digits">
Допускаются только числа
</label>
</div>
</div>
</div>
<div id="digits_options" class="<?php if(intval($item['validators']['digits'])==0){ ?>hidden<?php } ?>">
<div class="form-group">
<label class="col-sm-3 control-label">
Мин. допуст. значение
</label>
<div class="col-sm-2">
<input type="text" autocomplete="off" class="form-control" name="validators[min]" value="<?php if(intval($item['validators']['min'])!=0){ echo intval($item['validators']['min']); } ?>">
</div>
</div>
<div class="form-group">
<label class="col-sm-3 control-label">
Макс. допуст. значение
</label>
<div class="col-sm-2">
<input type="text" autocomplete="off" class="form-control" name="validators[max]" value="<?php if(intval($item['validators']['max'])!=0){ echo intval($item['validators']['max']); } ?>">
</div>
</div>
</div>
<div class="form-group">
<label class="col-sm-3 control-label"></label>
<div class="col-sm-5">
<div class="checkbox" style="padding-top:0;min-height:auto;">
<input type="checkbox" name="validators[required]" value="1" id="required"<?php if(intval($item['validators']['required'])==1){ ?> checked<?php } ?>>
<label for="required">
Обязательно для заполнения
</label>
</div>
</div>
</div>
<div class="form-group">
<label class="col-sm-3 control-label">
Мин. длина значения
</label>
<div class="col-sm-2">
<input type="text" autocomplete="off" class="form-control" name="validators[minlength]" value="<?php if(intval($item['validators']['minlength'])!=0){ echo intval($item['validators']['minlength']); } ?>">
</div>
</div>
<div class="form-group">
<label class="col-sm-3 control-label">
Макс. длина значения
</label>
<div class="col-sm-2">
<input type="text" autocomplete="off" class="form-control" name="validators[maxlength]" value="<?php if(intval($item['validators']['maxlength'])!=0){ echo intval($item['validators']['maxlength']); } ?>">
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
<div class="col-sm-3"></div>
<div class="col-sm-5">
<button type="submit" class="btn btn-primary"><?php if(trim($_GET['action'])=='edit'){ ?>Сохранить параметр<?php } else { ?>Добавить параметр<?php } ?></button>
</div>
</div>
</form>

<script type="text/javascript">
function generateKeys(){
var key=$.trim($('input[name=key]').val());
key=key.split('_rev')[0];
var type=$.trim($('select[name=type]').val());
var url_key='';
if(key!=''){
if(type=='checkboxes' || type=='select'){
url_key='filter_enum_'+key;
}
if(type=='input' || type=='price' || type=='salary'){
url_key='filter_float_'+key;
}
if(type=='date'){
url_key='filter_date_'+key;
}
}
$('input[name=url_key]').val(url_key);
var name='';
if(key!=''){
name=key;
}
$('input[name=name]').val(name);
var post_key='';
if(key!=''){
post_key='param_'+key;
}
$('input[name=post_key]').val(post_key);
}
</script>

<?php if(trim($_GET['action'])=='edit'){ ?>

<br>

<hr>

<h4 class="special-title">Значения данного параметра:</h4>

<hr>

<?php
$get_values=mysql_query("SELECT * FROM `category_parameter_values` WHERE (`keys` LIKE '".$item['key'].",%' OR `keys` LIKE '%,".$item['key'].",%' OR `keys` LIKE '%,".$item['key']."' OR `keys`='".$item['key']."') AND CONCAT(',', `cids`, ',') REGEXP ',(".str_replace(',', '|', $item['cids'])."),' AND (`active`='0' OR `active`='1') ORDER BY `sort` ASC;");
?>

<?php if(mysql_num_rows($get_values)){ ?>
<table class="table">
<thead>
<tr>
<th></th>
<th>
#
</th>
<th>
Ключ
</th>
<th>
Название параметра (<?php echo mb_strtoupper($config['lang']); ?>)
</th>
<th></th>
</tr>
</thead>
<tbody id="valuesList">
<?php
$rpar='';
$rpari=1;
?>
<?php while($value=mysql_fetch_assoc($get_values)){ ?>
<tr id="param_value_<?php echo $value['id']; ?>">
<td width="40">
<i class="fa fa-bars" style="color:#666;cursor:move;" title="Тащите для изменения порядка элементов"></i>
</td>
<td width="50">
<?php echo $value['id']; ?>
</td>
<td width="50">
<?php echo $value['key']; ?>
</td>
<td>
<?php echo htmlspecialchars($value['value_'.$config['lang']]); ?>
<?php if($rpar==$value['value_'.$config['lang']]){ ?>
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
<a title="Редактировать" target="_blank" class="btn btn-primary btn-xs" href="/admin/parameter-values/?action=edit&id=<?php echo $value['id']; ?>"><i class="fa fa-pencil"></i></a>
<?php if($value['active']=='0'){ ?>
<button title="Активировать" class="btn btn-success btn-xs" onclick="$.post('/ajax/admin/parameter-values/', 'id=<?php echo $value['id']; ?>&action=activate', function(){ location.reload(); }); return false;"><i class="fa fa-check"></i></button>
<?php } ?>
<?php if($value['active']=='1'){ ?>
<button title="Отключить" class="btn btn-warning btn-xs" onclick="$.post('/ajax/admin/parameter-values/', 'id=<?php echo $value['id']; ?>&action=deactivate', function(){ location.reload(); }); return false;"><i class="fa fa-ban"></i></button>
<?php } ?>
<button title="Удалить" class="btn btn-danger btn-xs" onclick="if(confirm('Вы уверены?')){ $.post('/ajax/admin/parameter-values/', 'id=<?php echo $value['id']; ?>&action=delete', function(){ location.reload(); }); } return false;"><i class="fa fa-remove"></i></button>
</td>
</tr>
<?php
$rpar=$value['value_'.$config['lang']];
?>
<?php } ?>
</tbody>
</table>

<script type="text/javascript">
$(function(){
var list=document.getElementById("valuesList");
Sortable.create(list, {
handle:'i.fa-bars',
draggable:'tr',
onEnd:function(e){
var valuesKeys=[];
$('#valuesList tr').each(function(){
var value_key=($(this).attr('id')).replace('param_value_', '');
valuesKeys.push(value_key);
});
var keys=valuesKeys.join(',');
$.post(langPrefix+'/ajax/admin/parameter-values/?keys='+keys, 'action=sort');
}
});
});
</script>
<?php } else { ?>
Здесь пока ничего нет.
<?php } ?>

<?php } ?>

<?php } else { ?>

<a class="btn btn-primary btn-sm" href="/admin/parameters/?action=add">Добавить параметр</a>

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
Название параметра (<?php echo mb_strtoupper($config['lang']); ?>)
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
<tr id="parameter_<?php echo $item['id']; ?>">
<td width="50">
<?php echo $item['id']; ?>
</td>
<td width="50">
<?php echo $item['key']; ?>
</td>
<td>
<?php echo htmlspecialchars($item['label_'.$config['lang']]); ?>
<?php if($rpar==$item['label_'.$config['lang']]){ ?>
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
<a title="Редактировать" class="btn btn-primary btn-xs" href="/admin/parameters/?action=edit&id=<?php echo $item['id']; ?>"><i class="fa fa-pencil"></i></a>
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
$rpar=$item['label_'.$config['lang']];
?>
<?php } ?>
</tbody>
</table>
<?php } else { ?>
Здесь пока ничего нет.
<?php } ?>

<?php } ?>

<?php include "includes/footer.php"; ?>