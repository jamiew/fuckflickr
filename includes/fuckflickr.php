<?php
##############################################################
##		___              __     ___ __ __        __           ##
##	.'  _|.--.--.----.|  |--.'  _|  |__|.----.|  |--.----.  ##
##	|   _||  |  |  __||    <|   _|  |  ||  __||    <|   _|  ##
##	|__|  |_____|____||__|__|__| |__|__||____||__|__|__|    ##
##############################################################

class fuckflickr extends imageResize {
	var $reqs = array();
	var $ff_items = array();
	var $ff_dirs = array();
	var $ff_vals = array();

	/*
	* constructor & dispatcher
	*/
	function fuckflickr() {
	
		// figure out what's going on
		$this->dir_root = $this->findURL() .'/';
		$this->dir_incl = $this->dir_root .'includes/';
		$this->dir_fs_tmpl = FF_TEMPLATE_DIR . FF_USE_TEMPLATE;
		$this->dir_tmpl = $this->dir_root . FF_TEMPLATE_DIR . FF_USE_TEMPLATE;
		$this->sortDir = true;
		$this->ff_total = 0;
		$this->timenow = time();

		// kick off this mother
		$this->processRequest();
		$this->readDir(); // read this dir
		$this->processImages(); // parse imgz
		foreach(array_merge( array($this->dir_name), $this->ff_dirs) as $dir) {
			// $this->readDirInfo($this->dir_name, $this->dir);
			$this->readDirInfo($dir, FF_DATA_DIR.$dir);
		}

	}

	/*
	*	actions to render
	* TODO probably don't even need these anymore
	*/
	function viewIndex($file = 'index.php') {
		$this->openTemplate($file);
	}

	function viewList($file = 'list.php') {
		$this->openTemplate($file);
	}

	function viewPhoto($file = 'photo.php') {
		$this->openTemplate($file);
	}
	
	function viewRSS() { // not templatable (for now?)
		include('rss.php');
	}
	

	/*
	*	parse the request URI and/or POST/GET vars
	*/
	function processRequest() {
		$this->parseRequest();
		$this->cleanRequest();

		$this->debug = (isset($this->reqs['d']));
		if ($this->debug) echo '<strong>Entering debug mode.</strong>'. FF_BR;

		$this->dir_name = $this->makeDirName($this->dir);
		$this->dir_origs = $this->dir;
		$this->dir_thumbs = $this->dir .'thumb/'; 
		$this->dir_web = $this->dir .'web/';
		$this->cur_page = ((is_numeric($this->reqs['page']) && $this->reqs['page'] > 0 || $this->reqs['page'] == 'all') ? floor($this->reqs['page']) : 1);
		$this->sortByDate = ($this->reqs['sort'] == 'name') ? false : true; // sort by the date uploaded, otherwise sort by filename (if sorting is enabled)
		$this->exclude = array('.', '..', $this->dir_origs, $this->web_dir, $this->thumb_dir, 'web', 'thumb', '.svn');
		$this->exclude = array_merge($this->exclude, split(',', FF_EXCLUDE_DIRS)); // Combine with config'd excludes
	}
	
	// ...
	function makeDirName($dir) {
		return substr($dir, 5);
	}
	
