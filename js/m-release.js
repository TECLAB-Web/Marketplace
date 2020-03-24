$(window).resize(function(e){
overlayResize();
});

function overlayResize(){
var wh=$(window).height()*1;
var ioth=$('.item-overlay-table').outerHeight()*1;
$('.item-overlay-table').css('margin-top', ((wh-ioth)/2)+'px');
}

function hideOverlay(){
$('.item-overlay-background').css('display', 'none');
$('.item-overlay').css('display', 'none');
$(document).unbind('keydown');
}

function showOverlayGalleryItem(key){
$('.item-overlay-background').css('display', 'block');
$('.item-overlay').css('display', 'block');
overlayResize();
$('.overlay-gallery-item').removeClass('current-photo');
$('.overlay-thumb').removeClass('current-thumb');
$('#overlay-gallery-item-'+key).addClass('current-photo');
$('#overlay-thumb-'+key).addClass('current-thumb');
$('.overlay-gallery-fullsize').attr('href', $('#overlay-gallery-item-'+key).data('src'));
$(document).unbind('keydown').bind('keydown', (function(e){
if(e.keyCode==27){
hideOverlay();
}
if(e.keyCode==37){
$('.item-overlay-table-photo-container .prev').click();
}
if(e.keyCode==39){
$('.item-overlay-table-photo-container .next').click();
}
}));
}

$('form.ajax-form').each(function(){
var form=$(this);
form.submit(function(e){
e.preventDefault();
form.find('.main-form-error').remove();
form.find('.error-block-container').remove();
form.find('.error-input').removeClass('error-input');
form.find('.suffix-container').removeClass('hidden');
var disableds=form.find(':input:disabled').removeAttr('disabled');
var data=form.serialize();
disableds.attr('disabled', 'disabled');
var callback=form.data('callback');
$.post(langPrefix+'/ajax'+(form.attr('action')), data, function(response){
form.find('button:focus').blur();
if(response.status=='error'){
$.each(response.errors, function(key, value){
if(key=='form'){
form.prepend('<div class="main-form-error">'+value+'</div>');
} else {
form.find('*[name^=\''+key+'\']').eq(0).closest('.form-group').append('<div class="error-block-container">'+value+'</div>');
form.find('*[name^=\''+key+'\']').last().addClass('error-input');
}
});
form.find('.info-block-container').addClass('hidden');
if(!form.hasClass('ajax-form-no-scrolling')){
var firstErrorMessageTop=form.find('.error-block-container, .main-form-error').first().closest('.form-group').offset().top;
$(window).scrollTop(firstErrorMessageTop-10);
}
}
if(response.status=='success'){
if(callback=='addCallBack' || callback=='editCallBack'){
window[callback](response.aid);
} else if(callback=='payCallBack'){
window[callback](response.initiator);
} else if(callback=='complaintCallBack'){
window[callback](response.message);
} else if(callback=='testGatewayCallBack'){
window[callback](response.response);
} else if(callback=='messageCallBack'){
window[callback](response.did);
} else if(callback=='replyCallBack'){
window[callback](response.dmuid);
} else if(callback=='settingsCallBack' || callback=='settingsPasswordCallBack' || callback=='settingsNotifyCallBack'){
window[callback](response.message);
} else {
window[callback]();
}
}
});
return false;
});
});

$('.item-overlay-table-photo-container').each(function(){
var gallery=$(this);
gallery.find('.next').click(function(e){
e.preventDefault;
var current=gallery.find('.current-photo');
var next=current.next('.overlay-gallery-item');
gallery.find('.overlay-gallery-item').removeClass('current-photo');
if(next.length>0){
var key=next.addClass('current-photo').attr('id').replace('overlay-gallery-item-', '');
} else {
var key=gallery.find('.overlay-gallery-item').first().attr('id').replace('overlay-gallery-item-', '');
}
showOverlayGalleryItem(key);
});
gallery.find('.prev').click(function(e){
e.preventDefault;
var current=gallery.find('.current-photo');
var prev=current.prev('.overlay-gallery-item');
if(prev.length>0){
var key=prev.addClass('current-photo').attr('id').replace('overlay-gallery-item-', '');
} else {
var key=gallery.find('.overlay-gallery-item').last().attr('id').replace('overlay-gallery-item-', '');
}
showOverlayGalleryItem(key);
});
});

