// FUCK FLICKR application javascript

// Firebug console decoy
if (!("console" in window) || !("firebug" in console)) {
  var names = ["log", "debug", "info", "warn", "error", "assert", "dir", "dirxml",
    "group", "groupEnd", "time", "timeEnd", "count", "trace", "profile", "profileEnd"];

  window.console = {};
  for (var i = 0; i < names.length; ++i) 
    window.console[names[i]] = function() {};
}



// items float and can be of varible height, so we need to clear each "row" (and update on resize)
// TODO: this would be a nice little jQuery plugin
var rowClearance = {
	init : function(target) {
	  console.log("rowClearance.init: "+target);
    this.clearRows(target);

    $(window).resize(function() {
      rowClearance.clearRows(target);
    });
  },
  
  clearRows : function(target) {

    // TESTME: pseudoselectors vs. $($()[0])? speed? cleanliness? another way to do this?
    var itemWidth = $('.item:first').width();
    var pageWidth = $('#items').width() - 40; // +20 for left/right padding; FIXME; doesn't jquery.dimension do box-model?
    var itemsPerRow = Math.floor(pageWidth/itemWidth);

    // console.log("itemWidth="+itemWidth+"  pageWidth="+pageWidth+"  perRow="+itemsPerRow);

    $('.item.clear').removeClass('clear');
    $('.item').each(function(i){
      // if the top has changed we're in a new row
      offset = $(this).offset();
      if( i == itemsPerRow ) {
        $(this).addClass('clear');
      }
    });
    
  }
};


// jquery magic
$(document).ready(function(){

  // start preloading link targets
  // $('#items a').preload();

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
  	  $('#items .selected').removeClass('selected');
  	  $('#item_'+ this.hash.substr(1).replace(/\./, '_')).addClass('selected');

      if ($target.length) {
        var targetOffset = $target.offset().top;
        $('html,body')
        .animate({scrollTop: targetOffset-100}, 500);
       return false;
      }
    }
  });

  // highlight anchor class of selected item
  if (location.hash != '' && $('#item_'+ location.hash.substr(1).replace(/\./, '_'))) 
    $('#item_'+ location.hash.substr(1).replace(/\./, '_')).addClass('selected');
  
  // initialize row clearing
  rowClearance.init('#items');

});



// click event mgmnt for lightbox links
function lightboxInit(){
  status = $('#lightbox').attr('checked') == true ? true : false;
  var items = $('.thumb a');
  if(status == true || status == 'true') { /* string for Safari */
    // imgLoader = new Image();// preload
    // imgLoader.src = "images/ajax-loader.gif";

    // engage thickboxing
    items.click(function(){
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
    items.unbind('click');
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
