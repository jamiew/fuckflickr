<?php
// ouput a simple RSS2.0 feed
// just another way of rendering list.php
header("Content-Type: application/rss+xml");
print "<?xml version=\"1.0\"?>\n"; // php tries to interpret as an open tag
?>
<rss version="2.0">
	<channel>
		<title><?php print FF_NAME ?></title>
		<link><?php print FF_LINK ?></link>
		<description><?php print FF_ANTI_FLICKR_MSG // FIXME make a config option ?></description>
		<language>en-us<?php // TODO in8l me ?></language>
		<generator>FuckFlickr</generator>
		<?php
		foreach($this->ff_items as $image):
			$dir = FF_DATA_DIR.preg_replace('/^\//', '', dirname(str_replace(FF_DATA_DIR, '', $image))).'/';
			$file = basename($image);
		?>
		<item>
			<guid><?php print $this->urlFor('original', $file, $dir); ?></guid>
			<link><?php print $this->urlFor('original', $file, $dir); ?></link>
			<title><?php print $file; ?></title>
			<pubDate><?php print time(); // FIME ?></pubDate>
			<?php /*
				<enclosure type="video/quicktime" url="http://www.rocketboom.com/video/rb_08_mar_28.mov" length="25512412" />
				<media:content isDefault="true" type="video/quicktime" url="http://www.rocketboom.com/video/rb_08_mar_28.mov" fileSize="25512412" /><media:rating>nonadult</media:rating> 
			*/
			?>
			<description><![CDATA[
				<a href="<?php print $this->urlFor('web', $file, $dir) ?>" title="<?php print $file ?>"><img src="<?php print $this->urlFor('thumb', $file, $dir) ?>" alt="<?php print $file ?>" border="0" /></a>
				<p><?php print wordWrap($this->dir_info[$this->dir_name]['images'][$image]['desc'], 15) ?></p>]]>
			</description>
		</item>
		<?php endforeach; ?>
	</channel>
</rss>
