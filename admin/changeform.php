<?php
session_start();
error_reporting(E_ALL ^ E_NOTICE);
/**
*
* CHANGE FORM VIEWER
* updates both gallery and image tables
* process map: dashboard > list_viewer > [click edit] > changeform
* @version 2.5 2015-02-20
* @since 1.0 2014-08-17
*/
/**
* function svalidate($formpost,$mode)
* @param array $formpost, user modified post data
* @param int $mode, level of sanitizing to apply 1 - lowest, 5 - highest
* @return array $filteres, sanitized post input or false on failure
*/
include 'svalidater.php';
/**
* get the object (gallery/image) data on initial load
* @usage $obj = new record();
* @usage $obj->recordrequest($params);
* @params array $params, [tblname] db table name, [recid] record id, [active] record active;
* @return stream HTML, form fields with input values prepopulated
*
* update the objects record on form post submit:
* @usage $obj = new recordchg($params);
* @usage $message = $obj->updaterc();
* @params array $params, [clean] sanitized post data, [table] db table name, [pattern] primary key
* @return string $message, update process feedback on success or failure 
*/
include '../lib/changeform_class.php';
/**
* admins error reporting objects, doesn't destroy staff var unless specified
* returns process to previous page
* @useage $errObj = errorwrapper::factory($errorparams);
* @useage $errObj->errorReport();
*/
include '../lib/errorReporting_class.php';
/**
* installation specific variables for gallery
* @param string GAL_TBL,  gallery table name
* @param string IMG_TBL,  image table name
*/
include '../GAL_CONF.php';
?>
<?php
if(empty($_SESSION['staff']['uid'])) {
  $dir = basename(GAL_PARENT_DIR);
  $errorpath = rawurlencode($dir."/index.php");
  $erno = 3;  //session expired
  header("Location: ../../error.php?erno=$erno&page=$errorpath");
  exit();
} else {
  $category = $_SESSION['category']; 		//set in list_view L40
  $recid = ( isset($_GET['recid']) ) ? filter_var($_GET['recid'],FILTER_SANITIZE_NUMBER_INT) : null ;
  /* set db tables, serves both galleries and images tables */
  if( $category=="gallery" ) {
	$chngtbl = GAL_TBL;
  } elseif( $category=="images" ) {
	$chngtbl = IMG_TBL;
  }	
  /* todo 2014-08-26 dynamically set $active, admin may not be able to update inactives */
  $active = "Y";
}
######### PROCESS POST DATA ##########
######################################
/* 
* sanitize post vars, then sort into separate process specific arrays
* the order of the fields/array are dependent on which add process is running 
* assign galc_* fields to $galArray for gallery processing
* assign gali_* fields to $imgArray for image processing
* $galArray prototype Array([galc_id]=>null,[galc_name]=>string,...)
* $imgArray prototype Array([gali_id]=>null,[gali_name]=>string,...)
*/
$filteres = svalidate($_POST,$mode=1);
if($filteres['exit']==1) {
	foreach($filteres['err'] as $err) { $mes .= $err."\n"; }
	$erparams=array('cat'=>2,'src'=>"changeform",'ref'=>"list_viewer",'fault'=>$mes);
	$errObj=errorwrapper::factory($erparams);
	$errObj->errorReport();
	exit();
} elseif(count($filteres)>2) {
    $keys = array_keys($filteres);			//get primary key for query binder
    $rkey = array_search("submit",$keys, true);
    $removeSubmit = ( $rkey!=false ) ? "submit" : false;
    if ( $removeSubmit!=false ) unset( $filteres[$removeSubmit] );
	/* instantiate params array for update query */
  	$updateparams['clean'] = $filteres;
  	$updateparams['table'] = $chngtbl;
  	$updateparams['pattern'] = $keys[0];
  	unset($chngtbl);
  	/* updaterec returns message string on success */
  	$postObj = new recordchg($updateparams);
  	$message = $postObj->updaterec();
} else {
	/* 
    * initial load, changeform model record::recordrequest parameters
    * set on initial page load, required for pulling data 
    * $_GET[recid] set in categoryClass $category::listrequest->buildhtml
    */
    $params['table'] = $chngtbl;
    $params['bindparam']['recid'] = $recid;
    $params['bindparam']['active'] = $active;
    unset($chngtbl);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>GALLERY ADMIN | EDIT RECORD</title>
<link href="adminStyle.css" rel="stylesheet" type="text/css" media="screen">
<link href="../../css/style.css" rel="stylesheet" type="text/css" media="screen">
<!-- VIEWPORT force mobile devices to use device-width instead -->
<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0" />
<link rel="stylesheet" type="text/css" media="only screen and (min-device-width: 768px) and (max-device-width: 1024px) and (orientation: landscape)" href="../../css/tablet.css" />
<!--JAVASCRIPT/JQUERY-->
<script type="text/javascript" src="../../js/jquery-1.11.1.js"></script>
<script type="text/javascript" src="../../js/jquery-migrate-1.2.1.js"></script>
<!-- refresh list view -->
<script type="text/javascript"> //change form popup
function openpopup(url, w, h) {
	winpop = window.open(url,
	'_blank',
	'top=200,screenx=25,left=200,screeny=25,width=' + w + ',height=' + h + ',scrollbars=yes,location=no,menubar=no,resizable=yes,status=no,toolbar=no');
}
function closepopup() {
	if(false==winpop.closed) {
		winpop.close();
	} else {
		alert('Window already closed!');
	}
}
</script>
</head>
<body>
<div id="fwrapper">
  <div id="fheader">
    <h2 class="subhead"><img src="../img/edit_icon.png" alt="Edit Record" height="35px" />&nbsp;&nbsp;Edit <?php echo ucwords($category)?></h2>
  </div>
  <div id="fform">
	  <?php
	    //ON SUBMIT, FILTER AND CHANGE DATA
	  	if(isset($message)) {
	  	  echo '<div id="fmes">'."\n"; 		//specific to messaging
	  	  echo $message."<br>\n";			//message set by class
	  	  echo "Please close this window to refresh your list.<br><br>\n";
	  	  echo '<span id="refresh" class="close"><a href="javascript:window.close()">Close</a></span>';
	  	  echo "</div>";
	  	} else {
	  	//INITIAL PAGE LOAD
		  $formObj = new record($params);
		  $formObj->recordrequest();
		}
	  ?>
   </div>
</div>
<!--moved here to remove reference errors from slower loading JQuery-->
<script type="text/javascript" language="javascript" src="../../js/val_form.js"></script>
</body>
</html>