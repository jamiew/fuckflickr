<?php
// FuckFlickr theme: list.php
// used for a directory, displays a gallery/set/list
// including its images and subdirectories

// do we have files?
if (!empty($this->ff_items) || !empty($this->ff_dirs)):

	// are we paginating?
	$use_pages = (FF_PER_PAGE > 0 && sizeof($this->ff_items) > FF_PER_PAGE && $this->cur_page != 'all');
	if ($use_pages) {
		$ct_start = ($this->cur_page-1) * FF_PER_PAGE;
		$ct_end = (($ct_start + FF_PER_PAGE) > sizeof($this->ff_items)) ? sizeof($this->ff_items) : ($ct_start + FF_PER_PAGE);
	}
?>


<?php echo ((!empty($this->dir_info[$this->dir_name]['directory']['title'])) ? '<h1 id="title">'. $this->dir_info[$this->dir_name]['directory']['title'] .'</h1>' : $this->dirname) ?>
<?php echo ((!empty($this->dir_info[$this->dir_name]['directory']['desc'])) ? '<h3 id="description">'. $this->dir_info[$this->dir_name]['directory']['desc'] .'</h3>' : '') ?>



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

<div class="pagination"><?php print $this->pagination() ?></div>

<div id="images">
<?php
$images = $this->ff_items;
if($use_pages)  //slice if we're paginating
	$images = array_splice($images, $ct_start, FF_PER_PAGE);

foreach($images as $image):
	$shortName = ((!empty($this->dir_info[$this->dir_name]['images'][$image]['title'])) ? $this->dir_info[$this->dir_name]['images'][$image]['title'] : substr($image,0,-4));
	$anchor = basename($this->urlFor('anchor', $image));
?>
	<div class="thumb-wrapper" id="img_<?php echo str_replace(array('#', '.'), array('', '_'), $anchor) ?>">
		<a name="<?php echo str_replace('#','',$anchor) ?>" class="anchor"></a>
		<h2><?php echo $shortName ?></h2>
		<div class="thumb">
			<a target="_blank" rel="lightbox" title="<?php echo $shortName ?>" href="<?php echo $this->urlFor('web', $image) ?>"><img src="<?php echo $this->urlFor('thumb', $image) ?>" alt="<?php echo $shortName ?>" title="<?php echo $shortName ?>" border="0" /></a>
		</div>
		<div class="info">
			<p class="description"><?php echo $this->dir_info[$this->dir_name]['images'][$image]['desc'] ?></p>
			<p class="meta"><a class="short-name" href="<?php echo $anchor ?>">#</a> 
			<a class="hi-res" href="<?php echo $this->urlFor('original', $image) ?>">Hi-Res Image</a>
		 	<span class="embed">embed <input class="embed-code" type="text" size="24" value="<?php echo htmlentities('<a href="'.$this->urlFor('anchor', $image, $this->dir_name.'/').'"><img src="'. $this->urlFor('web', $image).'" alt="'.$shortName.'" title="'.$shortName.'" border="0" /></a>') ?>" /></span><br />
		&nbsp;</p>
		</div>
	</div>
<?php endforeach ?>
</div> <!-- /#images -->

<div class="pagination"><?php print $this->pagination() ?></div>

<?php else: // no files in this directory ?>
	<h1>Empty directory!</h1>
<?php endif ?>

