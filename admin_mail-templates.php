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
$check_item=mysql_query("SELECT * FROM `mail_templates` WHERE `code`='"._F($_GET['code'])."';");
if(!mysql_num_rows($check_item)){
header("Location: /admin/mail-templates/");
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
if(trim($_POST['bcode'])==''){
$result['errors']['bcode']='Укажите ключ уведомления.';
} elseif((trim($_POST['action'])=='add' || (trim($_POST['action'])=='edit' && trim($_POST['bcode'])!=trim($_GET['code']))) && mysql_num_rows(mysql_query("SELECT * FROM `mail_templates` WHERE `code`='"._F($_POST['bcode'])."';"))){
$result['errors']['bcode']='Уведомление с данным ключом уже существует.';
}
foreach($config['langs'] as $l){
if(trim($_POST['title_'.$l])==''){
$result['errors']['title_'.$l]='Заполните это поле.';
}
if(trim($_POST['body_'.$l])==''){
$result['errors']['body_'.$l]='Заполните это поле.';
}
}
if(count($result['errors'])==0){
$flds=array();
$flds[]="`code`='"._F($_POST['bcode'])."'";
foreach($config['langs'] as $l){
$flds[]="`title_".$l."`='"._F($_POST['title_'.$l])."'";
$flds[]="`body_".$l."`='"._F($_POST['body_'.$l])."'";
}
if(trim($_POST['action'])=='add'){
mysql_query("INSERT INTO `mail_templates` SET ".implode(', ', $flds).";");
}
if(trim($_POST['action'])=='edit'){
mysql_query("UPDATE `mail_templates` SET ".implode(', ', $flds)." WHERE `code`='"._F($_POST['code'])."';");
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
mysql_query("DELETE FROM `mail_templates` WHERE `code`='"._F($_POST['code'])."';");
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
$get_items=mysql_query("SELECT * FROM `mail_templates` ORDER BY `code` ASC;");
?>
<?php
$pagetitle='Настройка EMail-уведомлений';
$pagedesc=$config['description'];
?>
<?php include "includes/header.php"; ?>

<h4 class="special-title"><a href="/admin/">Администрирование</a><i class="fa fa-chevron-right"></i><?php if(trim($_GET['action'])!=''){ ?><a href="/admin/mail-templates/">Настройка EMail-уведомлений</a><?php } else { ?>Настройка EMail-уведомлений<?php } ?><?php if(trim($_GET['action'])=='add'){ ?><i class="fa fa-chevron-right"></i>Добавление<?php } ?><?php if(trim($_GET['action'])=='edit'){ ?><i class="fa fa-chevron-right"></i>Редактирование<?php } ?></h4>

<hr>

<?php if(trim($_GET['action'])=='add' || trim($_GET['action'])=='edit'){ ?>

<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="POST" autocomplete="off" class="form-horizontal add-form ajax-form" data-callback="adminMailTemplatesCallBack">
<?php if(trim($_GET['action'])=='edit'){ ?>
<input type="hidden" name="code" value="<?php echo $item['code']; ?>">
<?php } ?>
<input type="hidden" name="action" value="<?php echo htmlspecialchars(trim($_GET['action'])); ?>">
<div class="form-group">
<label class="col-sm-3 control-label">Ключ уведомления <span class="input-required">*</span></label>
<div class="col-sm-7">
<input type="text" autocomplete="off" class="form-control" name="bcode" value="<?php echo htmlspecialchars($item['code']); ?>">
</div>
</div>
<?php if(count($config['langs'])>1){ ?><hr><?php } ?>
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
<label class="col-sm-3 control-label">HTML-код (<?php echo mb_strtoupper($l); ?>) <span class="input-required">*</span></label>
<div class="col-sm-7">
<textarea name="body_<?php echo $l; ?>" class="form-control" rows="3"><?php echo htmlspecialchars($item['body_'.$l]); ?></textarea>
</div>
</div>
<?php } ?>
<hr>
<div class="form-group">
<div class="col-sm-3"></div>
<div class="col-sm-7">
<button type="submit" class="btn btn-primary"><?php if(trim($_GET['action'])=='edit'){ ?>Сохранить уведомление<?php } else { ?>Добавить уведомление<?php } ?></button>
</div>
</div>
</form>

<?php } else { ?>

<a class="btn btn-primary btn-sm" href="/admin/mail-templates/?action=add">Добавить уведомление</a>

<hr>
<?php if(mysql_num_rows($get_items)){ ?>
<table class="table">
<thead>
<tr>
<th>
Ключ шаблона
</th>
<th>
Заголовок уведомления
</th>
<th></th>
</tr>
</thead>
<?php while($item=mysql_fetch_assoc($get_items)){ ?>
<tr>
<td>
<?php echo $item['code']; ?>
</td>
<td>
<?php echo htmlspecialchars($item['title_'.$config['lang']]); ?>
</td>
<td align="right">
<a title="Редактировать" class="btn btn-primary btn-xs" href="/admin/mail-templates/?action=edit&code=<?php echo $item['code']; ?>"><i class="fa fa-pencil"></i></a>
<button title="Удалить" class="btn btn-danger btn-xs" onclick="if(confirm('Вы уверены?')){ $.post('/ajax<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>', 'code=<?php echo $item['code']; ?>&action=delete', function(){ location.reload(); }); } return false;"><i class="fa fa-remove"></i></button>
</td>
</tr>
<?php } ?>
</table>
<?php } else { ?>
Здесь пока ничего нет.
<?php } ?>

<?php } ?>

<?php include "includes/footer.php"; ?>