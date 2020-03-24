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
form.find('.error-tooltip-container').closest('div, label').css('position', 'inherit');
form.find('.error-tooltip-container').remove();
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
form.find('*[name^=\''+key+'\']').eq(0).closest('div, label').next('.suffix-container').addClass('hidden');
form.find('*[name^=\''+key+'\']').eq(0).closest('div, label').css('position', 'relative').append('<div class="error-tooltip-container"><div class="error-tooltip-arrow"></div><div class="error-tooltip">'+value+'</div></div>');
form.find('*[name^=\''+key+'\']').addClass('error-input');
}
});
form.find('.info-tooltip-container').addClass('hidden');
var firstErrorMessageTop=form.find('.error-tooltip-container, .main-form-error').first().offset().top;
if(!form.hasClass('ajax-form-no-scrolling')){
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

$('.my-ads-list-item-control.reason').each(function(){
var actioner=$(this);
var id=actioner.data('id');
var reason=actioner.data('reason');
actioner.click(function(){
$(this).blur();
$('#reason-text').html(reason);
$.fancybox({'type':'inline', 'href':'#moderatedReason', 'closeBtn':false, helpers:{overlay:{locked:false}}});
return false;
});
});

$('.my-ads-list-item-control.activate').each(function(){
var actioner=$(this);
var id=actioner.data('id');
actioner.click(function(){
$(this).blur();
$('#ad_'+id).next('tr').next('tr').find('.my-ads-list-item-info').removeClass('hidden').css('width', $('#ad_'+id).outerWidth()+'px').css('height', $('#ad_'+id).outerHeight()+$('#ad_'+id).next('tr').outerHeight()+'px');
$.post(langPrefix+'/ajax/my/', 'action=activate&id='+id, function(response){
if(response.status=='success'){
myItemsCount=myItemsCount-1;
$('#ad_'+id).next('tr').next('tr').find('.my-ads-list-item-info').removeClass('hidden').html(response.message);
if(typeof postActionTimeout!=='undefined'){ clearTimeout(postActionTimeout); }
postActionTimeout=setTimeout(function(){
setTimeout(function(){
location.reload();
}, 500);
}, 3000);
}
});
return false;
});
});

$('.my-ads-list-item-control.deactivate').each(function(){
var actioner=$(this);
var id=actioner.data('id');
actioner.click(function(){
$(this).blur();
$('#ad_'+id).next('tr').next('tr').find('.my-ads-list-item-info').removeClass('hidden').css('width', $('#ad_'+id).outerWidth()+'px').css('height', $('#ad_'+id).outerHeight()+$('#ad_'+id).next('tr').outerHeight()+'px');
$.post(langPrefix+'/ajax/my/', 'action=deactivate&id='+id, function(response){
if(response.status=='success'){
myItemsCount=myItemsCount-1;
$('#ad_'+id).next('tr').next('tr').find('.my-ads-list-item-info').removeClass('hidden').html(response.message);
if(typeof postActionTimeout!=='undefined'){ clearTimeout(postActionTimeout); }
postActionTimeout=setTimeout(function(){
setTimeout(function(){
location.reload();
}, 500);
}, 3000);
}
});
return false;
});
});

$('.my-ads-list-item-control.remove, .my-ads-list-item-control.remove2, .my-ads-list-item-control.remove3').each(function(){
var actioner=$(this);
var id=actioner.data('id');
actioner.click(function(){
$(this).blur();
$('#ad_'+id).next('tr').next('tr').find('.my-ads-list-item-info').removeClass('hidden').css('width', $('#ad_'+id).outerWidth()+'px').css('height', $('#ad_'+id).outerHeight()+$('#ad_'+id).next('tr').outerHeight()+'px');
$.post(langPrefix+'/ajax/my/', 'action=remove&id='+id, function(response){
if(response.status=='success'){
myItemsCount=myItemsCount-1;
$('#ad_'+id).next('tr').next('tr').find('.my-ads-list-item-info').removeClass('hidden').html(response.message);
if(typeof postActionTimeout!=='undefined'){ clearTimeout(postActionTimeout); }
postActionTimeout=setTimeout(function(){
setTimeout(function(){
location.reload();
}, 500);
}, 3000);
}
});
return false;
});
});

$('.my-ads-list-item-control.moder_complaint_decline').each(function(){
var actioner=$(this);
var id=actioner.data('id');
actioner.click(function(){
$(this).blur();
$('#ad_'+id).next('tr').next('tr').find('.my-ads-list-item-info').removeClass('hidden').css('width', $('#ad_'+id).outerWidth()+'px').css('height', $('#ad_'+id).outerHeight()+$('#ad_'+id).next('tr').outerHeight()+'px');
$.post(langPrefix+'/ajax/moderation/', 'action=complaint_decline&aid='+id, function(response){
if(response.status=='success'){
myItemsCount=myItemsCount-1;
$('#ad_'+id).next('tr').next('tr').find('.my-ads-list-item-info').removeClass('hidden').html(response.message);
if(typeof postActionTimeout!=='undefined'){ clearTimeout(postActionTimeout); }
postActionTimeout=setTimeout(function(){
setTimeout(function(){
location.reload();
}, 500);
}, 3000);
}
});
return false;
});
});

$('.my-ads-list-item-control.moder_complaint_accept').each(function(){
var actioner=$(this);
var id=actioner.data('id');
actioner.click(function(){
$(this).blur();
$('#ad_'+id).next('tr').next('tr').find('.my-ads-list-item-info').removeClass('hidden').css('width', $('#ad_'+id).outerWidth()+'px').css('height', $('#ad_'+id).outerHeight()+$('#ad_'+id).next('tr').outerHeight()+'px');
$.post(langPrefix+'/ajax/moderation/', 'action=complaint_accept&aid='+id, function(response){
if(response.status=='success'){
myItemsCount=myItemsCount-1;
$('#ad_'+id).next('tr').next('tr').find('.my-ads-list-item-info').removeClass('hidden').html(response.message);
if(typeof postActionTimeout!=='undefined'){ clearTimeout(postActionTimeout); }
postActionTimeout=setTimeout(function(){
setTimeout(function(){
location.reload();
}, 500);
}, 3000);
}
});
return false;
});
});

$('.my-ads-list-item-control.moder_remove').each(function(){
var actioner=$(this);
var id=actioner.data('id');
actioner.click(function(){
$(this).blur();
$('#ad_'+id).next('tr').next('tr').find('.my-ads-list-item-info').removeClass('hidden').css('width', $('#ad_'+id).outerWidth()+'px').css('height', $('#ad_'+id).outerHeight()+$('#ad_'+id).next('tr').outerHeight()+'px');
$.post(langPrefix+'/ajax/moderation/', 'action=remove&aid='+id, function(response){
if(response.status=='success'){
myItemsCount=myItemsCount-1;
$('#ad_'+id).next('tr').next('tr').find('.my-ads-list-item-info').removeClass('hidden').html(response.message);
if(typeof postActionTimeout!=='undefined'){ clearTimeout(postActionTimeout); }
postActionTimeout=setTimeout(function(){
setTimeout(function(){
location.reload();
}, 500);
}, 3000);
}
});
return false;
});
});

$('.my-ads-list-item-control.moder_deactivate').each(function(){
var actioner=$(this);
var id=actioner.data('id');
actioner.click(function(){
$(this).blur();
$('#ad_'+id).next('tr').next('tr').find('.my-ads-list-item-info').removeClass('hidden').css('width', $('#ad_'+id).outerWidth()+'px').css('height', $('#ad_'+id).outerHeight()+$('#ad_'+id).next('tr').outerHeight()+'px');
$.post(langPrefix+'/ajax/moderation/', 'action=deactivate&aid='+id, function(response){
if(response.status=='success'){
myItemsCount=myItemsCount-1;
$('#ad_'+id).next('tr').next('tr').find('.my-ads-list-item-info').removeClass('hidden').html(response.message);
if(typeof postActionTimeout!=='undefined'){ clearTimeout(postActionTimeout); }
postActionTimeout=setTimeout(function(){
setTimeout(function(){
location.reload();
}, 500);
}, 3000);
}
});
return false;
});
});

$('.my-ads-list-item-control.moder_activate').each(function(){
var actioner=$(this);
var id=actioner.data('id');
actioner.click(function(){
$(this).blur();
$('#ad_'+id).next('tr').next('tr').find('.my-ads-list-item-info').removeClass('hidden').css('width', $('#ad_'+id).outerWidth()+'px').css('height', $('#ad_'+id).outerHeight()+$('#ad_'+id).next('tr').outerHeight()+'px');
$.post(langPrefix+'/ajax/moderation/', 'action=activate&aid='+id, function(response){
if(response.status=='success'){
myItemsCount=myItemsCount-1;
$('#ad_'+id).next('tr').next('tr').find('.my-ads-list-item-info').removeClass('hidden').html(response.message);
if(typeof postActionTimeout!=='undefined'){ clearTimeout(postActionTimeout); }
postActionTimeout=setTimeout(function(){
setTimeout(function(){
location.reload();
}, 500);
}, 3000);
}
});
return false;
});
});

$('.my-ads-list-item-control.moder_publish').each(function(){
var actioner=$(this);
var id=actioner.data('id');
actioner.click(function(){
$(this).blur();
$('#ad_'+id).next('tr').next('tr').find('.my-ads-list-item-info').removeClass('hidden').css('width', $('#ad_'+id).outerWidth()+'px').css('height', $('#ad_'+id).outerHeight()+$('#ad_'+id).next('tr').outerHeight()+'px');
$.post(langPrefix+'/ajax/moderation/', 'action=publish&aid='+id, function(response){
if(response.status=='success'){
myItemsCount=myItemsCount-1;
$('#ad_'+id).next('tr').next('tr').find('.my-ads-list-item-info').removeClass('hidden').html(response.message);
if(typeof postActionTimeout!=='undefined'){ clearTimeout(postActionTimeout); }
postActionTimeout=setTimeout(function(){
setTimeout(function(){
location.reload();
}, 500);
}, 3000);
}
});
return false;
});
});

$('.my-ads-list-item-control.moder_reject').each(function(){
var actioner=$(this);
var id=actioner.data('id');
actioner.click(function(){
$(this).blur();
$('#ad_'+id).next('tr').next('tr').find('.my-ads-list-item-info').removeClass('hidden').css('width', $('#ad_'+id).outerWidth()+'px').css('height', $('#ad_'+id).outerHeight()+$('#ad_'+id).next('tr').outerHeight()+'px');
$.post(langPrefix+'/ajax/moderation/', 'action=reject&aid='+id, function(response){
if(response.status=='success'){
myItemsCount=myItemsCount-1;
$('#ad_'+id).next('tr').next('tr').find('.my-ads-list-item-info').removeClass('hidden').html(response.message);
if(typeof postActionTimeout!=='undefined'){ clearTimeout(postActionTimeout); }
postActionTimeout=setTimeout(function(){
setTimeout(function(){
location.reload();
}, 500);
}, 3000);
}
});
return false;
});
});

$('.dialogs-list-item-control.star').each(function(){
var actioner=$(this);
var id=actioner.data('id');
actioner.click(function(){
$(this).blur();
$.post(langPrefix+'/ajax/my/messages/', 'action=star&id='+id, function(response){
if(response.status=='success'){
$('.dialogs-list-item-control.star[data-id='+id+']').addClass('hidden');
$('.dialogs-list-item-control.unstar[data-id='+id+']').removeClass('hidden');
globalActionsStarring();
}
});
return false;
});
});

$('.page-tabs-control.star').each(function(){
var actioner=$(this);
var id=actioner.data('id');
actioner.click(function(){
$(this).blur();
$.post(langPrefix+'/ajax/my/messages/', 'action=star&id='+id, function(response){
if(response.status=='success'){
$('.page-tabs-control.star[data-id='+id+']').addClass('hidden');
$('.page-tabs-control.unstar[data-id='+id+']').removeClass('hidden');
}
});
return false;
});
});

$('.dialogs-list-item-control.unstar').each(function(){
var actioner=$(this);
var id=actioner.data('id');
actioner.click(function(){
$(this).blur();
$.post(langPrefix+'/ajax/my/messages/', 'action=unstar&id='+id, function(response){
if(response.status=='success'){
$('.dialogs-list-item-control.unstar[data-id='+id+']').addClass('hidden');
$('.dialogs-list-item-control.star[data-id='+id+']').removeClass('hidden');
globalActionsStarring();
}
});
return false;
});
});

$('.page-tabs-control.unstar').each(function(){
var actioner=$(this);
var id=actioner.data('id');
actioner.click(function(){
$(this).blur();
$.post(langPrefix+'/ajax/my/messages/', 'action=unstar&id='+id, function(response){
if(response.status=='success'){
$('.page-tabs-control.unstar[data-id='+id+']').addClass('hidden');
$('.page-tabs-control.star[data-id='+id+']').removeClass('hidden');
}
});
return false;
});
});

$('.dialogs-list-item-control.archive').each(function(){
var actioner=$(this);
var id=actioner.data('id');
actioner.click(function(){
$(this).blur();
$('#dialog_'+id).next('tr').find('.dialogs-list-item-info').removeClass('hidden').css('width', $('#dialog_'+id).outerWidth()+'px').css('height', $('#dialog_'+id).outerHeight()+'px');
$.post(langPrefix+'/ajax/my/messages/', 'action=archive&id='+id, function(response){
if(response.status=='success'){
myItemsCount=myItemsCount-1;
$('#dialog_'+id).next('tr').find('.dialogs-list-item-info').removeClass('hidden').html(response.message);
if(typeof postActionTimeout!=='undefined'){ clearTimeout(postActionTimeout); }
postActionTimeout=setTimeout(function(){
setTimeout(function(){
location.reload();
}, 500);
}, 3000);
}
});
return false;
});
});

$('.page-tabs-control.archive').each(function(){
var actioner=$(this);
var id=actioner.data('id');
actioner.click(function(){
$(this).blur();
$.post(langPrefix+'/ajax/my/messages/', 'action=archive&id='+id, function(response){
if(response.status=='success'){
if($.trim(getParameterByName('ref'))==''){
window.location.href=langPrefix+'/my/messages/';
} else {
window.location.href=getParameterByName('ref');
}
}
});
return false;
});
});

$('.dialogs-list-item-control.restore').each(function(){
var actioner=$(this);
var id=actioner.data('id');
actioner.click(function(){
$(this).blur();
$('#dialog_'+id).next('tr').find('.dialogs-list-item-info').removeClass('hidden').css('width', $('#dialog_'+id).outerWidth()+'px').css('height', $('#dialog_'+id).outerHeight()+'px');
$.post(langPrefix+'/ajax/my/messages/', 'action=restore&id='+id, function(response){
if(response.status=='success'){
myItemsCount=myItemsCount-1;
$('#dialog_'+id).next('tr').find('.dialogs-list-item-info').removeClass('hidden').html(response.message);
if(typeof postActionTimeout!=='undefined'){ clearTimeout(postActionTimeout); }
postActionTimeout=setTimeout(function(){
setTimeout(function(){
location.reload();
}, 500);
}, 3000);
}
});
return false;
});
});

$('.page-tabs-control.restore').each(function(){
var actioner=$(this);
var id=actioner.data('id');
actioner.click(function(){
$(this).blur();
$.post(langPrefix+'/ajax/my/messages/', 'action=restore&id='+id, function(response){
if(response.status=='success'){
if($.trim(getParameterByName('ref'))==''){
window.location.href=langPrefix+'/my/messages/archive/';
} else {
window.location.href=getParameterByName('ref');
}
}
});
return false;
});
});

$('.dialogs-list-item-control.remove').each(function(){
var actioner=$(this);
var id=actioner.data('id');
actioner.click(function(){
$(this).blur();
$('#dialog_'+id).next('tr').find('.dialogs-list-item-info').removeClass('hidden').css('width', $('#dialog_'+id).outerWidth()+'px').css('height', $('#dialog_'+id).outerHeight()+'px');
$.post(langPrefix+'/ajax/my/messages/', 'action=remove&id='+id, function(response){
if(response.status=='success'){
myItemsCount=myItemsCount-1;
$('#dialog_'+id).next('tr').find('.dialogs-list-item-info').removeClass('hidden').html(response.message);
if(typeof postActionTimeout!=='undefined'){ clearTimeout(postActionTimeout); }
postActionTimeout=setTimeout(function(){
setTimeout(function(){
location.reload();
}, 500);
}, 3000);
}
});
return false;
});
});

$('.page-tabs-control.remove').each(function(){
var actioner=$(this);
var id=actioner.data('id');
actioner.click(function(){
$(this).blur();
$.post(langPrefix+'/ajax/my/messages/', 'action=remove&id='+id, function(response){
if(response.status=='success'){
if($.trim(getParameterByName('ref'))==''){
window.location.href=langPrefix+'/my/messages/archive/';
} else {
window.location.href=getParameterByName('ref');
}
}
});
return false;
});
});

$('html').click(function(e){
if(!$(e.target).closest('.search-form-filter').length){
$('.search-form-filter ul.dropdown-menu').addClass('hidden');
}
if(!$(e.target).closest('.search-form-category-selector-container').length){
$('.search-form-category-selector-container').addClass('hidden');
}
});

function initSearchParameters(){

$('.search-form-filter[data-type=select], .search-form-filter[data-type=checkboxes]').each(function(){
var filter=$(this);
var type=filter.data('type');
filter.find('.search-form-filter-input').each(function(){
var input=$(this);
var label=input.find('.filled-label');
var field=input.find('input, select');
var ul=input.find('ul.dropdown-menu');
ul.find('li').each(function(){
var li=$(this);
li.hover(function(){
li.addClass('active');
}, function(){
li.removeClass('active');
});
li.on('click', function(){
var is_selected=li.data('selected');
if(is_selected==false){
li.data('selected', true);
li.find('i.fa-check-square-o').removeClass('hidden');
li.find('i.fa-square-o').addClass('hidden');
} else {
li.data('selected', false);
li.find('i.fa-check-square-o').addClass('hidden');
li.find('i.fa-square-o').removeClass('hidden');
}
var new_values=[];
var new_labels=[];
ul.find('li').each(function(){
if($(this).data('selected')==true){
new_values.push($.trim($(this).data('value')));
new_labels.push($.trim($(this).find('a').text()));
}
});
var new_values_plain=new_values.join(',');
var new_labels_plain=new_labels.join(', ');
field.val(new_values_plain);
label.find('span').html(new_labels_plain);
if(new_values.length>0){
label.removeClass('hidden');
field.addClass('filled-input');
} else {
label.addClass('hidden');
field.removeClass('filled-input');
}
runSearching();
});
});
label.click(function(){
if($(this).closest('.search-form-filter-input').find('ul.dropdown-menu').hasClass('hidden')){
$('.search-form-filter ul.dropdown-menu').addClass('hidden');
$(this).closest('.search-form-filter-input').find('ul.dropdown-menu').removeClass('hidden');
} else {
$(this).closest('.search-form-filter-input').find('ul.dropdown-menu').addClass('hidden');
}
});
label.find('i.fa').click(function(e){
e.stopPropagation();
$('.search-form-filter ul.dropdown-menu').addClass('hidden');
field.removeClass('filled-input');
label.addClass('hidden');
field.val('');
label.find('span').html('');
ul.find('li').find('i.fa-check-square-o').addClass('hidden');
ul.find('li').find('i.fa-square-o').removeClass('hidden');
runSearching();
});
field.focus(function(){
$('.search-form-filter ul.dropdown-menu').addClass('hidden');
$(this).closest('.search-form-filter-input').find('ul.dropdown-menu').removeClass('hidden');
});
});
});

$('.search-form-filter[data-type=input], .search-form-filter[data-type=price], .search-form-filter[data-type=salary]').each(function(){
var filter=$(this);
var type=filter.data('type');
filter.find('.search-form-filter-input').each(function(){
var input=$(this);
var prefix=input.data('prefix');
var suffix=input.data('suffix');
var label=input.find('.filled-label');
var field=input.find('input, select');
var old_value='';
var ul=input.find('ul.dropdown-menu');
ul.find('li').each(function(){
var li=$(this);
li.hover(function(){
li.addClass('active');
}, function(){
li.removeClass('active');
});
li.on('click mousedown mouseup', function(){
var range=li.data('range');
li.closest('div').find('input, select').val(range);
});
});
label.click(function(){
field.removeClass('filled-input');
label.addClass('hidden');
field.focus();
});
label.find('i.fa').click(function(e){
e.stopPropagation();
field.removeClass('filled-input');
label.addClass('hidden');
field.val('');
runSearching();
});
field.focus(function(){
old_value=$.trim(field.val());
$('.search-form-filter ul.dropdown-menu').addClass('hidden');
$(this).closest('.search-form-filter-input').find('ul.dropdown-menu').removeClass('hidden');
});
field.blur(function(){
setTimeout(function(){
var value=$.trim(field.val());
if(value==''){
label.addClass('hidden');
field.removeClass('filled-input');
} else {
label.removeClass('hidden');
label.find('span').html(prefix+' '+value+' '+suffix);
field.addClass('filled-input');
}
field.closest('div').find('ul.dropdown-menu').addClass('hidden');
if(value!=old_value){
runSearching();
}
}, 10);
});
});
});

}

initSearchParameters();

$('.global-action.remove-ad').click(function(){
$('.checked-row+tr .my-ads-list-item-control.remove, .checked-row .my-ads-list-item-control.remove2, .checked-row .my-ads-list-item-control.remove3').click();
$('.my_items_actions_container').addClass('hidden');
$('#select_all:checked').click();
});

$('.global-action.upgrade').click(function(){
var ids=new Array();
$('.checked-row input[name*=selected]').each(function(){
var el=$(this);
ids.push(el.data('id'));
});
location.href='/pay/upgrade/?ids='+(ids.join(','))+'&ref=/my/';
});

$('.global-action.moder_reject').click(function(){
$('.checked-row .my-ads-list-item-control.moder_reject').click();
$('.my_items_actions_container').addClass('hidden');
$('#select_all:checked').click();
});

$('.global-action.moder_complaint_accept').click(function(){
$('.checked-row .my-ads-list-item-control.moder_complaint_accept').click();
$('.my_items_actions_container').addClass('hidden');
$('#select_all:checked').click();
});

$('.global-action.moder_complaint_decline').click(function(){
$('.checked-row .my-ads-list-item-control.moder_complaint_decline').click();
$('.my_items_actions_container').addClass('hidden');
$('#select_all:checked').click();
});

$('.global-action.moder_publish').click(function(){
$('.checked-row .my-ads-list-item-control.moder_publish').click();
$('.my_items_actions_container').addClass('hidden');
$('#select_all:checked').click();
});

$('.global-action.moder_remove').click(function(){
$('.checked-row .my-ads-list-item-control.moder_remove').click();
$('.my_items_actions_container').addClass('hidden');
$('#select_all:checked').click();
});

$('.global-action.moder_activate').click(function(){
$('.checked-row .my-ads-list-item-control.moder_activate').click();
$('.my_items_actions_container').addClass('hidden');
$('#select_all:checked').click();
});

$('.global-action.moder_deactivate').click(function(){
$('.checked-row .my-ads-list-item-control.moder_deactivate').click();
$('.my_items_actions_container').addClass('hidden');
$('#select_all:checked').click();
});

$('.global-action.deactivate').click(function(){
$('.checked-row .my-ads-list-item-control.deactivate').click();
$('.my_items_actions_container').addClass('hidden');
$('#select_all:checked').click();
});

$('.global-action.activate').click(function(){
$('.checked-row .my-ads-list-item-control.activate').click();
$('.my_items_actions_container').addClass('hidden');
$('#select_all:checked').click();
});

$('.global-action.archive').click(function(){
$('.checked-row .dialogs-list-item-control.archive').click();
$('.my_items_actions_container').addClass('hidden');
$('#select_all:checked').click();
});

$('.global-action.restore').click(function(){
$('.checked-row .dialogs-list-item-control.restore').click();
$('.my_items_actions_container').addClass('hidden');
$('#select_all:checked').click();
});

$('.global-action.remove').click(function(){
$('.checked-row .dialogs-list-item-control.remove').click();
$('.my_items_actions_container').addClass('hidden');
$('#select_all:checked').click();
});

$('.global-action.star').click(function(){
$('.checked-row .dialogs-list-item-control.star').click();
$('.my_items_actions_container').addClass('hidden');
$('#select_all:checked').click();
});

$('.global-action.unstar').click(function(){
$('.checked-row .dialogs-list-item-control.unstar').click();
$('.my_items_actions_container').addClass('hidden');
$('#select_all:checked').click();
});

$('#select_all').click(function(){
var length_all=$('input[name*=selected]').length;
var length_checked=$('input[name*=selected]:checked').length;
if(length_checked==length_all){
$('input[name*=selected]').prop('checked', false).trigger('change');
} else if(length_checked>0){
$('input[name*=selected]').prop('checked', false).trigger('change');
} else {
$('input[name*=selected]').prop('checked', true).trigger('change');
}
});

$('input[name*=selected]').each(function(){
var checkbox=$(this);
checkbox.change(function(){
if(this.checked){
$(this).closest('tr').addClass('checked-row');
} else {
$(this).closest('tr').removeClass('checked-row');	
}
var length_all=$('input[name*=selected]').length;
var length_checked=$('input[name*=selected]:checked').length;
if(length_checked==length_all){
$('#select_all').prop('checked', true).closest('.checkbox').removeClass('checkbox-half');
$('.my_items_actions_container').removeClass('hidden');
$('.global-action-hide').addClass('hidden');
globalActionsStarring();
} else if(length_checked>0){
$('#select_all').prop('checked', true).closest('.checkbox').addClass('checkbox-half');
$('.my_items_actions_container').removeClass('hidden');
$('.global-action-hide').addClass('hidden');
globalActionsStarring();
} else {
$('#select_all').prop('checked', false).closest('.checkbox').removeClass('checkbox-half');
$('.my_items_actions_container').addClass('hidden');
$('.global-action-hide').removeClass('hidden');
}
});
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

var temp_city_id='';
var temp_geo_text='';

function initGeoInput(){

$('input#geo').focus(function(){
temp_city_id=$('input#city_id').val();
temp_geo_text=$(this).val();
$(this).val('');
});

$('input#geo').blur(function(){
if($('input#city_id').val()==temp_city_id){
$(this).val(temp_geo_text);
} else {
if(document.getElementById('search_form')){
$('.search-form-geo-container .clear-button').removeClass('hidden');
$('input#geo').addClass('filled-input');
}
}
});

}

initGeoInput();

var temp_query_text='';

function initQueryInput(){

$('.search-form-query-container input#query').focus(function(){
temp_query_text=$.trim($(this).val());
});

$('.search-form-query-container input#query').blur(function(){
if($.trim($(this).val())!=temp_query_text){
temp_query_text=$.trim($(this).val());
if(document.getElementById('search_form')){
$('.search-form-query-container .clear-button').removeClass('hidden');
$(this).addClass('filled-input');
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
$('.index-category-selector-body a').each(function(){
$(this).attr('href', '/list/'+$(this).data('caturl')+'/');
});
$('.index-category-selector-title a').each(function(){
$(this).attr('href', '/list/'+$(this).data('caturl')+'/');
});
Cookies.set('city_id', '0', {expires:365});
$('#search_form_index form').attr('action', '/list/');
updateIndexPageAdsList();
}
});

$('.search-form-query-container .clear-button').click(function(){
$('#query').val('');
$('.search-form-query-container .clear-button').addClass('hidden');
$('input#query').removeClass('filled-input');
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

function globalActionsStarring(){
$('#global-star, #global-unstar').removeClass('hidden');
var stars=$('.checked-row .dialogs-list-item-control.star:visible').length;
var unstars=$('.checked-row .dialogs-list-item-control.unstar:visible').length;
if(stars==0){ $('#global-star').addClass('hidden'); }
if(unstars==0){ $('#global-unstar').addClass('hidden'); }
}

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
if(typeof scbto!=='undefined'){
clearTimeout(scbto);
}
$('#settings-success-message').css('display', 'inline-block').html(message);
scbto=setTimeout(function(){
$('#settings-success-message').fadeOut();
}, 5000);
}

function settingsPasswordCallBack(message){
if(typeof spcbto!=='undefined'){
clearTimeout(spcbto);
}
$('#collapsePassword input[type=password]').val('').blur();
$('#settings-password-success-message').css('display', 'inline-block').html(message);
spcbto=setTimeout(function(){
$('#settings-password-success-message').fadeOut();
}, 5000);
}

function settingsEMailCallBack(){
window.location.href=langPrefix+'/checkmail/?type=email';
}

function settingsNotifyCallBack(message){
if(typeof sncbto!=='undefined'){
clearTimeout(sncbto);
}
$('#settings-notify-success-message').css('display', 'inline-block').html(message);
sncbto=setTimeout(function(){
$('#settings-notify-success-message').fadeOut();
}, 5000);
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

function adminBannersCallBack(){
location.href='/admin/banners/';
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

var selectedCats=[];
var selectedCatsIDs=[];

function selectCat(id, name, level, photos){
selectedCats[(level*1)]=name;
selectedCatsIDs[(level*1)]=id;
selectedCats[(level*1+1)]='';
selectedCatsIDs[(level*1+1)]='';
selectedCats[(level*1+2)]='';
selectedCatsIDs[(level*1+2)]='';
if(document.getElementById('select_cat_'+id)){
$('.category-chooser-main').css('display', 'none');
$('.category-chooser-subselect').css('display', 'block');
$('#select_cat_level_'+level).find('.category-chooser-level-title span').html('&nbsp;');
$('#select_cat_level_3').find('.category-chooser-level-title span').html('&nbsp;');
$('#select_cat_level_'+level).find('.category-chooser-level-title span').html(name);
$('#select_cat_level_'+level).find('.select_cat_items').css('display', 'none');
$('#select_cat_level_3').find('.select_cat_items').css('display', 'none');
$('#select_cat_'+id).css('display', 'block');
$('#select_cat_level_'+(level-1)).find('.selected').removeClass('selected');
$('#select_cat_level_'+level).find('.selected').removeClass('selected');
$('#select_cat_link_'+id).addClass('selected');
$.fancybox.update();
} else {
$.fancybox.close();
var selectedCatsIcon=$('#select_cat_icon_'+selectedCatsIDs[2]+'').attr('src');
var selectedCatsString=selectedCats.filter(Boolean).join(' &raquo; ');
$('#catChooseLink').css('display', 'none');
$('#catChoosed').css('display', 'block');
$('#catChoosed img').attr('src', selectedCatsIcon);
$('#catChoosed b').html(selectedCatsString);
$('#category_id').val(id);
$('#photoUploader .add-photo-button').addClass('hidden');
$('#photoUploader .add-photo-button').slice(uploadedPhotos, photos).removeClass('hidden');
maxPhotos=photos;
$('#parameters').html('');
$.post(langPrefix+'/ajax/add/?category_id='+id, 'action=parameters', function(response){
$('#parameters').html(response);
});
}
}

function listSearchingCats(id, url){
$.post(langPrefix+'/ajax/list/'+((url!='')?url+'/':''), 'action=categories', function(response){
$('.search-form-category-selector').html(response);
$('.search-form-category-selector-arrow').css('right', ($('.search-form-category-select').outerWidth()/2)+'px');
$('.search-form-category-selector-container').removeClass('hidden');
});
}

function selectSearchingCat(id, name, url){
var query=$.trim($('input#query').val());
var user_id=$.trim($('input#user_id').val());
$('#category_id').val(id);
$('#category_url').val(url);
$('input#category').val(name);
$('input#category').addClass('filled-input');
$('.search-form-category-container .clear-button').removeClass('hidden');
$('.search-form-category-selector-container').addClass('hidden');
var city_url=$('#city_url').val();
$.post(langPrefix+'/ajax/'+(city_url!=''?city_url:'list')+'/'+((url!='')?url+'/':'')+'?v'+((query!='')?'&search[query]='+encodeURIComponent(query):'')+((user_id!='')?'&search[user_id]='+encodeURIComponent(user_id):''), 'action=form', function(response){
$('#search-form-container').html(response);
initSearchParameters();
initSearchClears();
initGeoInput();
initQueryInput();
runSearching();
});
}

function updateIndexPageAdsList(){
$.get(langPrefix+'/ajax/', function(response){
$('#index-page-container').html(response);
});
}

function clearFavorites(type){
$.post(langPrefix+'/ajax/favorites/', 'action=clearall'+type, function(response){
location.reload();
});
}
function rotateUploadedPhoto(apid, key){
$.post(langPrefix+'/ajax/upload/?apid='+apid+'&key='+key, 'action=rotate', function(response){
$('#photo_'+key).find('img').attr('src', response.file+'&'+Math.random());
});
}

function deleteUploadedPhoto(apid, key){
$.post(langPrefix+'/ajax/upload/?apid='+apid+'&key='+key, 'action=delete', function(response){
$('#photo_'+key).remove();
$('#photoUploader .add-photo-button').removeClass('first');
$('#photoUploader .add-photo-button:hidden').first().removeClass('hidden');
sortablePhotos($('#apid').val());
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

function sortablePhotos(apid){
var list=document.getElementById("photoUploader");
Sortable.create(list, {
draggable:'.added-photo',
onEnd:function(e){
var photosIDs=[];
$('#photoUploader .added-photo').each(function(){
var photo_id=($(this).attr('id')).replace('photo_', '');
photosIDs.push(photo_id);
});
var keys=photosIDs.join(',');
$.post(langPrefix+'/ajax/upload/?apid='+apid+'&keys='+keys, 'action=sort');
$('#photoUploader .added-photo').removeClass('first');
$('#photoUploader .added-photo').first().addClass('first');
}
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
$('.pay-items-list').each(function(){
var list=$(this);
var aid=list.attr('id').replace('ad_', '');
list.find('.pay-item').each(function(){
var item=$(this);
var id=item.attr('id').replace('service_', '');
if($('#check_service_'+aid+'_'+id).is(':checked')){
var price=item.find('.pay-item-price span').html()*1;
summ=summ+price;
}
});
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
$(window).scrollTop(0);
var page_width=$('body').width();
$('#search-form-loader').css('width', '1px').css('opacity', '1');
$('#search-form-loader').animate({'width':(page_width/100*70)+'px'}, 200);
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
$.each(['private_business', 'order', 'offer_seek', 'currency'], function(index, value){
if($.trim(getParameterByName('search%5B'+value+'%5D'))!=''){
filters.push('search['+value+']='+encodeURIComponent(getParameterByName('search%5B'+value+'%5D')));
}
if($.trim(getParameterByName('search['+value+']'))!=''){
filters.push('search['+value+']='+encodeURIComponent(getParameterByName('search['+value+']')));
}
});
$('#search_form input[name^=search], #search_form select[name^=search]').each(function(){
var el=$(this);
if(el.is(':checkbox')){
if(el.prop('checked')){
filters.push(el.attr('name')+'='+encodeURIComponent($.trim(el.val())));
}
} else {
if($.trim(el.val())!='' && $.trim(el.val())!='0'){
filters.push(el.attr('name')+'='+encodeURIComponent($.trim(el.val())));
}
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
$.post(langPrefix+'/ajax'+url, 'action=form', function(response){
$('#search-form-loader').animate({'width':(page_width/100*85)+'px'}, 200);
$('#search-form-container').html(response);
initSearchParameters();
initSearchClears();
initGeoInput();
initQueryInput();
$.post(langPrefix+'/ajax'+url, 'action=search', function(response){
$('#search-form-loader').stop(true, true).animate({'width':(page_width)+'px'}, 200, function(){ $('#search-form-loader').animate({'opacity':'0'}, 200); });
$('#search-page-container').html(response);
initFavoriteLinks();
try{
history.replaceState({url:window.location.href}, window.title, langPrefix+url);
} catch(e){};
});
});
//window.location.href=removeURLParameter(url, 'page');
}

function runIndexSearching(){
var url_parts=new Array();
var city_url=$('#city_url').val();
if(city_url==''){
url_parts.push('list');
} else {
url_parts.push(city_url);
}
var filters=[];
$('#search_form_index input[name^=search], #search_form_index select[name^=search]').each(function(){
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
window.location.href=langPrefix+url;
}

autosize($('textarea'));