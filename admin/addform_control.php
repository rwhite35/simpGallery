<?php
/**
* GALLERY AND IMAGE CONTROLLER
* @author Ron White rwhite@october-design.com <rwhite@october-design.com>
* @version 1.5, 2015-02-18
* @since 1.0, 2014
* process form input, adds new db records and file resources
* process map: dashboard > list_viewer > addform(addform_class) > addform_control
*
* @uses addform_class to inserts new records in GAL_TBL and/or IMG_TBL:
* @usage $insertObj = new datapull();
* @usage $insertid = $insertObj->dbmultii($queue);
*
* @uses filesave_class to move upload files from tmp to resource dir
* creates a thumbnail images and a scaled gallery image.
* @useage $fileObj = new mfile($_FILES,$params);
* @useage $fileObj->processimage();
*
* @uses errorReporting_class to report errors to auth users, 
* doesn't destroy staff var unless specified
* @useage $errObj = errorwrapper::factory($errorparams);
* @useage $errObj->errorReport();
*
* @return void
*/
session_start();
error_reporting(E_ALL ^ E_NOTICE);
include '../lib/addform_class.php';
include '../lib/filesave_class.php';
include '../lib/errorReporting_class.php';
include '../GAL_CONF.php';
include 'svalidater.php'; //returns sanitized form input

########### LOCAL VARS ############
###################################
$pass1 = false;							//set condition for testfiletype
$pass2 = false;							//set condition for testfilesize
$rrtoken = $_SESSION['staff']['token'];	//define challenge value
$ppath = basename(GAL_PARENT_DIR); 		//returns trailing name of dir string
$erreturnpath = rawurlencode($ppath."/index.php"); //public page custom error reporting
$returnpath = "list_viewer.php"; 		//L294, relative to addform_control
$gtblprefix = explode("_",GAL_TBL); 	//Array([0]=>galc,[1]=>cat)
$itblprefix = explode("_", IMG_TBL); 	//Array([0]=>gali,[1]=>img)
$pkey_galc = $gtblprefix[0]."_id"; 		//gallery primary key string
$pkey_gali = $itblprefix[0]."_id"; 		//images primary key string
$galArray = array(); 					//gallery fields array
$imgArray = array(); 					//image fields array
$fkey = ""; //L212, 216, 256-258 foreign key & img naming, both ref galc_cat.galc_id
$originals = GAL_PARENT_DIR.DIRECTORY_SEPARATOR.ORIG_DIR_NAME.DIRECTORY_SEPARATOR;
$images = GAL_PARENT_DIR.DIRECTORY_SEPARATOR.IMG_DIR_NAME.DIRECTORY_SEPARATOR;
$date = date('Y-m-d H:i:s', time());

########## INPUT VALIDATION ###########
#######################################
/* checks valid user input, send to public error, possible illegal access */
if(empty($_POST[$pkey_galc]) || empty($_POST[$pkey_gali])) {
	$mes = "L57 addform_control: missing fields possible illegal access.\n";
	error_log($mes);
	$erno = 3;
	header("Location: ../../$errorfile?erno=$erno&page=$erreturnpath");
	exit();
}
/* check rrtoken against authentication challenge */
if ($_POST['token']!=$rrtoken) {
	$mes = "L65 addform_control: authentication challenge failed, illegal access.\n";
	error_log($mes);
	$erno = 3;
	header("Location: ../../$errorfile?erno=$erno&page=$erreturnpath");
	exit();
} else {
	unset($_POST['token']); //were done with token, remove here
}
/*
* test $_FILES upload for MIME type and size limit
* if test fails, instance errorwrapper::factory object and call errorReport method
* $_FILES will be empty if upload file is over php.ini max size
*/
if(isset($_FILES)) {
	$ftype = $_FILES['img_file']['type'];
	$fsize = $_FILES['img_file']['size'];
	testfilesize($pass1,MAX_FILE_SIZE,$fsize);
	testfiletype($pass2,$ftype);
}
if($pass1 == false) {
	$mes = "L85: addform_control, testfilesize function failed with file size $fsize ";
	error_log($mes);
	$erparams=array('cat'=>3,'src'=>"addform_control",'ref'=>"addform",'fault'=>"over file size limit");
	$errObj=errorwrapper::factory($erparams);
	$errObj->errorReport();
	exit();
} elseif ($pass2 == false) {
	$mes = "L92: addform_control, testfiletype function failed with MIME type $ftype ";
	error_log($mes); //log system error reporting
	$erparams=array('cat'=>3,'src'=>"addform_control",'ref'=>"addform",'fault'=>"illegal file type");
	$errObj=errorwrapper::factory($erparams);
	$errObj->errorReport();
	exit();
}
######### PROCESS POST DATA ##########
######################################
/* 
* sanitize post vars, then sort into separate table specific arrays
* the order of the table array are dependent on which add process is running 
* assign galc_* fields to $galArray for gallery table
* assign gali_* fields to $imgArray for image table
* $galArray prototype Array([galc_id]=>null,[galc_name]=>string,...)
* $imgArray prototype Array([gali_id]=>null,[gali_name]=>string,...)
*/
$filteres = svalidate($_POST,$mode=1);
if($filteres['exit']==1) {
	foreach($filteres['err'] as $err) { $mes .= $err."\n"; }
	$erparams=array('cat'=>2,'src'=>"addform_control",'ref'=>"addform",'fault'=>$mes);
	$errObj=errorwrapper::factory($erparams);
	$errObj->errorReport();
	exit();
} else {
    foreach($filteres as $k=>$v) {
	  $keyparts = explode("_", $k);  //get current fields prefix
	  if ($keyparts[0] == $gtblprefix[0]) {  //if match, assign to gallery		  
		  $galArray[$k] = $v;
	  } elseif ($keyparts[0] == $itblprefix[0]) { //if match, assign to images
		  $imgArray[$k] = $v;
	  }
    }
}
/* 
* additional pre processing for gallery array 
* set galc_id to null for gallery table auto_increment
*/
$pattern = '/'.$pkey_galc.'/';
resetprimekey($galArray,$pattern);
unset($pattern);
/* 
* additional pre processing for image array 
* set gali_id to null for image table auto_increment
*/
$pattern = '/'.$pkey_gali.'/';
resetprimekey($imgArray,$pattern);
unset($pattern);   
/*
* add new record switch trigger L157 
* gallery array wont have a description if add image process running
* send to public error incase injection attack attempt
* $gry_trigger int, 1 - add gallery process, 2 - add image process 
*/
$gdescstr = $gtblprefix[0]."_desc";
$qry_trigger = (isset($galArray[$gdescstr])) ? 1 : 2 ;

