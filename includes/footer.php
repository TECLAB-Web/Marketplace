</div>
</div>
<?php if(trim($_GET['mode'])!='print'){ ?>
<footer id="footer-container" style="padding-top: 100px;">
<div class="wrapper small" id="lastwrapper">
<div class="margintop15 clr rel">
<div class="fleft">
<p><a href="/" class="tdnone" title=""><span class="icon websitegray inlblk vtop">&nbsp;</span></a></p>
</div>
<div class="boxindent">
<div class="clr">				
<div class="static box fleft">
<ul class="small lheight16">
<li class="block"><a id="footerLinkMobileApps" class="link gray" title="<?php echo l('mobile_version_link'); ?>" href="<?php echo 'http://m.'.$_SERVER['HTTP_HOST'].$langPrefix.$_SERVER['REQUEST_URI']; ?>"><span><?php echo l('mobile_version_link'); ?></span></a></li>
<li class="block"><a href="<?php echo $langPrefix; ?>/contact/" class="link gray" title="<?php echo l('footer_contact'); ?>"><span><?php echo l('footer_contact'); ?></span></a></li>
<li class="block"><a href="<?php echo $langPrefix; ?>/cookies/" class="link gray" title="Информация о cookies"><span>Информация о cookies</span></a></li>
</ul>
</div>
<div class="static box fleft">
<ul class="small lheight16">
<li class="block"><a href="<?php echo $langPrefix; ?>/terms/" class="link gray nowrap" title="<?php echo l('footer_terms'); ?>"><span><?php echo l('footer_terms'); ?></span></a></li>
<li class="block"><a href="<?php echo $langPrefix; ?>/list/" title="<?php echo l('footer_list'); ?>" class="link gray"><span><?php echo l('footer_list'); ?></span></a></li>
</ul>
</div>
<div class="footerapps fright rel tcenter">
<a href="/" id="footerAppAndroid" target="_blank" class="inlblk">
<span class="icon block googleplay"> в Google Play</span>
<span class="tag-line tleft hidden2">Скоро в<strong class="block">Google Play</strong></span></a>
<a href="/" id="footerAppIphone" target="_blank" class="inlblk">
<span class="icon block appstore"> в AppStore</span>
<span class="tag-line hidden2">Скоро в<strong class="block">AppStore</strong></span></a>
<a href="/" id="footerAppWin" target="_blank" class="inlblk">
<span class="icon block windowsstore"> в WindowsStore</span>
<span class="tag-line tright hidden2">Скоро в<strong class="block">MicrosoftStore</strong></span></a>
<p class="tag-line">Бесплатное приложение для твоего телефона</p>
</div>
</div>	
</div>
</div>
</footer>
<?php } ?>
<?php if(trim($_GET['mode'])!='print'){ ?>
<div id="captchaWindow" style="display:none;">
<div class="window-title"><?php echo l('captcha_title'); ?><a href="javascript:void(0);" onclick="$.fancybox.close();"></a></div>
<div class="window-message" style="width:480px;">
<span id="captcha_message"></span><?php echo l('captcha_please_type'); ?>
<table style="margin:15px 0 10px 0;">
<tr>
<td>
<img src="/images/blank.gif" id="captcha_image" onclick="$('#captcha_image').attr('src', '/captcha/?'+Math.random()); $('#captcha_input').val('').focus();">
</td>
<td width="20"></td>
<td>
<input type="text" class="form-control" id="captcha_input" placeholder="Введите код здесь" onkeyup="if(event.keyCode==13){ $('#captcha_ok').click(); }">
<a href="javascript:void(0);" id="captcha_reload" onclick="$('#captcha_image').attr('src', '/captcha/?'+Math.random()); $('#captcha_input').val('').focus();"><?php echo l('captcha_reload'); ?></a>
</td>
</tr>
</table>
</div>
<div class="window-buttons">
<button class="btn btn-primary" id="captcha_ok"><?php echo l('captcha_ok'); ?></button>
</div>
</div>
<script src="/js/autosize.js"></script>
<script src="/js/jquery.fancybox.pack.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/bootstrap-hover-dropdown.min.js"></script>
<script src="/js/bootstrap-typeahead.min.js?v=2"></script>
<script src="/js/release.js?v=2"></script>
<script src="/js/jQuery.Brazzers-Carousel.js"></script>
<?php } ?>
</body>
</html>