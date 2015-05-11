<?php
/**
* GALLERY VIEWER
* @version 2.5, 2015-05-26
* @since 1.0, 2015
* 
* client viewer for either lightbox or individual gallery images. transitions 
* are handled clientside and have two options, jquery.slideshow.min (default)
* is a simple transition script.  parallaxSlider is a slide show presentation.
* 
* @param int $pageid, required - change router if pageid is changed here.
* @param array $params, class properties for projectGallery_class 
* @@param string $params[ticket], triggers either lightbox or gallery sub class
* @@param string $params[gid], gallery id for individual gallery images
*
* @uses accesswrapper::factory($params) to display either the lightbox or the galler images
* @return html stream to viewer
*/
include 'GAL_CONF.php';
include 'admin/no-cache.php';
include 'lib/projectGallery_class.php';
/* 
* LOCAL VARS --- 
*/
$token = hash("md5",$token);
$pageid = 1;
$_GET['pageid'] = $pageid;
$gid = (isset($_GET['gid']) && !empty($_GET['gid'])) ? filter_var($_GET['gid'],FILTER_SANITIZE_NUMBER_INT) : null;
/* 
* CLASS PARAMS --- 
*/
if( isset($gid) && $gid!=null ) { //show project gallery images
	$params['ticket'] = "gallery";
	$params['active'] = "Y";
	$params['gid'] = $gid;
	$params['pxswidth'] = VIEWER_MAX_WIDTH;
	$params['imgwidth'] = IMG_MAX_WIDTH;
} else { //show lightbox thumbnails
	$params['ticket'] = "lightbox";
	$params['active'] = "Y";
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Portfolio | Gallery</title>
    <meta charset="UTF-8"/>
	<!--INCLUDE STYLE SHEETS-->
	<link rel="shortcut icon" href="../favicon.ico" type="image/x-icon"/>
	<link rel="stylesheet" type="text/css" href="galleryStyle.css" />
    <!--MOBILE DEVICE STYLE requires both tablet.css for UI and galleryStyle mobile web for gallery specific rules
	<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0" />
	<link rel="stylesheet" type="text/css" media="only screen and (min-device-width: 320px) and (max-device-width: 480px) and (orientation: portrait)" href="mobile.css" />
	<link rel="stylesheet" type="text/css" media="only screen and (min-device-width: 768px) and (max-device-width: 1024px) and (orientation: landscape)" href="tablet.css" />
	//-->
	<!--JQUERY/JAVASCRIPT-->
	<script type="text/javascript" src="//code.jquery.com/jquery-1.11.2.min.js"></script>
	<script type="text/javascript" src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
	<script type="text/javascript" src="js/jquery.slideshow.min.js"></script>
	<script type="text/javascript" src="js/parallaxSlider.js"></script>
	<script type="text/javascript">$(document).ready(function(){$('#pxs_container').slideshow({timeout:3000,type:'sequence'});});</script>
	<!-- JQuery for UI functionality -->
	<script type="text/javascript">
	  $(document).ready(function() {
	  	/* TOGGLE LOGIN */
	  	$('#login').hide();
	  	$('#show_login').click(function() {	  	
		  $('#login').toggle();
	  	  return false;
	  	});
	  	/* RELOAD LIGHTBOX */
	    $("#backbtn").click(function(){
		    window.history.back();
		});
		/* 
			* DISPLAY THUMBNAILS
			* Start a new row of thumbnails after the third thumbs div and set 
			* the left edge of the next thumbs div, based on the previous image width
		*/			
		for (i=3;i<=$('#thumbs').children().length;i++) {
			for (n=4;n<=6;n++){
			//define div's 4 thru 6 using the nth-child(n) position filter
			$('#thumbs:nth-child('+n+')').css({
			position: 'absolute',
			top: '285px'
  			});
 	  		}
		}
		/* ADD LEFT PROPERTY if a 5th or 6th thumbnail exists */  
		if ($('#thumbs:nth-child(5)')) {
			var evalpos4 = $('div#thumbs:nth-child(4)').has('img');
			var imagewidth = (evalpos4.width()+28)+"px"; //add 28 px of padding
			$('#thumbs:nth-child(5)').css({
			left: imagewidth
 			});
 		}
 		if ($('#thumbs:nth-child(6)')) {
 			var evalpos4 = $('div#thumbs:nth-child(4)').has('img');
 			var evalpos5 = $('div#thumbs:nth-child(5)').has('img');
 			var imagewidth = ((evalpos4.width())+(evalpos5.width())+50)+"px"; //add 50 px padding
 			$('#thumbs:nth-child(6)').css({ left: imagewidth });
 		}
   	});
  </script>
</head>
<body>
<div id="header">
    <div id="navcontainer">
	    <div id="header_logo">
		    <a href="../index.html" target="_self"><img id="clogo" src = "../images/c_logo.jpg" ALT="Company Name" border=0></a>
		</div>
    </div>
</div>
<!-- PAGE CONTENT -->
<div id="row" name="content row">
<div id="left" style="padding-left:2%">
	<?php
	  $cObj = accesswrapper::factory($params);
	  $cObj->jobrequest();
	  unset($params);
	  unset($cObj);
	?>
</div><!--close left -->
<div id="right">
	<span class="tagline" style="color:#fff">Show Your Projects With Style</span><br><br>
  </div><!--close second-->
</div>
<footer><div id="footer"><button id="show_login">Login</button></div></footer>
<!--PAGE LOGIN-->
<div id="login">
   <div style="padding-left:10px;">
	<div id="formstext">
	  <form action="router.php" method="post">
      <table class="admin_login">
      	  <tr>
      	  	<th class="caption" colspan="2">Enter login to edit page.</th>
		  <tr>
    		<td class="cellsL">User Name:</td><td width="50%"><input type="text" name="user_name" /></td></tr>
    	  <tr>
    		<td class="cellsL">Password:</td><td width="50%"><input type="password" name="user_pass" />
    	    <!--id specific to each parent page-->
    	    <input type="hidden" name="pageid" value="<?php echo $pageid?>" />
    	    <input type="hidden" name="token" value="<?php echo $token?>" /></td></tr>
		  <tr>
    		<td class="cells">&nbsp;</td>
    		<td><input class="button buttontxt" type="Submit" value="Log In" onmouseover="this.className='button buttonhov'"onmouseout="this.className='button'" /></td>
		  </tr>
		 </form>
      	</table>	
	  </div><!--end formstext-->
	 </div><!--end form div-->
</div><!--ends login div-->
<?php
if (isset($gid) && $gid!=null) { echo '<script type="text/javascript">$(function(){var $pxs_container=$(\'#pxs_container\');$pxs_container.parallaxSlider();});</script>'; }	
?>
<!-- pass screen width value to gallery class -->
<script>
  var swidth = screen.width;
  var url = "admin/getScreenWidth.php?sw=" + swidth;
  var onPageLoad = new XMLHttpRequest();
  onPageLoad.open("GET",url,true);
  onPageLoad.send();
</script>
</body>
</html>