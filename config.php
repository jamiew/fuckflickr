<?

//MODIFY THESE TO CUSTOMISE YOUR FUCKFLICKR SYSTEM

//THIS IS WHAT SHOWS UP AT THE TOP OF THE PAGE
$NAME = "F.A.T";

//THE LINK TO YOUR FUCKFLICKR SYSTEM
//THIS MUST BE THE FULL LINK WITH THE TRAILING SLASH
$LINK = "http://fffff.at/fuckflickr/"; // TRUNK: no longer really necessary, should only be used for emergencies

//THIS IS HOW MANY IMAGES FUCKFLICKR RESIZES ON ONE REFRESH
//10 IS A GOOD AMNT - YOU DONT WANT TO HAMMER YOUR SERVER
//IF IT IS SET TO 10 AND YOU UPLOAD 20 PICS THEN IT WILL 
//TAKE TWO PAGE REFRESHES TO PROCESS ALL THE IMAGES
$PROCESS_NUM = 30;

//USE CLEAN URLS? e.g. /fuckflickr/dir1 INSTEAD OF /fuckflickr/index.php?dir=data/dir1 
//REQUIRES mod_rewrite -- UNCOMMENT NOTED LINES IN .htaccess AS WELL
$CLEAN_URLS = true;

//WE'RE NOT GOING TO MAKE YOU BUT WE LIKE CC LICENSES VS HARDCORE COPYWRITE 
$CC_LICENSE = '<a rel="license" href="http://creativecommons.org/licenses/by-nc-sa/3.0/">Creative Commons Attribution-Noncommercial-Share Alike 3.0 License</a>';

//NOW OUR ANTI FLICKR MESSAGE FOR THE TITLE OF EACH PAGE
$ANTI_FLICKR_MSG = "FUCK FLICKR";

//DO YOU LIKE LIGHTBOXEN FOR VIEWING? THIS IS USER TOGGLABLE, BUT SET A DEFAULT
$LIGHTBOX_DEFAULT = true;

//ANY EXTRA DIRS YOU WANT TO EXCLUDE FROM THE INDEX LIST? COMMA-SEPARATED, NO SPACES
//CAN STILL VIEW THEM, THEY JUST WON'T BE LISTED
$EXCLUDE_DIRS = "secret,top_secret,ridiculously_secret";


function generateTitle($directory){

	global $ANTI_FLICKR_MSG, $NAME;

	echo $ANTI_FLICKR_MSG;
	echo " - ";
	echo "$NAME PHOTOS";
	echo " - ";
	//this last line gets the folder you are in and removes / _ - so it looks nicer.
	echo str_replace( array("data/", "/", "_", "-"), array("",""," ", " "),$directory); 
}


?>