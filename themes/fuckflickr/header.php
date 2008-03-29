<?php
// record how long this takes
$time = explode(' ', microtime());
$time = $time[1] + $time[0];
$begintime = $time;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!--
	##############################################################
	##		___              __     ___ __ __        __           ##
	##	.'  _|.--.--.----.|  |--.'  _|  |__|.----.|  |--.----.  ##
	##	|   _||  |  |  __||    <|   _|  |  ||  __||    <|   _|  ##
	##	|__|  |_____|____||__|__|__| |__|__||____||__|__|__|    ##
	##############################################################
-->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title><?php $this->generateTitle(); ?></title>

	<link href="<?php echo $this->dir_tmpl ?>css/stylesheet.css" rel="stylesheet" type="text/css" media="screen" charset="utf-8" />
	<link href="<?php echo $this->dir_tmpl ?>css/thickbox.css" rel="stylesheet" type="text/css" media="screen" charset="utf-8" />
	
  <script type="text/javascript" charset="utf-8">var tb_pathToImage = "<?php echo $this->dir_tmpl ?>images/loading.gif";</script>
	<script src="<?php echo $this->dir_tmpl ?>js/jquery.js" type="text/javascript" charset="utf-8"></script>
	<script src="<?php echo $this->dir_tmpl ?>js/jquery.thickbox.js" type="text/javascript" charset="utf-8"></script>
	<script src="<?php echo $this->dir_tmpl ?>js/jquery.preload-min.js" type="text/javascript" charset="utf-8"></script>
	<script src="<?php echo $this->dir_tmpl ?>js/application.js" type="text/javascript" charset="utf-8"></script>

</head>
<body>

<div id="header">

	<!-- have it your way -->
	<div id="settings">
		<?php $lightbox_enabled = (!empty($_COOKIE['fuckflickr_lightbox'])) ? $_COOKIE['fuckflickr_lightbox'] : FF_LIGHTBOX_DEFAULT; ?>
		<label for="lightbox">use lightbox: </label><input type="checkbox" id="lightbox" name="lightbox" value="lightbox" <?php echo $lightbox_enabled ? 'checked="checked"' : '' ?> /><br />
		<label for="ff_sort">sort by: </label><select id="ff_sort" name="sort" onchange="javascript: var ff_sort = this.options[this.selectedIndex].value; if (ff_sort != '' && ff_sort != '-1') location.href=ff_sort;">
			<form>
			<option value="<?php echo $this->urlFor('dir', '', $this->dir_name, '', 'sort') ?>"<?php if ($this->reqs['sort'] == 'date') echo ' selected' ?>>Recently Added</option>
			<option value="<?php echo $this->urlFor('dir', '', $this->dir_name, 'sort=name', 'sort') ?>"<?php if ($this->reqs['sort'] == 'name') echo ' selected' ?>>Name</option>
		</select><noscript><input type="submit" value="Sort" /></noscript></form>
	</div> <!-- /#settings -->
		
	<!-- humping graphic -->
	<div id="logo">
		<a href="<?php echo $this->dir_root ?>"><img src="<?php echo $this->dir_tmpl ?>/images/logos/fflickr_logo_PG_150px.gif" border="0" style="background-color: #FFFFFF;" /></a>
	</div>
	
	<!-- anti-yahoo propaganda; TODO make configurable -->
	<div id="fuckflickr-info">
		<a href="http://fffff.at/fuckflickr-info">Click here</a> to download fuckflickr 
		and learn more about why we should all be boycotting yahoo products.
	</div>
	
</div> <!-- /#header -->

<div id="navigation">
	<!-- regular title -->
	<a href="<?php echo $this->dir_root ?>"><?php echo FF_NAME .' '. FF_IMG_TYPE ?></a>
	<?php if ($this->dir != FF_DATA_DIR) echo ' / <a href="'. $this->urlFor('dir', str_replace(FF_DATA_DIR, '', $this->dir)).'">'.str_replace(FF_DATA_DIR, '', $this->dir).'</a>'; ?>
	<!-- jdubs: moved license to footer -->
</div>

<div id="main">