$('.page-tabs-search-box').each(function(){
var searchbox=$(this);
var input=searchbox.find('input');
var search=searchbox.find('.search-button');
var clear=searchbox.find('.clear-button');
input.keyup(function(){
var val=$.trim($(this).val());
if(val==''){
clear.addClass('hidden');
} else {
clear.removeClass('hidden');
}
});
input.focus(function(){
var val=$.trim($(this).val());
search.removeClass('not-active');
});
input.blur(function(){
var val=$.trim($(this).val());
if(val==''){
search.addClass('not-active');
} else {
search.removeClass('not-active');
}
});
});

function initFavoriteLinks(){

$('.favorite-link').each(function(){
var faver=$(this);
var id=faver.data('id');
var type=$.trim(faver.data('type'));
faver.unbind('click').click(function(){
$('.favorite-link').blur();
$.post(langPrefix+'/ajax/favorites/', 'action=favorites'+((type!='')?'_'+type:'')+'&id='+id, function(response){
if(response.status=='added'){
$('.favorite-link.photo-gallery-favorite[data-id='+id+']').addClass('photo-gallery-favorite-active');
$('.favorite-link.overlay-gallery-favorite[data-id='+id+']').addClass('overlay-gallery-favorite-active');
$('.add-favorite-'+id).addClass('hidden');
$('.delete-favorite-'+id).removeClass('hidden');
favoritesCount=favoritesCount+1;
}
if(response.status=='deleted'){
$('.favorite-link.photo-gallery-favorite[data-id='+id+']').removeClass('photo-gallery-favorite-active');
$('.favorite-link.overlay-gallery-favorite[data-id='+id+']').removeClass('overlay-gallery-favorite-active');
$('.add-favorite-'+id).removeClass('hidden');
$('.delete-favorite-'+id).addClass('hidden');
favoritesCount=favoritesCount-1;
}
if(favoritesCount>0){
$('#header_favorites_container i.fa').removeClass('fa-star-o').addClass('fa-star');
} else {
$('#header_favorites_container i.fa').removeClass('fa-star').addClass('fa-star-o');
}
});
});
});

}

initFavoriteLinks();

$('html').click(function(e){
if(!$(e.target).closest('.search-form-filter').length){
$('.search-form-filter ul.dropdown-menu').addClass('hidden');
}
if(!$(e.target).closest('.search-form-category-selector-container').length){
$('.search-form-category-selector-container').addClass('hidden');
}
});

$('.chars-left').each(function(){
var counter=$(this);
var input=counter.closest('.form-group').find('input, textarea');
var length=(($.trim(counter.closest('.form-group').find('input, textarea').val())).length)*1;
var maxlength=(counter.data('maxlength'))*1;
var left=maxlength-length;
counter.find('.chars-left-count').html(left);
input.on('keyup keypress keydown blur change', function(){
length=(($.trim(counter.closest('.form-group').find('input, textarea').val())).length)*1;
left=maxlength-length;
counter.find('.chars-left-count').html(left);
if(left<0){
counter.addClass('too-much-chars');
} else {
counter.removeClass('too-much-chars');
}
});
});

$('.item-contact-button').click(function(e){
if(!$(this).hasClass('contact-email')){
e.preventDefault();
var btn=$(this);
var type=btn.data('type');
var link=btn.find('a').attr('href');
if(type!=''){
$.post(langPrefix+'/ajax'+link, 'action=contact&type='+type+'&captcha='+$('#captcha_input').val(), function(response){
if(response.inactive){
$('.contact-'+type).find('span').html(''+response.inactive_label+'');
$('.contact-'+type).find('a').remove();
$('.contact-'+type).unbind('click');
} else if(response.captcha){
if($('#captchaWindow').is(':hidden')){
$.fancybox({'type':'inline', 'href':'#captchaWindow', 'closeBtn':false, helpers:{overlay:{locked:false}}});
}
$('#captcha_message').html(response.captcha_message+' ');
$('#captcha_input').val('').focus();
$('#captcha_image').attr('src', '/captcha/?'+Math.random());
$('#captcha_ok').unbind('click');
$('#captcha_ok').click(function(){
$('.contact-'+type).first().click();
});
} else {
$('#captcha_input').val('');
$('.contact-'+type).find('span').html(response.value);
$('.contact-'+type).find('a').remove();
$('.contact-'+type).unbind('click');
if(type=='skype'){
$('.contact-'+type).find('span').after('<a href="skype:'+response.value+'?call">'+response.call_label+'</a>');
}
if($('#captchaWindow').is(':visible')){
$.fancybox.close();
}
}
});
}
}
});

