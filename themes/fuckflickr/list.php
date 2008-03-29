<?php
// FuckFlickr theme: list.php
// used for a directory, displays a gallery/set/list
// including its images and subdirectories

// do we have files?
if (sizeof($this->ff_items) > 0):

	// are we paginating?
	$use_pages = (FF_PER_PAGE > 0 && sizeof($this->ff_items) > FF_PER_PAGE && $this->cur_page != 'all');
	if ($use_pages) {
		$ct_start = ($this->cur_page-1) * FF_PER_PAGE;
		$ct_end = (($ct_start + FF_PER_PAGE) > sizeof($this->ff_items)) ? sizeof($this->ff_items) : ($ct_start + FF_PER_PAGE);
	}
?>

<div id="description">
	<?php echo ((!empty($this->dir_info[$this->dir_name]['directory']['desc'])) ? $this->dir_info[$this->dir_name]['directory']['desc'] : str_replace(array('/', '_', '-'), array('', ' ', ' '), '')) ?>
</div>

<div class="pagination"><?php print $this->pagination() ?></div>

<div id="images">
<?php
$images = $this->ff_items;
if($use_pages)  
	$images = array_splice($images, $ct_start, FF_PER_PAGE);

foreach($images as $image):
	$shortName = ((!empty($this->dir_info[$this->dir_name]['images'][$image]['title'])) ? $this->dir_info[$this->dir_name]['images'][$image]['title'] : substr($image,0,-4));
?>
	<div class="thumb-wrapper">
		<h2><?php echo $shortName ?></h2>
		<a name="<?php echo str_replace(' ', '-', $shortName) ?>" class="title"></a>
		<div class="thumb">
			<a target="_blank" rel="lightbox" title="<?php echo $shortName ?>" href="<?php echo $this->urlFor('web', $image) ?>"><img src="<?php echo $this->urlFor('thumb', $image) ?>" alt="<?php echo $shortName ?>" title="<?php echo $shortName ?>" border="0" /></a>
		</div>
		<div class="info">
		 <span class="description"><?php echo $this->wordWrap($this->dir_info[$this->dir_name]['images'][$image]['desc'], 15) ?></span>
		 <p class="meta"><a class="short-name" href="#<?php echo $shortName ?>">#</a> 
			<a class="hi-res" href="<?php echo $this->urlFor('original', $image) ?>">Hi-Res Image</a>
		 	<span class="embed">embed <input class="embed-code" type="text" size="24" value="<?php echo htmlentities('<a href="'. $this->urlFor('anchor', $shortName) .'"><img src="'. $this->urlFor('web', $image).'" alt="'.$shortName.'" title="'.$shortName.'" border="0" /></a>') ?>" /></span><br />
		&nbsp;</p>
		</div>
	</div>
<?php endforeach ?>
</div> <!-- /#images -->

<div class="pagination"><?php print $this->pagination() ?></div>

<?php else: // no files in this directory ?>
	<h1>Empty directory!</h1>
<?php endif ?>

