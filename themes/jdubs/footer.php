<?php
// FuckFlickr theme: footer.php
// page footer (included on every page)
?>
		<div id="footer">
			<?php if (FF_CC_LICENSE != '') echo 'Content licensed under a '. FF_CC_LICENSE; ?> 
			- powered by <a href="http://fffff.at/fuckflickr-info">FuckFlickr</a> <br />
		
			<?php 
			//output page rendering stats 
			$time = explode(" ", microtime());
			$endtime = $time[1] + $time[0];
			$totaltime = ($endtime - $begintime);
			echo 'Rendered page in ' .substr($totaltime, 0, 8). ' seconds.';
			?>
		</div> <!-- /#footer -->
		
	</div> <!-- /#main -->
</div> <!-- /#container -->

<!-- stats -->
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
var pageTracker = _gat._getTracker("UA-96220-1");
pageTracker._initData();
pageTracker._trackPageview();
</script>


</body> 
</html>
