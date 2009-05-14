<?php 
	if(!$soundmanager) $soundmanager = false; // FIXME

	$link = '<a href="'.$file.'" class="playable">'.$shortName.'</a>';
	if($soundmanager) {
		// soundmanager MUXTAPE mode
		$embed = $link; // ....
	}
	elseif($extension == 'mp3') {
		// use MP3 audio player
		// TODO mirror this swf locally!!!

		$urlencoded = urlencode($file);
		$embed = <<<HEREDOC
		<object type="application/x-shockwave-flash" data="http://fffff.at/wp-content/plugins/audio-player/player.swf" id="<?php print $index; ?>" height="24" width="290">
		  <param name="movie" value="http://fffff.at/wp-content/plugins/audio-player/player.swf">
		  <param name="FlashVars" value="playerID=1&amp;bg=0xfff000&amp;leftbg=0xeeeeee&amp;lefticon=0x666666&amp;rightbg=0xcccccc&amp;rightbghover=0x999999&amp;righticon=0x666666&amp;righticonhover=0xffffff&amp;text=0x666666&amp;slider=0x666666&amp;track=0xFFFFFF&amp;border=0x666666&amp;loader=0xff00ff&amp;soundFile={$urlencoded}">
		  <param name="quality" value="high">
		  <param name="menu" value="false">
		  <param name="bgcolor" value="#FFFFFF">
		</object>			
HEREDOC;
	}
	else {		
		// Not an MP3? just use <embed>		
		$embed = '<embed src="'.$file.'" autostart="false" width="200" height="60" loop="1" />';
	}
?>


<?php if($soundmanager): ?>
	<?php print $link; ?>
<?php else: ?>
    <div class="audio-inline">
    <h2><?php echo $shortName ?></h2>
		<div class="audio-player">
		<?php print $embed ?>

			<div class="meta">
		  	  <a href="#">#</a>
	  		  <a href="#">audio</a>
   	 		  embed: <input type="text" class="embed-code" value="<?php print htmlentities(str_replace("\n", '', $embed)); ?>" />
			</div>
		</div>
	</div>
<?php endif; ?>
