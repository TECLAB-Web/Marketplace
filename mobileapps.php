<?php include "init.php"; ?>
<?php
$page_tabs=array();
$page_tabs['all']=array('tab'=>l('list_all'));
$page_tabs['private']=array('tab'=>l('list_private'));
$page_tabs['business']=array('tab'=>l('list_business'));
$page_orders=array();
$page_orders['time:desc']=array('tab'=>l('list_time_desc'));
$page_orders['price:asc']=array('tab'=>l('list_price_asc'));
$page_orders['price:desc']=array('tab'=>l('list_price_desc'));
?>
<?php
$pre_order='time:desc';
$current_tab='all';
$selected_currency=reset(array_keys($currencies));
?>
<?php
if(trim($_GET['mode'])!='ajax'){
include "includes/header.php";
?>
<section id="body-container">
	<div class="wrapper">
		<div class="content rel zi2">
			<div class="headinfo tcenter">
				<h2 class="icon">Бесплатное приложение OLX в твоем телефоне</h2>
				<p class="x-large lheight22">Диван, письменный стол, автомобиль, телефон или квартира в аренду - то что вы искали, теперь можно легко найти на вашем телефоне. Хотите что-то продать? Нет ничего проще, просто сфотографируйте ваш товар и опубликуйте объявление в течение нескольких секунд.</p>
			</div>
			<ul class="features">
				<li>
					<span class="icon f1"></span>
					<h3>Добавление объявлений</h3>
					<p>Легкое как никогда. Просто выберите категорию, вставьте короткое описание и вставьте фото с телефона. Готово!</p>
				</li>
				<li>
					<span class="icon f2"></span>
					<h3>Фото</h3>
					<p>Прямо с телефона в ваше объявление. Вам не придется искать кабель, чтобы подключиться к компьютеру.</p>
				</li>
				<li>
					<span class="icon f3"></span>
					<h3>Push-уведомления</h3>
					<p>Получайте уведомления о каждом новом ответе прямо на телефон - даже если приложение неактивно.</p>
				</li>
			</ul>
			<div class="buttons tcenter" style="padding-left: 40px;">
				<a class="button br5 inlblk" href="https://itunes.apple.com/by/app/olx.by-besplatnye-ob-avlenia/id663700789?l=pl&amp;ls=1&amp;mt=8" target="_blank"><span class="icon appstore">OLX.by AppStore</span></a>
				<a class="button br5 inlblk" href="https://play.google.com/store/apps/details?id=by.slando&amp;referrer=utm_source%3Dolx.by%26utm_medium%3Dcpc%26utm_campaign%3Dandroid-app-landing" target="_blank"><span class="icon googleplay">OLX.by GooglePlay</span></a>
                <a class="button br5 inlblk" href="http://windowsphone.com/be-by/store/app/olx-by/84855ed2-00b4-4434-a476-981c3c35a102" target="_blank"><span class="icon windowsstore">OLX.by WindowsStore</span></a>
            </div>
		</div>
	</div>
</section>
<?php
include "includes/footer.php";
}
?>