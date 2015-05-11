<?php
/**
* MANAGE FILE UPLOAD AND RESOURCES
* @author rondwhite, rwhite@october-design.com <rwhite@october-design.com>
* @version 2.0, 2015-02-18
* @since 1.0, 2014-08-23
*
* takes the uploaded file from /tmp and makes a thumbnail and scaled image files
* process map: dashboard > addform > addform_control(addform_class) > filesave_class
*
* @param array $params, class property defined in addform_control L287-290
*   prototype - Array([org]=>string 	path/to/originals, 
* 					  [img]=>string    path/to/images, 
*					  [fname]=>string   controller generated file name,
*					  [newfile]=>int   trigger)
*
* @param array $file, class property assigned from $_FILES in addform_control L291
*   prototype - Array([name]=>string   real file name,
			          [type]=>mime/type   system defined MIME type, 
			          [tmp_name]=>string   apache tmp/ name,
			          [error]=>int    error code if incountered,
			          [size]=>int   defined in bytes 
*/
class mfile {
  var $params;
  var $file;
	
 function __construct($file,$params) {
	$this->file = $file;
	$this->params = $params;
 }
 /**
 * @api
 * PUBLIC INTERFACE METHOD
 * calls createviewerimg method, resized gallery version
 * calls createthumbnail method, resized thumbnail version
 * called from addform_control L296
 * @return boolean true, true on success, false on fail
 */
 function processimage() {
 	$success = false;
	$ofsize = $this->moveoriginal();
	/* create the viewer image */
	if(isset($ofsize) && $ofsize!=false) {
		$vsuccess = $this->createviewerimg($ofsize);
		$success = ($vsuccess==true) ? true : false ;
	}
	/* create the thumbnail image */
	if(isset($this->params['newfile']) && $this->params['newfile']==1) {
		$tsuccess = $this->createthumbnail($ofsize);
		$success = ($tsuccess==true) ? true : false ;
	}
	return $success;
 }
 /*
 * PRIVATE MOVEORIGINAL METHOD
 * moves temporary image file to originals destination
 * requires GD Lib 2.0 support, no file resizing required
 * @return array $ofsize, width and height of original image file
 * prototype - Array([0]=>int-width, [1]=>int-height)
 */
 private function moveoriginal() {
  	$ofpath = $this->params['org'].DIRECTORY_SEPARATOR.$this->params['fname'];
  	$ofsize = array();
  	$success = "";
	if (is_uploaded_file($this->file['img_file']['tmp_name'])) {
		$ofsize=getimagesize($this->file['img_file']['tmp_name']);
		$success = move_uploaded_file($this->file['img_file']['tmp_name'], $ofpath);
		if ($success == true) {/* do nothing */} else { //bubbles up to addform_control
			$mes = "L66: mfile::moveoriginal move_uploaded_file failed, exiting process.";
			error_log($mes);
			return false;
			exit();
		}
	} else {  //bubbles up to addform_control
			$mes = "L328: mfile::processimages no file present for uploading, exiting process.";
			error_log($mes);
			return false;
			exit();
	}
	return $ofsize;
 }
 /*
 * PRIVATE CREATEVIEWERIMG METHOD
 * create image file for gallery viewer, saves file to images/galc_id/ directory
 * @param int IMG_MAX_WIDTH, 580px wide - set in GAL_CONF
 * @param array $ofsize, file size from GD getimagesize $_FILE['img_file']['tmp_name']
 * prototype - Array([0]=>width, [1]=>height)
 */
 private function createviewerimg($ofsize) {
	require '../GAL_CONF.php';	//need image max width
	/* path variables passed to other methods */
	$vipath = $this->params['img'].DIRECTORY_SEPARATOR.$this->params['fname'];
	$ofpath = $this->params['org'].DIRECTORY_SEPARATOR.$this->params['fname'];
	$fext = explode(".", $this->params['fname']);
	/* calculate new image size for viewer */
	$perc = round(IMG_MAX_WIDTH / $ofsize[0], 3);
	$iheight = round($ofsize[1] * $perc, PHP_ROUND_HALF_DOWN);
	/* resizing requires gd image resource */
	$dest_img_res = imagecreatetruecolor(IMG_MAX_WIDTH,$iheight);	
	$imgObj = new imgresource();
	if ($src_img_res = $imgObj->gdresource($fext=$fext[1],$fpath=$ofpath)) {
		imagecopyresampled($dest_img_res,$src_img_res,0,0,0,0,IMG_MAX_WIDTH,$iheight,$ofsize[0],$ofsize[1]);
	} else { //bubbles up to addform_control
	 	$mes = "L109 mfile::processimage->createviewerimg: source resource id wasn't create, exit here.";
	 	error_log($mes);
	 	return false;
	 	exit();
	}
	/* renderimg method requires a destination resourse id, file path and extention */
	$success = $this->renderimg($file_output=$dest_img_res, $dest=$vipath, $ext=$fext);
	if( $success == true ) {
		return true;
	} else { //bubbles up to addform_control
		$mes = "L119: mfile::processimage->createviewerimg renderimg failed to create file, exit here.";
		error_log($mes);
		return false;
		exit();
	}
 } 
/*
 * PRIVATE CREATETHUMBNAIL METHOD
 * create image file for gallery viewer, saves file to images/galc_id/ directory
 * @param int THUMB_MAX_WIDTH, 250px wide - set in GAL_CONF
 * @param array $ofsize, file size from GD getimagesize $_FILE['img_file']['tmp_name']
 * prototype - Array([0]=>width, [1]=>height)
 */
 private function createthumbnail($ofsize) {
	require '../GAL_CONF.php';
	/* passed to renderimg and gdresource */
	$tbpath = GAL_PARENT_DIR.DIRECTORY_SEPARATOR."thumb".DIRECTORY_SEPARATOR.$this->params['fname'];
	$ofpath = $this->params['org'].DIRECTORY_SEPARATOR.$this->params['fname'];
	$fext = explode(".", $this->params['fname']);
	/* calculate new thumbnail size */
	$perc = round(THUMB_MAX_WIDTH / $ofsize[0], 3);
	$iheight = round($ofsize[1] * $perc, PHP_ROUND_HALF_DOWN);
	/* instantiate destination resource id */
	$dest_img_res = imagecreatetruecolor(THUMB_MAX_WIDTH,$iheight);	
	$imgObj = new imgresource();
	if ($src_img_res = $imgObj->gdresource($fext=$fext[1],$fpath=$ofpath)) {
		imagecopyresampled($dest_img_res,$src_img_res,0,0,0,0,THUMB_MAX_WIDTH,$iheight,$ofsize[0],$ofsize[1]);
	} else {
	 	$mes = "L144 mfile::processimage->createviewerimg: resource id for thumbnail image wasn't create, exit here.";
	 	error_log($mes);
		return false;
		exit();
	}
	/* render image file to thumb/[galc_id], returns true on success */
	$success = $this->renderimg($file_output=$dest_img_res, $dest=$tbpath, $ext=$fext);
	if( $success == true ) {
		return true;
	} else {
		$mes = "L158 mfile::processimage->createthumbnail rendering method failed, exit here.";
		return false;
		exit();
	}
 } 
 /*
 * PRIVATE RENDERIMG METHOD
 * creates a file in to the destination path
 * requires gd lib image resource id
 */
 private function renderimg($file_output,$dest,$ext) {
  $success = false; 
  if ($ext=="jpg" || $ext=="jpeg") {
  	header('Content-Type: image/jpeg');
  	imagejpeg($file_output, $dest, 100);
  	$success = true;
  } elseif ($ext=="png") {
  	header('Content-Type: image/png'); 
  	imagepng($file_output, $dest, 0); //quality value 0: no compression, 9: max compression
  	$success = true;
  } else { //bubbles up to addform_contol
  	$mes = 'L182: mfile::processimage->renderimg failed to recognize file extension, continue on.';
  	error_log($mes);
  	$success = false;
  }
  imagedestroy($file_output);
  return $success;
 }
} //CLOSE MFILE CLASS

/** 
*
* IMAGE RESOURCE ID CLASS
* @since 1.0
* @param sting $fext From params[image_ext], file extension triggers imagecreatefrom$ext
* @param string $fpath Relative path to image file
* @return object, represents image element
*/
class imgresource {

public function gdresource($fext,$fpath) {
 $date = date('Y-m-d H:i:s',time());
 $grfx_obj = '';
 $fext = strtolower($fext);
 if ($fext=="jpg" || $fext=="jpeg") {
  	$grfx_obj = imagecreatefromjpeg($fpath);
 } elseif ($fext=="png") {
  	$grfx_obj = imagecreatefrompng($fpath);
  	imagesavealpha($grfx_obj, true);  //preserve background transparency
  	imagealphablending($grfx_obj, false);  //don't blend grapic pixels with background (like a solid color)
 } else { //bubbles up to parent class::method
  	$mes = 'L441 createElementResource::gdresource failed to recognize extension type';
  	error_log($mes);
 }
	return $grfx_obj;
}
} // CLOSE ELEMENT RESOURCE CLASS
?>