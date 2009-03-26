<?
##############################################################
##    ___              __     ___ __ __        __           ##
##  .'  _|.--.--.----.|  |--.'  _|  |__|.----.|  |--.----.  ##
##  |   _||  |  |  __||    <|   _|  |  ||  __||    <|   _|  ##
##  |__|  |_____|____||__|__|__| |__|__||____||__|__|__|    ##
##############################################################

// name your fflickr install
$NAME = "FuckFlickr";

// URL to your fuckflickr system, with trailing slash
// (optional, should autodetect just fine)
$LINK = "http://fffff.at/fuckflickr/";

// use clean urls? like "/fuckflickr/directory" instead of "/fuckflickr/index.php?dir=directory"
// you must also uncomment the lines in the .htaccess file!
$CLEAN_URLS = true;

// supported filetypes, and how they should be displayed
$FILETYPES = array(
	'image' => array('jpg','jpeg','gif','png','tiff','tga'),
	'video' => array('avi','xvid','mov','m4v','mp4','mpg','mpeg'),
	'audio'	=> array('aiff','wav','mp3', 'ogg', 'flac'),
	'document' => array('txt','rtf','pdf','doc','ppt','xls') // TODO
	);


// we're not going to make you but we like cc licenses vs hardcore copywrite 
$CC_LICENSE = '<a rel="license" href="http://creativecommons.org/licenses/by-nc-sa/3.0/">Creative Commons Attribution-Noncommercial-Share Alike 3.0 License</a>';

// your anti flickr message for the title & footer of each page
$ANTI_FLICKR_MSG = "FUCK FLICKR, YOUTUBE, MUXTAPE";

// separator for title
$SEPARATOR = ' &ndash; ';

// do you like lightboxen for viewing? this is user togglable, but set a default
$LIGHTBOX_DEFAULT = true;

// number of items per page -- 0 means unlimited
$IMAGES_PER_PAGE = 0;

// any dirs you want to exclude from the index list? comma-separated (no spaces)
// can still view them by typing in the URL manually, they just won't be advertised
$EXCLUDE_DIRS = "secret,top_secret";


// this is how many images fuckflickr resizes on one refresh
// 10 is a good amnt - you dont want to hammer your server
// if it is set to 10 and you upload 20 pics then it will 
// take two page refreshes to process all the images
$PROCESS_NUM = 30;

// image quality
// 	Optional "quality" parameter (defaults is 3). Fractional values are allowed, for example 1.5. Must be greater than zero.
// 	Between 0 and 1 = Fast, but mosaic results, closer to 0 increases the mosaic effect.
// 	1 = Up to 350 times faster. Poor results, looks very similar to imagecopyresized.
// 	2 = Up to 95 times faster.  Images appear a little sharp, some prefer this over a quality of 3.
// 	3 = Up to 60 times faster.  Will give high quality smooth results very close to imagecopyresampled, just faster.
// 	4 = Up to 25 times faster.  Almost identical to imagecopyresampled for most images.
// 	5 = No speedup. Just uses imagecopyresampled, no advantage over imagecopyresampled.
$IMAGE_QUALITY = 4;

// enable caching? important if you have lots of photos
// note: only applies to RSS feeds currently
$CACHING_ENABLED = true;


// ------------------
// power users might want to modify our standard directory structure
define(FF_DATA_DIR, 'data/');
define(FF_DATA_THUMB_DIR, 'thumb/');
define(FF_DATA_WEB_DIR, 'web/');
define(FF_TEMPLATE_DIR, 'theme/');

// output helpers TODO MOVEME
define(nl, "\n");
define(br, '<br />'. nl);
define(FF_SPACES, '    ');


?>
