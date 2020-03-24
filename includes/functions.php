<?php
function human_filesize($bytes, $decimals = 0)
{
	$size = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
	$factor = floor((strlen($bytes) - 1) / 3);
	return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . ' ' . @$size[$factor];
}

function removeThumbnail($file, $maxWidth, $maxHeight, $contain)
{
	$resized = str_replace('.jpg', '_' . ($maxWidth . 'x' . $maxHeight) . '_' . $contain . '.jpg', $file);
	if (file_exists($resized)) {
		unlink($resized);
	}
}

function generateThumbnail($apid, $key, $maxWidth, $maxHeight, $contain)
{
	$check = mysql_query("SELECT * FROM `ad_photos` WHERE `apid`='" . _F($apid) . "' AND `key`='" . _F($key) . "';");
	$photo = mysql_fetch_assoc($check);
	$image = file_get_contents($photo['file']);
	if ($image) {
		$im = new Imagick();
		$im->readImageBlob($image);
		$im->setImageFormat("png24");
		$geo = $im->getImageGeometry();
		$width = $geo['width'];
		$height = $geo['height'];
		if ($width > $height) {
			$scale = ($width > $maxWidth) ? $maxWidth / $width : 1;
		} else {
			$scale = ($height > $maxHeight) ? $maxHeight / $height : 1;
		}
		if (intval($contain) == 1) {
			$newWidth = $scale * $width;
			$newHeight = $scale * $height;
		} else {
			$newWidth = $maxWidth;
			$newHeight = $maxHeight;
		}
		$im->setImageCompressionQuality(85);
		if (intval($contain) == 1) {
			$im->resizeImage($newWidth, $newHeight, Imagick::FILTER_LANCZOS, 1.1);
		} else {
			$im->cropThumbnailImage($maxWidth, $maxHeight);
		}
		$filename = 'uploads/' . date("Y") . '/' . date("m") . '/' . date("d") . '/' . $key . '_' . intval($maxWidth) . 'x' . intval($maxHeight) . '_' . intval($contain) . '.jpg';
		$im->writeImage($filename);
		$im->clear();
		$im->destroy();
	}
}

function Words2AllForms($text)
{
	global $config;
	require_once('includes/morph/src/common.php');

	$opts = array(
		'storage' => PHPMORPHY_STORAGE_MEM,
		'predict_by_suffix' => true,
		'predict_by_db' => true,
		'graminfo_as_text' => true,
	);

	$dir = 'includes/morph/dicts';
	$lang = 'ru_RU';

	try {
		$morphy = new phpMorphy($dir, $lang, $opts);
	} catch (phpMorphy_Exception $e) {
		die('Error occurred while creating phpMorphy instance: ' . PHP_EOL . $e);
	}

	$text = str_replace(array('"', '\'', '.', ',', ':', ';', '(', ')', '-', '!', '?', "\r\n", "\n"), ' ', $text);

	$words = preg_split('/ /', $text, -1, PREG_SPLIT_NO_EMPTY);


	foreach ($words as $v) {
		$v = trim($v);
		if (mb_strlen($v) >= 3) {
			$result = $morphy->getAllForms(mb_strtoupper($v));
			if (count($result) < 2) {
				$result = array();
				$result[] = " " . mb_strtoupper($v) . " ";
			}
			$full .= " " . @implode(" ", $result);
		}
	}

	return trim($full);
}