var temp_query_text='';

function initQueryInput(){

$('.fast-search-form-query-container input#query').focus(function(){
temp_query_text=$.trim($(this).val());
});

$('.fast-search-form-query-container input#query').blur(function(){
if($.trim($(this).val())!=temp_query_text){
temp_query_text=$.trim($(this).val());
if(document.getElementById('search_form')){
$('.fast-search-form-query-container .clear-button').removeClass('hidden');
runSearching();
}
}
});

}

initQueryInput();

function initSearchClears(){

$('.search-form-geo-container .clear-button').click(function(){
$('#city_url').val('');
$('#city_id').val('0');
$('.search-form-geo-container .clear-button').addClass('hidden');
$('input#geo').removeClass('filled-input');
if(document.getElementById('search_form')){
$('input#geo').val($('input#geo').data('lang-inall'));
Cookies.set('city_id', '0', {expires:365});
runSearching();
}
if(document.getElementById('search_form_index')){
$('input#geo').val($('input#geo').data('lang-inall'));
$('.index-categories-item a').each(function(){
$(this).attr('href', '/list/'+$(this).data('url')+'/');
});
Cookies.set('city_id', '0', {expires:365});
$('#search_form_index form').attr('action', '/list/');
updateIndexPageAdsList();
}
});

$('.fast-search-form-query-container .clear-button').click(function(){
$('#query').val('');
$('.fast-search-form-query-container .clear-button').addClass('hidden');
runSearching();
});

$('.search-form-category-container .clear-button').click(function(){
$('#category_id').val('0');
$('#category_url').val('');
$('.search-form-filters-container input, .search-form-filters-container select').removeAttr("name");
$('.search-form-category-container .clear-button').addClass('hidden');
$('input#category').removeClass('filled-input');
$('input#category').val($('input#category').data('lang-inall'));
runSearching();
});

}

initSearchClears();

function registerCallBack(){
window.location.href=langPrefix+'/checkmail/?type=register';
}

function restoreCallBack(){
window.location.href=langPrefix+'/checkmail/?type=restore';
}

function loginCallBack(){
if($.trim(getParameterByName('ref'))==''){
window.location.href=langPrefix+'/my/';
} else {
window.location.href=getParameterByName('ref');
}
}

function addCallBack(aid){
window.location.href=langPrefix+'/success/?type=add&id='+aid;
}

function editCallBack(aid){
window.location.href=langPrefix+'/success/?type=edit&id='+aid;
}

function settingsCallBack(message){
window.location.href=langPrefix+'/my/settings/?msg='+encodeURIComponent(message);
}

function settingsPasswordCallBack(message){
window.location.href=langPrefix+'/my/settings/?msg='+encodeURIComponent(message);
}

function settingsEMailCallBack(){
window.location.href=langPrefix+'/checkmail/?type=email';
}

function settingsNotifyCallBack(message){
window.location.href=langPrefix+'/my/settings/?msg='+encodeURIComponent(message);
}

function settingsDeleteCallBack(){
window.location.href=langPrefix+'/deleted/';
}

function supportCallBack(){
$('#contactSuccess, #contactForm').toggle();
}

function messageCallBack(did){
window.location.href=langPrefix+'/my/message/'+did;
}

