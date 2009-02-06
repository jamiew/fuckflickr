<h2><?php echo $shortName ?></h2>

<div class="thumb">
	<a target="_blank" rel="lightbox" title="<?php echo $shortName ?>" href="<?php echo $this->urlFor('web', $item) ?>"><img src="<?php echo $this->urlFor('thumb', $item) ?>" alt="<?php echo $shortName ?>" title="<?php echo $shortName ?>" border="0" /></a>
</div>

<!-- meta -->
<div class="info">
	<?php $description = $this->dir_info[$this->dir_name]['images'][$item]['desc']; ?>
	<?php if($description): ?><p class="description"><?php print $description; ?></p><?php endif; ?>
	<p class="meta">
		<a class="short-name" href="<?php echo $anchor ?>">#</a> 
		<a class="hi-res" href="<?php echo $this->urlFor('original', $item) ?>"><?php print $link_to_original; ?></a>
	 	<span class="embed">embed <input class="embed-code" type="text" size="24" value="<?php echo htmlentities('<a href="'.$this->urlFor('anchor', $item, $this->dir_name.'/').'"><img src="'. $this->urlFor('web', $item).'" alt="'.$shortName.'" title="'.$shortName.'" border="0" /></a>') ?>" /></span><br />				
	</p>
</div>