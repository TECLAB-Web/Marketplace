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
$pagetitle='Администрирование';
$pagedesc=$config['description'];
?>
<?php include "includes/header.php"; ?>

<h4 class="special-title">Администрирование доски объявлений <?php echo $config['sitename']; ?></h4>

<hr>

<div class="panel panel-default">
<div class="panel-body" style="font-size:18px;font-weight:bold;">
<a href="/admin/categories/"><i class="fa fa-bars" style="margin-right:10px;"></i>Управление категориями</a>
</div>
</div>

<div class="panel panel-default">
<div class="panel-body" style="font-size:18px;font-weight:bold;">
<a href="/admin/parameters/"><i class="fa fa-cogs" style="margin-right:10px;"></i>Управление параметрами объявлений</a>
</div>
</div>

<div class="panel panel-default">
<div class="panel-body" style="font-size:18px;font-weight:bold;">
<a href="/admin/parameter-values/"><i class="fa fa-th-list" style="margin-right:10px;"></i>Управление значениями параметров</a>
</div>
</div>

<div class="panel panel-default">
<div class="panel-body" style="font-size:18px;font-weight:bold;">
<a href="/admin/services/"><i class="fa fa-dollar" style="margin-right:10px;"></i>Настройка платных услуг</a>
</div>
</div>

<div class="panel panel-default">
<div class="panel-body" style="font-size:18px;font-weight:bold;">
<a href="/admin/gateways/"><i class="fa fa-bank" style="margin-right:10px;"></i>Способы оплаты</a>
</div>
</div>

<div class="panel panel-default">
<div class="panel-body" style="font-size:18px;font-weight:bold;">
<a href="/admin/static-pages/"><i class="fa fa-pencil" style="margin-right:10px;"></i>Статические страницы</a>
</div>
</div>

<div class="panel panel-default">
<div class="panel-body" style="font-size:18px;font-weight:bold;">
<a href="/admin/mail-templates/"><i class="fa fa-envelope-square" style="margin-right:10px;"></i>Настройка EMail-уведомлений</a>
</div>
</div>

<div class="panel panel-default">
<div class="panel-body" style="font-size:18px;font-weight:bold;">
<a href="/admin/banners/"><i class="fa fa-bullhorn" style="margin-right:10px;"></i>Настройка баннерных мест</a>
</div>
</div>

<div class="panel panel-default">
<div class="panel-body" style="font-size:18px;font-weight:bold;">
<a href="/admin/complaint-types/"><i class="fa fa-exclamation-triangle" style="margin-right:10px;"></i>Настройка типов жалоб на объявления</a>
</div>
</div>

<div class="panel panel-default">
<div class="panel-body" style="font-size:18px;font-weight:bold;">
<a href="/admin/question-types/"><i class="fa fa-question-circle" style="margin-right:10px;"></i>Настройка тем обращений в службу поддержки</a>
</div>
</div>

<?php include "includes/footer.php"; ?>