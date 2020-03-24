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
$check_item=mysql_query("SELECT * FROM `categories` WHERE `id`='".intval($_GET['id'])."';");
if(!mysql_num_rows($check_item)){
header("Location: /admin/categories/");
exit;
}
$item=mysql_fetch_assoc($check_item);
if($item['parent_id']!=intval($_GET['parent_id'])){
header("Location: /admin/categories/");
exit;
}
}
if(intval($_GET['parent_id'])>0){
$check_parent=mysql_query("SELECT * FROM `categories` WHERE `id`='".intval($_GET['parent_id'])."';");
if(!mysql_num_rows($check_parent)){
header("Location: /admin/categories/");
exit;
}
$parent=mysql_fetch_assoc($check_parent);
}
?>
<?php
if(trim($_GET['mode'])=='ajax'){
header('Content-Type: application/json; charset=utf-8');
if(trim($_POST['action'])=='add' || trim($_POST['action'])=='edit'){
$result=array();
$result['errors']=array();
if(trim($_POST['url'])==''){
$result['errors']['url']='Укажите URL категории.';
} elseif((trim($_POST['action'])=='add' || (trim($_POST['action'])=='edit' && trim($_POST['url'])!=trim($item['url']))) && mysql_num_rows(mysql_query("SELECT * FROM `categories` WHERE `url`='"._F($_POST['url'])."' AND (`active`='0' OR `active`='1');"))){
$result['errors']['url']='Категория с данным URL уже существует.';
}
foreach($config['langs'] as $l){
if(trim($_POST['name_'.$l])==''){
$result['errors']['name_'.$l]='Заполните это поле.';
}
if(intval($_POST['private_business'])==1){
if(trim($_POST['private_name_'.$l])==''){
$result['errors']['private_name_'.$l]='Заполните это поле.';
}
if(trim($_POST['business_name_'.$l])==''){
$result['errors']['business_name_'.$l]='Заполните это поле.';
}
}
if(intval($_POST['offer_seek'])==1){
if(trim($_POST['seek_name_adding_'.$l])==''){
$result['errors']['seek_name_adding_'.$l]='Заполните это поле.';
}
if(trim($_POST['offer_name_adding_'.$l])==''){
$result['errors']['offer_name_adding_'.$l]='Заполните это поле.';
}
}
}
if(intval($_POST['max_photos'])<=0){
$result['errors']['max_photos']='Заполните это поле.';
}
if(intval($_GET['parent_id'])==0){
if(trim($_POST['icon'])==''){
$result['errors']['icon']='Загрузите иконку.';
}
}
if(count($result['errors'])==0){
$flds=array();
$flds[]="`url`='"._F($_POST['url'])."'";
$flds[]="`parent_id`='".intval($_GET['parent_id'])."'";
$flds[]="`max_photos`='".intval($_POST['max_photos'])."'";
$flds[]="`offer_seek`='".intval($_POST['offer_seek'])."'";
$flds[]="`private_business`='".intval($_POST['private_business'])."'";
$flds[]="`level`='".intval($parent['level']+1)."'";
foreach($config['langs'] as $l){
$flds[]="`name_".$l."`='"._F($_POST['name_'.$l])."'";
$flds[]="`metak_".$l."`='"._F($_POST['metak_'.$l])."'";
$flds[]="`metad_".$l."`='"._F($_POST['metad_'.$l])."'";
$flds[]="`private_name_".$l."`='"._F($_POST['private_name_'.$l])."'";
$flds[]="`business_name_".$l."`='"._F($_POST['business_name_'.$l])."'";
$flds[]="`seek_name_adding_".$l."`='"._F($_POST['seek_name_adding_'.$l])."'";
$flds[]="`offer_name_adding_".$l."`='"._F($_POST['offer_name_adding_'.$l])."'";
}
if(trim($_POST['action'])=='add'){
$flds[]="`sort`='99999'";
}
if(trim($_POST['action'])=='add'){
mysql_query("INSERT INTO `categories` SET `active`='1', ".implode(', ', $flds).";");
$_POST['id']=mysql_insert_id();
}
if(trim($_POST['action'])=='edit'){
mysql_query("UPDATE `categories` SET ".implode(', ', $flds)." WHERE `id`='".intval($_POST['id'])."';");
}
if(intval($_GET['parent_id'])==0){
if(trim($_POST['action'])=='add' || (trim($_POST['action'])=='edit' && trim($_POST['icon'])!=$item['id'].'.png')){
if(trim($_POST['action'])=='edit'){
unlink('images/cats/'.intval($_POST['id']).'.png');
}
rename('images/cats/'.trim($_POST['icon']), 'images/cats/'.intval($_POST['id']).'.png');
}
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
$check=mysql_query("SELECT * FROM `categories` WHERE `id`='".intval($key)."';");
if(mysql_num_rows($check)){
mysql_query("UPDATE `categories` SET `sort`='".$order."' WHERE `id`='".intval($key)."';");
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
mysql_query("UPDATE `categories` SET `active`='2' WHERE `id`='".intval($_POST['id'])."';");
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
mysql_query("UPDATE `categories` SET `active`='1' WHERE `id`='".intval($_POST['id'])."';");
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
mysql_query("UPDATE `categories` SET `active`='0' WHERE `id`='".intval($_POST['id'])."';");
$result['status']='success';
} else {
$result['status']='error';
}
echo json_encode($result);
}
if(intval($_GET['parent_id'])==0){
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
mkdir('images/cats/', 0777);
$filename='images/cats/'.$key.'.png';
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
}
exit;
}
?>
<?php
$get_items=mysql_query("SELECT * FROM `categories` WHERE `parent_id`='".intval($_GET['parent_id'])."' AND (`active`='0' OR `active`='1') ORDER BY `sort` ASC;");
?>
<?php
$pagetitle='Управление категориями';
$pagedesc=$config['description'];
?>
<?php include "includes/header.php"; ?>

<h4 class="special-title"><a href="/admin/">Администрирование</a><i class="fa fa-chevron-right"></i><?php if(trim($_GET['action'])!='' || intval($_GET['parent_id'])>0){ ?><a href="/admin/categories/">Управление категориями</a><?php } else { ?>Управление категориями<?php } ?><?php if(intval($_GET['parent_id'])>0){ ?><i class="fa fa-chevron-right"></i><?php if(trim($_GET['action'])!=''){ ?><a href="/admin/categories/?parent_id=<?php echo intval($_GET['parent_id']); ?>"><?php echo htmlspecialchars($parent['name_'.$config['lang']]); ?></a><?php } else { ?><?php echo htmlspecialchars($parent['name_'.$config['lang']]); ?><?php } ?><?php } ?><?php if(trim($_GET['action'])=='add'){ ?><i class="fa fa-chevron-right"></i>Добавление<?php } ?><?php if(trim($_GET['action'])=='edit'){ ?><i class="fa fa-chevron-right"></i>Редактирование<?php } ?></h4>

<hr>

<?php if(trim($_GET['action'])=='add' || trim($_GET['action'])=='edit'){ ?>

<div id="uploadErrors" style="display:none;">
<div class="window-title"><?php echo l('upload_error'); ?><a href="javascript:void(0);" onclick="$.fancybox.close();"></a></div>
<div class="window-message"></div>
<div class="window-buttons">
<button class="btn btn-primary" onclick="$.fancybox.close();">OK</button>
</div>
</div>

<input type="file" class="hidden" id="uploaderButton" name="file" data-url="/ajax/admin/categories/">
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
$('#icon_uploaded').html('<img src="/images/cats/'+item.file+'" style="width:32px;margin-bottom:20px;">');
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

<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="POST" autocomplete="off" class="form-horizontal add-form ajax-form" data-callback="adminCategoriesCallBack">
<?php if(trim($_GET['action'])=='edit'){ ?>
<input type="hidden" name="id" value="<?php echo $item['id']; ?>">
<?php } ?>
<input type="hidden" name="action" value="<?php echo htmlspecialchars(trim($_GET['action'])); ?>">
<?php foreach($config['langs'] as $l){ ?>
<div class="form-group">
<label class="col-sm-3 control-label">Название (<?php echo mb_strtoupper($l); ?>) <span class="input-required">*</span></label>
<div class="col-sm-5">
<input type="text" autocomplete="off" class="form-control" name="name_<?php echo $l; ?>" value="<?php echo htmlspecialchars($item['name_'.$l]); ?>">
</div>
</div>
<?php } ?>
<?php if(count($config['langs'])>1){ ?><hr><?php } ?>
<?php foreach($config['langs'] as $l){ ?>
<div class="form-group">
<label class="col-sm-3 control-label">Meta Keywords (<?php echo mb_strtoupper($l); ?>)</label>
<div class="col-sm-5">
<input type="text" autocomplete="off" class="form-control" name="metak_<?php echo $l; ?>" value="<?php echo htmlspecialchars($item['metak_'.$l]); ?>">
</div>
</div>
<?php } ?>
<?php if(count($config['langs'])>1){ ?><hr><?php } ?>
<?php foreach($config['langs'] as $l){ ?>
<div class="form-group">
<label class="col-sm-3 control-label">Meta Description (<?php echo mb_strtoupper($l); ?>)</label>
<div class="col-sm-5">
<textarea class="form-control" name="metad_<?php echo $l; ?>" rows="2"><?php echo htmlspecialchars($item['metad_'.$l]); ?></textarea>
</div>
</div>
<?php } ?>
<?php if(count($config['langs'])>1){ ?><hr><?php } ?>
<div class="form-group">
<label class="col-sm-3 control-label">URL <span class="input-required">*</span></label>
<div class="col-sm-5">
<input type="text" autocomplete="off" class="form-control" name="url" value="<?php echo htmlspecialchars($item['url']); ?>">
</div>
</div>
<?php if(count($config['langs'])>1){ ?><hr><?php } ?>
<div class="form-group">
<label class="col-sm-3 control-label"></label>
<div class="col-sm-5">
<div class="checkbox" style="padding-top:0;min-height:auto;">
<input type="checkbox" name="private_business" value="1" id="private_business" onchange="if(this.checked){ $('#private_business_options').removeClass('hidden'); } else { $('#private_business_options').addClass('hidden'); }"<?php if(intval($item['private_business'])==1){ ?> checked<?php } ?>>
<label for="private_business">
Включить опции частного лица и бизнеса
</label>
</div>
</div>
</div>
<div id="private_business_options" class="<?php if(intval($item['private_business'])==0){ ?>hidden<?php } ?>">
<?php if(count($config['langs'])>1){ ?><hr><?php } ?>
<?php foreach($config['langs'] as $l){ ?>
<div class="form-group">
<label class="col-sm-3 control-label">Опция частного лица (<?php echo mb_strtoupper($l); ?>) <span class="input-required">*</span></label>
<div class="col-sm-5">
<input type="text" autocomplete="off" class="form-control" name="private_name_<?php echo $l; ?>" value="<?php echo htmlspecialchars($item['private_name_'.$l]); ?>">
</div>
</div>
<?php } ?>
<?php if(count($config['langs'])>1){ ?><hr><?php } ?>
<?php foreach($config['langs'] as $l){ ?>
<div class="form-group">
<label class="col-sm-3 control-label">Опция бизнеса (<?php echo mb_strtoupper($l); ?>) <span class="input-required">*</span></label>
<div class="col-sm-5">
<input type="text" autocomplete="off" class="form-control" name="business_name_<?php echo $l; ?>" value="<?php echo htmlspecialchars($item['business_name_'.$l]); ?>">
</div>
</div>
<?php } ?>
</div>
<?php if(count($config['langs'])>1){ ?><hr><?php } ?>
<div class="form-group">
<label class="col-sm-3 control-label"></label>
<div class="col-sm-5">
<div class="checkbox" style="padding-top:0;min-height:auto;">
<input type="checkbox" name="offer_seek" value="1" id="offer_seek" onchange="if(this.checked){ $('#offer_seek_options').removeClass('hidden'); } else { $('#offer_seek_options').addClass('hidden'); }"<?php if(intval($item['offer_seek'])==1){ ?> checked<?php } ?>>
<label for="offer_seek">
Включить опции спроса и предложения
</label>
</div>
</div>
</div>
<div id="offer_seek_options" class="<?php if(intval($item['offer_seek'])==0){ ?>hidden<?php } ?>">
<?php if(count($config['langs'])>1){ ?><hr><?php } ?>
<?php foreach($config['langs'] as $l){ ?>
<div class="form-group">
<label class="col-sm-3 control-label">Опция спроса (<?php echo mb_strtoupper($l); ?>) <span class="input-required">*</span></label>
<div class="col-sm-5">
<input type="text" autocomplete="off" class="form-control" name="seek_name_adding_<?php echo $l; ?>" value="<?php echo htmlspecialchars($item['seek_name_adding_'.$l]); ?>">
</div>
</div>
<?php } ?>
<?php if(count($config['langs'])>1){ ?><hr><?php } ?>
<?php foreach($config['langs'] as $l){ ?>
<div class="form-group">
<label class="col-sm-3 control-label">Опция предложения (<?php echo mb_strtoupper($l); ?>) <span class="input-required">*</span></label>
<div class="col-sm-5">
<input type="text" autocomplete="off" class="form-control" name="offer_name_adding_<?php echo $l; ?>" value="<?php echo htmlspecialchars($item['offer_name_adding_'.$l]); ?>">
</div>
</div>
<?php } ?>
</div>
<hr>
<div class="form-group">
<label class="col-sm-3 control-label">
Макс. кол-во. фотографий <span class="input-required">*</span>
</label>
<div class="col-sm-2">
<input type="text" autocomplete="off" class="form-control" name="max_photos" value="<?php if($item['max_photos']>0){ echo htmlspecialchars($item['max_photos']); } ?>">
</div>
</div>
<?php if(intval($_GET['parent_id'])==0){ ?>
<hr>
<div class="form-group">
<label class="col-sm-3 control-label" style="padding-top:2px;">PNG-иконка категории <span class="input-required">*</span></label>
<div class="col-sm-2">
<input type="hidden" name="icon" id="icon" value="<?php if(intval($item['id'])>0){ echo $item['id'].'.png'; } ?>">
<div id="icon_uploaded">
<?php if(intval($item['id'])>0){ ?>
<img src="/images/cats/<?php echo $item['id']; ?>.png?<?php echo rand(1,9999); ?>" style="width:32px;margin-bottom:20px;">
<?php } ?>
</div>
<a href="javascript:void(0);" onclick="$('#uploaderButton').click(); return false;" style="font-size:12px;font-weight:bold;">Загрузить файл</a>
</div>
</div>
<?php } ?>
<hr>
<div class="form-group">
<div class="col-sm-3"></div>
<div class="col-sm-5">
<button type="submit" class="btn btn-primary"><?php if(trim($_GET['action'])=='edit'){ ?>Сохранить категорию<?php } else { ?>Добавить категорию<?php } ?></button>
</div>
</div>
</form>

<?php if(trim($_GET['action'])=='edit'){ ?>

<br>

<hr>

<h4 class="special-title">Параметры данной категории:</h4>

<hr>

<?php
$get_parameters=mysql_query("SELECT `category_parameter_sort`.`sort`, `category_parameters`.* FROM `category_parameters`, `category_parameter_sort` WHERE `category_parameter_sort`.`key`=`category_parameters`.`key` AND `category_parameter_sort`.`cid`=".$item['id']." AND FIND_IN_SET(".$item['id'].", `category_parameters`.`cids`) AND `category_parameters`.`type`!='hidden' AND (`category_parameters`.`active`='0' OR `category_parameters`.`active`='1') ORDER BY `category_parameter_sort`.`sort` ASC;");
?>

<?php if(mysql_num_rows($get_parameters)){ ?>
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
<tbody id="paramsList">
<?php
$rpar='';
$rpari=1;
?>
<?php while($param=mysql_fetch_assoc($get_parameters)){ ?>
<tr id="cat_param_<?php echo $param['key']; ?>">
<td width="40">
<i class="fa fa-bars" style="color:#666;cursor:move;" title="Тащите для изменения порядка элементов"></i>
</td>
<td width="50">
<?php echo $param['id']; ?>
</td>
<td width="50">
<?php echo $param['key']; ?>
</td>
<td>
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
</td>
<td align="right">
<a title="Редактировать" target="_blank" class="btn btn-primary btn-xs" href="/admin/parameters/?action=edit&id=<?php echo $param['id']; ?>"><i class="fa fa-pencil"></i></a>
<?php if($param['active']=='0'){ ?>
<button title="Активировать" class="btn btn-success btn-xs" onclick="$.post('/ajax/admin/parameters/', 'id=<?php echo $param['id']; ?>&action=activate', function(){ location.reload(); }); return false;"><i class="fa fa-check"></i></button>
<?php } ?>
<?php if($param['active']=='1'){ ?>
<button title="Отключить" class="btn btn-warning btn-xs" onclick="$.post('/ajax/admin/parameters/', 'id=<?php echo $param['id']; ?>&action=deactivate', function(){ location.reload(); }); return false;"><i class="fa fa-ban"></i></button>
<?php } ?>
<button title="Удалить" class="btn btn-danger btn-xs" onclick="if(confirm('Вы уверены?')){ $.post('/ajax/admin/parameters/', 'id=<?php echo $param['id']; ?>&action=delete', function(){ location.reload(); }); } return false;"><i class="fa fa-remove"></i></button>
</td>
</tr>
<?php
$rpar=$param['label_'.$config['lang']];
?>
<?php } ?>
</tbody>
</table>

<script type="text/javascript">
$(function(){
var list=document.getElementById("paramsList");
Sortable.create(list, {
handle:'i.fa-bars',
draggable:'tr',
onEnd:function(e){
var paramsKeys=[];
$('#paramsList tr').each(function(){
var param_key=($(this).attr('id')).replace('cat_param_', '');
paramsKeys.push(param_key);
});
var keys=paramsKeys.join(',');
$.post(langPrefix+'/ajax/admin/parameters/?keys='+keys+'&cid=<?php echo $item['id']; ?>', 'action=sort');
}
});
});
</script>
<?php } else { ?>
Здесь пока ничего нет.
<?php } ?>

<?php } ?>

<?php } else { ?>

<?php if(intval($_GET['parent_id'])>0){ ?>
<?php if(intval($parent['parent_id'])>0){ ?>
<a class="btn btn-default btn-sm" href="/admin/categories/?parent_id=<?php echo intval($parent['parent_id']); ?>">К родительской категории</a>
<?php } else { ?>
<a class="btn btn-default btn-sm" href="/admin/categories/">Ко всем категориям</a>
<?php } ?>
<a class="btn btn-primary btn-sm" href="/admin/categories/?action=add&parent_id=<?php echo intval($_GET['parent_id']); ?>">Добавить подкатегорию</a>
<?php } else { ?>
<a class="btn btn-primary btn-sm" href="/admin/categories/?action=add">Добавить категорию</a>
<?php } ?>

<hr>
<?php if(mysql_num_rows($get_items)){ ?>
<table class="table">
<thead>
<tr>
<th></th>
<th>
#
</th>
<?php if(intval($_GET['parent_id'])==0){ ?>
<th></th>
<?php } ?>
<th>
Название категории (<?php echo mb_strtoupper($config['lang']); ?>)
</th>
<th></th>
</tr>
</thead>
<tbody id="catsList">
<?php while($item=mysql_fetch_assoc($get_items)){ ?>
<tr id="cat_<?php echo $item['id']; ?>">
<td width="40">
<i class="fa fa-bars" style="color:#666;cursor:move;" title="Тащите для изменения порядка элементов"></i>
</td>
<td width="50">
<?php echo $item['id']; ?>
</td>
<?php if(intval($_GET['parent_id'])==0){ ?>
<td width="32" align="center">
<img src="/images/cats/<?php echo $item['id']; ?>.png?<?php echo rand(1,9999); ?>" style="max-width:16px;height:16px;">
</td>
<?php } ?>
<td style="<?php if($item['active']=='0'){ ?>text-decoration:line-through;<?php } ?>">
<?php echo htmlspecialchars($item['name_'.$config['lang']]); ?>
</td>
<td align="right">
<?php if($item['level']<3){ ?>
<a title="Управление подкатегориями" class="btn btn-default btn-xs" href="/admin/categories/?parent_id=<?php echo $item['id']; ?>"><i class="fa fa-arrow-right"></i></a>
<?php } ?>
<a title="Редактировать" class="btn btn-primary btn-xs" href="/admin/categories/?action=edit&id=<?php echo $item['id']; ?><?php if(intval($_GET['parent_id'])>0){ ?>&parent_id=<?php echo intval($_GET['parent_id']); ?><?php } ?>"><i class="fa fa-pencil"></i></a>
<?php if($item['active']=='0'){ ?>
<button title="Активировать" class="btn btn-success btn-xs" onclick="$.post('/ajax<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>', 'id=<?php echo $item['id']; ?>&action=activate', function(){ location.reload(); }); return false;"><i class="fa fa-check"></i></button>
<?php } ?>
<?php if($item['active']=='1'){ ?>
<button title="Отключить" class="btn btn-warning btn-xs" onclick="$.post('/ajax<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>', 'id=<?php echo $item['id']; ?>&action=deactivate', function(){ location.reload(); }); return false;"><i class="fa fa-ban"></i></button>
<?php } ?>
<button title="Удалить" class="btn btn-danger btn-xs" onclick="if(confirm('Вы уверены?')){ $.post('/ajax<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>', 'id=<?php echo $item['id']; ?>&action=delete', function(){ location.reload(); }); } return false;"><i class="fa fa-remove"></i></button>
</td>
</tr>
<?php } ?>
</tbody>
</table>

<script type="text/javascript">
$(function(){
var list=document.getElementById("catsList");
Sortable.create(list, {
handle:'i.fa-bars',
draggable:'tr',
onEnd:function(e){
var catsIDs=[];
$('#catsList tr').each(function(){
var cat_id=($(this).attr('id')).replace('cat_', '');
catsIDs.push(cat_id);
});
var keys=catsIDs.join(',');
$.post(langPrefix+'/ajax/admin/categories/?keys='+keys, 'action=sort');
}
});
});
</script>
<?php } else { ?>
Здесь пока ничего нет.
<?php } ?>

<?php } ?>

<?php include "includes/footer.php"; ?>