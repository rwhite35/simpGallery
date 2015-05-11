<?php
/**
* LIST VIEWER
* @author Ron D White <rwhite@october-design.com>
* @version 2.0, 2015-05-11
* @since 1.0, 2014-07-28
*
* displays gallery and image records in a list view format.
* process map: dashboard >> list_viewer >> list_class >> list_control
*
*/
session_start();
error_reporting(E_ALL ^ E_NOTICE);
include '../GAL_CONF.php';
/*
* USER CHECK --
*/
if(empty($_SESSION['staff']['uid'])) {
  $dir = basename(GAL_PARENT_DIR);
  $returnpath = rawurlencode($dir."/index.php");
  $erno = 3;  //session expired
  header("Location: ../../$errorfile?erno=$erno&page=$returnpath");
  exit();
} else {
	unset($_SESSION['category']); //clear last category loaded
	/*
	* list_control.php defines functions for modifing data
	* function assignSortOrder($labelClick)
	* param string $labelClick, holds the label_# the user clicked
	* return string $sortByCol, assigned to $params[orderparam]
	*/
	include 'list_control.php';
	/*
	* include list class, this factory returns a table with records displayed in rows, 
	* each category calles it own interface class. ie gallery or images
	*/
	include '../lib/list_class.php';
}
/* view urlencoded on dashboard L35/37 and list_viewer L145 */
$category = ( isset($_GET['view']) ) ? $_GET['view'] : 'gallery' ;
if( $category=="gallery" ) {
	$tbl1 = GAL_TBL;	//set gallery table first
	$tbl2 = IMG_TBL;
} elseif( $category=="images" ) {
	$tbl1 = IMG_TBL;	//set image table first
	$tbl2 = GAL_TBL;
}
/*
* assign category name to session var, 
* category is used to check all subsequent admin processes.
*/
$_SESSION['category'] = $category;
$collabel = $category." name"; //list label from category name
/* 
* set initial list view 
*/
$active = (isset($_GET['active'])) ? $_GET['active'] : "Y";
$actstate = ($active=="N") ? "Y" : "N" ;				//get var (opposit of view)
$actlabel = ($active=="N") ? "Inactive" : "Active" ;	//list label
$actbtn = ($active=="N") ? "Active" : "Inactive" ;		//button label (opposit of list)
/*
* set current list sort order
* holds previous label if/when page re-load and no post var
*/
$sortByCol = (isset($_SESSION['sort_by'])) ? $_SESSION['sort_by'] : "id";
/*
* process AJAX get for sort by re-assignment
* if $_GET is set, assign the label id and call assignSortOrder 
* assignSortOrder defined in $categoryControl.php
* returns the table column label for $_SESSION[sort_by], which holds state
*/
if (isset($_GET['label_col']) && (!empty($_GET['label_col']))) {
	$label_click = filter_var($_GET['label_col'],FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_HIGH);
	$sortByCol = assignSortOrder($label_click);
	$_SESSION['sort_by'] = $sortByCol;
}
/*
* categoryListClass parameters for list view
* param string [cat], category name ie gallery, images
* param array [bindparam], array of query placeholders, order dependant
* param string [orderby], column name for sort order ie id, name, date
*/
$params['cat'] = $category;
$params['bindparam']['active'] = $active;
$params['orderby'] = $sortByCol;
$params['ltable'] = $tbl1;
$params['ltable2'] = $tbl2;
$params['collabel'] = $collabel;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>GALLERY ADMIN | <?php echo ucwords($category)?></title>
<link href="../galleryStyle.css" rel="stylesheet" type="text/css" media="screen">
<!-- VIEWPORT force mobile devices to use device-width instead
<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0" />
<link rel="stylesheet" type="text/css" media="only screen and (min-device-width: 768px) and (max-device-width: 1024px) and (orientation: landscape)" href="../../css/tablet.css" />
-->
<!-- JQUERY/JAVASCRIPT-->
<script type="text/javascript" src="http://code.jquery.com/jquery-latest.js"></script>
<!-- popup 4.x -->
<script type="text/javascript">
//Edit record pop up function
function openpopup(url, w, h) {
	winpop = window.open(url,
	'_blank',
	'top=200,screenx=25,left=200,screeny=25,width=' + w + ',height=' + h + ',scrollbars=yes,location=no,menubar=no,resizable=yes,status=no,toolbar=no');
}
function closepopup() {
	if(false==winpop.closed) {
		winpop.close();
	}else {
		alert('Window already closed!');
	}
}
</script>
<!-- orientate top -->
<script type="text/javascript">
$(document).ready(function() {
   $('body').scrollTop($(document).height());
});
</script>
</head>
<body>
<div id="wrapper">
 <h2 class="subhead"><?php echo ucwords($category)?> List&nbsp;&nbsp;
 <span class="listviewdesc">Showing <?php echo $actlabel." ".$category?>,&nbsp;&nbsp;click Edit icon to popup change form.</span></h2>
 <div id="catlist">
 <?php
 	$cObj = accesswrapper::factory($params);
 	$cObj->listrequest();
 	unset($params);
 	unset($cObj);
 ?>
 </div>
 <a href="addform.php" target="_self">
 <div id="navbtn" class="button buttontxt" type="button"><span class="navbtntxt">Add <?php echo ucwords($category)?></span></div></a>&nbsp;&nbsp;
 <a href="list_viewer.php?active=<?php echo $actstate?>&view=<?php echo $category?>" target="_self">
 <div id="navbtn" class="button buttontxt" type="button"><span class="navbtntxt">View <?php echo ucwords($actbtn)?></span></div></a>&nbsp;&nbsp;
 <a href="dashboard.php" target="_self">
 <div id="navbtn" class="button buttontxt" type="button"><span class="navbtntxt">Admin Home</span></div></a>
</div>
</body>
</html>