####### INSERT NEW RECORDS IN DB #######
########################################
/* 
* each table has a unique field list and updates both gallery and images table
* routine programatically builds the query when field and values list sync up
* !!! order of execution is important - note sub query L179 !!!
*/
switch($qry_trigger) {
/*
*
* ADD NEW GALLERY
* insert a new gallery record from user input
* image file is required when adding a gallery, process updates gallery and images table.
*/
  case 1: 
	$key = array_keys($galArray);
	$field = '`'.implode('`, `',$key).'`'; 	//glue col field names with ","
	$marker = substr(str_repeat('?,', count($key)),0,-1); //placeholder for each field
	$binder = array_values($galArray); 		//proto Array([0]=>value1,[1]=>value2,..)
	/*
	* add new gallery record, sub query galc_id for image record foreign key
	*/
	$query = "INSERT INTO `galc_cat`( $field ) VALUES( $marker )";
	$queue[0]['q']=$query;
	$queue[0]['b']=$binder;
	/*
	* add new image record, SELECT galc_id is foreign key
	*/
	$binder = array_values($imgArray); 		//proto Array([0]=>value1,[1]=>value2,..)
	$ftype = explode("/", $ftype); 			//assigned on L80, should be jpeg or png
	$query = "INSERT INTO gali_img ( galc_id, gali_name, gali_type, gali_display, gali_active, gali_date ) VALUES (
	( SELECT galc_id FROM galc_cat ORDER BY galc_id DESC LIMIT 1 ),
	\"$binder[1]\",
	\"$ftype[1]\",
	\"$binder[3]\",
	\"$binder[4]\",
	\"$binder[5]\" 
	)";
	$queue[1]['q']=$query;
	$queue[1]['b']=null;	
	/*
	* new gallery process creates dir and adds in image file
	* return array $insertid, insert ids for gallery dir name and image file name
	* prototype Array([0]=>int galc_cat.galc_id, [1]=>int gali_img.gali_id)
	*/
	$insertObj = new datapull();
	$insertid = $insertObj->dbmultii($queue);
  break;
/*
*
* ADD NEW IMAGE
* gallery galc_id is an image table foreign key, 
* user selected a gallery in the add image form
* using $_FILES['img_file']['type'] instead of user input
* return int $insertid, gali_img.gali_id auto incremented, primary key
*/  
  case 2:
	$binder = array_values($imgArray); //proto Array([0]=>value1,[1]=>value2,..)
	$foreignkey = $gtblprefix[0]."_fkey"; //foreign key string ref galc_cat.galc_id
	$fkey = $galArray[$foreignkey]; //get from gallery array
	$ftype = explode("/", $ftype); //defines on L51, should be jpeg or png		
	if(empty($fkey)) {
	  $mes = "L219 addform_control add image critical stop error, missing foreign key.";
	  error_log($mes);
	  $erparams=array('cat'=>1,'src'=>"addform_control",'ref'=>"addform",'fault'=>"add image foreign key empty");
	  $errObj=errorwrapper::factory($erparams);
	  $errObj->errorReport();
	  exit();
	} else {
	  $query = "INSERT INTO gali_img ( galc_id, gali_name, gali_type, gali_display, gali_active, gali_date ) VALUES ( 
	  \"$fkey\",
	  \"$binder[1]\",
	  \"$ftype[1]\",
	  \"$binder[3]\",
	  \"$binder[4]\",
	  \"$binder[5]\"
	  )";
	$queue[0]['q']=$query;
	$queue[0]['b']=null;
	/*
	* new image process creates image files in various resource dirs
	* return string $insertid, insert id for last image record added
	*/
	$insertObj = new datapull();
	$insertid = $insertObj->dbinsert($queue);
	} //close if condition L208
  break;
} //close switch L:132
unset($query);
unset($queue);
unset($insertObj);

