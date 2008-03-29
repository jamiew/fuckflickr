<?php
##############################################################
##		___              __     ___ __ __        __           ##
##	.'  _|.--.--.----.|  |--.'  _|  |__|.----.|  |--.----.  ##
##	|   _||  |  |  __||    <|   _|  |  ||  __||    <|   _|  ##
##	|__|  |_____|____||__|__|__| |__|__||____||__|__|__|    ##
##############################################################

include_once('includes/fuckflickr.php');
include_once('includes/config.php');

// FIXME TODO: this is *extremely* heinous
define('FF_NAME', $NAME);
define('FF_ANTI_FLICKR_MSG', $ANTI_FLICKR_MSG);
define('FF_IMG_TYPE', $IMG_TYPE);
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

$fuckflickr = new fuckflickr();
$fuckflickr->processRequest();

// Determine page to use
if (isset($fuckflickr->reqs['photo']) && !empty($fuckflickr->reqs['photo'])) {
	$fuckflickr->viewPhoto();
} elseif ($fuckflickr->dir != FF_DATA_DIR) {
	$fuckflickr->viewSet();
} else {
	$fuckflickr->viewLists();
}

?>