function Words2BaseForm($text)
{
	global $config;
	require_once('includes/morph/src/common.php');

	$opts = array(
		'storage' => PHPMORPHY_STORAGE_MEM,
		'predict_by_suffix' => true,
		'predict_by_db' => true,
		'graminfo_as_text' => true,
	);

	$dir = 'includes/morph/dicts';
	$lang = 'ru_RU';

	try {
		$morphy = new phpMorphy($dir, $lang, $opts);
	} catch (phpMorphy_Exception $e) {
		die('Error occurred while creating phpMorphy instance: ' . PHP_EOL . $e);
	}

	$words = preg_replace('#\[.*\]#isU', '', $text);
	$words = str_replace('-', ' ', $words);
	$words = preg_split('#\s|[,.:-;/!?"\'()]#', $words, -1, PREG_SPLIT_NO_EMPTY);

	$bulk_words = array();
	foreach ($words as $v)
		if (mb_strlen($v) > 3)
			$bulk_words[] = mb_strtoupper($v);

	$base_form = $morphy->getBaseForm($bulk_words);

	$fullList = array();
	if (is_array($base_form) && count($base_form)) {
		foreach ($base_form as $k => $v) {
			if (is_array($v)) {
				foreach ($v as $v1) {
					if (mb_strlen($v1) > 3) {
						$fullList[$v1] = 1;
					}
				}
			} else {
				$fullList[$k] = $k;
			}
		}
	}

	$words = implode(' ', array_keys($fullList));

	//echo $words; exit;

	if (trim($words) == '') {
		$words = Words2AllForms($text);
	}

	return $words;
}

function liamSupport($to, $subject, $text, $from, $reply_to, $dmuid)
{
	global $config;
	$get_attachments = mysql_query("SELECT * FROM `dialog_message_uploads` WHERE `dmuid`='" . _F($dmuid) . "' ORDER BY `order` ASC;");
	require 'includes/phpmailer/PHPMailerAutoload.php';
	$mail = new PHPMailer;
	$mail->Encoding = "8bit";
	$mail->CharSet = "utf-8";
	$mail->Host = 'localhost';
	$mail->SMTPAuth = false;
	$mail->From = $from;
	$mail->FromName = $config['sitename'];
	$mail->AddReplyTo($reply_to, 'User');
	$mail->addAddress($to);
	$mail->WordWrap = 4096;
	$mail->isHTML(true);
	$mail->Subject = $subject;
	$mail->Body = $text;
	$mail->AltBody = strip_tags($text);
	while ($attachment = mysql_fetch_assoc($get_attachments)) {
		$mail->addAttachment($attachment['file'], $attachment['original']);
	}
	if (!$mail->send()) {
		return false;
	} else {
		return true;
	}
}

function liam($to, $subject, $text, $from)
{
	global $config;
	require 'includes/phpmailer/PHPMailerAutoload.php';
	$mail = new PHPMailer;
	$mail->Encoding = "8bit";
	$mail->CharSet = "utf-8";
	$mail->Host = 'localhost';
	$mail->SMTPAuth = false;
	$mail->From = $from;
	$mail->FromName = $config['sitename'];
	$mail->addAddress($to);
	$mail->WordWrap = 4096;
	$mail->isHTML(true);
	$mail->Subject = $subject;
	$mail->Body = $text;
	$mail->AltBody = strip_tags($text);
	if (!$mail->send()) {
		return false;
	} else {
		return true;
	}
}

function _F($text)
{
	return mysql_real_escape_string(trim($text));
}

function nl2p($string, $line_breaks = false, $xml = true)
{

	$string = str_replace(array('<p>', '</p>', '<br>', '<br />'), '', $string);

// It is conceivable that people might still want single line-breaks
// without breaking into a new paragraph.
	if ($line_breaks == true)
		return '<p>' . preg_replace(array("/([\n]{2,})/i", "/([^>])\n([^<])/i"), array("</p>\n<p>", '$1<br' . ($xml == true ? ' /' : '') . '>$2'), trim($string)) . '</p>';
	else
		return '<p>' . preg_replace(
			array("/([\n]{2,})/i", "/([\r\n]{3,})/i", "/([^>])\n([^<])/i"),
			array("</p>\n<p>", "</p>\n<p>", '$1<br' . ($xml == true ? ' /' : '') . '>$2'),

			trim($string)) . '</p>';
}