######### PROCESS FILE UPLOAD ########
######################################
/* 
* process image file to resource dirs
* creates or sets directory path here
* requires class mfile, creates image and saves resources
*/
if ( is_array($insertid) && $insertid[0] != null ) { //only gallery insert returns array
  $originals = $originals.$insertid[0]; //place originals here, gallery id always first element
  $images = $images.$insertid[0]; //place viewer images here, gallery id first element
  $filename = $insertid[0]."_".$insertid[1].".".$ftype[1]; //concat new file name for $_FILES image
} elseif( $insertid > 0 && $insertid != null ) { //image process must be int
  $originals = $originals.$fkey; //place originals in project dir
  $images = $images.$fkey; //place viewer image in images/project_dir
  $filename = $fkey."_".$insertid.".".$ftype[1]; //concat new file name for image upload process
} else {
  /* insert id not found, send to error reporting class, nothing to destroy */
  $mes = "L255: addform_control, critical stop error, no insert id from db update.\n";
  error_log($mes);
  $erparams=array('cat'=>1,'src'=>"addform_control",'ref'=>"addform",'fault'=>"insert record missing insert id");
  $errObj=errorwrapper::factory($erparams);
  $errObj->errorReport();
  exit();
}
unset($insertid);
/*
* check for dirs, if already exist, do nothing, 
* otherwise make the dir and set chmod
* first check for originals, then images dirs
*/
$dirset = false;
if (is_dir($originals)){
	$dirset = true;
} else {
	$dirset = mkdir($originals,0700);
}
if (is_dir($images)){
	$dirset = true;
} else {
	$dirset = mkdir($images,0700);
}
/*
* Create image resources for galleries
* for this implementation, file mime type is image files only.
* mfile::processimage creates a resized thumnail and image viewer
*/
if($dirset==true) {
  $params['org'] = $originals;
  $params['img'] = $images;
  $params['fname'] = $filename;
  $params['newfile'] = 1;
  /* leaving as switch for later multi file type support */
  switch($ftype[0]) { 		//$ftype[0] from L184
	case "image" :
		$fileObj = new mfile($_FILES,$params);
		$success = $fileObj->processimage(); //returns true on success or false on fail
		if($success==true) header("Location: $returnpath?active=Y");
		exit();
	break;
  }
}
######### FUNCTIONS AND METHODS #########
#########################################
/*
* function testfiletype
* @param boolean $pass1, passed by reference false by default
* @return boolean true, if passed
*/
function testfiletype(&$pa,$ft,$dev=DEV_LOG) {
	$vt = array("image/jpeg","image/png");
	foreach($vt as $v) { if($ft==$v) $pa=true;}		
	return $pa;	
}
/*
* function testfilesize
* @param boolean $pass2, passed by reference false by default
* @return boolean true, if passed
*/	
function testfilesize(&$pa,$mfs,$fs) {
	$ini_max = ini_get('post_max_size');
	$conf_max = $mfs;
	if(!empty($fs)) {//if not empty, run test
	if($fs <= $ini_max) $pa=true;
	if($fs <= $conf_max) $pa=true;
	}
	return $pa;
}
/* 
* function resetprimekey
* set primary key (galc_id, gali_id) to null so *_id is preserved in db 
* returns void
*/
function resetprimekey(&$array,$pattern) {
	$primekey = array_intersect_key($array, array_flip(preg_grep($pattern, array_keys($array), $flags)));
	$pkey = key($primekey); //prototype pkey[galc_id]=>3
	$array[$pkey] = null;
	return $array;
}
/* 
* helper function for debugging 
* @param mixed $vardump, container for scolar and array vars
* prototype - array($string, $array, $int,...)
* @param string $date, current data
* @param string $devlog, path to dev_mes.txt see GAL_CONF for path
* @return mixed $vardumpArray, 
* when assigning arrays use: array_push($vardumpArray,print_r($array,true));
*/
function outputvardump($vardump,$date,$devlog) {
  $i = 0;
  	$mes = $date." addform_control variables: "."\n";
  	foreach($vardump as $key=>$value) {
	  $mes .= $i.": key is ".$key." value is ".$value."\n";
	  $i++;
  	}
  	error_log($mes,3,$devlog);
  	return void;
 }
?>