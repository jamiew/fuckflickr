<?php
// FuckFlickr theme: header.php
// page header, included on every page

// record how long this takes
$time = explode(' ', microtime());
$time = $time[1] + $time[0];
$begintime = $time;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<!--
  ##############################################################
  ##    ___              __     ___ __ __        __           ##
  ##  .'  _|.--.--.----.|  |--.'  _|  |__|.----.|  |--.----.  ##
  ##  |   _||  |  |  __||    <|   _|  |  ||  __||    <|   _|  ##
  ##  |__|  |_____|____||__|__|__| |__|__||____||__|__|__|    ##
  ##############################################################
-->
<head>
	<title><?php $this->pageTitle(); ?></title>

	<link rel="alternate" type="application/rss+xml" title="RSS Feed" href="<?php print $this->urlFor('rss', $this->dir); ?>" />

	<link href="<?php echo $this->dir_tmpl ?>css/stylesheet.css" rel="stylesheet" type="text/css" media="screen" charset="utf-8" />
	<link href="<?php echo $this->dir_tmpl ?>css/thickbox.css" rel="stylesheet" type="text/css" media="screen" charset="utf-8" />
	<link href="<?php echo $this->dir_tmpl ?>css/galleria.css" rel="stylesheet" type="text/css" media="screen" charset="utf-8" />
	<!--[if lt IE 7]><style type="text/css">.preview a { border-color: #000000; }</style><![endif]-->
	
  <script type="text/javascript" charset="utf-8">var tb_pathToImage = "<?php echo $this->dir_tmpl ?>images/loading.gif";</script>
	<script src="<?php echo $this->dir_tmpl ?>js/jquery.js" type="text/javascript" charset="utf-8"></script> <!-- our poison of choice -->
	<script src="<?php echo $this->dir_tmpl ?>js/jquery.thickbox.js" type="text/javascript" charset="utf-8"></script> <!-- lightbox display -->
	<script src="<?php echo $this->dir_tmpl ?>js/jquery.preload-min.js" type="text/javascript" charset="utf-8"></script> <!-- image eagerloading -->
	<script src="<?php echo $this->dir_tmpl ?>js/jquery.galleria.js" type="text/javascript" charset="utf-8"></script> <!-- slideshow mode -->
	<script src="<?php echo $this->dir_tmpl ?>js/flashembed.min.js" type="text/javascript" charset="utf-8"></script> <!-- flowplayer for video -->
	<script src="<?php echo $this->dir_tmpl ?>js/application.js" type="text/javascript" charset="utf-8"></script> <!-- FuckFlickr-specific -->
</head>
<body>
<div id="container">
	<div id="header">

		<!-- have it your way -->
		<div id="settings"><form>
			<script type="text/javascript">console.log('lightbox cookie: <?php (array_key_exists('fuckflickr_lightbox', $_COOKIE)) ? print_r($_COOKIE["fuckflickr_lightbox"]) : '' ?>');</script>
			<?php 
				$lightbox_enabled = (array_key_exists('fuckflickr_lightbox', $_COOKIE) && !empty($_COOKIE['fuckflickr_lightbox']) ? $_COOKIE['fuckflickr_lightbox'] : FF_LIGHTBOX_DEFAULT);
				$checked = ($lightbox_enabled == 'true' ? 'checked="checked"' : '');
			?>
			<label for="lightbox">use lightbox: </label><input type="checkbox" id="lightbox" name="lightbox" value="lightbox" <?php echo $checked ?> /><br />
			<label for="ff_sort">sort by: </label><select id="ff_sort" name="sort">
				<option value="<?php echo $this->urlFor('dir', $this->dir, '', '', 'sort') ?>"<?php if (array_key_exists('sort', $this->reqs) && $this->reqs['sort'] == 'date') echo ' selected' ?>>Recently Added</option>
				<option value="<?php echo $this->urlFor('dir', $this->dir, '', 'sort=name', 'sort') ?>"<?php if (array_key_exists('sort', $this->reqs) && $this->reqs['sort'] == 'name') echo ' selected' ?>>Name</option>
			</select><noscript><input type="submit" value="Sort" /></noscript></form>

			<p>
				<input type="button" id="start_slideshow" name="start_slideshow" value="Start Slideshow" /><br />			
			</p>

		</div> <!-- /#settings -->
		
		<!-- humping graphic -->
		<div id="logo">
			<a href="<?php echo $this->dir_root ?>"><img src="<?php echo $this->dir_tmpl ?>images/fflickr_logo_PG_150px.gif" border="0" style="background-color: #FFFFFF;" /></a>
		</div>
	
		<!-- anti-yahoo propaganda; TODO make configurable -->
		<div id="fuckflickr-info">
			<a href="http://fffff.at/fuckflickr-info">Click here</a> to download FuckFlickr 
			and learn more about why we should all be boycotting Yahoo products.
		</div>
	
	</div> <!-- /#header -->

	<div id="navigation">
		<!-- rss -->
		<a href="<?php print $this->urlFor('rss', $this->dir); ?>" title="RSS feed of new adds" class="rss"><img src="<?php echo $this->dir_tmpl ?>images/feed-icon32x32.png" border="0" /></a>		
		
		<!-- regular title -->
		<a href="http://fffff.at">FFFFF.AT</a> / <a href="<?php echo $this->dir_root ?>">FUCKFLICKR</a>
		<?php 
		// TODO clean this up. could use a directory(), parent(), breadcrumbs(), navigation() etc.
		if ($this->dir != FF_DATA_DIR)
			$parent = str_replace(FF_DATA_DIR, '', $this->dir);
			$built = '';
			foreach (explode('/', str_replace(FF_DATA_DIR, '', $this->dir)) as $dir) {
				if (empty($dir)) continue;
				$path = FF_DATA_DIR . $built . $dir .'/';
				if (empty($this->dir_info[$path . $dir])) $this->readDirInfo($path, $path);
				$url = preg_replace('/\/$/','',$this->urlFor( 'dir', $built . cleanDirname($dir)) ).'/'; // strip possible trailing slash (?) and add again
				echo ' / <a href="'.$url.'">'. ((!empty($this->dir_info[$path]['directory']['title'])) ? $this->dir_info[$path]['directory']['title'] : $dir) .'</a>'; 
				$built .= $dir .'/';
			}

		?>
	</div>

	<div id="main">
