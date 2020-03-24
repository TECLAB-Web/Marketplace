<?php include_once "init.php"; ?>
<?php
if(!isset($_SESSION['userid'])){
if(trim($_GET['mode'])=='ajax' && trim($_GET['controller'])=='message' && trim($_POST['action'])=='new'){
echo '';	
} else {
header("Location: ".$langPrefix."/login/?ref=".urlencode($langPrefix.$_SERVER['REQUEST_URI']));
}
exit;
}
?>
<?php
$selected_tab=trim($_GET['controller']);
if(trim($_GET['controller'])=='message'){
$check_dialog_member=mysql_query("SELECT * FROM `dialog_members` WHERE `did`='".intval($_GET['mid'])."' AND `userid`='".$_SESSION['userid']."';");
if(!mysql_num_rows($check_dialog_member)){
header("HTTP/1.0 404 Not Found");
include "404.php";
exit;
}
$membering=mysql_fetch_assoc($check_dialog_member);
if($membering['active']==3){
header("HTTP/1.0 404 Not Found");
include "404.php";
exit;
}
$dialog=mysql_fetch_assoc(mysql_query("SELECT * FROM `dialogs` WHERE `did`='".$membering['did']."';"));
$correspondent=mysql_fetch_assoc(mysql_query("SELECT `users`.*, `dialog_members`.`active` as `c_status` FROM `dialog_members`, `users` WHERE `dialog_members`.`did`='".$membering['did']."' AND `dialog_members`.`userid`!='".$_SESSION['userid']."' AND `users`.`userid`=`dialog_members`.`userid`;"));
if($correspondent['person']==''){
$correspondent['person']=reset(explode('@', $correspondent['email']));
}
$ad=getAd($dialog['aid']);
$get_messages=mysql_query("SELECT * FROM `dialog_messages` WHERE `did`='".$membering['did']."' ORDER BY `dmid` ASC;");
mysql_query("DELETE FROM `dialog_unread_messages` WHERE `did`='".$membering['did']."' AND `userid`='".$_SESSION['userid']."';");
$my['unread']=mysql_num_rows(mysql_query("SELECT DISTINCT `dialogs`.* FROM `dialogs`, `dialog_unread_messages` WHERE `dialogs`.`did`=`dialog_unread_messages`.`did` AND `dialog_unread_messages`.`userid`='".$_SESSION['userid']."';"));
if($membering['active']==2){
$selected_tab='archive';
} else {
$initiator=mysql_result(mysql_query("SELECT `userid` FROM `dialog_messages` WHERE `did`='".$membering['did']."' ORDER BY `dmid` ASC LIMIT 1;"), 0, 0);
if($_SESSION['userid']==$initiator){
$selected_tab='sent';
} else {
$selected_tab='messages';
}
}
if(trim($_GET['ref'])==''){
$_GET['ref']='/my/messages/'.(($selected_tab=='messages')?'':$selected_tab.'/');
}
}
?>
<?php
if(trim($_GET['mode'])=='ajax'){
if(trim($_GET['controller'])=='message'){
if(trim($_POST['action'])=='new'){
$get_new_messages=mysql_query("SELECT * FROM `dialog_messages` WHERE `did`='".$membering['did']."' AND `dmid`>'".intval($_POST['dmid'])."' ORDER BY `dmid` ASC;");
while($message=mysql_fetch_assoc($get_new_messages)){
?>
<?php
$last_dmid=$message['dmid'];
$get_attachments=mysql_query("SELECT * FROM `dialog_message_uploads` WHERE `dmuid`='"._F($message['dmuid'])."' ORDER BY `order` ASC;");
?>
<div id="message_<?php echo $message['dmid']; ?>" class="dialog-message-container<?php if($message['userid']==$_SESSION['userid']){ ?> my<?php } ?>">
<div class="dialog-message-author"><?php if($message['userid']==$_SESSION['userid']){ echo l('my_messages_message_you'); } else { echo nl2br(htmlspecialchars($correspondent['person'])); } ?>:</div>
<div class="dialog-message-time"><?php displayTime($message['time']); ?></div>
<div class="clear"></div>
<div class="dialog-message-text">
<?php echo nl2br(htmlspecialchars($message['text'])); ?>
<?php if(mysql_num_rows($get_attachments)){ ?>
<div class="dialog-message-attachments">
<?php while($attachment=mysql_fetch_assoc($get_attachments)){ ?>
<?php
$mime=mime_content_type($attachment['file']);
?>
<div class="added-attachment">
<div class="file-icon" data-type="<?php echo end(explode('.', $attachment['original'])); ?>"></div>
<div class="attachment-title">
<b><?php echo $attachment['original']; ?></b>, <?php echo human_filesize(filesize($attachment['file'])); ?>
<br>
<?php if(in_array($mime, array('image/gif', 'image/png', 'image/jpeg'))){ ?>
<div style="background:white;padding:3px;display:inline-block;margin-top:3px;">
<a href="/attachment/<?php echo $attachment['dmuid']; ?>/<?php echo $attachment['order']; ?>/<?php echo $attachment['original']; ?>" target="_blank">
<img src="/attachment/<?php echo $attachment['dmuid']; ?>/<?php echo $attachment['order']; ?>/<?php echo $attachment['original']; ?>" style="max-width:140px;">
</a>
</div>
<?php } else { ?>
<a href="<?php echo $langPrefix; ?>/attachment/<?php echo $attachment['dmuid']; ?>/<?php echo $attachment['order']; ?>/<?php echo $attachment['original']; ?>" target="_blank"><?php echo l('my_messages_message_go_to_file'); ?></a>
<?php } ?>
</div>
<div class="clear"></div>
</div>
<?php } ?>
</div>
<?php } ?>
</div>
</div>
<div class="clear"></div>
<?php
}
if(mysql_num_rows($get_new_messages)){
?>
<script>
last_dmid=<?php echo $last_dmid; ?>;
</script>
<?php
}
exit;
}
}
header('Content-Type: application/json; charset=utf-8');
if(trim($_POST['action'])=='star'){
$result=array();
$result['errors']=array();
mysql_query("UPDATE `dialog_members` SET `starred`='1' WHERE `userid`='".$_SESSION['userid']."' AND `did`='".intval($_POST['id'])."';");
if(count($result['errors'])==0){
$result['status']='success';
} else {
$result['status']='error';
}
echo json_encode($result);
}
if(trim($_POST['action'])=='unstar'){
$result=array();
$result['errors']=array();
mysql_query("UPDATE `dialog_members` SET `starred`='0' WHERE `userid`='".$_SESSION['userid']."' AND `did`='".intval($_POST['id'])."';");
if(count($result['errors'])==0){
$result['status']='success';
} else {
$result['status']='error';
}
echo json_encode($result);
}
if(trim($_POST['action'])=='archive'){
$result=array();
$result['errors']=array();
mysql_query("UPDATE `dialog_members` SET `active`='2' WHERE `userid`='".$_SESSION['userid']."' AND `did`='".intval($_POST['id'])."';");
mysql_query("DELETE FROM `dialog_unread_messages` WHERE `did`='".intval($_POST['id'])."' AND `userid`='".$_SESSION['userid']."';");
if(count($result['errors'])==0){
$result['message']=l('my_messages_successfully_archived');
$result['status']='success';
} else {
$result['status']='error';
}
echo json_encode($result);
}
if(trim($_POST['action'])=='restore'){
$result=array();
$result['errors']=array();
mysql_query("UPDATE `dialog_members` SET `active`='1' WHERE `userid`='".$_SESSION['userid']."' AND `did`='".intval($_POST['id'])."';");
if(count($result['errors'])==0){
$result['message']=l('my_messages_successfully_unarchived');
$result['status']='success';
} else {
$result['status']='error';
}
echo json_encode($result);
}
if(trim($_POST['action'])=='remove'){
$result=array();
$result['errors']=array();
mysql_query("UPDATE `dialog_members` SET `active`='3' WHERE `userid`='".$_SESSION['userid']."' AND `did`='".intval($_POST['id'])."';");
if(count($result['errors'])==0){
$result['message']=l('my_messages_successfully_removed');
$result['status']='success';
} else {
$result['status']='error';
}
echo json_encode($result);
}
if(trim($_GET['controller'])=='message'){
if(trim($_POST['action'])=='reply'){
$result=array();
$result['errors']=array();
if($membering['active']==2){
$result['errors']['form']=l('my_messages_reply_error_archived_by_you');
} elseif($correspondent['c_status']==2){
$result['errors']['form']=l('my_messages_reply_error_archived_by_correspondent');
} elseif($correspondent['c_status']==3){
$result['errors']['form']=l('my_messages_reply_error_removed_by_correspondent');
} elseif(trim($_POST['text'])==''){
$result['errors']['text']=l('my_messages_reply_error_empty');
} elseif(mb_strlen(trim($_POST['text']))>4096){
$result['errors']['text']=l('my_messages_reply_error_long');
}
if(count($result['errors'])==0){
$index=Words2AllForms(trim($_POST['text']));
mysql_query("INSERT INTO `dialog_messages` SET `did`='".$membering['did']."', `userid`='".$_SESSION['userid']."', `text`='"._F($_POST['text'])."', `index`='"._F($index)."', `dmuid`='"._F($_POST['dmuid'])."', `time`='".$time."';");
$dmid=mysql_insert_id();
mysql_query("INSERT INTO `dialog_unread_messages` SET `did`='".$membering['did']."', `userid`='".$correspondent['userid']."', `dmid`='".$dmid."';");
$mail=mysql_fetch_assoc(mysql_query("SELECT * FROM `mail_templates` WHERE `code`='message';"));
$to=trim($correspondent['email']);
$mail['title']=$mail['title_'.$correspondent['lang']];
$mail['body']=$mail['body_'.$correspondent['lang']];
$mail['body']=str_replace('[SITE_NAME]', $config['sitename'], $mail['body']);
$mail['body']=str_replace('[SITE_URL]', $config['siteurl'], $mail['body']);
$mail['body']=str_replace('[AD_TITLE]', htmlspecialchars($ad['title']), $mail['body']);
$mail['body']=str_replace('[DID]', $membering['did'], $mail['body']);
liam($to, $mail['title'], $mail['body'], "noreply@".$config['siteurl']);
$result['dmuid']=uniqid('');
$result['status']='success';
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
$page_tabs=array();
$page_tabs['messages']=$lang['my_messages_tab_messages'];
$page_tabs['sent']=$lang['my_messages_tab_sent'];
$page_tabs['archive']=$lang['my_messages_tab_archive'];
?>
<?php
if(trim($_GET['controller'])!='message'){
if(in_array(trim($_GET['order']), array('asc', 'desc'))){
$order=trim($_GET['order']);
} else {
$order='desc';
}
if(in_array(trim($_GET['orderField']), array('time'))){
$orderField=trim($_GET['orderField']);
} else {
$orderField='time';
}
if(trim($_GET['q'])!=''){
$sq=" AND (MATCH (`index`) AGAINST ('"._F(Words2BaseForm(trim($_GET['q'])))."' IN NATURAL LANGUAGE MODE) OR `text` LIKE '%"._F(trim($_GET['q']))."%')";
} else {
$sq='';
}
if(intval($_GET['aid'])>0){
$af=" AND `did` IN(SELECT `did` FROM `dialogs` WHERE `aid`='".intval($_GET['aid'])."')";
} else {
$af='';
}
if(intval($_GET['type'])==1){
$tf=" AND EXISTS(SELECT * FROM `dialog_unread_messages` WHERE `dialog_unread_messages`.`dmid`=`t1`.`dmid`)";
} elseif(intval($_GET['type'])==2){
$tf=" AND `t3`.`starred`='1'";
} else {
$tf='';
}
$count=array();
$count['messages']=mysql_result(mysql_query("SELECT COUNT(`t1`.`dmid`) FROM `dialog_messages` `t1` JOIN (SELECT `did`, MAX(`dmid`) `dmid` FROM `dialog_messages` WHERE `userid`!='".$_SESSION['userid']."'".$af.$sq." GROUP BY `did`) `t2` ON `t1`.`did`=`t2`.`did` AND `t1`.`dmid`=`t2`.`dmid` JOIN (SELECT * FROM `dialog_members`) `t3` ON `t1`.`did`=`t3`.`did` AND `t3`.`userid`='".$_SESSION['userid']."' AND `t3`.`active`='1' WHERE 1=1".$tf.";"), 0, 0);
$count['sent']=mysql_result(mysql_query("SELECT COUNT(`t1`.`dmid`) FROM `dialog_messages` `t1` JOIN (SELECT `did`, MAX(`dmid`) `dmid` FROM `dialog_messages` WHERE `userid`='".$_SESSION['userid']."'".$af.$sq." GROUP BY `did`) `t2` ON `t1`.`did`=`t2`.`did` AND `t1`.`dmid`=`t2`.`dmid` JOIN (SELECT * FROM `dialog_members`) `t3` ON `t1`.`did`=`t3`.`did` AND `t3`.`userid`='".$_SESSION['userid']."' AND `t3`.`active`='1' WHERE 1=1".$tf.";"), 0, 0);
$count['archive']=mysql_result(mysql_query("SELECT COUNT(`t1`.`dmid`) FROM `dialog_messages` `t1` JOIN (SELECT `did`, MAX(`dmid`) `dmid` FROM `dialog_messages` WHERE 1=1".$af.$sq." GROUP BY `did`) `t2` ON `t1`.`did`=`t2`.`did` AND `t1`.`dmid`=`t2`.`dmid` JOIN (SELECT * FROM `dialog_members`) `t3` ON `t1`.`did`=`t3`.`did` AND `t3`.`userid`='".$_SESSION['userid']."' AND `t3`.`active`='2' WHERE 1=1".$tf.";"), 0, 0);
$pages=array();
$pages['count']=$count[trim($_GET['controller'])];
$pages['per']=10;
if(intval($_GET['page'])>ceil($pages['count']/$pages['per'])){
$pages['current']=ceil($pages['count']/$pages['per']);
} else {
$pages['current']=((intval($_GET['page'])>1)?intval($_GET['page']):1);
}
$pages['show']=9;
$get_params=$_GET;
$pages['url']=reset(explode('?', $_SERVER['REQUEST_URI']));
unset($get_params['controller']);
unset($get_params['page']);
unset($get_params['aid']);
$get_params['page']='';
if(count($get_params)>0){
$pages['url'].='?'.http_build_query($get_params).'(:page)';
}
$pagingHtml=paginator::getHtml($pages['count'], $pages['current'], $pages['per'], $pages['show'], $pages['url']);
if(trim($_GET['controller'])=='messages'){
$get_messages=mysql_query("SELECT `t1`.* FROM `dialog_messages` `t1` JOIN (SELECT `did`, MAX(`dmid`) `dmid` FROM `dialog_messages` WHERE `userid`!='".$_SESSION['userid']."'".$af.$sq." GROUP BY `did`) `t2` ON `t1`.`did`=`t2`.`did` AND `t1`.`dmid`=`t2`.`dmid` JOIN (SELECT * FROM `dialog_members`) `t3` ON `t1`.`did`=`t3`.`did` AND `t3`.`userid`='".$_SESSION['userid']."' AND `t3`.`active`='1' WHERE 1=1".$tf." ORDER BY `t1`.`".$orderField."` ".$order." LIMIT ".($pages['per']*($pages['current']-1)).", ".$pages['per'].";");
}
if(trim($_GET['controller'])=='sent'){
$get_messages=mysql_query("SELECT `t1`.* FROM `dialog_messages` `t1` JOIN (SELECT `did`, MAX(`dmid`) `dmid` FROM `dialog_messages` WHERE `userid`='".$_SESSION['userid']."'".$af.$sq." GROUP BY `did`) `t2` ON `t1`.`did`=`t2`.`did` AND `t1`.`dmid`=`t2`.`dmid` JOIN (SELECT * FROM `dialog_members`) `t3` ON `t1`.`did`=`t3`.`did` AND `t3`.`userid`='".$_SESSION['userid']."' AND `t3`.`active`='1' WHERE 1=1".$tf." ORDER BY `t1`.`".$orderField."` ".$order." LIMIT ".($pages['per']*($pages['current']-1)).", ".$pages['per'].";");
}
if(trim($_GET['controller'])=='archive'){
$get_messages=mysql_query("SELECT `t1`.* FROM `dialog_messages` `t1` JOIN (SELECT `did`, MAX(`dmid`) `dmid` FROM `dialog_messages` WHERE 1=1".$af.$sq." GROUP BY `did`) `t2` ON `t1`.`did`=`t2`.`did` AND `t1`.`dmid`=`t2`.`dmid` JOIN (SELECT * FROM `dialog_members`) `t3` ON `t1`.`did`=`t3`.`did` AND `t3`.`userid`='".$_SESSION['userid']."' AND `t3`.`active`='2' WHERE 1=1".$tf." ORDER BY `t1`.`".$orderField."` ".$order." LIMIT ".($pages['per']*($pages['current']-1)).", ".$pages['per'].";");
}
$get_ads=mysql_query("SELECT `ads`.* FROM `ads`, `dialogs` WHERE `ads`.`userid`='".$_SESSION['userid']."' AND `ads`.`aid`=`dialogs`.`aid` ORDER BY `ads`.`aid` DESC LIMIT 6;");
}
?>
<?php
$dmuid=uniqid('');
$show_top_tabs=true;
$is_cabinet=true;
$current_tab='messages';
$top_tabs_title=l('my_messages_title');
$top_tabs_description=l('my_messages_description');
$pagetitle=l('my_messages_title')." &bull; ".$config['sitename'];
$pagedesc=$config['description'];
?>
<?php
if($m){ include "m-my_messages.php"; exit; }
?>
<?php include "includes/header.php"; ?>

<?php if(trim($_GET['controller'])=='message'){ ?>

<div id="dialog-messages-list">
<div id="dialog-ad-info" class="dialog-message-container<?php if($ad['userid']==$_SESSION['userid']){ ?> my<?php } ?>">
<div class="dialog-message-author"><?php if($ad['userid']==$_SESSION['userid']){ echo l('my_messages_message_you'); } else { echo nl2br(htmlspecialchars($correspondent['person'])); } ?>:</div>
<div class="dialog-message-time"><?php displayTime($ad['time']); ?></div>
<div class="clear"></div>
<div class="dialog-message-text">
<table>
<tr>
<td>
<a href="<?php echo adurl($ad); ?>" target="_blank">
<?php if(count($ad['photos'])>0){ ?>
<?php $photo=reset($ad['photos']); ?>
<img src="/image/94x72/<?php echo $photo['apid']; ?>/<?php echo $photo['key']; ?>.jpg?rev=<?php echo $photo['rev']; ?>" width="60" alt="<?php echo htmlspecialchars($ad['title']).$ad['in_city']; ?>" title="<?php echo htmlspecialchars($ad['title']).$ad['in_city']; ?>">
<?php } else { ?>
<img src="/images/no-photos.png" width="60">
<?php } ?>
</a>
</td>
<td>
<div><?php echo htmlspecialchars($ad['title']); ?></div>
<a href="<?php echo adurl($ad); ?>" target="_blank"><i class="fa fa-external-link"></i><?php echo l('my_messages_message_go_to_ad'); ?></a>
&nbsp;|&nbsp;
<?php echo l('my_messages_message_ad_id'); ?> <?php echo $ad['aid']; ?>
</td>
</tr>
</table>
</div>
</div>
<?php while($message=mysql_fetch_assoc($get_messages)){ ?>
<?php
$last_dmid=$message['dmid'];
$get_attachments=mysql_query("SELECT * FROM `dialog_message_uploads` WHERE `dmuid`='"._F($message['dmuid'])."' ORDER BY `order` ASC;");
?>
<div id="message_<?php echo $message['dmid']; ?>" class="dialog-message-container<?php if($message['userid']==$_SESSION['userid']){ ?> my<?php } ?>">
<div class="dialog-message-author"><?php if($message['userid']==$_SESSION['userid']){ echo l('my_messages_message_you'); } else { echo nl2br(htmlspecialchars($correspondent['person'])); } ?>:</div>
<div class="dialog-message-time"><?php displayTime($message['time']); ?></div>
<div class="clear"></div>
<div class="dialog-message-text">
<?php echo nl2br(htmlspecialchars($message['text'])); ?>
<?php if(mysql_num_rows($get_attachments)){ ?>
<div class="dialog-message-attachments">
<?php while($attachment=mysql_fetch_assoc($get_attachments)){ ?>
<?php
$mime=mime_content_type($attachment['file']);
?>
<div class="added-attachment">
<div class="file-icon" data-type="<?php echo end(explode('.', $attachment['original'])); ?>"></div>
<div class="attachment-title">
<b><?php echo $attachment['original']; ?></b>, <?php echo human_filesize(filesize($attachment['file'])); ?>
<br>
<?php if(in_array($mime, array('image/gif', 'image/png', 'image/jpeg'))){ ?>
<div style="background:white;padding:3px;display:inline-block;margin-top:3px;">
<a href="/attachment/<?php echo $attachment['dmuid']; ?>/<?php echo $attachment['order']; ?>/<?php echo $attachment['original']; ?>" target="_blank">
<img src="/attachment/<?php echo $attachment['dmuid']; ?>/<?php echo $attachment['order']; ?>/<?php echo $attachment['original']; ?>" style="max-width:140px;">
</a>
</div>
<?php } else { ?>
<a href="<?php echo $langPrefix; ?>/attachment/<?php echo $attachment['dmuid']; ?>/<?php echo $attachment['order']; ?>/<?php echo $attachment['original']; ?>" target="_blank"><?php echo l('my_messages_message_go_to_file'); ?></a>
<?php } ?>
</div>
<div class="clear"></div>
</div>
<?php } ?>
</div>
<?php } ?>
</div>
</div>
<div class="clear"></div>
<?php } ?>

<div id="new-ajax-messages-mark"></div>

<?php if($membering['active']==2){ ?>
<div class="dialog-reply-impossible">
<?php echo l('my_messages_reply_error_archived_by_you'); ?>
</div>
<?php } elseif($correspondent['c_status']==2){ ?>
<div class="dialog-reply-impossible">
<?php echo l('my_messages_reply_error_archived_by_correspondent'); ?>
</div>
<?php } elseif($correspondent['c_status']==3){ ?>
<div class="dialog-reply-impossible">
<?php echo l('my_messages_reply_error_removed_by_correspondent'); ?>
</div>
<?php } else { ?>
<div id="uploadErrors" style="display:none;">
<div class="window-title"><?php echo l('upload_error'); ?><a href="javascript:void(0);" onclick="$.fancybox.close();"></a></div>
<div class="window-message"></div>
<div class="window-buttons">
<button class="btn btn-primary" onclick="$.fancybox.close();">OK</button>
</div>
</div>
<input type="file" class="hidden" id="uploaderButton" name="file" data-url="<?php echo $langPrefix; ?>/ajax/attach/?dmuid=<?php echo $dmuid; ?>">
<script>
var uploadedAttachments=0;
var maxAttachments=5;
var uploadErrors=[];
$(function(){
$("#attachmentUploader button").keydown(function(e){
if(e.keyCode===13){
e.preventDefault();
}
});
$('#uploaderButton').fileupload({
dataType: 'json',
change:function (e, data){
$('.add-attachment-button').find('i.fa-spinner').removeClass('hidden');
$('.add-attachment-button').find('i.fa-cloud-upload').addClass('hidden');
$('.add-attachment-button').addClass('upload-working');
uploadErrors=[];
},
done:function (e, data){
var item=data.result;
if(item.status=='success'){
uploadedAttachments=uploadedAttachments+1;
var new_attachment=$('#attachmentUploaderTemplate').clone();
new_attachment.removeAttr('id');
new_attachment.removeClass('hidden');
new_attachment.find('.attachment-title').html('<b>'+item.original+'</b>, '+item.size);
new_attachment.find('.file-icon').attr('data-type', item.type);
new_attachment.find('.delete-attachment').click(function(){
deleteUploadedAttachment($('#dmuid').val(), item.key);
});
new_attachment.attr('id', 'attachment_'+item.key);
var oldh=$(document).outerHeight();
$('#attachmentUploader').append(new_attachment);
var newh=$(document).outerHeight();
$(window).scrollTop($(window).scrollTop()+(newh-oldh));
if(uploadedAttachments==5){
$('.add-attachment-button').addClass('hidden');
}
} else {
uploadErrors.push('<p><b>'+item.original+':</b><br>'+item.errors.file+'</p>');
}
$('.add-attachment-button').find('i.fa-spinner').addClass('hidden');
$('.add-attachment-button').find('i.fa-cloud-upload').removeClass('hidden');
$('.add-attachment-button').removeClass('upload-working');
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
<div class="dialog-reply-form">
<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="POST" autocomplete="off" class="ajax-form" data-callback="replyCallBack">
<input type="hidden" name="action" value="reply">
<input type="hidden" name="dmuid" id="dmuid" value="<?php echo $dmuid; ?>">
<div class="form-group">
<textarea name="text" class="form-control" rows="4" placeholder="<?php echo l('my_messages_reply_text'); ?>" id="post-message-text"></textarea>
</div>
<div class="form-group">
<div class="added-attachment hidden" id="attachmentUploaderTemplate"><div class="file-icon"></div><div class="attachment-title"></div><a href="javascript:void(0);" class="delete-attachment"><?php echo l('remove_attachment'); ?></a></div>
<div id="attachmentUploader"></div>
</div>
<div class="form-group" style="margin-bottom:0;">
<table width="100%">
<tr>
<td valign="top">
<div>
<div class="add-attachment-button" id="add_attachment_button" title="<?php echo l('add_attachment'); ?>">
<button type="button" onclick="$('#uploaderButton').click(); return false;"><i class="fa fa-cloud-upload"></i><i class="fa fa-spinner fa-spin hidden"></i></button>
</div>
</div>
</td>
<td valign="top" width="40%" align="right">
<button type="submit" class="btn btn-success" style="margin:0;"><?php echo l('my_messages_reply_submit'); ?></button>
</td>
</tr>
</table>
</div>
</form>
</div>
<?php } ?>
<div class="clear"></div>

</div>

<script>
var last_dmid=<?php echo $last_dmid; ?>;
$(function(){
var checkNewMessages=setInterval(function(){
getNewMessages('<?php echo $langPrefix; ?>/ajax<?php echo $_SERVER['REQUEST_URI']; ?>');
}, 1500);
});
</script>

<?php } else { ?>

<script>
var myItemsCount=<?php echo $pages['count']; ?>;
</script>

<?php if(intval($_GET['aid'])>0 || intval($_GET['type'])>0 || mysql_num_rows($get_messages)){ ?>
<div class="my_items_title">
<?php
if(trim($_GET['q'])==''){
echo $page_tabs[trim($_GET['controller'])]['title'];
} else {
echo str_replace('[QUERY]', htmlspecialchars(trim($_GET['q'])), $page_tabs[trim($_GET['controller'])]['search']);
}
?>
<?php if(trim($_GET['controller'])!='archive'){ ?>
<table class="my_items_filter" style="<?php if(trim($_GET['controller'])!='messages' || (trim($_GET['controller'])=='messages' && !mysql_num_rows($get_ads))){ ?>margin-top:-28px;<?php } ?>">
<tr>
<td width="50%">
<?php if(trim($_GET['controller'])=='messages' && mysql_num_rows($get_ads)){ ?>
<?php
$get_params=$_GET;
$by_ad_params='';
unset($get_params['controller']);
unset($get_params['page']);
unset($get_params['aid']);
if(count($get_params)>0){
$by_ad_params.='?'.http_build_query($get_params);
}
?>
<?php echo l('my_messages_by_ad'); ?>
<select class="form-control input-sm" onchange="location.href=this.value;">
<option value="<?php echo $langPrefix; ?>/my/messages/<?php echo $by_ad_params; ?>"<?php if(intval($_GET['aid'])==0){ ?> selected<?php } ?>><?php echo l('my_messages_by_ad_select'); ?></option>
<?php while($ad=mysql_fetch_assoc($get_ads)){ ?>
<option value="<?php echo $langPrefix; ?>/my/messages/<?php echo $ad['aid']; ?><?php echo $by_ad_params; ?>"<?php if(intval($_GET['aid'])==$ad['aid']){ ?> selected<?php } ?>><?php echo htmlspecialchars(cutString($ad['title'], 32, '...')); ?></option>
<?php } ?>
</select>
<?php } ?>
</td>
<td align="right" width="50%">
<?php
$get_params=$_GET;
$by_type_params='';
unset($get_params['controller']);
unset($get_params['page']);
unset($get_params['aid']);
unset($get_params['type']);
if(count($get_params)>0){
$by_type_params.=http_build_query($get_params);
}
?>
<?php echo l('my_messages_sort'); ?>
<select class="form-control input-sm" onchange="location.href=this.value;">
<option value="<?php echo $langPrefix.reset(explode('?', $_SERVER['REQUEST_URI'])); ?><?php if($by_type_params!=''){ echo '?'.$by_type_params; } ?>"<?php if(intval($_GET['type'])==0){ ?> selected<?php } ?>><?php echo l('my_messages_sort_all'); ?></option>
<?php if(trim($_GET['controller'])=='messages'){ ?>
<option value="<?php echo $langPrefix.reset(explode('?', $_SERVER['REQUEST_URI'])); ?>?type=1<?php if($by_type_params!=''){ echo '&'.$by_type_params; } ?>"<?php if(intval($_GET['type'])==1){ ?> selected<?php } ?>><?php echo l('my_messages_sort_unread'); ?></option>
<?php } ?>
<option value="<?php echo $langPrefix.reset(explode('?', $_SERVER['REQUEST_URI'])); ?>?type=2<?php if($by_type_params!=''){ echo '&'.$by_type_params; } ?>"<?php if(intval($_GET['type'])==2){ ?> selected<?php } ?>><?php echo l('my_messages_sort_starred'); ?></option>
</select>
</td>
</tr>
</table>
<?php } ?>
</div>
<?php } ?>

<div class="empty-list<?php if(mysql_num_rows($get_messages)){ ?> hidden<?php } ?>">
<div>
<i class="fa fa-envelope-o"></i>
</div>
<?php if(trim($_GET['q'])==''){ ?>
<?php echo l('my_messages_empty'); ?>
<?php } else { ?>
<?php echo str_replace('[QUERY]', htmlspecialchars(trim($_GET['q'])), l('my_messages_search_empty')); ?>
<?php } ?>
</div>

<?php if(mysql_num_rows($get_messages)){ ?>
<table class="my_items_list">
<tr class="my_items_list_head">
<td>
<div class="checkbox" style="margin-top:0;">
<input type="checkbox" id="select_all">
<label for="select_all"></label>
</div>
</td>
<td>
<div class="my_items_actions_container hidden">
<div class="my_items_actions">
<?php if(trim($_GET['controller'])=='archive'){ ?>
<a href="javascript:void(0);" class="global-action restore"><?php echo l('my_messages_restore_from_archive'); ?></a><span class="separator"></span><a href="javascript:void(0);" class="global-action remove"><?php echo l('my_messages_remove_forever'); ?></a>
<?php } else { ?>
<a href="javascript:void(0);" class="global-action archive"><?php echo l('my_messages_add_to_archive'); ?></a><span id="global-star"><span class="separator"></span><a href="javascript:void(0);" class="global-action star"><?php echo l('my_messages_add_to_favorites'); ?></a></span><span id="global-unstar"><span class="separator"></span><a href="javascript:void(0);" class="global-action unstar"><?php echo l('my_messages_remove_from_favorites'); ?></a></span>
<?php } ?>
</div>
</div>
</td>
<td>
<?php echo l('my_messages_item_from'); ?>
</td>
<td>
<?php echo l('my_messages_item_title'); ?>
</td>
<td align="right">
<?php
$get_params=$_GET;
$order_link=$langPrefix.reset(explode('?', $_SERVER['REQUEST_URI']));
unset($get_params['controller']);
unset($get_params['page']);
unset($get_params['aid']);
unset($get_params['order']);
if($order=='desc' && $orderField=='time'){
$get_params['order']='asc';
}
if(count($get_params)>0){
$order_link.='?'.http_build_query($get_params);
}
?>
<a href="<?php echo $order_link; ?>" class="order-link"><?php echo l('my_messages_item_time'); ?><?php if($orderField=='time'){ ?><i class="fa fa-chevron-<?php if($order=='desc'){ ?>down<?php } else { ?>up<?php } ?>"></i><?php } ?></a>
</td>
</tr>
<?php while($message=mysql_fetch_assoc($get_messages)){ ?>
<?php
$dialog=mysql_fetch_assoc(mysql_query("SELECT * FROM `dialogs` WHERE `did`='".$message['did']."';"));
$membering=mysql_fetch_assoc(mysql_query("SELECT * FROM `dialog_members` WHERE `did`='".$message['did']."' AND `userid`='".$_SESSION['userid']."';"));
$correspondent=mysql_fetch_assoc(mysql_query("SELECT `users`.* FROM `dialog_members`, `users` WHERE `dialog_members`.`did`='".$message['did']."' AND `dialog_members`.`userid`!='".$_SESSION['userid']."' AND `users`.`userid`=`dialog_members`.`userid`;"));
if($correspondent['person']==''){
$correspondent['person']=reset(explode('@', $correspondent['email']));
}
$ad=mysql_fetch_assoc(mysql_query("SELECT * FROM `ads` WHERE `aid`='".$dialog['aid']."';"));
$unread=mysql_num_rows(mysql_query("SELECT * FROM `dialog_unread_messages` WHERE `dmid`='".$message['dmid']."' AND `userid`='".$_SESSION['userid']."';"));
$ad_dialog_position=intval(mysql_num_rows(mysql_query("SELECT * FROM `dialogs` WHERE `aid`='".$ad['aid']."' AND `did`<='".$message['did']."';")));
?>
<tr class="dialogs-list-item<?php if($unread){ ?> unread<?php } ?>" id="dialog_<?php echo $message['did']; ?>">
<td width="20">
<div class="checkbox">
<input type="checkbox" name="selected[<?php echo $message['did']; ?>]" value="1" id="selected_<?php echo $message['did']; ?>">
<label for="selected_<?php echo $message['did']; ?>"></label>
</div>
</td>
<td width="77">
<?php if(trim($_GET['controller'])=='archive'){ ?>
<div class="dialogs-list-item-control restore" title="<?php echo l('my_messages_restore_from_archive'); ?>" data-id="<?php echo $dialog['did']; ?>"><i class="fa fa-history"></i></div>
<div class="dialogs-list-item-control remove" title="<?php echo l('my_messages_remove_forever'); ?>" data-id="<?php echo $dialog['did']; ?>"><i class="fa fa-trash"></i></div>
<?php } else { ?>
<div class="dialogs-list-item-control star<?php if($membering['starred']=='1'){ ?> hidden<?php } ?>" title="<?php echo l('my_messages_add_to_favorites'); ?>" data-id="<?php echo $dialog['did']; ?>"><i class="fa fa-star-o"></i></div>
<div class="dialogs-list-item-control unstar<?php if($membering['starred']=='0'){ ?> hidden<?php } ?>" title="<?php echo l('my_messages_remove_from_favorites'); ?>" data-id="<?php echo $dialog['did']; ?>"><i class="fa fa-star-o"></i></div>
<div class="dialogs-list-item-control archive" title="<?php echo l('my_messages_add_to_archive'); ?>" data-id="<?php echo $dialog['did']; ?>"><i class="fa fa-trash-o"></i></div>
<?php } ?>
</td>
<td width="150" onclick="location.href='<?php echo $langPrefix; ?>/my/message/<?php echo $message['did']; ?>?ref=<?php echo urlencode($langPrefix.$_SERVER['REQUEST_URI']); ?>';">
<div class="dialogs-list-item-correspondent"><?php echo htmlspecialchars($correspondent['person']); ?></div>
</td>
<td onclick="location.href='<?php echo $langPrefix; ?>/my/message/<?php echo $message['did']; ?>?ref=<?php echo urlencode($langPrefix.$_SERVER['REQUEST_URI']); ?>';">
<div class="dialogs-list-item-title">
<?php echo htmlspecialchars(cutString($ad['title'], 60, '...')); ?>
<?php if($ad['userid']==$_SESSION['userid'] && trim($_GET['controller'])=='messages'){ ?>
<span>#<?php echo $ad_dialog_position; ?></span>
<?php } ?>
</div>
<div class="dialogs-list-item-text">
<?php echo htmlspecialchars(cutString($message['text'], 70, '...')); ?>
</div>
</td>
<td width="150" align="right">
<div class="dialogs-list-item-time"><?php displayTime($message['time']); ?></div>
</td>
</tr>
<tr class="dialogs-list-item-info-container">
<td colspan="5">
<div class="dialogs-list-item-info hidden"><i class="fa fa-spinner fa-spin"></i><?php echo l('waiting'); ?></div>
</td>
</tr>
<?php } ?>
</table>
<?php } ?>

<?php
if($pages['count']/$pages['per']>1){
echo $pagingHtml;
}
?>

<?php } ?>

<?php include "includes/footer.php"; ?>