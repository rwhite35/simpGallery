<?php
session_start();
/* 
	* javascript screen.width, L182-189 - captures user screen size for gallery->calcimgsize image size 
	* required for scaling image to mobile devices
*/
$screenwidth = (isset($_GET['sw'])) ? filter_var($_GET['sw'],FILTER_SANITIZE_NUMBER_INT) : 1152;
$_SESSION['screenwidth'] = $screenwidth;
?>