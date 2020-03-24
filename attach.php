<?php include "init.php"; ?>
<?php
if(trim($_GET['mode'])=='ajax'){
header('Content-Type: application/json; charset=utf-8');
if(trim($_POST['action'])=='delete'){
$check=mysql_query("SELECT * FROM `dialog_message_uploads` WHERE `dmuid`='"._F($_GET['dmuid'])."' AND `key`='"._F($_GET['key'])."';");
if(mysql_num_rows($check)){
$result=array();
$attachment=mysql_fetch_assoc($check);
unlink($attachment['file']);
mysql_query("DELETE FROM `dialog_message_uploads` WHERE `dmuid`='"._F($_GET['dmuid'])."' AND `key`='"._F($_GET['key'])."';");
$result['status']='success';
echo json_encode($result);
}
} else {
if(is_uploaded_file($_FILES['file']['tmp_name'])){
$ext=end((explode(".", $_FILES['file']['name'])));
$result=array();
$result['errors']=array();
$result['original']=$_FILES['file']['name'];
if($_FILES['file']['size']>1024*1024*5){
$result['errors']['file']=l('attach_errors_too_big');
} elseif(!in_array($ext, $extensions)){
$result['errors']['file']=l('attach_errors_bad_extension');
}
if(count($result['errors'])==0){
$key=uniqid('');
mkdir('uploads/'.date("Y").'/', 0777);
mkdir('uploads/'.date("Y").'/'.date("m").'/', 0777);
mkdir('uploads/'.date("Y").'/'.date("m").'/'.date("d").'/', 0777);
$filename='uploads/'.date("Y").'/'.date("m").'/'.date("d").'/'.$key.'.attachment.txt';
if(move_uploaded_file($_FILES['file']['tmp_name'], $filename)){
$max_order=mysql_result(mysql_query("SELECT MAX(`order`) FROM `dialog_message_uploads` WHERE `dmuid`='"._F($_GET['dmuid'])."';"), 0, 0);
mysql_query("INSERT INTO `dialog_message_uploads` SET `dmuid`='"._F($_GET['dmuid'])."', `key`='"._F($key)."', `original`='"._F($_FILES['file']['name'])."', `file`='"._F($filename)."', `order`='".(intval($max_order)+1)."';");
$result['key']=$key;
$result['size']=human_filesize($_FILES['file']['size']);
$result['type']=$ext;
$result['status']='success';
} else {
$result['errors']['file']=l('attach_errors_unable');
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