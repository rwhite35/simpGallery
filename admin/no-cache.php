<?php
/**
* force client browser to not cache page
* use when dynamic content is updated regularly
*
* NOTE: NOT FOR WITH SESSION BASED PAGES
* just public pages
*/
header('Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT');
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); 	// Date in the past
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header('Cache-Control: post-check=0, pre-check=0', false); 
header('Pragma: no-cache');							// IE specific
?>