	// process the URL into sweet, sweet information
	function parseRequest() {
		if (FF_CLEAN_URLS && empty($_REQUEST['dir'])) { // bail on dir queryvar, for dual-compatibility
			$path = str_replace(dirname($_SERVER['PHP_SELF']), '', $_SERVER['REQUEST_URI']);
			$path = preg_replace('/^\//', '', $path); // remove preceding slash
			$path = preg_replace('/\?'. $_SERVER['QUERY_STRING'] .'/', '', $path); // Remove any phony GET queries
			$paths = explode('/', $path);

			$dir = (!empty($paths[0]) && is_dir(FF_DATA_DIR . $paths[0])) ? FF_DATA_DIR . $paths[0] : FF_DATA_DIR;
			$dir .= (preg_match('/\/$/', $dir)) ? '' : '/'; // add trailing slash if necessary

			// Create single array for requests
			if(is_dir(FF_DATA_DIR . $paths[0])) array_shift($paths);
			if(sizeof($paths) > 0) {
				for($i=0; $i<sizeof($paths); $i+=2) {
					if (!empty($paths[$i])) $this->reqs[$paths[$i]] = (isset($paths[$i+1])) ? $paths[$i+1] : true; // at least make true if set (ex. /d for debugging)
				}
			}
		} else {
			if (!empty($_REQUEST['dir'])) {
				$prefix = preg_match('/^data/', $_REQUEST['dir']) ? '' : FF_DATA_DIR; // BACK COMPAT 07/11/22: no longer prefix messy URLs w/ DATA_DIR
				$dir = $prefix . urldecode(stripslashes($_REQUEST['dir']));
			} else {
				$dir = FF_DATA_DIR;
			}

			// Create single array for requests
			if (!empty($_SERVER['QUERY_STRING'])) {
				$args = explode('&', $_SERVER['QUERY_STRING']);
				if (sizeof($args) > 0) {
					for ($i=0; $i<sizeof($args); $i++) {
						list($k, $v) = explode('=', $args[$i]);
						$this->reqs[$k] = $v;
					}
				}
			}
		}
		
		$this->dir = $dir;
	}

	/*
	* passing the directory in the URL is never a good idea
	* so we check against as many possible hacks as possible.
	**/
	function cleanRequest() {

		// common hax, hidden dirs, move dir cmds, etc.
		if (strstr($this->dir,'.') || strstr($this->dir,'../') ||  strstr($this->dir,'../../') || strstr($this->dir,'/./') || strstr($this->dir, FF_DATA_DIR.'..') || strstr($this->dir,'.svn') ){
			echo 'you entered an cheating url or your folder contains shady characters - change \'em out!';
			exit;
		}
	
		// don't allow *anything* w/ a dot
		$testDir = explode('data', $this->dir);
		if (strstr($testDir[1],'.')) {
			echo "folders cannot have dots (.) in them";
			exit;
		}

		// make sure dir has trailing slash
		if (substr($this->dir, -1) != '/') $this->dir .= '/';
	}


	/*
	*	resize all unresized images
	*/	
	function processImages() {
		if ($this->dir != 'data' && sizeof($this->ff_items) > 0){
			// check if directory is writable [halvfet]
			if (!is_writable($this->dir)) {
				if ($this->debug) echo 'making directory writable'. FF_BR;
				chmod($this->dir, 0777);
			}

			// check if thumbs directory exists
			if (!file_exists($this->dir_thumbs)) {
				if ($this->debug) echo 'making thumbs directory'. FF_BR;
				mkdir($this->dir_thumbs, 0777);
			}

			// check if web directory exists
			if (!file_exists($this->dir_web)){
				if ($this->debug) echo 'making web directory'. FF_BR;
				mkdir($this->dir_web, 0777);
			}
	
			$ct1 = 0;

			// lets make thumbnails
			for ($i=0; $i<sizeof($this->ff_items); $i++){
				if ($ct1 >= FF_PROCESS_NUM) break; // Don't blow a gasket

				if (!file_exists($this->dir_web . $this->ff_items[$i]) || !file_exists($this->dir_thumbs . $this->ff_items[$i]) ) {

					// if failed the first time (web), it should fail the second time (thumb). [halvfet]
					if($this->debug) echo 'making web image for '. $this->ff_items[$i] . FF_BR;
					if ($this->resizeImage($this->dir . $this->ff_items[$i], $this->dir_web . $this->ff_items[$i], 600, 450, 93, 1)) {

						// make a thumbnail
						if ($this->debug) echo 'making thumbnail for '. $this->ff_items[$i]. FF_BR;
						if ($this->resizeImage($this->dir_web . $this->ff_items[$i], $this->dir_thumbs . $this->ff_items[$i], 300, 225, 93, 0)) {

							// make our index page thumbnail
							if ($ct1 == FF_PROCESS_NUM || $i == sizeof($this->ff_items)-1){
								if ($this->debug) echo 'making index thumbnail for '. $this->dir . FF_BR;
								if ($this->resizeImage($this->dir_web . $this->ff_items[$i], $this->dir_thumbs . FF_INDEX_THUMB_NAME, 120, 90, 93, 0)) {
									$pf = dirname(dirname(dirname($this->dir_thumbs . FF_INDEX_THUMB_NAME)));
									if (file_exists($this->dir_thumbs . FF_INDEX_THUMB_NAME) && $pf != 'data' && !file_exists($pf .'/thumb/')) mkdir($pf .'/thumb/');
									@copy($this->dir_thumbs . FF_INDEX_THUMB_NAME, $pf .'/thumb/'. FF_INDEX_THUMB_NAME);
								} elseif ($this->debug) {
									echo '<strong>FAILED</strong> making index thumbnail image for '. $this->dir . FF_BR;
								}
							}
						} elseif ($this->debug) {
							echo '<strong>FAILED</strong> making thumb image for '. $this->ff_items[$i] . FF_BR;
						}
					} elseif ($this->debug) {
						echo '<strong>FAILED</strong> making web image for '. $this->ff_items[$i] . FF_BR;
					}
					$ct1++;
				}
			}
		}		
	}	
	
