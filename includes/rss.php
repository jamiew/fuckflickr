<?php
// ouput a simple RSS2.0 feed
// just another way of rendering list.php

header("Content-Type: application/rss+xml");
header("Cache-Control: no-cache, must-revalidate"); // keep smart, mo'fo
header("Expires: Thu, 25 Oct 2007 11:00:00 GMT"); // remember the F.A.T. Incorporation Date =)
print "<?xml version=\"1.0\"?>\n"; // php tries to interpret as an open tag
?>
<rss version="2.0">
	<channel>
		<title><?php print FF_NAME ?></title>
		<link><?php print FF_LINK ?></link>
		<description><?php print FF_ANTI_FLICKR_MSG // FIXME make a config option ?></description>
		<language>en-us<?php // TODO in8l me ?></language>
		<generator>FuckFlickr</generator>
<?php // not indented so the raw XML looks nice 
		foreach($this->ff_items as $image):
			$image = str_replace('//','/',$image); // bug in RSS feed collection, FIXME
			$dir = FF_DATA_DIR.preg_replace('/^\//', '', dirname(str_replace(FF_DATA_DIR, '', $image))).'/';
			// $dir = cleanDirname($image);
			$filename = basename($image); 			
			$imageURL = $this->urlFor('anchor', $filename, str_replace(FF_DATA_DIR, $dir)); /* nasty, should fix anchor urlFor... */

?>
		<item>
			<guid><?php print $imageURL; ?></guid>
			<link><?php print $imageURL ?></link>
			<title><?php print $filename; ?></title>
			<pubDate><?php print time(); // FIME ?></pubDate>
<?php /*
				<enclosure type="video/quicktime" url="http://www.rocketboom.com/video/rb_08_mar_28.mov" length="25512412" />
				<media:content isDefault="true" type="video/quicktime" url="http://www.rocketboom.com/video/rb_08_mar_28.mov" fileSize="25512412" /><media:rating>nonadult</media:rating> 
*/?>
			<description><![CDATA[
				<a href="<?php print $this->urlFor('original', $filename, $dir) ?>" title="<?php print $filename ?>"><img src="<?php print $this->urlFor('thumb', $filename, $dir) ?>" alt="<?php print $filename ?>" border="0" /></a>
				<p><?php print wordWrap($this->dir_info[$this->dir_name]['images'][$image]['desc'], 15) ?></p>]]>
			</description>
		</item>
		<?php endforeach; ?>
	</channel>
</rss>
