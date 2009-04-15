<?php
// FuckFlickr theme: list.php
// used for a directory, displays a gallery/set/list
// including its images and subdirectories

// do we have files?
if (!empty($this->ff_items) || !empty($this->ff_dirs)):

	// are we paginating?
	// TODO put this into the template functions
	$use_pages = (FF_PER_PAGE > 0 && sizeof($this->ff_items) > FF_PER_PAGE && $this->cur_page != 'all');
	if ($use_pages) {
		$ct_start = ($this->cur_page-1) * FF_PER_PAGE;
		$ct_end = (($ct_start + FF_PER_PAGE) > sizeof($this->ff_items)) ? sizeof($this->ff_items) : ($ct_start + FF_PER_PAGE);
	}
?>

<!-- folder title & description (from YAML) -->
<?php //echo ((!empty($this->dir_info[$this->dir_name]['directory']['title'])) ? '<h1 id="title">'. $this->dir_info[$this->dir_name]['directory']['title'] .'</h1>' : $this->dirname) ?>
<?php echo ((!empty($this->dir_info[$this->dir_name]['directory']['desc'])) ? '<h3 id="description">'. $this->dir_info[$this->dir_name]['directory']['desc'] .'</h3>' : '') ?>


<!-- list sub-directories, if any -->
<?php if(!empty($this->ff_dirs)): ?>
<div id="directories">
	<?php for ($i=0; $i<sizeof($this->ff_dirs); $i++): ?>	
<?php $path = $this->dir . $this->ff_dirs[$i]; ?>
	<div class="preview">
		<a href="<?php echo $this->urlFor('dir', $this->dir . $this->ff_dirs[$i]) ?>" style="background-image: url(<?php echo $this->urlFor('indexThumb', 'data/'. $this->dir . $this->ff_dirs[$i]) . FF_INDEX_THUMB_NAME ?>);">
			<span><?php echo ((!empty($this->dir_info[$path]['directory']['title'])) ? $this->dir_info[$path]['directory']['title'] : str_replace(array('/', '_', '-'), array('', ' ', ' '), $this->ff_dirs[$i])) ?></span>
		</a>
	</div>
	<?php endfor; ?>
</div> <!-- /#directories -->
<?php endif; ?>

<!-- prev/next/random/sideways -->
<?php if(sizeof($this->ff_items) > 0): ?>
<?php if($IMAGES_PER_PAGE > 0): ?><div class="pagination"><?php print $this->pagination() ?></div><?php endif; ?>
<?php endif; ?>

<!-- everything in this directory -->
<ul id="items" class="playlist">
<?php
$items = $this->ff_items;
if($use_pages)  //slice if we're paginating
	$items = array_splice($items, $ct_start, FF_PER_PAGE);

$index = 0;

// determine what kind(s) of files we have and adjust output accdingly
$types = array();
foreach($items as $item) {
	$type = getFileType($item);
	if(empty($types[$type])) $types[$type] = true;
}

// do we have only one filetype? dope
if(count($types) == 1) {
	if($types['audio']) {
		$soundmanager = true;
		// it's ONLY audio
		// MuxTape mode
		?>
		
	 <div id="control-template">
	  <!-- control markup inserted dynamically after each link -->
	  <div class="controls">
	   <div class="statusbar">
	    <div class="loading"></div>
	     <div class="position"></div>
	   </div>
	  </div>
	  <div class="timing">
	   <div id="sm2_timing" class="timing-data">
	    <span class="sm2_position">%s1</span> / <span class="sm2_total">%s2</span></div>
	  </div>
	  <div class="peak">
	   <div class="peak-box"><span class="l"></span><span class="r"></span>
	   </div>
	  </div>
	 </div>

	 <div id="spectrum-container" class="spectrum-container">
	  <div class="spectrum-box">
	   <div class="spectrum"></div>
	  </div>
	 </div>
	
		<link rel="stylesheet" href="<?php print $this->dir_tmpl ?>css/soundmanager.css" type="text/css" media="screen" title="no title" charset="utf-8">
		<script src="<?php print $this->dir_tmpl ?>js/soundmanager2.min.js" type="text/javascript" charset="utf-8"></script>
		<script type="text/javascript" charset="utf-8">
			soundManager.url = '<?php print $this->dir_tmpl ?>soundmanager/';
			var PP_CONFIG = {
			  flashVersion: 9,       // version of Flash to tell SoundManager to use - either 8 or 9. Flash 9 required for peak / spectrum data.
			  usePeakData: true,     // [Flash 9 only] whether or not to show peak data (left/right channel values) - nor noticable on CPU
			  useWaveformData: false, // [Flash 9 only] show raw waveform data - WARNING: LIKELY VERY CPU-HEAVY
			  useEQData: true,       // [Flash 9 only] show EQ (frequency spectrum) data
			  useFavIcon: false,      // try to apply peakData to address bar (Firefox + Opera) - performance note: appears to make Firefox 3 do some temporary, heavy disk access/swapping/garbage collection at first(?)
			  useMovieStar: false,     // Flash 9.0r115+ only: Support for a subset of MPEG4 formats.
				autoStart: true,
			};
		</script>
		<script src="<?php print $this->dir_tmpl ?>js/page-player.js" type="text/javascript" charset="utf-8"></script>		
		<?
	}
	elseif($types['video']) {
		// it's ONLY video...
		// TODO cinema mode		
	}
	elseif($types['image']) {
		// it's ONLY images...
		// TODO lightboxes and slideshows		
	}	
	else {
		debug("Dunno wtf is in this directory");
	}
}



// otherwise it's a smooth blend of any or all of the above
foreach($items as $item):
	// TODO: this shortname parsing should be a function
	$shortName = ((!empty($this->dir_info[$this->dir_name]['images'][$item]['title'])) ? $this->dir_info[$this->dir_name]['images'][$item]['title'] : substr($item,0,-4));
  $file = $this->urlFor('original', $item);
	$type = getFileType($file);
	$extension = getFileExtension($file);
	$link_to_original = "$type";

	$anchor = basename($this->urlFor('anchor', $item));
	$index++;	// ghetto pre-incremenet so we start at 1
?>
	<li class="item <?php print $type ?>" id="item_<?php print $index ?>">
		<a name="<?php echo str_replace('#','',$anchor) ?>" class="anchor"></a>
		
		<!-- content -->
		<?php
		// ghetto "partial" rendering
		// TODO: should possibly include one file at first which loads functions for each of these
		//   simple PHP like this is pretty quick as-is, but it's still a TODO
		if($type == 'audio' || $type == 'video' || $type == 'image')
			include("_$type.php");
		else
			include('_default.php');
    ?>
		
	</li>
<?php endforeach; ?>
</ul> <!-- /#songs -->

<?php if($IMAGES_PER_PAGE > 0): ?><div class="pagination"><?php print $this->pagination() ?></div><?php endif; ?>

<?php else: // no files in this directory ?>
	<h1>Empty directory!</h1>
<?php endif ?>

