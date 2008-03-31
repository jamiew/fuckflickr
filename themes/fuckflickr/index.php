<?php 
// fuckflickr theme: index.php
// primary page, shown when there's no specific request
// in our case, list all directories.
// TODO show any images in /data too! right?

$url = $this->findURL(); 
?>

<?php if (sizeof($this->ff_dirs) > 0): ?>


<?php else: ?>
	<p>Ain't no flicks to show; throw some ish in <strong>/data</strong>!</p>
<?php endif ?>