<?php include "init.php"; ?>
<?php
if(trim($_GET['token'])!=$my['logout_token']){
header("HTTP/1.0 404 Not Found");
include "404.php";
exit;
}
unset($_SESSION['userid']);
unset($_SESSION['social_temp_userid']);
header("Location: ".$langPrefix."/");
exit;
?>