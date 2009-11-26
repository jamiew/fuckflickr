// FUCK FLICKR application javascript

// console decoy
if (!("console" in window) || !("firebug" in console)) {
  var names = ["log", "debug", "info", "warn", "error", "assert", "dir", "dirxml",
    "group", "groupEnd", "time", "timeEnd", "count", "trace", "profile", "profileEnd"];

  window.console = {};
  for (var i = 0; i < names.length; ++i)
    window.console[names[i]] = function() {};
}

$(document).ready(function(){

  // start preloading link targets
  // $('#images a').preload();

  // select embed code on click
  $('input.embed-code').click(function(){
    $(this).select();    
  });

  // lightboxen
  lightboxInit();
  
  // setting toggle handlerr
  $('input#lightbox').change(function(){
    lightboxInit();
    createCookie('fuckflickr_lightbox', status, 365);  
  });
  
  $('select#ff_sort').change(function(){
    var ff_sort = this.options[this.selectedIndex].value;    
    var basename = ff_sort.match(/[\/|\\]([^\\\/]+)\/$/); // ff_sort is the URL to redirect to
    if(basename[1] == 'name') 
      createCookie('fuckflickr_sort', 'name', 365);
    else
      createCookie('fuckflickr_sort', 'date', 365); // FIXME should allow for more options than these, really. need to impose a /sort/date too
    if (ff_sort != '' && ff_sort != '-1') 
      location.href = ff_sort;
  });
	
	
  // anchor scroll-to and highlighting
  $('a[href*=#]').click(function() {
    if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
      var $target = $(this.hash);
      $target = $target.length && $target
      || $('[name=' + this.hash.slice(1) +']');

  	  // switch selected class
  	  $('#images .selected').removeClass('selected');
  	  $('#img_'+ this.hash.substr(1).replace(/\./, '_')).addClass('selected');

      if ($target.length) {
        var targetOffset = $target.offset().top;
        $('html,body')
        .animate({scrollTop: targetOffset-100}, 500);
       return false;
      }
    }
  });

  // highlight anchor class to selected item
  if (location.hash != '' && $('#img_'+ location.hash.substr(1).replace(/\./, '_'))) 
    $('#img_'+ location.hash.substr(1).replace(/\./, '_')).addClass('selected');

  
});



// click event mgmnt for lightbox links
function lightboxInit(){
  status = $('#lightbox').attr('checked') == true ? true : false;
  var images = $('.thumb a');
  if(status == true || status == 'true') { /* string for Safari */
    // imgLoader = new Image();// preload
    // imgLoader.src = "images/ajax-loader.gif";
    // engage thickboxing
    images.click(function(){
    	var t = this.title || this.name || null;
    	var a = this.href || this.alt;
    	var g = this.rel || false;
      tb_show(t,a,g);
      // TB_show(t,a,g);
    	this.blur();
    	return false;
    });
  }
  else {
    images.unbind('click');
  }
}


/* 
* cookie manip functions, from http://www.quirksmode.org/js/cookies.html
*/
function createCookie(name, value, days) {
  console.log("createCookie: "+name+" "+value+" "+days);
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = "";
	document.cookie = name+"="+value+expires+"; path=/";
}
function readCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}
function eraseCookie(name) {
	createCookie(name,"",-1);
}


/*
* Slideshow mode, via jQuery Supersized plugin by Sam Dunn
* http://www.buildinternet.com/project/supersized/
*/

// Slideshow
$(function(){
  
  // Bind to the #start_slideshow link/button
  $('#start_slideshow').click(function(){
    console.log("ACTIVATE SLIDESHOW...");
    
    // Active Galleria mode...
    $('ul#items').galleria();
    
    // Hide the FuckFlickr UI
    $('#header, #navigation, #footer, .info').remove();
    $('body').css('background-color', '#000000');
    
      
    // Set a 'Next' timer -- currently 3 seconds
    setInterval( function(){ $.galleria.next(); }, 3000 );
    
    return false;
  });
});







/* FLOWRPLAYERIFICATION */
/*
 flashembed("video_<?php print $index ?>", 
	{
		src:'<?php echo $this->dir_tmpl ?>/flowplayer/FlowPlayerDark.swf',
		// width: <?php echo $this->video_width ?>, 
		// height: <?php echo $this->video_height ?>,
	},
	
	// second argument is Flowplayer specific configuration. See full list:
	// http://flowplayer.org/player/configuration.html
	{config: { 
		videoFile: '<?php print $this->urlFor('web', $item) ?>.flv',
		// videoLink: 'TODO',
		
		// playlist?
	  // { playList: [
	  //    { url: 'http://flowplayer.org/skiing.mp4' },
	  //    { url: 'http://flowplayer.org/river.mp4' },
	  //    { url: 'http://flowplayer/hacking.mp4' },
	  //    { url: 'http://www.images.org/byebye.jpg' }
	  //    ]
	  // }			

		autoPlay: false,
		autoBuffering: true,
		loop: false,
		// initialScale: 'scale',
		initialScale: 'fit',
		controlBarBackgroundColor:'0x000000',

		// minimalist vimeo-esque look
		controlsOverVideo: 'ease',			
		controlBarBackgroundColor: -1,
		controlBarGloss: 'low',
	}} 
);
*/
