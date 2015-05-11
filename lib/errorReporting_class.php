<?php
/**
*
* ERROR REPORTING CLASS FOR ADMINS
* custom error reporting on failed process, 
* may unset session variables when necessary
* @author Ron White rwhite@october-design.com, <rwhite@october-design.com>
* @version 1.0, 2015-02-18
* @since 1.0, 2015-02-18
*
* @usage $errObj = errorwrapper::factory($errorparams);
* @usage $errObj->errorReport();
*
* @params array $errorparams, user defined error message and functions
* prototype - Array([cat]=>int triggers instance of einterface, 
* 					[src]=>string calling script file name without ext, 
*					[ref]=>string referring script file name, omit ext,
*					[fault]=>string brief custom error message)
*/
interface einterface {
	function errorReport();
}
/*
* DATABASE FAULTS ---
* Error reporting related to database transactions
*/
class dbfault implements einterface {
  var $params;
	
  function __construct($params) {
	$this->params = $params;
  }
  
  public function errorReport() {
  	echo $this->assembleLine();
  }
  
  private function assembleLine() { 
	$ui_mes = $this->params['src']." said ".$this->params['fault'];
  	$back_path = $this->params['ref'].".php";
  	$style_str = "position:relative;top:10%;left:10%;width:75%;padding:10px;background-color:#c7c7d3;border:1px solid black;font-family:sans-serif;font-size:0.90em;";
  
  	$message_body = <<<EOMESSBODY
  	<div id="errorbox" style="$style_str">
  	<p class="errmess"><span style="font-weight:900;color:red">DB Transaction Error</span><br>$ui_mes, please try again.<br>
  	<a href="javascript:window.close()">Close</a></p></div>
EOMESSBODY;
  	echo $message_body;
  }
  
}
/*
* SYSTEM FAULTS ---
* Error reporting related to file system resources
* missing source or image files
*/
class sysfault implements einterface {
  var $params;
	
  function __construct($params) {
	$this->params = $params;
  }
  
  public function errorReport() {
	echo $this->assembleLine();
  }
  
  private function assembleLine() { 
	$ui_mes = $this->params['src']." said ".$this->params['fault'];
  	$back_path = $this->params['ref'].".php";
  	$style_str = "position:relative;top:10%;left:10%;width:75%;padding:10px;background-color:#c7c7d3;border:1px solid black;font-family:sans-serif;font-size:0.90em;";
  
  	$message_body = <<<EOMESSBODY
  	<div id="errorbox" style="$style_str">
  	<p class="errmess"><span style="font-weight:900;color:red">Form Error</span><br>$ui_mes<br>
  	<a href="javascript:window.close()">Close</a></p></div>
EOMESSBODY;
  	echo $message_body;
  }

  
}
/*
* FILE UPLOAD/PROCESSING FAULTS ---
* Error reporting when file outside environment variables
* keep session staff, nothing to detroy
*/
class filefault implements einterface {
  var $params;
	
  function __construct($params) {
	$this->params = $params;
  }
  
  public function errorReport() {
	  echo $this->assembleLine();
  }
  
  private function assembleLine() {
  	$ui_mes = $this->params['src']." said ".$this->params['fault'];
  	$back_path = $this->params['ref'].".php";
  	$style_str = "position:relative;top:10%;left:10%;width:75%;padding:5px;background-color:#c7c7d3;border:1px solid black;font-family:sans-serif;font-size:0.90em;";
  
  	$message_body = <<<EOMESSBODY
  	<div id="errorbox" style="$style_str">
  	<p class="errmess"><span style="font-weight:900;color:red">File Processing Error</span><br>$ui_mes, please try again.<br>
  	<a href="javascript:window.close()">Close</a></p></div>
EOMESSBODY;
  	echo $message_body;
  }
  
}
/*
* PUBLIC INTERFACE CLASS
* accepts input from calling script and routes
* category id routes process to appropriate sub class
*/
class errorwrapper {
	public static function factory($params) {
		$cat = $params['cat'];
		switch($cat){
			case $cat == 1 :
				$inst = new dbfault($params);
			break;
			case $cat == 2 :
				$inst = new sysfault($params);
			break;
			case $cat == 3 :
				$inst = new filefault($params);
			break;
		}
		if ($inst instanceof einterface) { return $inst; }
	}
}