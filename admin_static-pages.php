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
$check_item=mysql_query("SELECT * FROM `static_pages` WHERE `pid`='".intval($_GET['pid'])."';");
if(!mysql_num_rows($check_item)){
header("Location: /admin/static-pages/");
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
if(trim($_POST['url'])==''){
$result['errors']['url']='Укажите URL страницы.';
} elseif((trim($_POST['action'])=='add' || (trim($_POST['action'])=='edit' && trim($_POST['url'])!=trim($item['url']))) && mysql_num_rows(mysql_query("SELECT * FROM `static_pages` WHERE `url`='"._F($_POST['url'])."';"))){
$result['errors']['url']='Страница с данным URL уже существует.';
}
foreach($config['langs'] as $l){
if(trim($_POST['title_'.$l])==''){
$result['errors']['title_'.$l]='Заполните это поле.';
}
if(trim($_POST['text_'.$l])==''){
$result['errors']['text_'.$l]='Заполните это поле.';
}
}
if(count($result['errors'])==0){
$flds=array();
$flds[]="`url`='"._F($_POST['url'])."'";
foreach($config['langs'] as $l){
$flds[]="`title_".$l."`='"._F($_POST['title_'.$l])."'";
$flds[]="`text_".$l."`='"._F($_POST['text_'.$l])."'";
}
if(trim($_POST['action'])=='add'){
mysql_query("INSERT INTO `static_pages` SET ".implode(', ', $flds).";");
}
if(trim($_POST['action'])=='edit'){
mysql_query("UPDATE `static_pages` SET ".implode(', ', $flds)." WHERE `pid`='".intval($_POST['pid'])."';");
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
mysql_query("DELETE FROM `static_pages` WHERE `pid`='".intval($_POST['pid'])."';");
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
$get_items=mysql_query("SELECT * FROM `static_pages` ORDER BY `title_".$config['lang']."` ASC;");
?>
<?php
$pagetitle='Статические страницы';
$pagedesc=$config['description'];
?>
<?php include "includes/header.php"; ?>
<script src="/js/ckeditor/ckeditor.js"></script>

<h4 class="special-title"><a href="/admin/">Администрирование</a><i class="fa fa-chevron-right"></i><?php if(trim($_GET['action'])!=''){ ?><a href="/admin/static-pages/">Статические страницы</a><?php } else { ?>Статические страницы<?php } ?><?php if(trim($_GET['action'])=='add'){ ?><i class="fa fa-chevron-right"></i>Добавление<?php } ?><?php if(trim($_GET['action'])=='edit'){ ?><i class="fa fa-chevron-right"></i>Редактирование<?php } ?></h4>

<hr>

<?php if(trim($_GET['action'])=='add' || trim($_GET['action'])=='edit'){ ?>

<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="POST" autocomplete="off" class="form-horizontal add-form ajax-form" data-callback="adminStaticPagesCallBack">
<?php if(trim($_GET['action'])=='edit'){ ?>
<input type="hidden" name="pid" value="<?php echo $item['pid']; ?>">
<?php } ?>
<input type="hidden" name="action" value="<?php echo htmlspecialchars(trim($_GET['action'])); ?>">
<?php foreach($config['langs'] as $l){ ?>
<div class="form-group">
<label class="col-sm-2 control-label">Заголовок (<?php echo mb_strtoupper($l); ?>) <span class="input-required">*</span></label>
<div class="col-sm-10">
<input type="text" autocomplete="off" class="form-control" name="title_<?php echo $l; ?>" value="<?php echo htmlspecialchars($item['title_'.$l]); ?>">
</div>
</div>
<?php } ?>
<?php if(count($config['langs'])>1){ ?><hr><?php } ?>
<?php foreach($config['langs'] as $l){ ?>
<div class="form-group">
<label class="col-sm-2 control-label">Содержимое (<?php echo mb_strtoupper($l); ?>) <span class="input-required">*</span></label>
<div class="col-sm-10">
<textarea name="text_<?php echo $l; ?>" id="text_<?php echo $l; ?>" class="form-control" rows="3"><?php echo htmlspecialchars($item['text_'.$l]); ?></textarea>
</div>
</div>
<script type="text/javascript">
CKEDITOR.replace('text_<?php echo $l; ?>' ,{
	filebrowserBrowseUrl : '/filemanager/dialog.php?type=1&editor=ckeditor&fldr=',
	filebrowserUploadUrl : '/filemanager/dialog.php?type=1&editor=ckeditor&fldr=',
	filebrowserImageBrowseUrl : '/filemanager/dialog.php?type=1&editor=ckeditor&fldr='
});
CKEDITOR.instances['text_<?php echo $l; ?>'].on('change', function() { CKEDITOR.instances['text_<?php echo $l; ?>'].updateElement() });
</script>
<?php } ?>
<hr>
<div class="form-group">
<label class="col-sm-2 control-label">URL <span class="input-required">*</span></label>
<div class="col-sm-6">
<input type="text" autocomplete="off" class="form-control" name="url" value="<?php echo htmlspecialchars($item['url']); ?>">
</div>
</div>
<hr>
<div class="form-group">
<div class="col-sm-2"></div>
<div class="col-sm-10">
<button type="submit" class="btn btn-primary"><?php if(trim($_GET['action'])=='edit'){ ?>Сохранить страницу<?php } else { ?>Добавить страницу<?php } ?></button>
</div>
</div>
</form>

<?php } else { ?>

<a class="btn btn-primary btn-sm" href="/admin/static-pages/?action=add">Добавить страницу</a>

<hr>
<?php if(mysql_num_rows($get_items)){ ?>
<table class="table">
<thead>
<tr>
<th>
#
</th>
<th>
Заголовок страницы (<?php echo mb_strtoupper($config['lang']); ?>)
</th>
<th></th>
</tr>
</thead>
<tbody id="static_pagesList">
<?php while($item=mysql_fetch_assoc($get_items)){ ?>
<tr id="service_<?php echo $item['pid']; ?>">
<td>
<?php echo $item['pid']; ?>
</td>
<td style="<?php if($item['active']=='0'){ ?>text-decoration:line-through;<?php } ?>">
<a href="<?php echo $langPrefix; ?>/<?php echo htmlspecialchars($item['url']); ?>/" target="_blank"><?php echo htmlspecialchars($item['title_'.$config['lang']]); ?></a>
</td>
<td align="right">
<a title="Редактировать" class="btn btn-primary btn-xs" href="/admin/static-pages/?action=edit&pid=<?php echo $item['pid']; ?>"><i class="fa fa-pencil"></i></a>
<button title="Удалить" class="btn btn-danger btn-xs" onclick="if(confirm('Вы уверены?')){ $.post('/ajax<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>', 'pid=<?php echo $item['pid']; ?>&action=delete', function(){ location.reload(); }); } return false;"><i class="fa fa-remove"></i></button>
</td>
</tr>
<?php } ?>
</tbody>
</table>

<?php } else { ?>
Здесь пока ничего нет.
<?php } ?>

<?php } ?>

<?php include "includes/footer.php"; ?>