	/*
	* start rendering out, including header/footer as available
	* TODO: possibly allow rendering w/o a template, for non-standard output like AJAX/RSS/etc
	*/
	function openTemplate($file) {
		if (is_file($this->dir_fs_tmpl .'header.php') && FF_USE_TEMPLATE) include($this->dir_fs_tmpl .'header.php');

		if (is_dir($this->dir_fs_tmpl)) {
			if (is_file($this->dir_fs_tmpl . $file)) {
				include($this->dir_fs_tmpl . $file);
			} else {
				echo 'you ain\'t  got a page to tag, toy. set your page properly for '. $this->dir_tmpl . $file;
			}
		} else {
			echo 'you need a place to hang your shit. add a fuckflickr template.';
		}

		if (is_file($this->dir_fs_tmpl .'footer.php') && FF_USE_TEMPLATE) include($this->dir_fs_tmpl .'footer.php');
	}
	
	/*
	* generate URLs for internal routes
	* clean or messy, as you like it
	* TODO untested w/ messy URLs w/ all halvfet additions
	*/
	function urlFor($type, $what, $dir='', $etc='', $excl=false) {
		$what = str_replace(FF_DATA_DIR, '', $what);
		switch ($type) {
			case 'dir':
				return (FF_CLEAN_URLS) ? $this->dir_root . $dir . $what . $this->makeReqLinks($excl, ((!empty($etc)) ? ','. $etc : '')) : $this->dir_root .'index.php'. $this->makeReqLinks($excl, 'dir='.urlencode($what) . ((!empty($etc)) ? ','. $etc : ''));
				break;
			case 'page':
				return (FF_CLEAN_URLS) ? $this->dir_root . $dir . $what . $this->makeReqLinks('page', ((!empty($etc)) ? ','. $etc : '')) : $this->dir_root .'index.php'. $this->makeReqLinks(false, 'dir='. urlencode($what) . ((!empty($etc)) ? ','. $etc : ''));
				break;
			case 'original':
				return $this->findURL() .'/'. $this->dir_origs . $what;
				break;
			case 'web';
				return $this->findURL() .'/'. $this->dir_web . $what;
				break;
			case 'thumb';
				return $this->findURL() .'/'. $this->dir_thumbs . $what;
				break;
			case 'indexThumb';
				return $this->dir_root .'data/'. $dir . $what .'thumb/';
				break;
			case 'anchor':
				return $this->urlFor('dir', $this->dir) .'#'. $what;
				break;
			default:
				echo 'ERROR: bad url type \''. $type .'\' requested'. FF_BR;
				return 'do-not-comprehenend-homey';
				break;
		}	
	}


