<?php include "init.php"; ?>
<?php
if(trim($_POST['action'])=='m-adding-city-select'){
$get_cities=mysql_query("SELECT * FROM `cities` WHERE `region_id`='".intval($_GET['region_id'])."' ORDER BY `important` DESC, `title_".$config['lang']."` ASC;");
if(mysql_num_rows($get_cities)){
?>
<select size="1" name="city_id" class="form-control" style="margin-bottom:5px;">
<option value=""><?php echo l('add_select_param_value'); ?></option>
<?php
while($city=mysql_fetch_assoc($get_cities)){
?>
<option value="<?php echo $city['city_id']; ?>"><?php echo htmlspecialchars($city['title_'.$config['lang']]); ?></option>
<?php
}
?>
</select>
<?php
}
}
if(trim($_POST['action'])=='m-adding-category-select'){
if(intval($_GET['category_id'])>0){
$get_cats=mysql_query("SELECT * FROM `categories` WHERE `parent_id`='".intval($_GET['category_id'])."' AND `active`='1' ORDER BY `sort` ASC;");
if(mysql_num_rows($get_cats)){
$cat=mysql_fetch_assoc(mysql_query("SELECT * FROM `categories` WHERE `id`='".intval($_GET['category_id'])."';"));
?>
<select size="1" name="category_id" class="form-control" style="margin-bottom:5px;" onchange="selectCat(this.value, <?php echo $cat['max_photos']; ?>, $(this));">
<option value=""><?php echo l('add_select_param_value'); ?></option>
<?php
while($cat=mysql_fetch_assoc($get_cats)){
?>
<option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name_'.$config['lang']]); ?></option>
<?php
}
?>
</select>
<?php
}
}
} elseif(trim($_GET['controller'])=='robots'){
header('Content-Type: text/plain; charset=utf-8');
?>
Host: <?php echo $config['siteurl']; ?> 
User-agent: *
Disallow: /ajax/
Disallow: /my/
Disallow: /pay/
Disallow: /favorites/
Disallow: /edit/
Disallow: /logout/
Disallow: /deleted/
Disallow: /checkmail/
Disallow: /activate/
Disallow: /captcha/
Disallow: /success/
Disallow: /changelang/
Disallow: */rss/
Allow: /
<?php
exit;
}
?>