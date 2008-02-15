<?php

include('config.php');

define(DATA_DIR, 'data/');
define(ROOT, curPageURL().'/'); // tempish i think

define(nl, "\n"); // helpers, REMOVEME
define(br, "<br />".nl);


//----------------------------------
//----------------------------------
function fastimagecopyresampled (&$dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h, $quality = 3) {
  // Plug-and-Play fastimagecopyresampled function replaces much slower imagecopyresampled.
  // Just include this function and change all "imagecopyresampled" references to "fastimagecopyresampled".
  // Typically from 30 to 60 times faster when reducing high resolution images down to thumbnail size using the default quality setting.
  // Author: Tim Eckel - Date: 09/07/07 - Version: 1.1 - Project: FreeRingers.net - Freely distributable - These comments must remain.
  //
  // Optional "quality" parameter (defaults is 3). Fractional values are allowed, for example 1.5. Must be greater than zero.
  // Between 0 and 1 = Fast, but mosaic results, closer to 0 increases the mosaic effect.
  // 1 = Up to 350 times faster. Poor results, looks very similar to imagecopyresized.
  // 2 = Up to 95 times faster.  Images appear a little sharp, some prefer this over a quality of 3.
  // 3 = Up to 60 times faster.  Will give high quality smooth results very close to imagecopyresampled, just faster.
  // 4 = Up to 25 times faster.  Almost identical to imagecopyresampled for most images.
  // 5 = No speedup. Just uses imagecopyresampled, no advantage over imagecopyresampled.

  if (empty($src_image) || empty($dst_image) || $quality <= 0) { return false; }
  if ($quality < 5 && (($dst_w * $quality) < $src_w || ($dst_h * $quality) < $src_h)) {
    $temp = imagecreatetruecolor ($dst_w * $quality + 1, $dst_h * $quality + 1);
    imagecopyresized ($temp, $src_image, 0, 0, $src_x, $src_y, $dst_w * $quality + 1, $dst_h * $quality + 1, $src_w, $src_h);
    imagecopyresampled ($dst_image, $temp, $dst_x, $dst_y, 0, 0, $dst_w, $dst_h, $dst_w * $quality, $dst_h * $quality);
    imagedestroy ($temp);
  } else imagecopyresampled ($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
  return true;
}

//----------------------------------
//----------------------------------
function curPageURL() {
	
	$root = dirname($_SERVER['PHP_SELF']); // dynamically pick up where we are.
	$pageURL = 'http';
	$pageUrl .= $_SERVER["HTTPS"] == "on" ? "s" : ''; // https
	$pageURL .= "://";
	$port = $_SERVER["SERVER_PORT"] == "80" ? '' : $_SERVER["SERVER_PORT"];
	$pageURL .= $_SERVER["SERVER_NAME"].$port.$root;
	return $pageURL;
}

//----------------------------------
//----------------------------------
function parseRequestURI() {
	global $CLEAN_URLS;
	if($CLEAN_URLS && empty($_REQUEST['dir'])) { // bail on dir queryvar, for dual-compatibility
		$path = str_replace(dirname($_SERVER['PHP_SELF']), '', $_SERVER['REQUEST_URI']);
		$path = preg_replace('/^\//', '', $path); // remove preceding slash
		$path .= preg_match('/\/$/', $path) ? '' : '/'; // add trailing slash if necessary
		$directory = ( empty($path) || $path == '/') ? DATA_DIR : DATA_DIR.$path;
	}
	else {
		if($_REQUEST['dir'] == NULL) {
			$directory = DATA_DIR; 
		}
		else {
			$prefix = preg_match('/^data/', $_REQUEST['dir']) ? '' : DATA_DIR; // BACK COMPAT 07/11/22: no longer prefix messy URLs w/ DATA_DIR 
			$directory = $prefix.urldecode(stripslashes($_REQUEST['dir']));
		}
	}
	return $directory;	
}

//----------------------------------
//----------------------------------
// generate a url for a specific thing based on clean URLs or not
// - TODO could be made smarter by building array of input queryvars and outputting as
//   appropriate cleanurl string or as SCRIPT_NAME?join('&',$vars)
function urlFor($type, $what, $directory = "") { 
	global $CLEAN_URLS;
	$our_data = curPageURL().'/';
	switch($type) {
		case 'dir':
			return $CLEAN_URLS ? ROOT.$directory.$what : ROOT.'index.php?dir='.urlencode( $what ); // maintains trailing slash... FIXME?
		case 'original':
			return $our_data.ORIGINALS_DIR.$what;
		case 'web';
			return $our_data.WEB_DIR.$what;
		case 'thumb';
			return $our_data.THUMB_DIR.$what;
		case 'indexThumb';
			return ROOT.'data/'.$directory.$what."thumb/";			
		case 'anchor':
			return curPageURL().'#'.$what;
		default:
			print "ERROR: bad url type '$type' requested".br;
			return 'do-not-comprehenend-homey';
	}	
}

//----------------------------------
//----------------------------------
function checkGoodRequest($directory){
	
	//passing the directory is never a good idea
	//so we check against as many possible hacks as possible.
	
	if( /*!strstr($directory,'data') || substr($directory, 0, 1) != 'd' ||*/ strstr($directory,'.') || strstr($directory,'../') ||  strstr($directory,'../../') || strstr($directory,'/./') || strstr($directory,DATA_DIR.'..') || strstr($directory,'.svn') ){
		echo "you entered an illegal url or your folder contains dodgy charecters - sort it out!";
		exit;
	}
	
	$testDir = explode('data', $directory);
	if(  strstr($testDir[1],'.')){ echo "folders cannot have dots (.) in them";  exit; }
}

//----------------------------------
//----------------------------------
function resizeImage($fileToResize, $destination, $newWidth, $newHeight, $quality, $bResizeOnWidth){

	if( !file_exists($fileToResize) ){
		return "source doesn't exist!";
	}


	// Create an Image from it so we can do the resize
	if( @$src = imagecreatefromjpeg($fileToResize) ){
	        ;
	} else return "problem opening $fileToResize - check it is complete";


	// Capture the original size of the uploaded image
	list($width,$height)=getimagesize($fileToResize);

	$w = 0;
	$h = 0;

	if($bResizeOnWidth == 1 || ($width > $height) ){
		//lets use the newWidth is the target width and calc the correct height for the ratio
		$w 	= $newWidth;
		$ratio 	= $height/$width;
		$h 	= $ratio * $w;
	}else{
		//lets use the newWidth is the target width and calc the correct height for the ratio
		$h 	= $newHeight;
		$ratio 	= $width/$height;
		$w 	= $ratio * $h;
	}

$tmp=imagecreatetruecolor($w,$h);

// this line actually does the image resizing, copying from the original
// image into the $tmp image
fastimagecopyresampled($tmp,$src,0,0,0,0,$w,$h,$width,$height);

// now write the resized image to disk. I have assumed that you want the
// resized, uploaded image file to reside in the ./images subdirectory.
imagejpeg($tmp,$destination, $quality);

imagedestroy($src);
imagedestroy($tmp); // NOTE: PHP will clean up the temp file it created 

}

if( !function_exists('str_split') ){

//Create a string split function for pre PHP5 versions
function str_split($str, $nr) {   
             
     //Return an array with 1 less item then the one we have
     return array_slice(split("-l-", chunk_split($str, $nr, '-l-')), 0, -1);
      
}

}

//----------------------------------
//----------------------------------
function DateCmp($a, $b){
  return ($a[1] > $b[1]) ? -1 : 1;
}

function SortByDate(&$fileIn){
  usort($fileIn, 'DateCmp');
}

//----------------------------------
//----------------------------------
function getFileExtension($str){
	$i = strrpos($str,".");
  if (!$i)
		return "";
	$l = strlen($str) - $i;
	$ext = substr($str,$i+1,$l);
	return $ext;
}

$directory = parseRequestURI();
checkGoodRequest($directory);

//this makes sure that our thumbs directory has LaserTag/thumbs 
//as opposed to LaserTagthumbs
if( substr($directory, -1) != '/'){
	$directory .= '/';
}

//here we get just the name of the current folder
//we remove the 'data/' from the begining
$dirName = substr($directory, 5);

define(ORIGINALS_DIR, $directory); // defines might not be the cleanest method, but globals probably aren't either
define(THUMB_DIR, $directory."thumb/"); 
define(WEB_DIR, $directory."web/");

$debug = $_REQUEST['d'];

$t = $_REQUEST['t'];
$valueList; // <-- wtf?

$total = 0;
$sortDir = false;
$timenow = time();

// read through the directory and filter files to an array
$default_exclusions = array( ORIGINALS_DIR, WEB_DIR, THUMB_DIR, 'web', 'thumb', '.svn' ); // both full & partial for complete coverage, FIXME?
$exclusions = !empty($EXCLUDE_DIRS) ? array_merge($default_exclusions, split(',', $EXCLUDE_DIRS)) : $default_exclusions;
@$d = dir($directory);
if ($d) { 
	while($entry = $d->read()) {  
		
		if($entry == '.' || $entry == '..') continue;
		if( in_array($entry, $exclusions) ) continue; // F THAT DIR
		if( substr($entry, -5) == 'thumb' || substr($entry, -3) == 'web' ) continue; //Super hack to get rid of the LASERTAGDEVthumb folders

		if(is_dir($directory.$entry) ) {
			$dirs[] = array($entry, filemtime($directory.$entry));
			$sortDir = true;
		}

		$ps = getFileExtension(strtolower($entry));
		if ($ps == "jpg") {  
			$items[] = array($entry, filemtime($directory.$entry));
			$sortMe = true;
			$valueList[] = 1;
			$total++;
		}
 
	}
	$d->close();

	if($sortDir)SortByDate($dirs);	//lets sort the directories by newest to oldest
	if($sortMe)SortByDate($items);	//lets sort the images by newest to oldest

	$dirSize = sizeof($dirs);
	for($i = 0; $i < $dirSize; $i++){
		$dirs[$i] = $dirs[$i][0];
		$dirs[$i] .= "/";
	}

	$numImages = sizeof($items);
	for($i = 0; $i < $numImages; $i++){
		$items[$i] = $items[$i][0];
	}


	if( $directory != 'data' && sizeof($items) > 0){

	//check if thumbs directory exists
	if(!file_exists(THUMB_DIR)){
		if($debug)printf("making thumbs directory".br);
		mkdir(THUMB_DIR);
	}

	//check if web directory exists
	if(!file_exists(WEB_DIR)){
		if($debug)printf("making web directory".br);
		mkdir(WEB_DIR);
	}

	
	$count1 = 0;
	$count2 = 0;

	//lets make thumnails
	for($i = 0; $i < sizeof($items); $i++){
		if($count1 > $PROCESS_NUM)break;
		

		if( !file_exists(WEB_DIR.$items[$i]) || !file_exists(THUMB_DIR.$items[$i]) ){

			//make the web image		
			if($debug)printf("making web image for $items[$i]".br);
			resizeImage($directory.$items[$i] , WEB_DIR.$items[$i], 600, 450, 93, 1); 
		
			//make a thumbnail		
			if($debug)printf("making thumnail for $items[$i]".br);
			resizeImage(WEB_DIR.$items[$i], THUMB_DIR.$items[$i], 300, 225, 93, 0); 

			//make our index page thumbnail
			if( $count1 == $PROCESS_NUM || $i == sizeof($items)-1 ){
			
				$previewName = "dir_thumb.jpg";
				$fileToCreate = THUMB_DIR.$previewName;
				
				resizeImage(WEB_DIR.$items[$i], $fileToCreate, 120, 90, 93, 0); 
				if($debug)echo "TRYING TO RESIZE".br.nl;

				$parentFolder = dirname(dirname(dirname($fileToCreate)));
			
				if(file_exists( $fileToCreate ) &&  $parentFolder != 'data' ){
						
					if( !file_exists($parentFolder.'/thumb/') ){
						mkdir($parentFolder.'/thumb/');						
					}
					copy($fileToCreate, $parentFolder.'/thumb/'.$previewName);

				}
			}

			$count1++;
		}
		
	}

	}

}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title><?php generateTitle($directory); ?></title>

	<link href="<?php echo ROOT ?>css/stylesheet.css" rel="stylesheet" type="text/css" media="screen" charset="utf-8" />
	<link href="<?php echo ROOT ?>css/thickbox.css" rel="stylesheet" type="text/css" media="screen" charset="utf-8" />
	
        <script type="text/javascript" charset="utf-8">var tb_pathToImage = "<?php echo ROOT ?>images/loading.gif";</script>
	<script src="<?php echo ROOT ?>js/jquery.js" type="text/javascript" charset="utf-8"></script>
	<script src="<?php echo ROOT ?>js/jquery.thickbox.js" type="text/javascript" charset="utf-8"></script>
	<script src="<?php echo ROOT ?>js/application.js" type="text/javascript" charset="utf-8"></script>

</head>
<body>

<div id="header">
	<!-- anti-yahoo propaganda -->
	<a href="<?php echo ROOT ?>"><img src="<?php print ROOT ?>/images/logos/fflickr_logo_PG_150px.gif" /></a><br />
	<font color='#ff007e'><a href="http://fffff.at/fuckflickr-info">Click here</a> to download fuckflickr and learn more about why we should all be boycotting yahoo products.<br></font>
	<br />

	<!-- regular title -->
	<h1><a href="<?php echo ROOT ?>"><?php echo $NAME; ?> PHOTOS</a></h1>
	<?php if($directory != DATA_DIR) echo ' - Directory: <a href="'.urlFor('dir', str_replace(DATA_DIR, '', $directory)).'">'.str_replace(DATA_DIR, "", $directory).'</a>'; ?>
	<?php if(!empty($CC_LICENSE)) echo " - licensed under a $CC_LICENSE."; ?>
	
	<div id="settings">
		<?php $lightbox_enabled = !empty($_COOKIE['fuckflickr_lightbox']) ? $_COOKIE['fuckflickr_lightbox'] : $LIGHTBOX_DEFAULT; ?>
		<label for="lightbox">lightbox: </label><input type="checkbox" id="lightbox" name="lightbox" value="lightbox" <?php print $lightbox_enabled ? 'checked="checked"' : '' ?> />
	</div> <!-- /#settings -->
</div> <!-- /#header -->

<div id="main">
<?php

$url = curPageURL();

for($i = 0; $i < sizeof($items); $i++){
	
	$link = $url."#".$items[$i];
	$shortName = substr($items[$i],0,-4);

	echo '<div class="thumb-contain" title="'.$shortName.'">'.nl;
	echo '<a name="'.str_replace(' ', '-', $shortName).'" class="title"></a>'.nl;
	echo '<div class="thumb"><a target="_blank" rel="thuggin" title="'.$shortName.'" href="'.urlFor('web', $items[$i]).'"><img src="'.urlFor('thumb', $items[$i]).'" alt="'.$shortName.'" title="'.$shortName.'" border="0" /></a></div>'.nl;
	echo '<div class="info"><a class="short-name" href="#'.$shortName.'">#</a> <a class="hi-res" href="'.urlFor('original', $items[$i]).'">Hi-Res Image</a>'.nl;
	$embed = '<a href="'.urlFor('anchor', $items[$i]).'><img src="'.urlFor('anchor', $items[$i]).'" alt="'.$shortName.'" border="0" /></a>'.nl;
	echo '<span class="embed">embed <input class="embed-code" type="text" size="24" value="'.htmlentities($embed).'" /></span></div>'.nl;
	echo '</div>'.nl.nl;
}

echo br.br;

if(sizeof($dirs) > 2) 
	echo "DIRS:<p>";
echo '<div id="dirs">'.nl;


for($i = 0; $i < sizeof($dirs); $i++)
{	
	echo '<div class="preview" style="background-image:url('.urlFor('indexThumb', $dirs[$i], $dirName).'dir_thumb.jpg)"><!-- --><br><a class="previewLink" href="'.urlFor('dir', $dirs[$i], $dirName).'">&nbsp;'.str_replace( array("/", "_", "-"), array("", " ", " "), $dirs[$i]).'&nbsp;</a></div>';

}
echo '</div> <!-- /#dirs -->'.nl;


?>
</div> <!-- /#main -->

</body>
</html>
