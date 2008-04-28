// FUCK FLICKR application javascript


// we're not $ toyz, we're $$$$$ ballers.
function $$$$$() {var a = new Array(); for (var i = 0; i < arguments.length; i++) {var b = arguments[i]; if (typeof b == 'string') b = document.getElementById(b); if (arguments.length == 1) return b; a.push(b);} return a;}
function getStyle(a, b) {c = ((a.currentStyle) ? a.currentStyle[b] : ((window.getComputedStyle) ? document.defaultView.getComputedStyle(a,null).getPropertyValue(b) : false)); return (c != null) ? c : 0;}


var clearance = {
	init : function() {
		if ($('#images')) { // check if thumbs exist
			this.ff_gc = $('#images').children();
			var rmh = false; // max row height (false indicates first in new row)
			var rmt = false; // max row offsetHeight (false indicates first in new row)
			var rch = 0; // cur row height
			var rct = 0; // cur row offsetHeight;
			var r = 0; // row #
			var rc = 0; // element increment
			var rpc = 0; // cols per row
			var rs = []; // array - sort into rows
			var rsmh = []; // array - max height of each row

			// break out into rows -- just get number across from first row
			for (i=0; i<this.ff_gc.length; i++) {
				if (this.ff_gc[i].offsetHeight == null) continue;

				rct = this.findPos(this.ff_gc[i]); // get offsets

				// convert into rows
				if (rct[1] > rmt) {// current offset height is greater than previous -- must be new row
					if (rmt !== false) break; // we've got number per row
					rmt = rct[1];
				}
				rpc++;
			}

			var j = 0;
			// get max height for each row
			for (i=0; i<this.ff_gc.length; i++) {
				if (this.ff_gc[i].offsetHeight == null) continue;

				if (j == 0 || (j % rpc) == 0) {
					if (j > 0) r++; // start new row if not first
					rs[r] = []; // intialize row array
					rsmh[r] = 0; // initalize max row height
					rc = 0;
				}

				// offsetHeight includes padding, so we deduct padding since we are adding it to the element's height
				rch = this.ff_gc[i].offsetHeight - parseInt(getStyle(this.ff_gc[i], 'padding-top')) - parseInt(getStyle(this.ff_gc[i], 'padding-bottom'));

				if (rch > rsmh[r]) rsmh[r] = rch; // check max height
				rs[r][rc] = this.ff_gc[i];
				rc++;
				j++;
			}
			
			for (i=0; i<rs.length; i++) { // each row
				if (!rs[i] == null) continue;
				for (j=0; j<rs[i].length; j++) {// each column in row
					rs[i][j].style.height = rsmh[i] +'px';
				}
			}
		}
	},

	findPos : function(a) {l = 0; t = 0; if (a.offsetParent) {do {l += a.offsetLeft; t += a.offsetTop;} while (a = a.offsetParent);} return [l,t];}
};



$(document).ready(function(){

  // start preloading link targets
  $('#images a').preload();

  // select embed code on click
  $('input.embed-code').click(function(){
    $(this).select();    
  });

  //  lightboxen
  lightboxInit();
  $('#lightbox').change(lightboxInit);

  // codaSlider + lazy loading = ultimate lightbox
  // jQuery(window).bind("load", function() {
    // $("#main").codaSlider()
  // });
  // $("#main img").lazyload({ /*placeholder : "/fatlab/fuckflickr/images/grey.gif"*/ });
	
  // animated anchor scroll-to
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
        .animate({scrollTop: targetOffset}, 1000);
       return false;
      }
    }
  });

  // Add class to selected item
  if (location.hash != '' && $('#img_'+ location.hash.substr(1).replace(/\./, '_'))) $('#img_'+ location.hash.substr(1).replace(/\./, '_')).addClass('selected');

  clearance.init();
});

$(window).resize(function() {
  clearance.init();
});

// click event mgmnt for lightbox links
function lightboxInit(){
  status = $('#lightbox').attr('checked');
  createCookie('fuckflickr_lightbox', status);
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




// cookie functions, from http://www.quirksmode.org/js/cookies.html
function createCookie(name,value,days) {
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
