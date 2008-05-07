<?php
##############################################################
##    ___              __     ___ __ __        __           ##
##  .'  _|.--.--.----.|  |--.'  _|  |__|.----.|  |--.----.  ##
##  |   _||  |  |  __||    <|   _|  |  ||  __||    <|   _|  ##
##  |__|  |_____|____||__|__|__| |__|__||____||__|__|__|    ##
##############################################################

// main beast

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
		$this->dir_date = 0;
		$this->timenow = time();

		// kick off this mother
		$this->processRequest();

	}

	/*
	*	actions to render
	* TODO FIXME don't really need these anymore
	*/
	function viewList($file = 'list.php') {
		$this->readDir(); // read this dir
		$this->processImages(); // parse imgz
		if ($this->dir != FF_DATA_DIR) $this->evalDirInfo($this->dir_name, $this->dir);
		for ($i=0; $i<sizeof($this->ff_dirs); $i++) {
		  if($this->debug) echo 'dir'. $this->ff_dirs[$i].'<br />'.$this->dir . $this->ff_dirs[$i] .'<br /><br />';
		  $this->readDirInfo($this->ff_dirs[$i], $this->dir . $this->ff_dirs[$i]);
	  }
		$this->openTemplate($file);
	}

	function viewPhoto($file = 'photo.php') {
		// eventually allow a photo page, with title, descripton, comments, toytags, etc.
		$this->openTemplate($file);
	}


	/*
	* RSS: find all media in our install and render as an XML feed
	*/
	function viewRSS() {
	  
    // build our own exclusion list; $this->exclude is too tight
    $patterns = array();
    foreach($this->exclude as $f) 
      if( empty($f) || $f == '.' || $f == '..' || $f == $this->dir ) continue; // some stuff doesn't fly w/ `find`
      else $patterns[] = $f;
    $pattern = join('\|', $patterns);
    $files = explode("\n", shell_exec("find $this->dir -type f | grep -v -e '$pattern'")); // system call; less than ideal, but fast
    
    // build file modified hash and sort
    $sort = array();
    foreach ($files as $path) {
      $sort[$path] = filemtime($path);
		}
		arsort($sort);

    // limit & render
		$this->ff_items = array_splice( array_keys($sort), 0, FF_RSS_ITEM_COUNT );
		include('rss.php');
	}
	  

	/*
	*	parse the request URI and/or POST/GET vars
	*/
	function processRequest() {
		$this->parseRequest();
		$this->cleanRequest();

		//$this->debug = (isset($this->reqs['d']));
		//$this->debug = true;
		if ($this->debug) echo '<strong>Entering debug mode.</strong>'. FF_BR;

		$this->dir_name = $this->makeDirName($this->dir);
		$this->dir_origs = $this->dir;
		$this->dir_thumbs = $this->dir.FF_DATA_THUMB_DIR;
		$this->dir_web = $this->dir.FF_DATA_WEB_DIR;
		
		$this->cur_page = ((is_numeric($this->reqs['page']) && $this->reqs['page'] > 0 || $this->reqs['page'] == 'all') ? floor($this->reqs['page']) : 1);
		$this->sortByDate = ($this->reqs['sort'] == 'name') ? false : true; // sort by the date uploaded, otherwise sort by filename (if sorting is enabled)
		$this->exclude = array('.', '..', $this->dir_origs, $this->web_dir, $this->thumb_dir, 'web', 'thumb', '.svn', '.git', '.DS_Store', 'info.yml');
		$this->exclude = array_merge($this->exclude, split(',', FF_EXCLUDE_DIRS)); // Combine with config'd excludes
	}
	
	// get the dir name
	function makeDirName($dir) {
	  $a = explode('/', $dir);
	  while (empty($b)) $b = array_pop($a);
		return $b;
	}
	
	// process the URL into sweet, sweet information
	function parseRequest() {
		
		// seed cookie values first; TODO don't hard-code keys
		if(!empty($_COOKIE['fuckflickr_sort'])) $this->reqs['sort'] = $_COOKIE['fuckflickr_sort'];
		
		// then parse URL
		if (FF_CLEAN_URLS && empty($_REQUEST['dir'])) { // bail on dir queryvar, for dual-compatibility
			$path = urldecode(str_replace(dirname($_SERVER['PHP_SELF']), '', $_SERVER['REQUEST_URI']));
			$path = preg_replace('/^\//', '', $path); // remove preceding slash
			$path = preg_replace('/\?'. $_SERVER['QUERY_STRING'] .'/', '', $path); // Remove any phony GET queries
			$paths = explode('/', $path);
			$reqs = $paths;

      $dir = FF_DATA_DIR;

      if (sizeof($paths) > 0) {
        for ($i=0; $i<sizeof($paths); $i++) {
          if (empty($paths[$i])) continue;
          if (is_dir($dir . $paths[$i])) {
            $dir .= $paths[$i] .'/';
            array_shift($reqs);
          } else {
            break;
          }
        }

        for ($i=0; $i<sizeof($reqs); $i+=2) 
					if (!empty($reqs[$i])) 
						$this->reqs[$reqs[$i]] = (isset($reqs[$i+1])) ? $reqs[$i+1] : true; // at least make true if set (ex. /d for debugging)
				  unset($paths, $reqs);
			}
		} else { // messy URL parsing
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
		
		// set it and move on
		$this->dir = $dir . ((preg_match('/\/$/', $dir)) ? '' : '/'); // add trailing slash if necessary
	}

	/*
	* passing the directory in the URL is never a good idea
	* so we check against as many possible hacks as possible.
	**/
	function cleanRequest() {

		// common hax, hidden dirs, move dir cmds, etc.
		if (strstr($this->dir,'.') || strstr($this->dir,'../') ||  strstr($this->dir,'../../') || strstr($this->dir,'/./') || strstr($this->dir, FF_DATA_DIR.'..') || strstr($this->dir,'.svn')  || strstr($this->dir,'.git') ){
			echo 'you entered a cheating url or your folder contains shady characters - change \'em out!';
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


	// get contents of a directory
	function readDir($dirname = '') {
		if (empty($dirname)) $dirname = $this->dir;
		$rdir = @dir($dirname);
		if ($rdir) {
			if ($this->debug) echo 'reading directory '. $this->dir . FF_BR;

			while ($rfile = $rdir->read()) {
				if (in_array($rfile, $this->exclude) || substr($rfile, -5) == 'thumb' || substr($rfile, -3) == 'web') continue; // FUCK THAT DIR
				if(is_dir($this->dir . $rfile) ) {
					$this->ff_childs[] = array($rfile, filemtime($this->dir . $rfile));
					$sortDir = true;
				}

				if (in_array(getFileExtension(strtolower($rfile)), array('jpg', 'jpeg', 'jpe', 'png', 'gif'))) {
					// Grab as array to sort either by name or date 
					$date = filemtime($this->dir . $rfile);
					$this->ff_files[] = array($rfile, $date);
					if ($date > $this->dir_date) $this->dir_date = $date;
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
			if (sizeof($this->ff_childs) > 0) $this->sortDir();
			if (sizeof($this->ff_files) > 0) $this->sortItems();
		}

		// Compound into names
		$ds = sizeof($this->ff_childs);
		for($i = 0; $i < $ds; $i++) $this->ff_dirs[$i] = $this->ff_childs[$i][0] .'/';
		$is = sizeof($this->ff_files);
		for($i = 0; $i < $is; $i++) $this->ff_items[$i] = $this->ff_files[$i][0];
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
	
			$this->resize_count = 0;
			// lets make thumbnails
			foreach($this->ff_items as $item) {
				if ($this->resize_count >= FF_PROCESS_NUM) break; // Don't blow a gasket
				if (!file_exists($this->dir_web . $item) || !file_exists($this->dir_thumbs . $item) ) {

					// if failed the first time (web), it should fail the second time (thumb). [halvfet]
					if($this->debug) echo 'making web image for '. $item . FF_BR;
					if ($this->resizeImage($this->dir . $item, $this->dir_web . $item, 600, 450, 93, 1)) {
						// make a thumbnail
						if ($this->debug) echo 'making thumbnail for '. $item. FF_BR;
						if ($this->resizeImage($this->dir_web . $item, $this->dir_thumbs . $item, 300, 225, 93, 0)) {
              // moved thumbnail gen script outside of loop to ensure it is generated
						} elseif ($this->debug) {
							echo '<strong>FAILED</strong> making thumb image for '. $item . FF_BR;
						}
					} elseif ($this->debug) {
						echo '<strong>FAILED</strong> making web image for '. $item . FF_BR;
					}
					$this->resize_count++;
				}
			}

			// make our index page thumbnail
			// if thumbnail does not exist or directory has been modified since last generation, make it
			if (sizeof($this->ff_items) > 0 && (!is_file($this->dir_thumbs . FF_INDEX_THUMB_NAME) || $this->dir_date > filemtime($this->dir_thumbs . FF_INDEX_THUMB_NAME))) {
		    if ($this->debug) echo 'making index thumbnail for '. $this->dir . FF_BR;
				$items = $this->ff_files;
				usort($items, array($this, 'dateSort'));
				$item = array_shift($items);
				unset($items);
				if ($this->resizeImage($this->dir_web . $item[0], $this->dir_thumbs . FF_INDEX_THUMB_NAME, 120, 90, 93, 0)) {
				  // DO WE REALLY NEED THIS?
					//$pf = dirname(dirname(dirname($this->dir_thumbs . FF_INDEX_THUMB_NAME)));
					//if (file_exists($this->dir_thumbs . FF_INDEX_THUMB_NAME) && $pf != 'data' && !file_exists($pf .'/thumb/')) mkdir($pf .'/thumb/');
					//@copy($this->dir_thumbs . FF_INDEX_THUMB_NAME, $pf .'/thumb/'. FF_INDEX_THUMB_NAME);
				} elseif ($this->debug) {
					echo '<strong>FAILED</strong> making index thumbnail image for '. $this->dir . FF_BR;
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
	* TODO untested w/ messy URLs with new halvfet code
	*/
	function urlFor($type, $what, $dir='', $etc='', $excl=false) {
		
		// use whatever dir we're in if we aren't passed a root dir
		// currently for images only, throwing other ish off
		if(empty($dir) && ($type == 'original' || $type == 'web' || $type == 'thumb')) $dir = $this->dir;
		$what = str_replace(FF_DATA_DIR, '', $what);
		switch ($type) {
			case 'dir':
				//if($this->debug) print "urlFor(dir): $what, $dir, $etc, $excl".FF_BR;
				return (FF_CLEAN_URLS) ? $this->dir_root . $dir . $what . (($excl) ? $this->makeReqLinks($excl, ((!empty($etc)) ? $etc : '')) : '') : $this->dir_root .'index.php'. $this->makeReqLinks($excl, 'dir='.urlencode($what) . ((!empty($etc)) ? $etc : ''));
				break;
			case 'page':
				return (FF_CLEAN_URLS) ? $this->dir_root . $dir . $what . $this->makeReqLinks('page', ((!empty($etc)) ? $etc : '')) : $this->dir_root .'index.php'. $this->makeReqLinks(false, 'dir='. urlencode($what) . ((!empty($etc)) ? $etc : ''));
				break;
			case 'original':
				return $this->findURL() .'/'.$dir.$what; // FIXME
				break;
			case 'web';
				return $this->findURL() .'/'.$dir.FF_DATA_WEB_DIR.$what;
				break;
			case 'thumb';
				return $this->findURL() .'/'.$dir.FF_DATA_THUMB_DIR.$what;
				break;
			case 'indexThumb';
				return $this->dir_root . FF_DATA_DIR . $dir . str_replace(' ', '%20', $what) . FF_DATA_THUMB_DIR;
				break;
			case 'anchor':
				return /*$this->urlFor('dir', $this->dir)*/ $this->findURL().'/'.$dir.'#'.urlencode($what);
				break;
			case 'rss':
				return $this->dir_root.'rss';
			default:
				echo 'ERROR: bad url type \''. $type .'\' requested'. FF_BR;
				return 'do-not-comprehenend-homey';
				break;
		}	
	}	


	// dynamically pick up where the application is installed
	function findURL() {
		return (($_SERVER["HTTPS"] == 'on') ? 'https' : 'http') .'://'. $_SERVER["SERVER_NAME"] . (($_SERVER["SERVER_PORT"] != '80') ? $_SERVER["SERVER_PORT"] : '') . dirname($_SERVER['PHP_SELF']);
	}

	// build args for clean or messy URLs
	function makeReqLinks($excl=false, $incl=false) {
		//if($this->debug) echo "makeReqLinks: excl = $excl  incl = $incl".FF_BR;
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

		return ((!empty($args)) ? $args : '');
	}



	// list sorting
	function sortDir() {usort($this->ff_childs, array($this, (($this->sortByDate) ? 'dateSort' : 'nameSort')));}
	function sortItems() {usort($this->ff_files, array($this, (($this->sortByDate) ? 'dateSort' : 'nameSort')));}

	// comparison functions
	function dateSort($a, $b) {return ($a[1] > $b[1]) ? -1 : 1;}
	function nameSort($a, $b) {return ($a[0] > $b[0]) ? 1 : -1;}


	// shortcut for generating navigation breadcrumbs / titles
	function pageTitle() {
		echo FF_NAME .' ' 
		. (($this->dir != FF_DATA_DIR) ? FF_SEPARATOR.str_replace( array('data/', '/', '-', '_'), array('', '/', ' ', ' '), cleanDirname($this->dir)) : (defined('FF_ANTI_FLICKR_MSG') ? FF_SEPARATOR.FF_ANTI_FLICKR_MSG : ''));
	}
	
	// shortcut for pagination links inside the theme
	// can rewrite for your own theme
	function pagination() {
		$out = '';
		$ct_start = ($this->cur_page-1)*FF_PER_PAGE;
		$ct_end = (($ct_start + FF_PER_PAGE) > sizeof($this->ff_items)) ? sizeof($this->ff_items) : ($ct_start + FF_PER_PAGE);
		if (sizeof($this->ff_items) > 0) {// || sizeof($this->ff_dirs) > 0) { // will eventually list # of nested dirs
		  $out = '<p>';
		  if (FF_PER_PAGE > 0 && sizeof($this->ff_items) > 0) $out .= '<strong>Page</strong>'.$this->pagesLinks(sizeof($this->ff_items), $this->dir).' &ndash; '.FF_NL;
		  $out .= 'Viewing '. ((sizeof($this->ff_items) > 0) ? ((FF_PER_PAGE > 0 && $this->cur_page != 'all') ? ($ct_start+1) .'&ndash;'. $ct_end .' of' : 'all').' '.sizeof($this->ff_items).' image'. ((sizeof($this->ff_items) != 1) ? 's' : '') : ' nothing') .'</p>' .FF_NL;
		}
		return $out;
	}

	// pagination HTML for use in the theme
	function pagesLinks($num=0, $what) {
		if (FF_PER_PAGE < 1 || $num < 1) return ''; // Don't need no pages if their ain't nuttin' to sho'

		$total = ceil($num/FF_PER_PAGE);
		if ($this->cur_page > $total) $this->cur_page = $total;
		
		for($i=1; $i<=$total; $i++)
			$pages .= ' &nbsp; '. (($i == $this->cur_page) ? '<strong>&lt;'. $i .'&gt;</strong>' : '<a href="'. $this->urlFor('page', $what, '', 'page='.$i) .'">'. $i .'</a>');
		
		if ($total > 1) $pages .= ' &nbsp; '. (($this->cur_page == 'all') ? '<strong>&lt;all&gt;</strong>' : '<a href="'. $this->urlFor('page', $what, '', 'page=all') .'">all</a>');
		return $pages;
	}

  // Allow only a read of yaml file (if exists)
  function readDirInfo($name, $dir) {
    if (is_file($dir . FF_DIR_INFO_FILENAME)) {
  		if ($this->debug) echo 'reading readonly directory info file'. FF_BR;
  		$content = file_get_contents($dir . FF_DIR_INFO_FILENAME);
  		if (!empty($content)) $this->dir_info[$name] = $this->readYAML($content);
  	}
  }

  // Allow read of yaml file or create one to be read (with check to attempt once).
	function evalDirInfo($name, $dir=false, $repeat=false) {
		if (!$dir) $dir = $this->dir;

		if (is_file($dir . FF_DIR_INFO_FILENAME)) {
			if ($this->debug) echo 'reading directory info file'. FF_BR;
			$content = file_get_contents($dir . FF_DIR_INFO_FILENAME);
			if (!empty($content)) {
				$this->dir_info[$name] = $this->readYAML($content);

				// if things have changed, re-make YAML and re-process images
				if (sizeof($this->ff_items) > 0 && (!is_array($this->dir_info[$name]['images']) || sizeof($this->ff_items) != sizeof($this->dir_info[$name]['images'])) && !$repeat) {
					$this->makeYAML($name, $dir, $this->dir_info[$name]); // remake yaml file for new images if there are images in the items array
					$this->processImages();
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
	
	
	/*
	* YAML "database"
	*/
	function makeYAML($name='', $dir='', $info=false) {
	  $dirs = explode('/', $name);
    $title = false;
    while (empty($title)) {
      if (sizeof($dirs) < 1) break;
      $title = array_pop($dirs);
    }
		$content = 'directory:'. FF_NL . FF_SPACES .'title:'. ((isset($info['directory']['title'])) ? $info['directory']['title'] : (($title) ? $title : preg_replace('/\/$/', '', $name))) . FF_NL . FF_SPACES .'desc:'. $info['directory']['desc'] . FF_NL .'images:'. FF_NL;

		// go through each image
		foreach ($this->ff_items as $v) 
			$content .= FF_SPACES . $v .':'. FF_NL . str_repeat(FF_SPACES, 2) . 'title:'. (isset($info['images'][$v]['title']) ? $info['images'][$v]['title'] : '') . FF_NL . str_repeat(FF_SPACES, 2) . 'desc:'. (isset($info['images'][$v]['desc']) ? $info['images'][$v]['desc'] : '') . FF_NL . str_repeat(FF_SPACES, 2) . 'tags:'. (isset($info['images'][$v]['tags']) ? $info['images'][$v]['tags'] : '') .FF_NL;

		// open directory info yaml
		if ($r = fopen($dir . FF_DIR_INFO_FILENAME, 'w+')) {
			if ($this->debug) echo 'making directory info file'. FF_BR;
			fwrite($r, $content);
			fclose($r);
			chmod($dir. FF_DIR_INFO_FILENAME, 0777);

			// reload information
			$this->evalDirInfo($name, $dir, true);
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


/*
* misc helper functions
* including PHP4<=>5 backwards compat
*/
if (!function_exists('str_split')){
	//Create a string split function for pre PHP5 versions
	function str_split($str, $nr) {return array_slice(split("-l-", chunk_split($str, $nr, '-l-')), 0, -1);}
}

// clean up a directory name, removing "/data", leading/trailing slashes, etc.
function cleanDirname($path) {
	if(empty($path)) return '';
	// $path = str_replace(FF_DATA_DIR, '', );
	return preg_replace('/^\//', '', preg_replace('/\/$/', '', $path) );
}

// parse extension from a filename
function getFileExtension($str){
	$i = strrpos($str, '.');
		if (!$i) return '';
	$l = strlen($str) - $i;
	return substr($str,$i+1,$l);
}

// wordwrap for PHP4, truncate a string
if (!function_exists('wordwrap')) {
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
}

?>