function rus2translit($string)
{

	$converter = array(

		'а' => 'a', 'б' => 'b', 'в' => 'v',

		'г' => 'g', 'д' => 'd', 'е' => 'e',

		'ё' => 'e', 'ж' => 'zh', 'з' => 'z',

		'и' => 'i', 'й' => 'y', 'к' => 'k',

		'л' => 'l', 'м' => 'm', 'н' => 'n',

		'о' => 'o', 'п' => 'p', 'р' => 'r',

		'с' => 's', 'т' => 't', 'у' => 'u',

		'ф' => 'f', 'х' => 'h', 'ц' => 'ts',

		'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch',

		'ь' => '', 'ы' => 'y', 'ъ' => '',

		'э' => 'e', 'ю' => 'yu', 'я' => 'ya',


		'А' => 'A', 'Б' => 'B', 'В' => 'V',

		'Г' => 'G', 'Д' => 'D', 'Е' => 'E',

		'Ё' => 'E', 'Ж' => 'Zh', 'З' => 'Z',

		'И' => 'I', 'Й' => 'Y', 'К' => 'K',

		'Л' => 'L', 'М' => 'M', 'Н' => 'N',

		'О' => 'O', 'П' => 'P', 'Р' => 'R',

		'С' => 'S', 'Т' => 'T', 'У' => 'U',

		'Ф' => 'F', 'Х' => 'H', 'Ц' => 'Ts',

		'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sch',

		'Ь' => '', 'Ы' => 'Y', 'Ъ' => '',

		'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',

	);

	return strtr($string, $converter);

}

function str2url($str)
{
	$str = rus2translit($str);
	$str = strtolower($str);
	$str = preg_replace('~[^-a-z0-9_]+~u', '-', $str);
	$str = trim($str, "-");
	$str = implode('-', array_filter(explode('-', trim($str, "-"))));
	return $str;
}

function adurl($ad, $prefix = '')
{
	global $langPrefix;
	return $langPrefix . '/item/' . $prefix . str2url($ad['title']) . '_' . $ad['aid'];
}

function cutString($str, $sCount, $cutParam)
{
	if (mb_strlen($str) > $sCount) {
		$str = mb_substr($str, 0, $sCount) . $cutParam;
	}
	return $str;
}

function declOfNum($pre_titles, $number, $titles)
{
	$cases = array(2, 0, 1, 1, 1, 2);
	return $pre_titles[($number % 100 > 4 && $number % 100 < 20) ? 2 : $cases[min($number % 10, 5)]] . " " . number_format($number, 0, '.', ' ') . " " . $titles[($number % 100 > 4 && $number % 100 < 20) ? 2 : $cases[min($number % 10, 5)]];
}

function tojpg($img)
{
	$img_info = getimagesize($img);
	$width = $img_info[0];
	$height = $img_info[1];
	switch ($img_info[2]) {
		case IMAGETYPE_GIF  :
			$src = imagecreatefromgif($img);
			break;
		case IMAGETYPE_JPEG :
			$src = imagecreatefromjpeg($img);
			break;
		case IMAGETYPE_PNG  :
			$src = imagecreatefrompng($img);
			break;
	}
	$tmp = imagecreatetruecolor($width, $height);
	imagecopyresampled($tmp, $src, 0, 0, 0, 0, $width, $height, $width, $height);
	imagejpeg($tmp, $img);
}

function getCityForms($text)
{
	global $config;
	return $text;
	if ($config['lang'] != 'ru') {
		return $text;
	}
	require_once('includes/morph/src/common.php');

	$opts = array(
		'storage' => PHPMORPHY_STORAGE_MEM,
		'predict_by_suffix' => true,
		'predict_by_db' => true,
		'graminfo_as_text' => true,
	);

	$dir = 'includes/morph/dicts';
	$lang = 'ru_RU';

	try {
		$morphy = new phpMorphy($dir, $lang, $opts);
	} catch (phpMorphy_Exception $e) {
		die('Error occurred while creating phpMorphy instance: ' . PHP_EOL . $e);
	}

	$city_parts = array();
	$words = explode('-', $text);
	foreach ($words as $v) {
		$v = trim($v);
		$vf = $morphy->getAllForms(mb_strtoupper($v));
//var_dump($vf); exit;
		if (!isset($vf[4])) {
			$city_parts[] = mb_convert_case($vf[0], MB_CASE_TITLE, "UTF-8");
		} else {
			$city_parts[] = mb_convert_case($vf[4], MB_CASE_TITLE, "UTF-8");
		}
	}
	return implode('-', $city_parts);
}

