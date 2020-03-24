<!DOCTYPE html>
<html lang="<?php echo $config['lang']; ?>">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php if(trim($_GET['mode'])!='print'){ ?>
<meta name="keywords" content="<?php echo (trim($pagekeywords)!='')?$pagekeywords:$config['keywords']; ?>">
<meta name="description" content="<?php echo $pagedesc; ?>">
<?php } ?>
<title><?php echo $pagetitle; ?></title>
<?php if($is_ad_page && count($ad['photos'])>0){ ?>
<?php $photo=reset($ad['photos']); ?>
<meta property="og:image" content="http://<?php echo $_SERVER['HTTP_HOST']; ?>/image/650x450/<?php echo $photo['apid']; ?>/<?php echo $photo['key']; ?>.jpg?rev=<?php echo $photo['rev']; ?>&contain=1">
<link rel="image_src" href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/image/650x450/<?php echo $photo['apid']; ?>/<?php echo $photo['key']; ?>.jpg?rev=<?php echo $photo['rev']; ?>&contain=1" />
<?php } else { ?>
<meta property="og:image" content="http://<?php echo $_SERVER['HTTP_HOST']; ?>/images/no-photos.png">
<link rel="image_src" href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/images/no-photos.png" />
<?php } ?>
<link rel="shortcut icon" type="image/png" href="/images/favicon.png">
<?php if(count($config['langs'])>1){ ?>
<?php
$_li=0;
foreach($config['langs'] as $_l){
?>
<link rel="stylesheet" href="/css/jQuery.Brazzers-Carousel.css">
<link rel="alternate" href="http://<?php echo $_SERVER['HTTP_HOST']; ?><?php if($_li>0){ echo '/'.$_l; } ?><?php echo $_SERVER['REQUEST_URI']; ?>" hreflang="<?php echo $_l; ?>" />
<?php
$_li++;
}
?>
<?php } ?>
<link href="/css/bootstrap.min.css" rel="stylesheet">
<link href="/css/font-awesome.min.css" rel="stylesheet">
<?php if(trim($_GET['mode'])!='print'){ ?>
<link href="/css/jquery.fancybox.css" rel="stylesheet">
<link href="/css/fileicon.css" rel="stylesheet">
<?php } ?>
<link href="/css/default.css" rel="stylesheet">
<?php if(trim($_GET['mode'])!='print'){ ?>
<?php if($is_ad_page){ ?>
<script src="https://maps.googleapis.com/maps/api/js?sensor=false&language=<?php echo $config['lang']; ?>&key=AIzaSyCoRRjORmzSTpSZFelO_ISeUtzwSVqBvZk"></script>
<?php } ?>
<script src="/js/jquery.min.js"></script>
<script src="/js/jquery.ui.widget.js"></script>
<script src="/js/jquery.iframe-transport.js"></script>
<script src="/js/jquery.fileupload.js"></script>
<script src="/js/jquery.scrollTo.min.js"></script>
<script src="/js/sticky-kit.min.js"></script>
<script src="/js/sortable.min.js"></script>
<script src="/js/js.cookie.js"></script>
<!--<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>-->

<?php } ?>
<script type="text/javascript">
var lang='<?php echo $config['lang']; ?>';
var langPrefix='<?php echo $langPrefix; ?>';
var favoritesCount=<?php echo intval($favorites_count); ?>;
</script>
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<script>
  (adsbygoogle = window.adsbygoogle || []).push({
    google_ad_client: "ca-pub-4760862704241472",
    enable_page_level_ads: true
  });
