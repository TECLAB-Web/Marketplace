<?php include_once "init.php"; ?>
<?php
$question_types=array();
$get_question_types=mysql_query("SELECT * FROM `question_types` WHERE `active`='1' ORDER BY `sort` ASC;");
while($qt=mysql_fetch_assoc($get_question_types)){
$question_types[$qt['qtid']]=$qt['title_'.$config['lang']];
}
?>
<?php
if(trim($_GET['mode'])=='ajax'){
header('Content-Type: application/json; charset=utf-8');
if(trim($_POST['action'])==htmlspecialchars(trim($_GET['controller']))){
$result=array();
$result['errors']=array();
if(!isset($question_types[intval($_POST['qtid'])])){
$result['errors']['qtid']=l('contact_error_type');
}
if(trim($_POST['text'])==''){
$result['errors']['text']=l('contact_error_text');
}
if(trim($_POST['email'])==''){
$result['errors']['email']=l('contact_error_email_empty');
} elseif(!preg_match("/^([-a-zA-Z0-9._]+@[-a-zA-Z0-9.]+(\.[-a-zA-Z0-9]+)+)*$/", trim($_POST['email']))){
$result['errors']['email']=l('contact_error_email_invalid');
}
if(count($result['errors'])==0){
$to=$config['support_email'];
$mail['title']='#'.$time.': '.$question_types[intval($_POST['qtid'])];
$mail['body']='<b>E-Mail:</b> '.trim($_POST['email']).'<br><b>Subject:</b> '.trim($question_types[intval($_POST['qtid'])]).'<br><b>Datetime:</b> '.trim(date("d.m.Y H:i", $time)).'<br><br>'.nl2br(htmlspecialchars(trim($_POST['text'])));
liamSupport($to, $mail['title'], $mail['body'], "noreply@".$config['siteurl'], trim($_POST['email']), trim($_POST['dmuid']));
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
$dmuid=uniqid('');
$pagetitle=l('contact_title')." &bull; ".$config['sitename'];
$pagedesc=$config['description'];
?>
<?php
if($m){ include "m-contact.php"; exit; }
?>
<?php include "includes/header.php"; ?>

<div id="contactForm">

<h4 class="special-title"><?php echo l('contact_title'); ?></h4>

<div class="support-description"><?php echo l('contact_description'); ?></div>

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
$('.add-attachment-button button').prop('disabled', true);
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

<form action="/<?php echo htmlspecialchars(trim($_GET['controller'])); ?>/" method="POST" autocomplete="off" class="form-horizontal add-form ajax-form" data-callback="supportCallBack">
<input type="hidden" name="action" value="<?php echo htmlspecialchars(trim($_GET['controller'])); ?>">
<input type="hidden" name="dmuid" id="dmuid" value="<?php echo $dmuid; ?>">
<hr>
<div class="form-group">
<label class="col-sm-3 control-label"><?php echo l('contact_type_title'); ?> <span class="input-required">*</span></label>
<div class="col-sm-6">
<select class="form-control" name="qtid" size="1">
<option value="0"><?php echo l('contact_type_select'); ?></option>
<?php foreach($question_types as $qtid=>$qtitle){ ?>
<option value="<?php echo $qtid; ?>"><?php echo htmlspecialchars($qtitle); ?></option>
<?php } ?>
</select>
</div>
</div>
<div class="form-group">
<label class="col-sm-3 control-label"><?php echo l('contact_text_title'); ?> <span class="input-required">*</span></label>
<div class="col-sm-6">
<textarea name="text" class="form-control" rows="5"></textarea>
</div>
</div>
<div class="form-group">
<label class="col-sm-3 control-label"><?php echo l('contact_email_title'); ?> <span class="input-required">*</span></label>
<div class="col-sm-6">
<input type="text" name="email" class="form-control" value="<?php if(isset($_SESSION['userid'])){ echo $my['email']; } ?>">
</div>
</div>
<div class="form-group">
<label class="col-sm-3 control-label"><?php echo l('contact_files_title'); ?></label>
<div class="col-sm-6">
<div class="added-attachment hidden" id="attachmentUploaderTemplate"><div class="file-icon"></div><div class="attachment-title"></div><a href="javascript:void(0);" class="delete-attachment"><?php echo l('remove_attachment'); ?></a></div>
<div id="attachmentUploader"></div>
<div class="add-attachment-button" id="add_attachment_button" title="<?php echo l('add_attachment'); ?>" style="height:34px;">
<button type="button" onclick="$('#uploaderButton').click(); return false;"><i class="fa fa-cloud-upload"></i><i class="fa fa-spinner fa-spin hidden"></i></button>
</div>
</div>
</div>
<hr>
<div class="form-group">
<div class="col-sm-3"></div>
<div class="col-sm-7">
<button type="submit" class="btn btn-primary"><?php echo l('contact_submit'); ?></button>
</div>
</div>
</form>

</div>

<div id="contactSuccess" style="display:none;">
<div class="success-page">
<div class="success-box">
<?php
echo l('contact_success_info_message');
?>
</div>
<p><?php echo l('contact_success_info_message_note'); ?></p>
<p><a href="<?php echo $langPrefix; ?>/"><?php echo l('go_to_main_page'); ?></a></p>
<p><a href="<?php echo $langPrefix; ?>/list/"><?php echo l('go_to_ads_list'); ?></a></p>
</div>
</div>

<?php include "includes/footer.php"; ?>