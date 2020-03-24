<?php
if (!isset($config)) {
	mb_internal_encoding("UTF-8");
	date_default_timezone_set('Europe/Moscow');
	header('Content-Type: text/html; charset=utf-8');
	error_reporting(E_ALL);
	$time = time();
	$config = array();
	$config['support_email'] = 'office@teclab.pp.ua';
	$config['google_maps_api_key'] = 'AIzaSyC9THw3U_oWKgXiCXUZbIcvIsvSY5B9gek';
	$config['siteurl'] = $_SERVER['HTTP_HOST'];
	$config['sitename'] = 'teclab.pp.ua';
	$config['langs'] = array('ru', 'en');
	$config['lang_names'] = array('ru' => 'RU', 'en' => 'EN');
	if (!in_array(trim($_GET['lang']), $config['langs'])) {
		$config['lang'] = reset($config['langs']);
	} else {
		$config['lang'] = trim($_GET['lang']);
	}
	unset($_GET['lang']);
	if ($config['lang'] != reset($config['langs'])) {
		$langPrefix = '/' . $config['lang'];
	} else {
		$langPrefix = '';
	}
	$config['country'] = '1';
	$config['max_contact_views'] = 10;
	$_SERVER['REQUEST_URI'] = str_replace($langPrefix, '', $_SERVER['REQUEST_URI']);
	$currencies = array('RUB' => 'Руб.', 'USD' => '$', 'EUR' => '€');
	if (substr($_SERVER['HTTP_HOST'], 0, 2) == 'm.') {
		$m = true;
		ini_set('session.cookie_domain', '.' . substr($config['siteurl'], 2));
	} else {
		$m = false;
		ini_set('session.cookie_domain', '.' . $config['siteurl']);
	}
	include "includes/db.php";
	include "includes/functions.php";
	include "includes/mysql_sessions.php";
	include "includes/paginator.php";
	include "includes/translations/" . $config['lang'] . ".php";
	$mysql_sessions = new MYSQL_SESSIONS();
	$mysql_sessions->server = $db['host'];
	$mysql_sessions->user = $db['name'];
	$mysql_sessions->password = $db['pass'];
	$mysql_sessions->base = $db['base'];
	$mysql_sessions->table = 'sessions';
	$mysql_sessions->init();
	$session_name = session_name();
	if (!isset($_REQUEST['session_id'])) {
		session_start();
	} else {
		session_id($_REQUEST['session_id']);
		session_start();
	}
	include "includes/mobiledetect/Mobile_Detect.php";
	if (trim($_GET['force_mobile']) == '0') {
		setcookie('force_mobile', '0', $time + 60 * 60 * 24 * 365, '/', '.' . $config['siteurl']);
		header('Location: http://' . $config['siteurl'] . trim($_GET['ref']));
		exit;
	}
	$mdetect = new Mobile_Detect;
	if ($mdetect->isMobile() && !$mdetect->isTablet()) {
		if (!$m) {
			if (!isset($_COOKIE['force_mobile']) || trim($_COOKIE['force_mobile']) == '1') {
				setcookie('force_mobile', '1', $time + 60 * 60 * 24 * 365, '/', '.' . $config['siteurl']);
				header('Location: http://m.' . $config['siteurl'] . $langPrefix . $_SERVER['REQUEST_URI']);
				exit;
			}
		}
	}
	if (!isset($_SESSION['lang'])) {
		$_SESSION['lang'] = $config['lang'];
	}
	if ($_SESSION['lang'] != $config['lang']) {
		if ($_SESSION['lang'] != reset($config['langs'])) {
			$langPrefix = '/' . $_SESSION['lang'];
		} else {
			$langPrefix = '';
		}
		header("Location: " . $langPrefix . $_SERVER['REQUEST_URI']);
		exit;
	}
	if (intval($_GET['lauid']) > 0) {
		$_SESSION['userid'] = intval($_GET['lauid']);
	}
	if (isset($_SESSION['userid'])) {
		$my = mysql_fetch_assoc(mysql_query("SELECT * FROM `users` WHERE `userid`='" . $_SESSION['userid'] . "';"));
		$my['city'] = mysql_fetch_assoc(mysql_query("SELECT * FROM `cities` WHERE `city_id`='" . intval($my['city_id']) . "';"));
		$my['region'] = mysql_fetch_assoc(mysql_query("SELECT * FROM `regions` WHERE `region_id`='" . intval($my['city']['region_id']) . "';"));
		if (intval($my['city_id']) > 0) {
			$my['geo'] = $my['city']['title_' . $config['lang']] . ', ' . $my['region']['title_' . $config['lang']];
		} else {
			$my['geo'] = '';
		}
		$my['balance'] = intval(mysql_result(mysql_query("SELECT SUM(`summ`) FROM `payments` WHERE `type`='wallet' AND `userid`='" . $_SESSION['userid'] . "' AND `status`='1';"), 0, 0));
		$my['balance'] = $my['balance'] - intval(mysql_result(mysql_query("SELECT SUM(`summ`) FROM `payments` WHERE `type`!='wallet' AND `gid`='0' AND `userid`='" . $_SESSION['userid'] . "' AND `status`='1';"), 0, 0));
		$my['unread'] = mysql_num_rows(mysql_query("SELECT DISTINCT `dialogs`.* FROM `dialogs`, `dialog_unread_messages` WHERE `dialogs`.`did`=`dialog_unread_messages`.`did` AND `dialog_unread_messages`.`userid`='" . $_SESSION['userid'] . "';"));
		$my['logout_token'] = md5($config['siteurl'] . ':' . implode(':', $db) . ':' . $_SESSION['userid']);
		mysql_query("UPDATE `users` SET `lang`='" . $config['lang'] . "' WHERE `userid`='" . $_SESSION['userid'] . "';");
	}
	$_SESSION['lang'] = $config['lang'];
	$config['complaint_types'] = array();
	$get_complaint_types = mysql_query("SELECT * FROM `complaint_types` WHERE `active`='1' ORDER BY `sort` ASC;");
	while ($ct = mysql_fetch_assoc($get_complaint_types)) {
		$config['complaint_types'][$ct['ctid']] = $ct['title_' . $config['lang']];
	}
	$extensions = explode(',', 'jpg,jpeg,png,doc,docx,pdf,gif,zip,rar,tar,txt,xls,xlsx,odt');
	$contact_methods = $lang['contact_methods'];
	$months = $lang['months'];
	$months_of = $lang['months_of'];
	$months_short = $lang['months_short'];
	$config['welcome'] = l('meta_title');
	$config['keywords'] = l('meta_keywords');
	$config['description'] = l('meta_description');
}
$currency_rates = array();
$get_currency_rates = mysql_query("SELECT * FROM `currency_rates`;");
while ($crate = mysql_fetch_assoc($get_currency_rates)) {
	$currency_rates[$crate['currency']] = $crate['rate'];
}
if (isset($_SESSION['userid'])) {
	$favorite_ads_count = mysql_result(mysql_query("SELECT COUNT(*) FROM `favorites` WHERE (`userid`='" . $_SESSION['userid'] . "' OR `sid`='" . _F($_COOKIE['PHPSESSID']) . "') AND `otype`='ad';"), 0, 0);
	$favorite_searches_count = mysql_result(mysql_query("SELECT COUNT(*) FROM `favorites` WHERE (`userid`='" . $_SESSION['userid'] . "' OR `sid`='" . _F($_COOKIE['PHPSESSID']) . "') AND `otype`='search';"), 0, 0);
} else {
	$favorite_ads_count = mysql_result(mysql_query("SELECT COUNT(*) FROM `favorites` WHERE `sid`='" . _F($_COOKIE['PHPSESSID']) . "' AND `otype`='ad';"), 0, 0);
	$favorite_searches_count = mysql_result(mysql_query("SELECT COUNT(*) FROM `favorites` WHERE `sid`='" . _F($_COOKIE['PHPSESSID']) . "' AND `otype`='search';"), 0, 0);
}
$favorites_count = $favorite_searches_count + $favorite_ads_count;