<?php
// ouput a simple RSS2.0 feed 
// just another way of rendering list.php, really
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
		$images = $images = $this->ff_items;
		$images = array_splice($images, $ct_start, FF_PER_PAGE); // TODO use a diff option for RSS
		foreach($images as $image):
			$shortName = ((!empty($this->dir_info[$this->dir_name]['images'][$image]['title'])) ? $this->dir_info[$this->dir_name]['images'][$image]['title'] : substr($image,0,-4));
		?>
		<item>
			<guid><?php print $this->urlFor('original', $image); ?></guid>
			<link><?php print $this->urlFor('original', $image); ?></link>
			<title><?php print $shortName; ?></title>
			<pubDate><?php print time(); // FIME ?></pubDate>
			<?php /*
				<enclosure type="video/quicktime" url="http://www.rocketboom.com/video/rb_08_mar_28.mov" length="25512412" />
				<media:content isDefault="true" type="video/quicktime" url="http://www.rocketboom.com/video/rb_08_mar_28.mov" fileSize="25512412" /><media:rating>nonadult</media:rating> 
			*/
			?>
			<description><![CDATA[
				<a href="<?php print $this->urlFor('web', $image) ?>" title="<?php print $shortName ?>"><img src="<?php print $this->urlFor('thumb', $image) ?>" alt="<?php print $shortName ?>" border="0" /></a>
				<p><?php print $this->wordWrap($this->dir_info[$this->dir_name]['images'][$image]['desc'], 15) ?></p>]]>
			</description>
		</item>
		<?php endforeach ?>
	</channel>
</rss>