function replyCallBack(dmuid){
$('#dmuid').val(dmuid);
$('#uploaderButton').fileupload('option', {url: '/ajax/attach/?dmuid='+dmuid});
$('#post-message-text').val('').trigger('change');
$('#attachmentUploader').html('');
$('.add-attachment-button').removeClass('hidden');
}

function complaintCallBack(message){
$('#complaintWindow .window-message').html(message);
$('#complaintWindow .window-buttons').html('');
}

function payCallBack(form){
$('#external-payment-form').html(form);
setTimeout(function(){
$('#external-payment-form form').submit();
}, 1000);
}

function testGatewayCallBack(response){
setTimeout(function(){
if($.trim(response)=='OK'){
location.href=success_url;
} else {
location.href=fail_url;
}
}, 1000);
}

function adminCallBack(){
location.reload();
$.fancybox.close();
}

function moderationMasterCallBack(){
location.reload();
}

function adminServicesCallBack(){
location.href='/admin/services/';
}

function adminCategoriesCallBack(){
if($.trim(getParameterByName('parent_id'))==''){
location.href='/admin/categories/';
} else {
location.href='/admin/categories/?parent_id='+getParameterByName('parent_id');
}
}

function adminParametersCallBack(){
location.href='/admin/parameters/';
}

function adminParameterValuesCallBack(){
location.href='/admin/parameter-values/';
}

function adminMailTemplatesCallBack(){
location.href='/admin/mail-templates/';
}

function adminStaticPagesCallBack(){
location.href='/admin/static-pages/';
}

function adminGatewaysCallBack(){
location.href='/admin/gateways/';
}

function getParameterByName(name) {
name=name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
var regex=new RegExp("[\\?&]"+name+"=([^&#]*)"),
results=regex.exec(location.search);
return results===null?"":decodeURIComponent(results[1].replace(/\+/g, " "));
}

function removeURLParameter(url, parameter){
var urlparts=url.split('?');   
if(urlparts.length>=2){
var prefix=encodeURIComponent(parameter)+'=';
var pars=urlparts[1].split(/[&;]/g);
for(var i=pars.length;i-->0;){    
if(pars[i].lastIndexOf(prefix, 0)!==-1){
pars.splice(i, 1);
}
}
url=urlparts[0]+'?'+pars.join('&');
return url;
} else {
return url;
}
}

function showCatChooser(){
$.fancybox({'type':'inline', 'href':'#catChoose', 'closeBtn':false});
$('.category-chooser-main').css('display', 'block');
$('.category-chooser-subselect').css('display', 'none');
}

function selectCat(id, photos, el){
maxPhotos=photos;
$('#photoUploader .add-photo-button').addClass('hidden');
$('#photoUploader .add-photo-button').slice(uploadedPhotos, photos).removeClass('hidden');
el.nextAll('select[name=category_id]').remove();
$('#parameters').html('');
$.post(langPrefix+'/ajax/misc/?category_id='+id, 'action=m-adding-category-select', function(response){
if($.trim(response)!=''){
el.after(response);	
} else {
$.post(langPrefix+'/ajax/add/?category_id='+id, 'action=parameters', function(response){
$('#parameters').html(response);
});
}
});
}

function selectRegion(id, el){
el.nextAll('select[name=city_id]').remove();
$.post(langPrefix+'/ajax/misc/?region_id='+id, 'action=m-adding-city-select', function(response){
if($.trim(response)!=''){
el.after(response);	
}
});
}

function clearFavorites(type){
$.post(langPrefix+'/ajax/favorites/', 'action=clearall'+type, function(response){
location.reload();
});
}

function deleteUploadedPhoto(apid, key){
$.post(langPrefix+'/ajax/upload/?apid='+apid+'&key='+key, 'action=delete', function(response){
$('#photo_'+key).remove();
$('#photoUploader .add-photo-button').removeClass('first');
$('#photoUploader .add-photo-button:hidden').first().removeClass('hidden');
uploadedPhotos=uploadedPhotos-1;
if(uploadedPhotos==0){
$('#photoUploader .add-photo-button:visible').first().addClass('first');
$('#pre_upload_info').removeClass('hidden');
$('#post_upload_info').addClass('hidden');
}
$('#photoUploader .added-photo').removeClass('first');
$('#photoUploader .added-photo').first().addClass('first');
});
}

