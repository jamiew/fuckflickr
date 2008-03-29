<?php
##############################################################
##		___              __     ___ __ __        __           ##
##	.'  _|.--.--.----.|  |--.'  _|  |__|.----.|  |--.----.  ##
##	|   _||  |  |  __||    <|   _|  |  ||  __||    <|   _|  ##
##	|__|  |_____|____||__|__|__| |__|__||____||__|__|__|    ##
##############################################################

include_once('includes/fuckflickr.php');
include_once('config.php');

// redeclare/sanitize configuration vars
// FIXME TODO: this is *extremely* heinous
// we should choose globals or define
define('FF_NAME', $NAME);
define('FF_ANTI_FLICKR_MSG', $ANTI_FLICKR_MSG);
define('FF_SEPARATOR', $SEPARATOR);
define('FF_DEFAULT_DIR_NAME', $DEFAULT_DIR_NAME);
define('FF_LINK', $LINK);
define('FF_PROCESS_NUM', $PROCESS_NUM);
define('FF_CLEAN_URLS', $CLEAN_URLS);
define('FF_CC_LICENSE', $CC_LICENSE);
define('FF_LIGHTBOX_DEFAULT', $LIGHTBOX_DEFAULT);
define('FF_EXCLUDE_DIRS', $EXCLUDE_DIRS);
define('FF_INDEX_THUMB_NAME', 'dir_image.jpg');
define('FF_DIR_INFO_FILENAME', 'info.yml');
define('FF_IMG_QUALITY', ($IMAGE_QUALITY > 0 && $IMAGE_QUALITY <= 5) ? $IMAGE_QUALITY : 3);
define('FF_PER_PAGE', (is_numeric($IMAGES_PAGE) && $IMAGES_PAGE > 0) ? floor($IMAGES_PAGE) : 0);
define('FF_USE_TEMPLATE', (!empty($TEMPLATE)) ? $TEMPLATE . ((substr($TEMPLATE, -1, 1) != '/') ? '/' : '') : 'fuckflickr/');

// initialize environment
$fuckflickr = new fuckflickr();

// main dispatcher: based on parsed request, decide what to do
if( isset($fuckflickr->reqs['photo']) && !empty($fuckflickr->reqs['photo']) ) {
	$fuckflickr->viewPhoto();
} elseif( !empty($fuckflickr->reqs['rss']) ) {
	$fuckflickr->viewRSS();
} elseif( $fuckflickr->dir != FF_DATA_DIR ) {
	$fuckflickr->viewList();
} else { // index page is default action; also, dir will = 
	$fuckflickr->viewIndex();
}

?>