function getCoordinates($address)
{
	$check = mysql_query("SELECT * FROM `addresses` WHERE `address`='" . _F($address) . "';");
	if (mysql_num_rows($check)) {
		return mysql_fetch_assoc($check);
	} else {
		$get = file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($address));
		if (trim($get) == '') {
			return false;
		}
		$data = json_decode($get, true);
		$location = $data['results'][0]['geometry']['location'];
		if ($location['lat'] > 0 && $location['lng'] > 0) {
			mysql_query("INSERT INTO `addresses` SET `address`='" . _F($address) . "', `lat`='" . floatval($location['lat']) . "', `lng`='" . floatval($location['lng']) . "';");
			return mysql_fetch_assoc(mysql_query("SELECT * FROM `addresses` WHERE `address`='" . _F($address) . "';"));
		} else {
			return false;
		}
	}
}

function getCategoryURL($cid)
{
	$category_url = array();
	$get_category = mysql_query("SELECT * FROM `categories` WHERE `id`='" . $cid . "';");
	if (mysql_num_rows($get_category)) {
		$category = mysql_fetch_assoc($get_category);
		$category_url[] = $category['url'];
		$get_category = mysql_query("SELECT * FROM `categories` WHERE `id`='" . $category['parent_id'] . "';");
		if (mysql_num_rows($get_category)) {
			$category = mysql_fetch_assoc($get_category);
			$category_url[] = $category['url'];
			$get_category = mysql_query("SELECT * FROM `categories` WHERE `id`='" . $category['parent_id'] . "';");
			if (mysql_num_rows($get_category)) {
				$category = mysql_fetch_assoc($get_category);
				$category_url[] = $category['url'];
			}
		}
	}
	return implode('/', array_reverse($category_url));
}

function getCategoryFullTitle($cid)
{
	global $config;
	$category_title = array();
	$get_category = mysql_query("SELECT * FROM `categories` WHERE `id`='" . $cid . "';");
	if (mysql_num_rows($get_category)) {
		$category = mysql_fetch_assoc($get_category);
		$category_title[] = $category['name_' . $config['lang']];
		$get_category = mysql_query("SELECT * FROM `categories` WHERE `id`='" . $category['parent_id'] . "';");
		if (mysql_num_rows($get_category)) {
			$category = mysql_fetch_assoc($get_category);
			$category_title[] = $category['name_' . $config['lang']];
			$get_category = mysql_query("SELECT * FROM `categories` WHERE `id`='" . $category['parent_id'] . "';");
			if (mysql_num_rows($get_category)) {
				$category = mysql_fetch_assoc($get_category);
				$category_title[] = $category['name_' . $config['lang']];
			}
		}
	}
	return implode('/', array_reverse($category_title));
}