</script>
</head>
<body>
<?php renderBanner('top'); ?>
<?php if(trim($_GET['mode'])!='print'){ ?>
<div class="top-menu">
<div class="container-non-responsive">
<a class="logo" href="<?php echo $langPrefix; ?>/"></a>

<!--
<div class="pull-left dropdown countries-selecter">
<a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" data-delay="0" data-close-others="false" aria-expanded="false">
<img src="/images/flag_ru.png">Россия <b class="caret"></b>
</a>
<ul class="dropdown-menu">
<li><a tabindex="-1" href=""><img src="/images/flag_ua.png">English</a></li>
<li><a tabindex="-1" href=""><img src="/images/flag_by.png">Russian</a></li>
</ul>
</div>
-->

<div class="top-menu-links">
<div class="pull-right top-adder">
<a href="<?php echo $langPrefix; ?>/add/"><i class="fa fa-plus"></i><?php echo l('header_add_item'); ?></a>
</div>
<?php if(isset($_SESSION['userid'])){ ?>
<div class="pull-right dropdown">
<a href="<?php echo $langPrefix; ?>/my/" class="dropdown-toggle" data-toggle="dropdown" data-delay="0" data-close-others="false">
<div class="user-box__photo"><i class="fa fa-user-circle-o"></i></div><?php echo reset(explode('@', $my['email'])); ?> <b class="caret"></b>
</a>
<ul class="dropdown-menu">
<li><a tabindex="-1" href="<?php echo $langPrefix; ?>/my/"><?php echo l('my_items'); ?></a></li>
<li><a tabindex="-1" href="<?php echo $langPrefix; ?>/my/messages/"><?php echo l('my_messages'); ?></a></li>
<li><a tabindex="-1" href="<?php echo $langPrefix; ?>/my/wallet/"><?php echo l('my_wallet'); ?></a></li>
<li><a tabindex="-1" href="<?php echo $langPrefix; ?>/my/settings/"><?php echo l('my_settings'); ?></a></li>
<li class="divider"></li>
<li><a tabindex="-1" href="<?php echo $langPrefix; ?>/logout/?token=<?php echo $my['logout_token']; ?>"><?php echo l('header_logout'); ?></a></li>
</ul>
</div>
<?php } else { ?>
<div class="pull-right">
<a href="<?php echo $langPrefix; ?>/my/">
<div class="user-box__photo"><i class="fa fa-user-circle-o"></i></div>
<?php echo l('header_my_profile'); ?></a>
</div>
<?php } ?>
<div class="pull-right" id="header_favorites_container">
<a href="<?php echo $langPrefix; ?>/favorites/" title="<?php echo l('header_favorites'); ?>">
<i class="fa <?php if($favorites_count>0){ ?>fa-star<?php } else { ?>fa-star-o<?php } ?>"></i>
</a>
</div>
<?php if(isset($_SESSION['userid'])){ ?>
<?php if($my['unread']>0){ ?>
<div class="pull-right" id="header_unread_messages">
<a href="<?php echo $langPrefix; ?>/my/messages/?type=1" title="<?php echo l('header_new_messages'); ?>">
<i class="fa fa-envelope"></i>
</a>
</div>
<?php } ?>
<?php } ?>
<?php if(isset($_SESSION['userid'])){ ?>
<?php if(intval($my['admin'])==1){ ?>
<?php
$unmoderated_count=intval(mysql_result(mysql_query("SELECT COUNT(*) FROM `ads` WHERE `active`='0';"), 0, 0));
$complaints_count=intval(mysql_result(mysql_query("SELECT COUNT(*) FROM `ads` WHERE `active`='1' AND EXISTS(SELECT * FROM `complaints` WHERE `complaints`.`aid`=`ads`.`aid`);"), 0, 0));
?>
<div class="pull-right">
<a href="<?php if($unmoderated_count==0 && $complaints_count>0){ ?>/moderation/list/complaints/<?php } else { ?>/moderation/<?php } ?>" style="color:#FF6A6A;"><i class="fa fa-check"></i>Модерация<?php if($unmoderated_count+$complaints_count>0){ ?> <b>(<?php echo $unmoderated_count+$complaints_count; ?>)</b><?php } ?></a>
</div>
<div class="pull-right">
<a href="/admin/" style="color:#FF6A6A;"><i class="fa fa-cogs"></i>Администрирование</a>
</div>
<?php } ?>
<?php } ?>


<?php if(count($config['langs'])>1){ ?>
<div class="lang-switcher"><?php
foreach($config['langs'] as $_l){
?><?php if($config['lang']==$_l){ ?><span class="active"><?php echo $config['lang_names'][$_l]; ?></span><?php } else { ?><a href="<?php echo $langPrefix; ?>/changelang/?l=<?php echo $_l; ?>"><?php echo $config['lang_names'][$_l]; ?></a><?php } ?><span class="separator"></span><?php
}
?></div>
<?php } ?>


<div class="clear"></div>
</div>
</div>
</div>
<?php } ?>
<?php if(isset($show_top_tabs)){ ?>
<div class="top-tabs">
<div class="container-non-responsive">
<?php if(isset($top_tabs_title)){ ?>
<div class="top-tabs-title"><?php echo $top_tabs_title; ?></div>
<?php } ?>
<?php if(isset($top_tabs_description)){ ?>
<div class="top-tabs-description"><?php echo $top_tabs_description; ?></div>
<?php } ?>
<?php if(isset($is_main)){ ?>
<div id="search-form-container">
<?php include "includes/search_form_index.php"; ?>
</div>
<?php } ?>
<?php if(isset($is_search)){ ?>
<div id="search-form-container">
<?php include "includes/search_form.php"; ?>
</div>
<?php } ?>
<?php if(isset($is_cabinet)){ ?>
<div class="top-tabs-ul">
<ul class="nav nav-tabs">
<li class="<?php if($current_tab=='ads'){ ?>active<?php } else { ?>not-active<?php } ?>"><a href="<?php echo $langPrefix; ?>/my/"><?php echo l('my_items'); ?></a></li>
<li class="<?php if($current_tab=='messages'){ ?>active<?php } else { ?>not-active<?php } ?>"><a href="<?php echo $langPrefix; ?>/my/messages/"><?php echo l('my_messages'); ?></a></li>
<li class="<?php if($current_tab=='wallet'){ ?>active<?php } else { ?>not-active<?php } ?>"><a href="<?php echo $langPrefix; ?>/my/wallet/"><?php echo l('my_wallet'); ?></a></li>
<li class="<?php if($current_tab=='settings'){ ?>active<?php } else { ?>not-active<?php } ?>"><a href="<?php echo $langPrefix; ?>/my/settings/"><?php echo l('my_settings'); ?></a></li>
</ul>
</div>
<?php } ?>
<?php if(isset($is_favorites)){ ?>
<div class="top-tabs-ul">
<ul class="nav nav-tabs">
<li class="<?php if($current_tab=='ads'){ ?>active<?php } else { ?>not-active<?php } ?>"><a href="<?php echo $langPrefix; ?>/favorites/"><?php echo l('favorites_ads'); ?></a></li>
<li class="<?php if($current_tab=='searches'){ ?>active<?php } else { ?>not-active<?php } ?>"><a href="<?php echo $langPrefix; ?>/favorites/searches/"><?php echo l('favorites_searches'); ?></a></li>
</ul>
</div>
<?php } ?>
<?php if(isset($is_moderation)){ ?>
<div class="top-tabs-ul">
<ul class="nav nav-tabs">
<li class="<?php if($current_tab=='master'){ ?>active<?php } else { ?>not-active<?php } ?>"><a href="/moderation/">Мастер модерации новых объявлений</a></li>
<li class="<?php if($current_tab=='list'){ ?>active<?php } else { ?>not-active<?php } ?>"><a href="/moderation/list/">Расширенное управление объявлениями</a></li>
</ul>
</div>
<?php } ?>
</div>
</div>
<?php if(isset($is_cabinet) && $current_tab=='messages'){ ?>
<div class="page-tabs">
<div class="container-non-responsive">
<table>
<tr>
<td>
<?php foreach($page_tabs as $k=>$v){ ?><?php if($selected_tab==$k){ ?><b class="page-tabs-link"><?php echo $v['tab']; ?></b><?php } else { ?><a href="<?php echo $langPrefix; ?>/my/messages/<?php if($k!='messages'){ echo $k.'/'; } ?>" class="page-tabs-link"><?php echo $v['tab']; ?></a><?php } ?><span class="separator"></span><?php } ?>
</td>
<?php if(trim($_GET['controller'])=='message'){ ?>
<td align="right">
<?php if($selected_tab=='archive'){ ?>
<div class="page-tabs-control remove" data-id="<?php echo $dialog['did']; ?>"><i class="fa fa-trash"></i><span><?php echo l('my_messages_remove_forever'); ?></span></div>
<div class="page-tabs-control restore" data-id="<?php echo $dialog['did']; ?>"><i class="fa fa-history"></i><span><?php echo l('my_messages_restore_from_archive'); ?></span></div>
<?php } else { ?>
<div class="page-tabs-control archive" data-id="<?php echo $dialog['did']; ?>"><i class="fa fa-trash-o"></i><span><?php echo l('my_messages_add_to_archive'); ?></span></div>
<div class="page-tabs-control star<?php if($membering['starred']=='1'){ ?> hidden<?php } ?>" data-id="<?php echo $dialog['did']; ?>"><i class="fa fa-star-o"></i><span><?php echo l('my_messages_add_to_favorites'); ?></span></div>
<div class="page-tabs-control unstar<?php if($membering['starred']=='0'){ ?> hidden<?php } ?>" data-id="<?php echo $dialog['did']; ?>"><i class="fa fa-star-o"></i><span><?php echo l('my_messages_remove_from_favorites'); ?></span></div>
<?php } ?>
<div class="page-tabs-control" onclick="location.href='<?php echo htmlspecialchars($_GET['ref']); ?>';"><i class="fa fa-arrow-left"></i><span><?php echo l('my_messages_back'); ?></span></div>
</td>
<?php } else { ?>
<td align="right" width="25%">
<form action="" method="GET">
<div class="page-tabs-search-box">
<input type="text" name="q" value="<?php echo htmlspecialchars(trim($_GET['q'])); ?>" placeholder="<?php echo l('search'); ?>" class="form-control input-sm" autocomplete="off">
<a href="javascript:void(0);" tabindex="-1" class="clear-button<?php if(trim($_GET['q'])==''){ ?> hidden<?php } ?>" onclick="$(this).closest('form').find('input').val(''); $(this).closest('form').submit();"><i class="fa fa-times"></i></a>
<a href="javascript:void(0);" tabindex="-1" class="search-button<?php if(trim($_GET['q'])==''){ ?> not-active<?php } ?>" onclick="$(this).closest('form').submit();"><i class="fa fa-search"></i></a>
</div>
</form>
</td>
<?php } ?>
</tr>
</table>
</div>
</div>
<?php } ?>
<?php if(isset($is_cabinet) && $current_tab=='ads'){ ?>
<?php if(array_sum($count)>0 || trim($_GET['q'])!=''){ ?>
<div class="page-tabs">
<div class="container-non-responsive">
<table>
<tr>
<td>
<?php foreach($page_tabs as $k=>$v){ ?><?php if($count[$k]>0 || $selected_tab==$k){ ?><?php if($selected_tab==$k){ ?><b class="page-tabs-link"><?php echo $v['tab']; ?><span class="<?php echo $k; ?>-counter<?php if($count[$k]==0){ ?> hidden<?php } ?>"> (<?php echo $count[$k]; ?>)</span></b><?php } else { ?><a href="<?php echo $langPrefix; ?>/my/<?php if($k!='my'){ echo $k.'/'; } ?><?php if(trim($_GET['q'])!=''){ ?>?q=<?php echo urlencode(trim($_GET['q'])); ?><?php } ?>" class="page-tabs-link<?php if($k=='moderated'){ ?> moderated<?php } ?>"><?php echo $v['tab']; ?><span class="<?php echo $k; ?>-counter<?php if($count[$k]==0){ ?> hidden<?php } ?>"> (<?php echo $count[$k]; ?>)</span></a><?php } ?><span class="separator"></span><?php } ?><?php } ?><a href="<?php echo $langPrefix; ?>/add/" class="page-tabs-link"><i class="fa fa-plus" style="margin-right:5px;"></i><?php echo l('my_empty_add_item'); ?></a>
</td>
<td align="right" width="25%">
<form action="" method="GET">
<div class="page-tabs-search-box">
<input type="text" name="q" value="<?php echo htmlspecialchars(trim($_GET['q'])); ?>" placeholder="<?php echo l('search'); ?>" class="form-control input-sm" autocomplete="off">
<a href="javascript:void(0);" tabindex="-1" class="clear-button<?php if(trim($_GET['q'])==''){ ?> hidden<?php } ?>" onclick="$(this).closest('form').find('input').val(''); $(this).closest('form').submit();"><i class="fa fa-times"></i></a>
<a href="javascript:void(0);" tabindex="-1" class="search-button<?php if(trim($_GET['q'])==''){ ?> not-active<?php } ?>" onclick="$(this).closest('form').submit();"><i class="fa fa-search"></i></a>
</div>
</form>
</td>
</tr>
</table>
</div>
</div>
<?php } ?>
<?php } ?>
<?php if(isset($is_moderation) && $current_tab=='list'){ ?>
<div class="page-tabs">
<div class="container-non-responsive">
<table>
<tr>
<td>
<?php foreach($page_tabs as $k=>$v){ ?><?php if($selected_tab==$k){ ?><b class="page-tabs-link"><?php echo $v['tab']; ?><span class="<?php echo $k; ?>-counter"> (<?php echo $count[$k]; ?>)</span></b><?php } else { ?><a href="<?php echo $langPrefix; ?>/moderation/list/<?php if($k!='list'){ echo $k.'/'; } ?><?php if(trim($_GET['q'])!=''){ ?>?q=<?php echo urlencode(trim($_GET['q'])); ?><?php } ?>" class="page-tabs-link"><?php echo $v['tab']; ?><span class="<?php echo $k; ?>-counter"> (<?php echo $count[$k]; ?>)</span></a><?php } ?><span class="separator"></span><?php } ?>
</td>
<td align="right" width="25%">
<form action="" method="GET">
<div class="page-tabs-search-box">
<input type="text" name="q" value="<?php echo htmlspecialchars(trim($_GET['q'])); ?>" placeholder="<?php echo l('search'); ?>" class="form-control input-sm" autocomplete="off">
<a href="javascript:void(0);" tabindex="-1" class="clear-button<?php if(trim($_GET['q'])==''){ ?> hidden<?php } ?>" onclick="$(this).closest('form').find('input').val(''); $(this).closest('form').submit();"><i class="fa fa-times"></i></a>
<a href="javascript:void(0);" tabindex="-1" class="search-button<?php if(trim($_GET['q'])==''){ ?> not-active<?php } ?>" onclick="$(this).closest('form').submit();"><i class="fa fa-search"></i></a>
</div>
</form>
</td>
</tr>
</table>
</div>
</div>
<?php } ?>
<?php } ?>
<?php if(isset($is_search)){ ?>
<div id="search-form-loader"></div>
<?php } ?>
<div class="main-content">
<div class="container-non-responsive<?php if(trim($_GET['mode'])=='print'){ ?> printable-page<?php } ?>">