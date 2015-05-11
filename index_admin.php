<?php
/**
* ADMIN PANEL
* @version 2.5, 2015-05-01
* @since 1.0, 2015
*
* displays admin controls for managing gallery and images data.  
* GALLERY adds,removes and modifies records.
* IMAGES adds,removes and modifies image records.
*
*/
session_start();
include 'GAL_CONF.php';
/*
* LOCAL VARS --
*/
$rrtoken = hash('md5',$token);
/* 
* USER CHECK --	
*/
if ( !isset($_SESSION['staff']['uid']) ) {
  $erno = 3;  //session expired
  header("Location: ../$errorfile?erno=$erno");
  exit();
} elseif ( $rrtoken!==$_SESSION['staff']['token'] ) {
  $erno = 3;  //session expired
  header("Location: ../$errorfile?erno=$erno");
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<title>Gallery Admin</title>
<!-- DESKTOP STYLE SHEETS -->
<link type="text/css" rel="stylesheet" href="galleryStyle.css" />
<!-- VIEWPORT force mobile devices to use device-width instead of man/max-width which is for desktop
<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0" />
<link rel="stylesheet" type="text/css" media="only screen and (min-device-width: 768px) and (max-device-width: 1024px) and (orientation: portrait)" href="../css/tablet.css" />
-->
<!-- JQuery/JAVASCRIPT note: Safari Webkit has a bug in its JQuery parsing, requires migrate-1.2 -->
<script type="text/javascript" src="//code.jquery.com/jquery-1.11.2.min.js"></script>
<script type="text/javascript" src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
<script type="text/javascript">
  $(document).ready(function() {
  /* EDIT RECORD WINDOW */
	$('a#editrecord').click(function() { //each instance is assigned to href var
		var href = $(this).attr('href');
  		window.open(href, 'edit_record', 'width=400,height=250');
  			return false;
	});
  /* ADD GALLERY WINDOW */
	$('a#addgallery').click(function() { //each instance is assigned to href var
		var href = $(this).attr('href');
  		window.open(href, 'add_gallery', 'width=400,height=200');
  			return false;
	});
/* ADD IMAGE WINDOW */
	$('a#addimages').click(function() { //each instance is assigned to href var
		var href = $(this).attr('href');
  		window.open(href, 'add_image', 'width=400,height=200');
  			return false;
	});
/* zebra stripe the active/inactive list */
	$('table.glist tr:nth-child(even)').addClass("zebra");
  });
</script>
</head>
<body>
<!-- CONTENT -->
<div id="header">
    <div id="navcontainer">
	    <div id="header_logo">
		    <a href="#" target="_self"><img id="clogo" src = "../images/c_logo.jpg" ALT="Company Name" border=0></a>
		</div>
    </div>
</div>
<!-- PANELS -->
<div id="row" name="content row">
	<div id="left" style="padding-left:2%">
		<h2 class="subhead">PROJECT GALLERY <span class="adminstr">ADMIN</span></h2>
		<iframe class="preview" src="admin/dashboard.php" width="85%" height="535px" frameBorder=0 border=0 seamless></iframe>
	</div><!--close left-->
<div id="right">
	<span class="tagline" style="color:#fff">Welcome Administrator</span><br><br>
 </div><!--close second-->
</div>
</body>
</html>