<?php
$embed = '<embed src="'.$this->dir_tmpl.'flowplayer/FlowPlayerDark.swf" bgcolor="#ffffff" allowfullscreen="true" allowscriptaccess="always" quality="high" type="application/x-shockwave-flash" pluginspage="http://www.adobe.com/go/getflashplayer" flashvars="config={&quot;videoFile&quot;:&quot;'.$this->urlFor('web', $item).'.flv&quot;,&quot;autoPlay&quot;:false,&quot;autoBuffering&quot;:true,&quot;loop&quot;:false,&quot;initialScale&quot;:&quot;fit&quot;,&quot;controlBarBackgroundColor&quot;:-1,&quot;controlsOverVideo&quot;:&quot;ease&quot;,&quot;controlBarGloss&quot;:&quot;low&quot;}" height="100%" width="100%">';
?>

<h2><?php echo $shortName ?></h2>
<div class="video-wrapper" id="video_<?php print $index ?>">

	<?php print $embed; ?>
	
	<!-- thumbnail: TODO use click-to-play -->
	<!--<a title="<?php echo $shortName ?>" href="<?php echo $this->urlFor('web', $item) ?>">
		<img src="<?php echo $this->urlFor('thumb', $item) ?>.jpg" alt="<?php echo $shortName ?>" title="<?php echo $shortName ?>" border="0" />
	</a>-->
</div>

<!-- meta -->
<div class="info">
	<?php $description = $this->dir_info[$this->dir_name]['images'][$item]['desc']; ?>
	<?php if($description): ?><p class="description"><?php print $description; ?></p><?php endif; ?>
	<p class="meta">
		<a class="short-name" href="<?php echo $anchor ?>">#</a> 
		<a class="hi-res" href="<?php echo $this->urlFor('original', $item) ?>">video</a>
		
	 	<span class="embed">embed 
			<input class="embed-code" type="text" size="30" value="<?php echo htmlentities($embed.'<br /><a href="'.$this->urlFor('anchor', $item, $this->dir_name.'/').'">'.$shortName.' on '.$_SERVER['HTTP_HOST'].'</a>') ?>" />
		</span><br />				
	</p>
</div>