function getAd($aid, $currency = false)
{
	global $config, $currencies, $currency_rates;
	$get_ad = mysql_query("SELECT * FROM `ads` WHERE `aid`='" . intval($aid) . "' AND `active`<'4';");
	if (!mysql_num_rows($get_ad)) {
		return false;
	}
	$_ad = mysql_fetch_assoc($get_ad);
	$_ad['params'] = array();
	$get_ad_parameters = mysql_query("SELECT * FROM `ad_parameters` WHERE `aid`='" . intval($aid) . "';");
	while ($ad_parameter = mysql_fetch_assoc($get_ad_parameters)) {
		$_ad['params'][$ad_parameter['key']] = $ad_parameter['values'];
	}
	$_ad['photos'] = array();
	$get_ad_photos = mysql_query("SELECT * FROM `ad_photos` WHERE `apid`='" . _F($_ad['apid']) . "' ORDER BY `order` ASC;");
	while ($ad_photo = mysql_fetch_assoc($get_ad_photos)) {
		$ad_photo['size'] = getimagesize($ad_photo['file']);
		$_ad['photos'][$ad_photo['key']] = $ad_photo;
	}
	$_ad['user'] = mysql_fetch_assoc(mysql_query("SELECT * FROM `users` WHERE `userid`='" . intval($_ad['userid']) . "';"));
	$_ad['city'] = mysql_fetch_assoc(mysql_query("SELECT * FROM `cities` WHERE `city_id`='" . intval($_ad['city_id']) . "';"));
	$_ad['region'] = mysql_fetch_assoc(mysql_query("SELECT * FROM `regions` WHERE `region_id`='" . intval($_ad['city']['region_id']) . "';"));
	$_ad['geo'] = $_ad['city']['title_' . $config['lang']] . ', ' . $_ad['region']['title_' . $config['lang']];
	$_ad['geo_short'] = $_ad['city']['title_' . $config['lang']];
	$_ad['plain_currency'] = $_ad['currency'];
	if ($currency && $_ad['currency'] != $currency) {
		$_ad['currency'] = $currency;
		if ($_ad['salary_from'] > 0) {
			$_ad['salary_from'] = floor($_ad['salary_from'] / $currency_rates[$_ad['plain_currency']] * $currency_rates[$currency]);
		}
		if ($_ad['salary_to'] > 0) {
			$_ad['salary_to'] = floor($_ad['salary_to'] / $currency_rates[$_ad['plain_currency']] * $currency_rates[$currency]);
		}
		if ($_ad['price'] > 0) {
			$_ad['price'] = floor($_ad['price'] / $currency_rates[$_ad['plain_currency']] * $currency_rates[$currency]);
		}
	}
	if ($_ad['salary_from'] > 0 || $_ad['salary_to'] > 0) {
		if ($_ad['salary_from'] > 0 && $_ad['salary_to'] > 0) {
			$_ad['display_price'] = number_format($_ad['salary_from'], 0, '.', ' ') . " - " . number_format($_ad['salary_to'], 0, '.', ' ') . " " . $currencies[$_ad['currency']];
		} elseif ($_ad['salary_from'] > 0) {
			$_ad['display_price'] = number_format($_ad['salary_from'], 0, '.', ' ') . " " . $currencies[$_ad['currency']];
		} elseif ($_ad['salary_to'] > 0) {
			$_ad['display_price'] = number_format($_ad['salary_to'], 0, '.', ' ') . " " . $currencies[$_ad['currency']];
		}
		if ($_ad['salary_arranged'] == 'arranged') {
			$_ad['arranged_price'] = true;
		}
	} else {
		if ($_ad['price_type'] == 'free') {
			$_ad['display_price'] = l('price_free');
		} elseif ($_ad['price_type'] == 'exchange') {
			$_ad['display_price'] = l('price_exchange');
		} else {
			$_ad['display_price'] = number_format($_ad['price'], 0, '.', ' ') . " " . $currencies[$_ad['currency']];
		}
		if ($_ad['price_type'] == 'arranged') {
			$_ad['arranged_price'] = true;
		}
	}
	if ($_ad['salary_from'] == 0 && $_ad['salary_to'] == 0 && $_ad['price'] == 0 && $_ad['price_type'] == '') {
		$_ad['display_price'] = '&mdash;';
	}
	$_ad['favorites'] = mysql_num_rows(mysql_query("SELECT * FROM `favorites` WHERE `otype`='ad' AND `oid`='" . intval($_ad['aid']) . "';"));
	if (isset($_SESSION['userid'])) {
		$_ad['favorite'] = mysql_num_rows(mysql_query("SELECT * FROM `favorites` WHERE (`userid`='" . $_SESSION['userid'] . "' OR `sid`='" . _F($_COOKIE['PHPSESSID']) . "') AND `otype`='ad' AND `oid`='" . intval($_ad['aid']) . "';"));
	} else {
		$_ad['favorite'] = mysql_num_rows(mysql_query("SELECT * FROM `favorites` WHERE `sid`='" . _F($_COOKIE['PHPSESSID']) . "' AND `otype`='ad' AND `oid`='" . intval($_ad['aid']) . "';"));
	}
	$_ad['coordinates'] = getCoordinates($_ad['geo']);
	return $_ad;
}

