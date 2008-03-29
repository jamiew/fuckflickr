<?php
// set.php
// displays a gallery index/listing page
// including its images and subdirectories

// do we have files
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

<div id="pagination">
	<?php if (FF_PER_PAGE > 0): ?><p><strong>Page</strong> <?php echo $this->pagesLinks(sizeof($this->ff_items), $this->dir, $this->dir_name) ?><?php endif ?>
	- viewing <?php echo (($use_pages) ? ($ct_start+1) .'&ndash;'. $ct_end .' of' : 'all') ?> <?php echo sizeof($this->ff_items) ?> images
</div>

<div id="images">
<?php
$img = $this->ff_items;
if($use_pages) 
	$img = array_splice($img, $ct_start, FF_PER_PAGE);

for($i = 0; $i < sizeof($img); $i++):
	$link = $this->findURL()."#".$img[$i];
	$shortName = ((!empty($this->dir_info[$this->dir_name]['images'][$img[$i]]['title'])) ? $this->dir_info[$this->dir_name]['images'][$img[$i]]['title'] : substr($img[$i],0,-4));
?>
<div class="thumb-wrapper" title="<?php echo $shortName ?>">
<a name="<?php echo str_replace(' ', '-', $shortName) ?>" class="title"></a>
<div class="thumb"><a target="_blank" rel="thuggin" title="<?php echo $shortName ?>" href="<?php echo $this->urlFor('web', $img[$i]) ?>"><img src="<?php echo $this->urlFor('thumb', $img[$i]) ?>" alt="<?php echo $shortName ?>" title="<?php echo $shortName ?>" border="0" /></a></div>
<div class="info">
 <h2><?php echo $shortName ?></h2>
 <p><?php echo $this->wordWrap($this->dir_info[$this->dir_name]['images'][$img[$i]]['desc'], 15) ?></p>
 <p><a class="short-name" href="#<?php echo  $shortName ?>">#</a> <a class="hi-res" href="<?php echo $this->urlFor('original', $img[$i]) ?>">Hi-Res Image</a>
 <span class="embed">embed <input class="embed-code" type="text" size="24" value="<?php echo htmlentities('<a href="'. $this->urlFor('anchor', $img[$i]) .'"><img src="'. $this->urlFor('anchor', $img[$i]).'" alt="'.$shortName.'" border="0" /></a>') ?>" /></span><br />&nbsp;</p>
</div>
</div>
<?php endfor ?>
</div>

<?php if (FF_PER_PAGE > 0): ?><p id="pages"><strong>Page</strong> <?php echo $this->pagesLinks(sizeof($this->ff_items), $this->dir, $this->dir_name) ?></p><?php endif ?>

<?php else: ?>
	<h1>Empty directory</h1>
<?php endif ?>