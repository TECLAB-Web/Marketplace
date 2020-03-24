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
if(trim($_GET['mode'])=='ajax'){
header('Content-Type: application/json; charset=utf-8');
if(trim($_POST['action'])=='add' || trim($_POST['action'])=='edit'){
$result=array();
$result['errors']=array();
foreach($config['langs'] as $l){
if(trim($_POST['title_'.$l])==''){
$result['errors']['title_'.$l]='Заполните это поле.';
}
}
if(count($result['errors'])==0){
$flds=array();
foreach($config['langs'] as $l){
$flds[]="`title_".$l."`='"._F($_POST['title_'.$l])."'";
}
if(trim($_POST['action'])=='add'){
$flds[]="`sort`='99999'";
}
if(trim($_POST['action'])=='add'){
mysql_query("INSERT INTO `question_types` SET `active`='1', ".implode(', ', $flds).";");
}
if(trim($_POST['action'])=='edit'){
mysql_query("UPDATE `question_types` SET ".implode(', ', $flds)." WHERE `qtid`='".intval($_POST['qtid'])."';");
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
$check=mysql_query("SELECT * FROM `question_types` WHERE `qtid`='".intval($key)."';");
if(mysql_num_rows($check)){
mysql_query("UPDATE `question_types` SET `sort`='".$order."' WHERE `qtid`='".intval($key)."';");
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
mysql_query("UPDATE `question_types` SET `active`='2' WHERE `qtid`='".intval($_POST['qtid'])."';");
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
mysql_query("UPDATE `question_types` SET `active`='1' WHERE `qtid`='".intval($_POST['qtid'])."';");
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
mysql_query("UPDATE `question_types` SET `active`='0' WHERE `qtid`='".intval($_POST['qtid'])."';");
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
$get_items=mysql_query("SELECT * FROM `question_types` WHERE `active`='0' OR `active`='1' ORDER BY `sort` ASC;");
?>
<?php
$pagetitle='Настройка тем обращений в службу поддержки';
$pagedesc=$config['description'];
?>
<?php include "includes/header.php"; ?>

<h4 class="special-title"><a href="/admin/">Администрирование</a><i class="fa fa-chevron-right"></i>Настройка тем обращений в службу поддержки</h4>

<hr>

<button class="btn btn-primary btn-sm" onclick="$.fancybox({'type':'inline', 'href':'#addWindow', 'closeBtn':false, helpers:{overlay:{locked:false}}}); return false;">Добавить</button>

<hr>

<table class="table">
<thead>
<tr>
<th></th>
<th>
#
</th>
<th>
Название темы обращения (<?php echo mb_strtoupper($config['lang']); ?>)
</th>
<th></th>
</tr>
</thead>
<tbody id="ctList">
<?php while($item=mysql_fetch_assoc($get_items)){ ?>
<tr id="ct_<?php echo $item['qtid']; ?>">
<td width="40">
<i class="fa fa-bars" style="color:#666;cursor:move;" title="Тащите для изменения порядка элементов"></i>
</td>
<td>
<?php echo $item['qtid']; ?>
</td>
<td style="<?php if($item['active']=='0'){ ?>text-decoration:line-through;<?php } ?>">
<?php echo htmlspecialchars($item['title_'.$config['lang']]); ?>
</td>
<td align="right">
<button title="Редактировать" class="btn btn-primary btn-xs" onclick="$.fancybox({'type':'inline', 'href':'#editWindow_<?php echo $item['qtid']; ?>', 'closeBtn':false, helpers:{overlay:{locked:false}}}); return false;"><i class="fa fa-pencil"></i></button>
<?php if($item['active']=='0'){ ?>
<button title="Активировать" class="btn btn-success btn-xs" onclick="$.post('/ajax<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>', 'qtid=<?php echo $item['qtid']; ?>&action=activate', function(){ location.reload(); }); return false;"><i class="fa fa-check"></i></button>
<?php } ?>
<?php if($item['active']=='1'){ ?>
<button title="Отключить" class="btn btn-warning btn-xs" onclick="$.post('/ajax<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>', 'qtid=<?php echo $item['qtid']; ?>&action=deactivate', function(){ location.reload(); }); return false;"><i class="fa fa-ban"></i></button>
<?php } ?>
<button title="Удалить" class="btn btn-danger btn-xs" onclick="if(confirm('Вы уверены?')){ $.post('/ajax<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>', 'qtid=<?php echo $item['qtid']; ?>&action=delete', function(){ location.reload(); }); } return false;"><i class="fa fa-remove"></i></button>
</td>
</tr>
<?php } ?>
</table>

<script type="text/javascript">
$(function(){
var list=document.getElementById("ctList");
Sortable.create(list, {
handle:'i.fa-bars',
draggable:'tr',
onEnd:function(e){
var qtids=[];
$('#ctList tr').each(function(){
var service_id=($(this).attr('id')).replace('ct_', '');
qtids.push(service_id);
});
var keys=qtids.join(',');
$.post(langPrefix+'/ajax/admin/question-types/?keys='+keys, 'action=sort');
}
});
});
</script>

<?php mysql_data_seek($get_items, 0); ?>
<?php while($item=mysql_fetch_assoc($get_items)){ ?>
<div id="editWindow_<?php echo $item['qtid']; ?>" style="display:none;">
<div class="window-title">Редактирование<a href="javascript:void(0);" onclick="$.fancybox.close();"></a></div>
<div class="window-message" style="width:400px;">
<form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="POST" autocomplete="off" class="form ajax-form ajax-form-no-scrolling" id="editForm_<?php echo $item['qtid']; ?>" data-callback="adminCallBack">
<input type="hidden" name="qtid" value="<?php echo $item['qtid']; ?>">
<input type="hidden" name="action" value="edit">
<?php foreach($config['langs'] as $l){ ?>
<div class="form-group">
<label>Название (<?php echo mb_strtoupper($l); ?>)</label>
<div>
<input type="text" autocomplete="off" class="form-control" name="title_<?php echo $l; ?>" value="<?php echo htmlspecialchars($item['title_'.$l]); ?>">
</div>
</div>
<?php } ?>
</form>
</div>
<div class="window-buttons">
<button class="btn btn-primary" onclick="$('#editForm_<?php echo $item['qtid']; ?>').submit();">Сохранить</button>
</div>
</div>
<?php } ?>

<div id="addWindow" style="display:none;">
<div class="window-title">Добавление<a href="javascript:void(0);" onclick="$.fancybox.close();"></a></div>
<div class="window-message" style="width:400px;">
<form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="POST" autocomplete="off" class="form ajax-form ajax-form-no-scrolling" id="editForm" data-callback="adminCallBack">
<input type="hidden" name="action" value="add">
<?php foreach($config['langs'] as $l){ ?>
<div class="form-group">
<label>Название (<?php echo mb_strtoupper($l); ?>)</label>
<div>
<input type="text" autocomplete="off" class="form-control" name="title_<?php echo $l; ?>" value="">
</div>
</div>
<?php } ?>
</form>
</div>
<div class="window-buttons">
<button class="btn btn-primary" onclick="$('#editForm').submit();">Добавить</button>
</div>
</div>

<?php include "includes/footer.php"; ?>