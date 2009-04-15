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

</body> 
</html>