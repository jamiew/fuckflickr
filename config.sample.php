<?
##############################################################
##    ___              __     ___ __ __        __           ##
##  .'  _|.--.--.----.|  |--.'  _|  |__|.----.|  |--.----.  ##
##  |   _||  |  |  __||    <|   _|  |  ||  __||    <|   _|  ##
##  |__|  |_____|____||__|__|__| |__|__||____||__|__|__|    ##
##############################################################

// name your fflickr install
$NAME = "F.A.T. PHOTOS";

// URL to your fuckflickr system, with trailing slash
// (optional, should autodetect just fine)
$LINK = "http://fffff.at/fuckflickr/";

// this is how many images fuckflickr resizes on one refresh
// 10 is a good amnt - you dont want to hammer your server
// if it is set to 10 and you upload 20 pics then it will 
// take two page refreshes to process all the images
$PROCESS_NUM = 10;

// use clean urls? e.g. /fuckflickr/dir1 instead of /fuckflickr/index.php?dir=data/dir1 
// requires mod_rewrite -- uncomment noted lines in .htaccess as well
$CLEAN_URLS = true;

// we're not going to make you but we like cc licenses vs hardcore copywrite 
$CC_LICENSE = '<a rel="license" href="http://creativecommons.org/licenses/by-nc-sa/3.0/">Creative Commons Attribution-Noncommercial-Share Alike 3.0 License</a>';

// your anti flickr message for the title & footer of each page
$ANTI_FLICKR_MSG = "FUCK FLICKR";

// separator for title
$SEPARATOR = ' &ndash; ';

// do you like lightboxen for viewing? this is user togglable, but set a default
$LIGHTBOX_DEFAULT = true;

// any extra dirs you want to exclude from the index list? comma-separated (no spaces)
// can still view them, they just won't be listed
$EXCLUDE_DIRS = "secret,top_secret";

// image quality
// 	Optional "quality" parameter (defaults is 3). Fractional values are allowed, for example 1.5. Must be greater than zero.
// 	Between 0 and 1 = Fast, but mosaic results, closer to 0 increases the mosaic effect.
// 	1 = Up to 350 times faster. Poor results, looks very similar to imagecopyresized.
// 	2 = Up to 95 times faster.  Images appear a little sharp, some prefer this over a quality of 3.
// 	3 = Up to 60 times faster.  Will give high quality smooth results very close to imagecopyresampled, just faster.
// 	4 = Up to 25 times faster.  Almost identical to imagecopyresampled for most images.
// 	5 = No speedup. Just uses imagecopyresampled, no advantage over imagecopyresampled.
$IMAGE_QUALITY = 3;

// number of photos per page
$IMAGES_PER_PAGE = 25;

// what's yr theme? (keep the trailing slash)
$THEME = 'fuckflickr/';

// enable caching? important if you have lots of photos
$CACHING_ENABLED = true;


// ------------------

define(FF_DATA_DIR, 'data/');
define(FF_DATA_THUMB_DIR, 'thumb/');
define(FF_DATA_WEB_DIR, 'web/');
define(FF_TEMPLATE_DIR, 'themes/');

define(FF_NL, "\n"); // helpers
define(FF_BR, '<br />'. FF_NL);
define(FF_SPACES, '    ');

?>
