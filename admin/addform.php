<?php
/**
*
* ADD FORM VIEWER 
* @author rondwhite <rwhite@october-design.com>
* @version 1.2 2015-05-11
* @since 1.0, 2014-08-07
*
* Displays either the Add Gallery or Add Image form.  When adding a gallery
* the process will require an image that will be the thumbnail for that gallery.
* process map: index_admin > dashboard > list_viewer > addform > addform_class
*
* @api
* addFormClass.php, displays either gallery or image input form
* @params string $params, [tblname] db table name
* @return stream HTML, form with table specific fields
* @usage $Obj = new form($table);
* @usage $Obj->formrequest();
*/
session_start();
error_reporting(E_ALL ^ E_NOTICE);
/*
* configuration file for script vars
* const string GAL_TBL, GAL_CONF table name for gallery records
* const string IMG_TBL, GAL_CONF table name for image records
* const string GAL_PARENT_DIR, absolute path to subsections directory
*/
include '../GAL_CONF.php';
include '../lib/addform_class.php';
/*
* LOCAL VARS ---
*/
$dir = basename(GAL_PARENT_DIR);
$returnpath = rawurlencode($dir."/index.php");
/*
* USER CHECK ---
*/
if ( empty($_SESSION['staff']['uid']) ) {
  $erno = 3;  //session expired
  header("Location: ../../$errorfile?erno=$erno&page=$returnpath");
  exit();
} else { //set tables for update
  $rrtoken = $_SESSION['staff']['token'];	
  $category = $_SESSION['category'];
  if( $category=="gallery" ) {
	$tables = array(GAL_TBL, IMG_TBL);	//pull gallery tbl first
  } elseif( $category=="images" ) {
	$tables = array(IMG_TBL, GAL_TBL);	//pull image tbl first
  }	
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Gallery Admin | <?php echo ucwords($category)?></title>
<link href="adminStyle.css" rel="stylesheet" type="text/css" media="screen">
<!-- VIEWPORT force mobile devices to use device-width instead
<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0" />
<link rel="stylesheet" type="text/css" media="only screen and (min-device-width: 768px) and (max-device-width: 1024px) and (orientation: landscape)" href="../../css/tablet.css" />
-->
<!-- JQUERY/JAVASCRIPT-->
<script type="text/javascript" src="//code.jquery.com/jquery-latest.js"></script>
<script type="text/javascript" src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
<script type="text/javascript">
/* set page to top */
$(document).ready(function() {
   $('body').scrollTop($(document).height());
});
</script>
</head>
<body>
<div id="fwrapper">
  <div id="fheader">
  	<h2 class="subhead"><img src="../img/addicon.png" alt="Add Record" height="35px" />&nbsp;Add New <?php echo ucwords($category)?></h2>
  </div>
  <div id="fform">
       <p>Please fill in all the fields, each category requires input for this gallery.</p>
	   <?php	  	
	  	if(isset($message)) {  //ON SUBMIT, UI feedback on success, control sends to admin error class on fail
	  	  echo '<div id="fmes">'."\n";
	  	  echo $message."<br>\n";  //message defined in addform class
	  	  echo "Please close this window to refresh your list.<br><br>\n";
	  	  echo '<span id="refresh" class="close"><a href="javascript:window.close()">Close</a></span>';
	  	  echo "</div>";
	  	} else {  //INITIAL PAGE LOAD
		  $formObj = new form($tables,$rrtoken);
		  $formObj->formrequest();
		}
	  ?>
 </div>
</div>
<!--Not using in git project: <script type="text/javascript" language="javascript" src="../../js/val_form.js"></script>-->
</body>
</html>