function createPayment($order_id, $gid, $userid, $type, $oid, $service, $summ, $till)
{
	global $config, $time;
	mysql_query("INSERT INTO `payments` SET `order_id`='" . intval($order_id) . "', `gid`='" . intval($gid) . "', `userid`='" . intval($userid) . "', `type`='" . _F($type) . "', `oid`='" . intval($oid) . "', `service`='" . _F($service) . "', `summ`='" . floatval($summ) . "', `status`='0', `till`='" . $till . "', `time`='" . $time . "';");
	return mysql_insert_id();
}

function processPayment($payment)
{
	global $config, $time;
	if ($payment['till'] > 0) {
		$payment['till'] = $time - $payment['time'] + $payment['till'];
	}
	mysql_query("UPDATE `payments` SET `status`='1', `till`='" . $payment['till'] . "', `time`='" . $time . "' WHERE `pid`='" . $payment['pid'] . "';");
	if ($payment['type'] == 'upgrade') {
		$ad = getAd(intval($payment['oid']));
		mysql_query("UPDATE `ads` SET `time`='" . $time . "', `time_to`='" . ($time + 60 * 60 * 24 * $ad['days']) . "' WHERE `aid`='" . intval($payment['oid']) . "';");
	}
	return true;
}

function checkActiveService($oid, $service, $type)
{
	global $config, $time;
	$check_payment = mysql_query("SELECT * FROM `payments` WHERE `status`='1' AND `oid`='" . _F($oid) . "' AND `type`='" . _F($type) . "' AND `service`='" . _F($service) . "' AND (`till`='0' OR `till`>='" . $time . "');");
	if (mysql_num_rows($check_payment)) {
		return true;
	} else {
		return false;
	}
}

function l($phrase, $subkey = '')
{
	global $lang, $config;
	if ($subkey == '') {
		$result = $lang[$phrase];
	} else {
		$result = $lang[$phrase][$subkey];
	}
	if (trim($result) == '') {
		$result = $phrase;
		if (trim($subkey) != '') {
			$result .= ':' . $subkey;
		}
		$result = '{$lang[' . $result . ']}';
	}
	$result = str_replace('[SITENAME]', $config['sitename'], $result);
	$result = str_replace('[SITEURL]', $config['siteurl'], $result);
	return $result;
}

function renderBanner($key)
{
	$get_banner = mysql_query("SELECT `html` FROM `banners` WHERE `key`='" . _F($key) . "' AND `active`='1';");
	if (mysql_num_rows($get_banner)) {
		?>
		<div class="banner_<?php echo $key; ?>">
			<?php echo mysql_result($get_banner, 0, 0); ?>
		</div>
		<?php
	}
}

function adminEditParameterCategories($parent, $level)
{
	global $config, $item;
	$get_cats = mysql_query("SELECT * FROM `categories` WHERE `parent_id`='" . intval($parent) . "' AND `active`='1' ORDER BY `sort` ASC;");
	while ($cat = mysql_fetch_assoc($get_cats)) {
		$get_child_cats = mysql_query("SELECT * FROM `categories` WHERE `parent_id`='" . intval($cat['id']) . "' AND `active`='1';");
		?>
		<div style="padding-left:<?php echo $level * 25; ?>px;padding-top:5px;">
			<div class="checkbox" style="padding-top:0;min-height:auto;">
				<input type="checkbox" name="cids[]" value="<?php echo $cat['id']; ?>"
					   id="cid_<?php echo $cat['id']; ?>"<?php if (in_array($cat['id'], explode(',', $item['cids']))) { ?> checked<?php } ?>>
				<label for="cid_<?php echo $cat['id']; ?>">
					<?php echo $cat['name_' . $config['lang']]; ?><?php if (mysql_num_rows($get_child_cats)) { ?> (в поисковой форме)<?php } ?>
				</label>
			</div>
		</div>
		<?php adminEditParameterCategories($cat['id'], $level + 1); ?>
		<?php
	}
}