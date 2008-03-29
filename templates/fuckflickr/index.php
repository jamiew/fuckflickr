<?php $url = $this->findURL(); ?>

<?php if (sizeof($this->ff_dirs) > 0): ?>
<div id="dirs">
	<?php for($i = 0; $i < sizeof($this->ff_dirs); $i++): ?>	
	<div class="preview">
		<a href="<?php echo $this->urlFor('dir', $this->ff_dirs[$i], $this->dir_name) ?>" style="background-image: url(<?php echo $this->urlFor('indexThumb', $this->ff_dirs[$i], $this->dir_name) . FF_INDEX_THUMB_NAME ?>);">
			<span><?php echo ((!empty($this->dir_info[$this->ff_dirs[$i]]['directory']['title'])) ? $this->dir_info[$this->ff_dirs[$i]]['directory']['title'] : str_replace(array('/', '_', '-'), array('', ' ', ' '), $this->ff_dirs[$i])) ?></span>
		</a>
	</div>
	<?php endfor ?>
</div> <!-- /#dirs -->

<?php else: ?>
	<p>ain't no flicks to show</p>
<?php endif ?>