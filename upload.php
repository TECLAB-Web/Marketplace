<?php include "init.php"; ?>
<?php
//ini_set('display_errors', '1');
if(trim($_GET['mode'])=='ajax'){
header('Content-Type: application/json; charset=utf-8');
if(trim($_POST['action'])=='sort'){
$keys=explode(',', trim($_GET['keys']));
if(count($keys)>0){
$result=array();
$order=1;
foreach($keys as $key){
$check=mysql_query("SELECT * FROM `ad_photos` WHERE `apid`='"._F($_GET['apid'])."' AND `key`='"._F($key)."';");
if(mysql_num_rows($check)){
$photo=mysql_fetch_assoc($check);
mysql_query("UPDATE `ad_photos` SET `order`='".$order."' WHERE `apid`='"._F($_GET['apid'])."' AND `key`='"._F($key)."';");
$order++;
}
}
$result['status']='success';
echo json_encode($result);
}
} elseif(trim($_POST['action'])=='delete'){
$check=mysql_query("SELECT * FROM `ad_photos` WHERE `apid`='"._F($_GET['apid'])."' AND `key`='"._F($_GET['key'])."';");
if(mysql_num_rows($check)){
$result=array();
$photo=mysql_fetch_assoc($check);
unlink($photo['file']);
mysql_query("DELETE FROM `ad_photos` WHERE `apid`='"._F($_GET['apid'])."' AND `key`='"._F($_GET['key'])."';");
removeThumbnail($photo['file'], 650, 450, 1);
removeThumbnail($photo['file'], 261, 203, 1);
removeThumbnail($photo['file'], 94, 72, 0);
removeThumbnail($photo['file'], 92, 72, 0);
$result['status']='success';
echo json_encode($result);
}
} elseif(trim($_POST['action'])=='rotate'){
$check=mysql_query("SELECT * FROM `ad_photos` WHERE `apid`='"._F($_GET['apid'])."' AND `key`='"._F($_GET['key'])."';");
if(mysql_num_rows($check)){
$result=array();
$photo=mysql_fetch_assoc($check);
$source=imagecreatefromjpeg($photo['file']);
$rotate=imagerotate($source, -90, 0);
imagejpeg($rotate, $photo['file']);
mysql_query("UPDATE `ad_photos` SET `rev`='".$time."' WHERE `apid`='"._F($_GET['apid'])."' AND `key`='"._F($_GET['key'])."';");
generateThumbnail(trim($_GET['apid']), trim($_GET['key']), 650, 450, 1);
generateThumbnail(trim($_GET['apid']), trim($_GET['key']), 261, 203, 1);
generateThumbnail(trim($_GET['apid']), trim($_GET['key']), 94, 72, 0);
generateThumbnail(trim($_GET['apid']), trim($_GET['key']), 92, 72, 0);
$result['file']='/image/92x72/'.trim($_GET['apid']).'/'.trim($_GET['key']).'.jpg?rev='.$time;
$result['status']='success';
echo json_encode($result);
}
} else {
if(is_uploaded_file($_FILES['file']['tmp_name'])){
$ext=end((explode(".", $_FILES['file']['name'])));
$result=array();
$result['errors']=array();
$result['original']=$_FILES['file']['name'];
if($_FILES['file']['size']<1024){
$result['errors']['file']=l('upload_errors_too_small');
} elseif($_FILES['file']['size']>1024*1024*5){
$result['errors']['file']=l('upload_errors_too_big');
} elseif(!in_array(strtolower($ext), array('jpeg', 'jpg', 'png', 'gif'))){
$result['errors']['file']=l('upload_errors_bad_extension');
}
if(count($result['errors'])==0){
$key=uniqid('');
mkdir('uploads/'.date("Y").'/', 0777);
mkdir('uploads/'.date("Y").'/'.date("m").'/', 0777);
mkdir('uploads/'.date("Y").'/'.date("m").'/'.date("d").'/', 0777);
$filename='uploads/'.date("Y").'/'.date("m").'/'.date("d").'/'.$key.'.jpg';
if(move_uploaded_file($_FILES['file']['tmp_name'], $filename)){
tojpg($filename);
$max_order=mysql_result(mysql_query("SELECT MAX(`order`) FROM `ad_photos` WHERE `apid`='"._F($_GET['apid'])."';"), 0, 0);
mysql_query("INSERT INTO `ad_photos` SET `apid`='"._F($_GET['apid'])."', `key`='"._F($key)."', `original`='"._F($_FILES['file']['name'])."', `file`='"._F($filename)."', `rev`='".$time."', `order`='".(intval($max_order)+1)."';");
generateThumbnail(trim($_GET['apid']), $key, 650, 450, 1);
generateThumbnail(trim($_GET['apid']), $key, 261, 203, 1);
generateThumbnail(trim($_GET['apid']), $key, 94, 72, 0);
generateThumbnail(trim($_GET['apid']), $key, 92, 72, 0);
$result['key']=$key;
$result['file']='/image/92x72/'.trim($_GET['apid']).'/'.$key.'.jpg?rev='.$time;
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