function deleteUploadedAttachment(dmuid, key){
$.post(langPrefix+'/ajax/attach/?dmuid='+dmuid+'&key='+key, 'action=delete', function(response){
$('#attachment_'+key).remove();
uploadedAttachments=uploadedAttachments-1;
if(uploadedAttachments<5){
$('.add-attachment-button button').prop('disabled', false);
}
});
}

function showComplaintDialog(){
$.fancybox({'type':'inline', 'href':'#complaintWindow', 'closeBtn':false, helpers:{overlay:{locked:false}}});
}

function showMapDialog(){
$.fancybox({'type':'inline', 'href':'#mapWindow', 'closeBtn':false, helpers:{overlay:{locked:false}}});
initMap();
}

function recountPayedServices(){
var summ=0;
$('input[name^=services]:checked').closest('table').find('.service-price').each(function(){
var price=$(this).html()*1;
summ=summ+price;
});
$('#pay-summ span').html(summ);
if(summ==0){
$('#select-payment-method').css('display', 'none');
$('#pay-summ').css('display', 'none');
$('#pay-submit-button').attr('disabled', 'disabled');
} else {
$('#select-payment-method').css('display', 'block');
$('#pay-summ').css('display', 'inline-block');
$('#pay-submit-button').removeAttr('disabled');
}
}

function recountWalletSumm(summ){
summ=$.trim(summ);
summ=parseInt(summ, 10);
if(summ>0){
$('#pay-summ span').html(summ);
$('#pay-summ').css('display', 'inline-block');
$('#pay-submit-button').removeAttr('disabled');	
} else {
$('#pay-summ span').html('0');
$('#pay-summ').css('display', 'none');
$('#pay-submit-button').attr('disabled', 'disabled');
}
}

function getNewMessages(url){
$.post(url, 'action=new&dmid='+last_dmid, function(response){
if($.trim(response)!=''){
var oldh=$(document).outerHeight();
$('#new-ajax-messages-mark').before(response);
var newh=$(document).outerHeight();
$(window).scrollTop($(window).scrollTop()+(newh-oldh));
}
});
}

function runSearching(default_url, force_page){
default_url=default_url||'';
force_page=force_page||false;
if(default_url==''){
var url_parts=new Array();
var city_url=$('#city_url').val();
if(city_url==''){
url_parts.push('list');
} else {
url_parts.push(city_url);
}
var category_id=$('#category_id').val();
var category_url=$('#category_url').val();
if(category_id!='0'){
url_parts.push(category_url);
}
var filters=[];
$.each(['offer_seek'], function(index, value){
if($.trim(getParameterByName('search%5B'+value+'%5D'))!=''){
filters.push('search['+value+']='+encodeURIComponent(getParameterByName('search%5B'+value+'%5D')));
}
if($.trim(getParameterByName('search['+value+']'))!=''){
filters.push('search['+value+']='+encodeURIComponent(getParameterByName('search['+value+']')));
}
});
$('#fast-search-form input[name^=search], #fast-search-form select[name^=search]').each(function(){
var el=$(this);
if($.trim(el.val())!='' && $.trim(el.val())!='0'){
filters.push(el.attr('name')+'='+encodeURIComponent($.trim(el.val())));
}
});
if(filters.length>0){
var filters_string='?'+filters.join('&');
} else {
var filters_string='';
}
var url='/'+(url_parts.join('/'))+'/'+filters_string;
} else {
var url=default_url.replace(langPrefix, '').replace('//', '/');
}
if(!force_page){
url=removeURLParameter(url, 'page');
}
window.location.href=url;
}

$('.radioBtn a').on('click', function(){
    var sel = $(this).data('title');
    var tog = $(this).data('toggle');
    $('#'+tog).prop('value', sel);
    
    $('a[data-toggle="'+tog+'"]').not('[data-title="'+sel+'"]').addClass('not-active');
    $('a[data-toggle="'+tog+'"][data-title="'+sel+'"]').removeClass('not-active');
});

autosize($('textarea'));