	// get contents of a directory
	function readDir() {
		$rdir = dir($this->dir);
		if ($rdir) {
			if ($this->debug) echo 'reading directory '. $this->dir . FF_BR;

			while ($rfile = $rdir->read()) {
				if (in_array($rfile, $this->exclude) || substr($rfile, -5) == 'thumb' || substr($rfile, -3) == 'web') continue; // FUCK THAT DIR

				if(is_dir($this->dir . $rfile) ) {
					$this->ff_dirs[] = array($rfile, filemtime($this->dir . $rfile));
					$sortDir = true;
				}

				if (in_array($this->getFileExtension(strtolower($rfile)), array('jpg', 'jpeg', 'jpe', 'png', 'gif'))) {
					// Grab as array to sort either by name or date 
					$this->ff_items[] = array($rfile, filemtime($this->dir . $rfile));
					$sortMe = true;
					$this->ff_vals[] = 1;
					$this->ff_total++;
				}
			}
			$rdir->close();
		} elseif ($this->debug) {
			echo 'could not read directory '. $this->dir;
		}

		// Do sorting (if enabled)
		if ($this->sortDir) {
			if (sizeof($this->ff_dirs) > 0) $this->sortDir();
			if (sizeof($this->ff_items) > 0) $this->sortItems();
		}

		// Compound into names
		$ds = sizeof($this->ff_dirs);
		for($i = 0; $i < $ds; $i++) $this->ff_dirs[$i] = $this->ff_dirs[$i][0] .'/';
		$is = sizeof($this->ff_items);
		for($i = 0; $i < $is; $i++) $this->ff_items[$i] = $this->ff_items[$i][0];
	}

	// dynamically pick up where the application is installed
	function findURL() {		
		return (($_SERVER["HTTPS"] == 'on') ? 'https' : 'http') .'://'. $_SERVER["SERVER_NAME"] . (($_SERVER["SERVER_PORT"] != '80') ? $_SERVER["SERVER_PORT"] : '') . dirname($_SERVER['PHP_SELF']);
	}

	// list sorting
	function sortDir() {usort($this->ff_dirs, array($this, (($this->sortByDate) ? 'dateSort' : 'nameSort')));}
	function sortItems() {usort($this->ff_items, array($this, (($this->sortByDate) ? 'dateSort' : 'nameSort')));}

	// comparison functions for above
	function dateSort($a, $b) {return ($a[1] > $b[1]) ? -1 : 1;}
	function nameSort($a, $b) {return ($a[0] > $b[0]) ? 1 : -1;}

	function getFileExtension($str){
		$i = strrpos($str, '.');
  		if (!$i) return '';
		$l = strlen($str) - $i;
		return substr($str,$i+1,$l);
	}

	function wordWrap($text, $len=15) {
		if (empty($text)) return '';

		$words = explode(' ', $text);
		for($i=0; $i<$len; $i++) {
			if ($i > sizeof($words)) break;
			$r .= (!empty($r) ? ' ' : '') . $words[$i];
		}

		if (sizeof($words) > $len) $r .= '...';
		return $r;
	}

	// shortcut for generating navigation breadcrumbs / titles
	function generateTitle() {
		echo FF_NAME .' '. FF_IMG_TYPE 
		. FF_SEPARATOR 
		. (($this->dir != FF_DATA_DIR) ? str_replace( array('data/', '/', '-', '_'), array('', '', ' ', ' '), $this->dir) : FF_DEFAULT_DIR_NAME) 
		. FF_SEPARATOR . FF_ANTI_FLICKR_MSG;
	}
	
	// shortcut for pagination links inside the theme
	// can rewrite for your own theme
	function pagination() {
		$out = '';
		if (FF_PER_PAGE > 0) 
			$out .= '<p><strong>Page</strong>'.$this->pagesLinks(sizeof($this->ff_items), $this->dir, $this->dir_name).FF_NL;
		$out .= '&ndash; viewing '.(($use_pages) ? ($ct_start+1) .'&ndash;'. $ct_end .' of' : 'all').' '.sizeof($this->ff_items).' items'.FF_NL;
		return $out;
	}

	function makeReqLinks($excl=false, $incl=false) {
		if (!is_array($excl) && !empty($excl)) $excl = explode(',', str_replace(' ', '', $excl));
		if (!is_array($incl) && !empty($incl)) $incl = explode(',', str_replace(' ', '', $incl));

		if (is_array($incl) && sizeof($incl) > 0) {
			foreach ($incl as $a) {
				list($k, $v) = explode('=', $a);
				if (empty($k)) continue;
				$args .= ((FF_CLEAN_URLS) ? $k .'/'. $v .'/' : ((!empty($args)) ? '&' : '?') . $k .'='. $v);
			}
		}

		if (is_array($this->reqs) && sizeof($this->reqs) > 0) {
			foreach ($this->reqs as $k=>$v) {
				if ((is_array($excl) && in_array($k, $excl)) || empty($k)) continue;
				$args .= ((FF_CLEAN_URLS) ? $k .'/'. $v .'/' : ((!empty($args)) ? '&' : '?') . $k .'='. $v);
			}
		}
		return $args;
	}

