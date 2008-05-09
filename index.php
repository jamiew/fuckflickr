<?php
##############################################################
##    ___              __     ___ __ __        __           ##
##  .'  _|.--.--.----.|  |--.'  _|  |__|.----.|  |--.----.  ##
##  |   _||  |  |  __||    <|   _|  |  ||  __||    <|   _|  ##
##  |__|  |_____|____||__|__|__| |__|__||____||__|__|__|    ##
##############################################################

include_once('includes/imageresize.php');
include_once('includes/fuckflickr.php');
include_once('config.php');

// redeclare/sanitize configuration vars
// FIXME TODO: this is *extremely* heinous
// need to just choose globals or defines
define('FF_NAME', $NAME);
define('FF_ANTI_FLICKR_MSG', $ANTI_FLICKR_MSG);
define('FF_SEPARATOR', $SEPARATOR);
define('FF_LINK', $LINK);
define('FF_PROCESS_NUM', $PROCESS_NUM);
define('FF_CLEAN_URLS', $CLEAN_URLS);
define('FF_CC_LICENSE', $CC_LICENSE);
define('FF_LIGHTBOX_DEFAULT', $LIGHTBOX_DEFAULT);
define('FF_EXCLUDE_DIRS', $EXCLUDE_DIRS);
define('FF_INDEX_THUMB_NAME', 'dir_thumb.jpg');
define('FF_DIR_INFO_FILENAME', 'info.yml');
define('FF_IMG_QUALITY', ($IMAGE_QUALITY > 0 && $IMAGE_QUALITY <= 5) ? $IMAGE_QUALITY : 3);
define('FF_PER_PAGE', (is_numeric($IMAGES_PER_PAGE) && $IMAGES_PER_PAGE > 0) ? floor($IMAGES_PER_PAGE) : 0);
define('FF_RSS_ITEM_COUNT', 15);
define('FF_USE_TEMPLATE', (!empty($THEME)) ? $THEME . ((substr($THEME, -1, 1) != '/') ? '/' : '') : 'fuckflickr/');
define('FF_CACHING_ENABLED', $CACHING_ENABLED);

define(FF_NL, "\n"); // helpers
define(FF_BR, '<br />'. FF_NL);
define(FF_SPACES, '    ');


// initialize environment
$fuckflickr = new fuckflickr();

// simple page caching, if enabled
// FIXME only cache RSS for now, need to test the rest more
$use_cache = empty($fuckflickr->reqs['rss']) ? false : true;
if(FF_CACHING_ENABLED && $use_cache) {
	$cachedir = dirname(__FILE__).'/cache/';
	@mkdir($cachedir); // create if it doesn't exist
	$cachetime = 900; // seconds; TODO FIXME configme
	$cacheext = 'cache'; // extension to give cached files

	$page = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$cachefile = $cachedir . md5($page) . '.' . $cacheext;
	$cachefile_created = (@file_exists($cachefile) && $use_cache) ? @filemtime($cachefile) : 0;
	clearstatcache();

	// show file from cache if still valid
	if (time() - $cachetime < $cachefile_created) {
		//ob_start('ob_gzhandler');
		readfile($cachefile);
		//ob_end_flush();
		exit();
	}
}

// invalidated cache; render & recache
ob_start();

// based on parsed request, decide what to do
if( isset($fuckflickr->reqs['photo']) && !empty($fuckflickr->reqs['photo']) ) {
	$fuckflickr->viewPhoto();
} elseif( !empty($fuckflickr->reqs['rss']) ) { /* FIXME will allow for any /rss URL, which doesn't really work */
	$fuckflickr->viewRSS();
} else { // index page is default action; also, dir will = 
	$fuckflickr->viewList();
}
		
// cache output, unless the magic bit has been set during rendering
if(FF_CACHING_ENABLED && $use_cache && !$DISABLE_CACHE) {
	$fp = fopen($cachefile, 'w');
	fwrite($fp, ob_get_contents());
	fclose($fp);		
	ob_end_flush();
}

?>
