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
$check_item=mysql_query("SELECT * FROM `banners` WHERE `key`='"._F($_GET['key'])."';");
if(!mysql_num_rows($check_item)){
header("Location: /admin/banners/");
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
if(trim($_POST['bkey'])==''){
$result['errors']['bkey']='Укажите ключ баннера.';
} elseif((trim($_POST['action'])=='add' || (trim($_POST['action'])=='edit' && trim($_POST['bkey'])!=trim($_GET['key']))) && mysql_num_rows(mysql_query("SELECT * FROM `banners` WHERE `key`='"._F($_POST['bkey'])."';"))){
$result['errors']['bkey']='Баннер с данным ключом уже существует.';
}
if(count($result['errors'])==0){
$flds=array();
$flds[]="`key`='"._F($_POST['bkey'])."'";
$flds[]="`html`='"._F($_POST['html'])."'";
if(trim($_POST['action'])=='add'){
mysql_query("INSERT INTO `banners` SET `active`='1', ".implode(', ', $flds).";");
}
if(trim($_POST['action'])=='edit'){
mysql_query("UPDATE `banners` SET ".implode(', ', $flds)." WHERE `key`='"._F($_POST['key'])."';");
}
$result['status']='success';
} else {
$result['status']='error';
}
echo json_encode($result);
}
if(trim($_POST['action'])=='delete'){
$result=array();
$result['errors']=array();
if(count($result['errors'])==0){
mysql_query("UPDATE `banners` SET `active`='2' WHERE `key`='"._F($_POST['key'])."';");
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
mysql_query("UPDATE `banners` SET `active`='1' WHERE `key`='"._F($_POST['key'])."';");
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
mysql_query("UPDATE `banners` SET `active`='0' WHERE `key`='"._F($_POST['key'])."';");
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
$get_items=mysql_query("SELECT * FROM `banners` WHERE `active`='0' OR `active`='1' ORDER BY `key` ASC;");
?>
<?php
$pagetitle='Настройка баннеров';
$pagedesc=$config['description'];
?>
<?php include "includes/header.php"; ?>

<h4 class="special-title"><a href="/admin/">Администрирование</a><i class="fa fa-chevron-right"></i><?php if(trim($_GET['action'])!=''){ ?><a href="/admin/banners/">Настройка баннеров</a><?php } else { ?>Настройка баннеров<?php } ?><?php if(trim($_GET['action'])=='add'){ ?><i class="fa fa-chevron-right"></i>Добавление<?php } ?><?php if(trim($_GET['action'])=='edit'){ ?><i class="fa fa-chevron-right"></i>Редактирование<?php } ?></h4>

<hr>

<?php if(trim($_GET['action'])=='add' || trim($_GET['action'])=='edit'){ ?>

<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="POST" autocomplete="off" class="form-horizontal add-form ajax-form" data-callback="adminBannersCallBack">
<?php if(trim($_GET['action'])=='edit'){ ?>
<input type="hidden" name="key" value="<?php echo $item['key']; ?>">
<?php } ?>
<input type="hidden" name="action" value="<?php echo htmlspecialchars(trim($_GET['action'])); ?>">
<div class="form-group">
<label class="col-sm-3 control-label">Ключ баннера <span class="input-required">*</span></label>
<div class="col-sm-7">
<input type="text" autocomplete="off" class="form-control" name="bkey" value="<?php echo htmlspecialchars($item['key']); ?>">
</div>
</div>
<div class="form-group">
<label class="col-sm-3 control-label">HTML-код баннера</label>
<div class="col-sm-7">
<textarea name="html" class="form-control" rows="3"><?php echo htmlspecialchars($item['html']); ?></textarea>
</div>
</div>
<hr>
<div class="form-group">
<div class="col-sm-3"></div>
<div class="col-sm-7">
<button type="submit" class="btn btn-primary"><?php if(trim($_GET['action'])=='edit'){ ?>Сохранить баннер<?php } else { ?>Добавить баннер<?php } ?></button>
</div>
</div>
</form>

<?php } else { ?>

<a class="btn btn-primary btn-sm" href="/admin/banners/?action=add">Добавить баннер</a>

<hr>
<?php if(mysql_num_rows($get_items)){ ?>
<table class="table">
<thead>
<tr>
<th>
Ключ баннера
</th>
<th>
PHP-код баннера
</th>
<th></th>
</tr>
</thead>
<?php while($item=mysql_fetch_assoc($get_items)){ ?>
<tr>
<td style="<?php if($item['active']=='0'){ ?>text-decoration:line-through;<?php } ?>">
<?php echo $item['key']; ?>
</td>
<td style="<?php if($item['active']=='0'){ ?>text-decoration:line-through;<?php } ?>font-size:10px;">
<?php echo htmlspecialchars("<"."?"."php renderBanner('".$item['key']."'); ?".">"); ?>
</td>
<td align="right">
<a title="Редактировать" class="btn btn-primary btn-xs" href="/admin/banners/?action=edit&key=<?php echo $item['key']; ?>"><i class="fa fa-pencil"></i></a>
<?php if($item['active']=='0'){ ?>
<button title="Активировать" class="btn btn-success btn-xs" onclick="$.post('/ajax<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>', 'key=<?php echo $item['key']; ?>&action=activate', function(){ location.reload(); }); return false;"><i class="fa fa-check"></i></button>
<?php } ?>
<?php if($item['active']=='1'){ ?>
<button title="Отключить" class="btn btn-warning btn-xs" onclick="$.post('/ajax<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>', 'key=<?php echo $item['key']; ?>&action=deactivate', function(){ location.reload(); }); return false;"><i class="fa fa-ban"></i></button>
<?php } ?>
<button title="Удалить" class="btn btn-danger btn-xs" onclick="if(confirm('Вы уверены?')){ $.post('/ajax<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>', 'key=<?php echo $item['key']; ?>&action=delete', function(){ location.reload(); }); } return false;"><i class="fa fa-remove"></i></button>
</td>
</tr>
<?php } ?>
</table>
<?php } else { ?>
Здесь пока ничего нет.
<?php } ?>

<?php } ?>

<?php include "includes/footer.php"; ?>