	function pagesLinks($num=0, $what) {
		if (FF_PER_PAGE < 1 || $num < 1) return ''; // Don't need no pages if their ain't nuttin' to sho'

		$total = ceil($num/FF_PER_PAGE);
		if ($this->cur_page > $total) $this->cur_page = $total;
		
		for($i=1; $i<=$total; $i++) {
			$pages .= ' &nbsp; '. (($i == $this->cur_page) ? '<strong>&lt;'. $i .'&gt;</strong>' : '<a href="'. $this->urlFor('page', $what, '', 'page='.$i) .'">'. $i .'</a>');
		}
		
		if ($total > 1) $pages .= ' &nbsp; '. (($this->cur_page == 'all') ? '<strong>&lt;all&gt;</strong>' : '<a href="'. $this->urlFor('page', $what, '', 'page=all') .'">all</a>');

		return $pages;
	}

	function readDirInfo($name, $dir=false, $repeat=false) {
		if (!$dir) $dir = $this->dir;

		if (is_file($dir . FF_DIR_INFO_FILENAME)) {
			if ($this->debug) echo 'reading directory info file'. FF_BR;
			$content = file_get_contents($dir . FF_DIR_INFO_FILENAME);
			if (!empty($content)) {
				$this->dir_info[$name] = $this->readYAML($content);
				// if (sizeof($this->ff_items) > 0 && sizeof($this->ff_items) != sizeof($this->dir_info[$name]['images']) && !$repeat) {
				if (sizeof($this->ff_items) > 0 && (!is_array($this->dir_info[$name][‘images’]) || sizeof($this->ff_items) != sizeof($this->dir_info[$name][‘images’])) && !$repeat) {
					$this->makeYAML($name, $dir, $this->dir_info[$name]); // remake yaml file for new images if there are images in the items array
				}
			} else {
				$this->makeYAML($name, $dir, false);
			}
		} elseif (!$repeat) {
			// Lets create one
			$this->makeYAML($name, $dir, false);
		} elseif ($this->debug) { // Prevent from looping if cannot read or create YAML file
			echo 'could not create or read dir file'. FF_BR;
		}
	}

	function makeYAML($name='', $dir='', $info=false) {
		$content = 'directory:'. FF_NL . FF_SPACES .'title:'. ((isset($info['directory']['title'])) ? $info['directory']['title'] : $name) . FF_NL . FF_SPACES .'desc:'. $info['directory']['desc'] . FF_NL .'images:'. FF_NL;

		// go through each image
		foreach ($this->ff_items as $v) 
			$content .= FF_SPACES . $v .':'. FF_NL . str_repeat(FF_SPACES, 2) . 'title:'. (isset($info['images'][$v]['title']) ? $info['images'][$v]['title'] : '') . FF_NL . str_repeat(FF_SPACES, 2) . 'desc:'. (isset($info['images'][$v]['desc']) ? $info['images'][$v]['desc'] : '') . FF_NL . str_repeat(FF_SPACES, 2) . 'tags:'. (isset($info['images'][$v]['tags']) ? $info['images'][$v]['tags'] : '') .FF_NL;

		// open directory info yaml
		if ($r = @fopen($dir . FF_DIR_INFO_FILENAME, 'w+')) {
			if ($this->debug) echo 'making directory info file'. FF_BR;
			fwrite($r, $content);
			fclose($r);
			chmod($dir. FF_DIR_INFO_FILENAME, 0777);

			// reload information
			$this->readDirInfo($name, $dir, true);
		} elseif ($this->debug) {
			echo 'could not make directory info file'. FF_BR;
		}
	}

