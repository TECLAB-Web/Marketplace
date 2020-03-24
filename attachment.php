<?php
include "includes/db.php";
$check=mysql_query("SELECT * FROM `dialog_message_uploads` WHERE `dmuid`='".mysql_real_escape_string(trim($_GET['dmuid']))."' AND `order`='".mysql_real_escape_string(trim($_GET['order']))."' AND `original`='".mysql_real_escape_string(trim($_GET['original']))."';");
if(!mysql_num_rows($check)){
header("HTTP/1.0 404 Not Found");
include "404.php";
exit;
}
$attachment=mysql_fetch_assoc($check);
//var_dump($attachment); exit;
$mime=mime_content_type($attachment['file']);
if(in_array($mime, array('image/gif', 'image/png', 'image/jpeg', 'text/plain', 'application/pdf'))){
header('Content-Type: '.$mime);
readfile($attachment['file']);
exit;
} else {
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename='.$attachment['original']);
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: '.filesize($attachment['file']));
readfile($attachment['file']);
exit;
}
?>