<?php
/**
* ADMIN CONTROL PANEL
*@version 2.2 2015-05-26
*@since 1.0 2015
*
* displays GALLERY and IMAGE icons.  
*/
session_start();
error_reporting(E_ALL ^ E_NOTICE);
include '../GAL_CONF.php';
/*
* USER CHECK --
*/
if(empty($_SESSION['staff']['uid'])) {
  $ppath = basename(GAL_PARENT_DIR);
  $errorpath = rawurlencode($ppath."/index.php");
  $erno = 3;  //session expired
  header("Location: ../../$errorfile?erno=$erno&page=$errorpath");
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Manage Media Center</title>
<link href="../galleryStyle.css" rel="stylesheet" type="text/css" media="screen">
<!-- VIEWPORT force mobile devices to use device-width instead
<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0" />
<link rel="stylesheet" type="text/css" media="only screen and (min-device-width: 768px) and (max-device-width: 1024px) and (orientation: landscape)" href="../css/tablet.css" />
-->
</head>
<body>
<div id="wrapper">
	<p>Manage Project Gallery and Image Data.</p>
	<div id="mediaNav">
	  <div id="dash_row">
		<div id="dash_left"><a href="list_viewer.php?view=gallery" target="_self">
			<img src="../img/mgallery.png" alt="Manage Gallery" title="Manage Gallery Properties" border=0/><br>Gallery</a></div>
		<div id="dash_right"><a href="list_viewer.php?view=images" target="_self">
			<img src="../img/aimage.png" alt="Add Images" title="Add Images to Galleries" border=0/><br>Images</a></div>
	  </div>
	</div>
	<p>What to do from here:<br>
	<ul id="definitions">
		<li>GALLERY - Add/Delete project galleries, Edit gallery properties</li>
		<li>IMAGES - Add/Delete images, Edit image properties</li>
		<li>&nbsp;</li>
		<li><button class="button buttontxt" type="button" onclick=window.parent.location.href='../../<?php echo $logoutfile?>?pdir=gallery' target='_parent'>Log Out</button></li>
	</ul>
</div>
</body>
</html>