	function readYAML($content='') {
		// format out the yaml file for easier read
		$content = str_replace(array("\t"), array(FF_SPACES), $content);
		$lines = explode(FF_NL, $content);

		if ($this->debug) echo 'reading...';

		$s = 0; // level count
		$r = array(); // values array
		$p = array(); // pointer array
		foreach ($lines as $v) {
			$c = substr_count($v, FF_SPACES);
			$f = strpos($v, ':');
			if ($f < 1) continue; // you gotta keep a style, bro
			$t = trim(substr($v, 0, $f));
			$d = trim(substr($v, ($f+1)));

			if ($c == 0) {
				$p = array(); // time to start a new family!
			} elseif ($c < $s) {
				for ($i=0; $i<=($s-$c); $i++) array_pop($p); // lengthy divorce!
			} elseif ($c == $s) {
				array_pop($p); // make room for another child!
			}
	
			$p[] = $t; // add current child to pointer array
			$s = $c; // set new level count
			$ps = ''; // pointer string
			foreach ($p as $pv) $ps .= '[\''. addslashes($pv) .'\']';
			eval('$r'. $ps .' = '. ((!empty($d)) ? '\''. str_replace('\'', '\\\'', $d) .'\'' : '\'\'') .';'); // best way to write out multi-dimensional array		
		}

		if ($this->debug) echo 'done.' . FF_BR;
		return $r;
	}
}


class imageResize {
	function resizeImage($file, $dest, $newWidth, $newHeight, $quality, $bResizeOnWidth){
		if (!file_exists($file)) {
			if ($this->debug) echo 'source does not exist!'. FF_BR;
			return false;
		}

		$w = 0;
		$h = 0;
		$src = false;

		// Create an Image from it so we can do the resize
		list($width, $height, $type) = getimagesize($file);
		switch ($type) {
			case '2': $src = @imagecreatefromjpeg($file); break;
			case '3': $src = imagecreatefrompng($file); break;
			case '1': $src = imagecreatefromgif($file); break;
		}

		if (!$src) {
	      if ($this->debug) echo 'problem opening '. $file .' - check it is complete'. FF_BR;
			return false;
		}

		if ($bResizeOnWidth == 1 || ($width > $height)){
			$w = $newWidth;
			$ratio = $height/$width;
			$h = $ratio * $w;
		} else {
			$h = $newHeight;
			$ratio = $width/$height;
			$w = $ratio * $h;
		}

		if ($tmp = imagecreatetruecolor($w,$h)) {
			// FROM Author: Tim Eckel - Date: 09/07/07 - Version: 1.1 - Project: FreeRingers.net - Freely distributable
			if (FF_IMG_QUALITY < 5 && (($w * FF_IMG_QUALITY) < $width || ($h * FF_IMG_QUALITY) < $height)) {
				if ($tmp2 = imagecreatetruecolor (($w * FF_IMG_QUALITY + 1), ($h * FF_IMG_QUALITY + 1))) {
					imagecopyresized ($tmp2, $src, 0, 0, 0, 0, ($w * FF_IMG_QUALITY + 1), ($h * FF_IMG_QUALITY + 1), $width, $height);
					imagecopyresampled ($tmp, $tmp2, 0, 0, 0, 0, $w, $h, ($w * FF_IMG_QUALITY), ($h * FF_IMG_QUALITY));
					imagedestroy ($tmp2);
				} elseif ($this->debug) {
					echo 'could not create temp image for faster resize'. FF_BR;
					return false;
				}
			} else {
				imagecopyresampled ($tmp, $src, 0, 0, 0, 0, $w, $h, $width, $height);
			}

			switch ($type) {
				case '2': @imagejpeg($tmp, $dest, $quality); break;
				case '3': imagepng($tmp, $dest, (9-round($quality/9))); break; // quality is 0-9, with 0 being the best. convert from jpeg 100 scale.
				case '1': imagegif($tmp, $dest); break; // gif has no quality
			}
			imagedestroy($src);
			imagedestroy($tmp);

			return (is_file($dest)); // return true if file was created successfully
		} elseif ($this->debug) {
			echo 'could not create temp image for resize'. FF_BR;
		}

		return false;
	}
}

/*
* PHP4<=>5 backwards compat functions
*/
if (!function_exists('str_split')){
	//Create a string split function for pre PHP5 versions
	function str_split($str, $nr) {return array_slice(split("-l-", chunk_split($str, $nr, '-l-')), 0, -1);}
}

?>
