<?php
session_start();
/**
* LIGHTBOX AND GALLERY FACTORY
* @version 3.0, 2015-03-14
* @since 1.0, 2013
*
* for cjp, delivering both lightbox thumbs and individual project
* gallery presentation in the viewer (index.php).
*
* @usage $cObj = accessfinal::factory($params);
* @usage $cObj->jobrequest();
* 
* @param array $params, unique to each sub class, see class comments
*/
/*
* pforeman interface method
* each sub class must implement jobrequest
*/
interface pforeman {
	function jobrequest();
}
/**
*
* ######### LIGHTBOX SUB CLASS #########
* displays a lightbox of all active project galleries, 3 thumbs per row
* @param array $params, defined in index.php(viewer), assigns [ticket]=lightbox, [active]=Y/N
* @return obj HTML, active link to each gallery from thumbnail
*/
class lightbox implements pforeman {
  var $params;
	
  public function __construct($params) {
	$this->params = $params;
  }
	
  /* 
  * --- PUBLIC INTERFACE ---
  * jobrequest interface method, visibility must be public
  * called from the viewer, $cObj->jobrequest();
  */
  public function jobrequest() {
	$qresult = $this->prepared();
	$this->buildlightbox($qresult);
  }
  /* 
  * 
  * --- DATA PULL ---
  * a.table query is dependent on the sub query b.table for gallery id match
  * c.table retrieves the file type extension (jpeg or png) 
  *
  * requires datapull::dbquery($queue), 
  * return array $result, array of projects from gal_cat table
  */
  private function prepared() {
	$queue = array();			//transaction queue
	$qres = array();
	$q = "SELECT a.galc_id, a.galc_name, (SELECT b.gali_id FROM gali_img AS b WHERE b.galc_id=a.galc_id LIMIT 1) AS gali_id, (SELECT c.gali_type FROM gali_img AS c WHERE c.galc_id=a.galc_id LIMIT 1) AS gali_type FROM galc_cat AS a WHERE a.galc_active=?";
	$queue[0]['q'] = $q;
	$queue[0]['b'] = $this->params['active'];
	$qObj = new datapull();
	$qres = $qObj->dbquery($queue);
	unset($queue);	
	unset($qObj);
	return $qres;
  }
  /*
  *
  * -- HTML THUMBNAILS OUTPUT ---
  * buildlightbox method outputs each lightbox thumbnail from db query result
  * if query result is empty (no active galleries), return UI notice string
  * param array $qresult,  multi-dim array contains records for lightboxs
  * prototype - array([1]=>(array([0]=>(array(galc_id=>int,galc_name=>string,gali_id=>int,gali_type=>string),
  *								  [1]=>(array(galc_id=>int,galc_name=>string,...))))
  * return stream html, displays lightbox thumbnails
  */ 
  private function buildlightbox($qresult) {
   /* start lightbox output here */
   echo '<div id="lightbox">'."\n";
   echo '<h2 class="subhead"><strong>PORTFOLIO LIGHTBOX</strong></h2>'."\n";
   echo '<div id="livearea">'."\n";
   /* start individual lightbox thumbs here */
   if (is_array($qresult) && !empty($qresult[1][0]['galc_id'])) {
    foreach($qresult as $value) {
     foreach($value as $k=>$v) {
	    $fpath = "thumb".DIRECTORY_SEPARATOR.$v['galc_id']."_".$v['gali_id'].".".$v['gali_type'];
    	echo '<div id="gframe">'."\n";
		echo '<div id="gthumbs">'."\n";
		printf ("<a href='?gid=%s' target=\"_self\">",$v['galc_id']);
		printf ('<img id="thumbimg" src="%s" alt="%s" title="%s"></a><br>',$fpath,$v['galc_name'],$v['galc_name']);
		echo '</div>'."\n";
		echo '<span class="gtitle">'.$v['galc_name'].'</span>'."\n";
		echo '</div>'."\n";
	  }
	 }
   } else {
	echo '<span class="rnotice">Sorry,&nbsp;No active project galleries at this time.</span>';
   }
  echo '</div></div>'."\n"; //close lightbox and lightbox div
  }
  
} //CLOSE lightbox CLASS
/**
*
* ######### gallery IMAGES SUB CLASS ##########
* display individual project gallery images
* @param array $params, [active]=>string Y/N defines active state for query, [winwidth]=>int width of active window
* @return obj HTML, gallery thumbnails in lightbox
*/
class gallery implements pforeman {
  var $params;
  
  public function __construct($params) {
	$this->params = $params;
  }

  public function jobrequest() {
	$qresult = $this->prepared();
	$this->buildgallery($qresult);
  }
  /*
  * 
  * --- QUERY DB FOR IMAGES ---
  * calls datapull::dbquery method, returns results object
  * return array $qres, multidim array of image records
  */
  private function prepared() {
	$queue = array();
	$qres = array();
	$q = "SELECT m.gali_id, m.galc_id, m.gali_type, m.gali_name, g.galc_name FROM gali_img AS m JOIN galc_cat AS g ON m.galc_id=g.galc_id WHERE m.galc_id=? AND m.gali_active='Y'";
	$queue[0]['q'] = $q;
	$queue[0]['b'] = $this->params['gid'];	//defines which project gallery
	$qObj = new datapull();
	$qres = $qObj->dbquery($queue);
	unset($queue);	
	unset($qObj);
	return $qres;
  }
  /*
  *
  * --- GALLERY IMAGES ---
  * buildproject method process image records and generates html stream 
  * param array $qresult, images belonging to a specific project gallery
  * prototype - Array([1]=>Array([0]=>Array([gali_id]=>int,[galc_id]=>int,[gali_name]=>string,...),
  * 				  [2]=>Array([0]=>Array([gali_id]=>int,[galc_id]=>int,[gali_name]=>string,...))
  */ 
  private function buildgallery($qresult) {
   $galleryname = $qresult[1][0]['galc_name'];
   $screenwidth = $_SESSION['screenwidth']; //defined in getScreenWidth.php
   /* start html output */
   echo '<div id="gallerymenubar">';
   echo '<h2 class="gallerysubhead">'.strtoupper($galleryname).' GALLERY</h2>&nbsp;<button id="backbtn" class="buttontxt">Back to Lightbox</button>'."\n";
   echo '</div>'."\n";
   echo '<div class="preswrapper">'."\n";
   printf ('<div id="pxs_container" class="pxs_container" style="width:%s">'."\n",$this->params['pxswidth']);
   echo '<div class="pxs_loading">Loading</div>'."\n";
   echo '<div class="pxs_slider_wrapper">'."\n";
   echo '<ul class="pxs_slider">'."\n";
   /* start individual images */
   if (is_array($qresult) && !empty($qresult[1][0]['gali_id'])) {
    foreach($qresult as $value) {	//loop through each array    
	  foreach($value as $v) {		//inner loop $v Array([gali_id],[galc_id]...)
	   $imagename = $v['galc_id']."_".$v['gali_id'].".".$v['gali_type'];
	   $dirname = $v['galc_id'];	//images sub dir name inherits gallery id
	   $fpath = "images".DIRECTORY_SEPARATOR.$dirname.DIRECTORY_SEPARATOR.$imagename;
	   if(is_file($fpath)) {
	   	 $nwidth = $this->calcimgsize($fpath,$this->params['pxswidth'],$screenwidth); //calculates image width against container width
	     printf ('<li><img src="%s" width="%spx" alt="%s"><span class="gtitle" style="color:#000">%s</span></li>',$fpath,$nwidth,$v['galc_name'],$v['gali_name']);
	     echo "\n";
	   } else {
		 printf ('<li><img src="img/missing_icon.png" width="585px" alt="Missing Image"><span class="gtitle" style="color:#000">Nards, I can\'t seem to find that image.</span></li>');
	   } //do nothing, continue loop
     } 	//close inner loop L140
    }	//close outter loop L139
   } else { //if the array was empty, non of the galleries are active, show message instead
	 echo '<span class="rnotice">&nbsp;No images for this gallery at this time.</span>';
   }
   echo '</ul>';
   echo '</div></div></div>';
  } //close buildgallery
  /* 
  * --- HELPER METHOD --- 
  */
  /*
  * calcimgsize method
  * calculate the image width and height for slide show presentation
  * @version 2.0, 2015-03-17
  * @require library GD, getimagesize function GD 2.0 specific
  * @param string $fpath, file path to current image
  * @param int $cwidth, div container width (in pixels) for images
  * @param int $wwidth, viewer window width, for mobile web
  * @return int $newwidth, new image width for img tag
  */
  private function calcimgsize($fpath,$cwidth,$swidth) {
	list($iwidth,$iheight) = getimagesize($fpath);
	if($iwidth <= $cwidth) { //scale image up
		$perc = round($cwidth / $iwidth, 3);
		$newwidth = round($iwidth * $perc, PHP_ROUND_HALF_UP);
	} elseif ($iwidth > $cwidth) { //scale image down
		$perc = round($iwidth / $cwidth, 3);
		$newwidth = round($iwidth / $perc, PHP_ROUND_HALF_DOWN);
	} else {
		$newwidth = $iwidth * .95; //same size, reduce 5%
	}
	/* set image width based on mobile devices */
	if($swidth <= 1024 && $swidth >= 768) {
		$newwidth = round($newwidth * .80, 2);
	} elseif($swidth <= 767 && $swidth >= 320) {
		$newwidth = round($newwidth * .40, 2);
	}
	return $newwidth;
  }
} //CLOSE gallery CLASS
/**
*
* ########## IMAGE CLASS ###########
* 2015-02-20 defined as empty, not using at this time
* @todo, 2015-05-11 algorithm to optimze image width and height for gallery presentation
*/
class image implements pforeman {
public function jobrequest() {}
}
/**
*
* ########## DATAPULL CLASS ############
* instantiates db connection
* queries db, then returns a result
* by this point in execution, any user input has already been 
* sanitized and formated for db transactions.
*/
class datapull {
	var $query;
	var $result;
	var $bind;
	
	protected function conn() {
	  try {
		$DB = new PDO(DB_HOST, DB_UNAME, DB_UPWORD);
		$DB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Set Errorhandling to Exception
		return $DB;
	  } catch( Exception $e ) {
		$mes = "L226: projectGallery_class.php Caught Exception --- ";
		$mes .= $e->getMessage();
		error_log($mes);  //uses system error report  
	}
   }
   /*
   *
   * dbquery method runs queries from queue
   * using transactions for multiple querys per requestion, for example,
   * describing one table and selecting records from another.
   * param array $queue, This class doesn't require multiple queries, leaving in for future development
   * prototype -  
   * return object $result, prototype Obj([query]=>,[result]=>array([0]=>([Field]=>$field_name,[type]=>$data_type,...),[bind]=>
   */
   public function dbquery($qqueue) {
	  $DB = (!isset($DB)) ? $this->conn() : $DB;
	  try {
	  	$DB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Set Errorhandling to Exception
		$DB->beginTransaction();
		/* optimize and execute each query in query array */
		$i=1;
		foreach($qqueue as $query) {
		  $stmt = $DB->prepare($query['q']);
		  if ($query['b']!=null) {
			  $stmt->bindParam($i,$query['b']);
		  }
		  $stmt->execute();
		  $result[$i] = $stmt->fetchAll(PDO::FETCH_ASSOC);  
		  $i++;
		}
		/* commit transactions to process */
		$DB->commit();
	  
	  } catch(PDOException $e) {
		$DB->rollBack();
		echo "Failed: " . $e->getMessage();		
		error_log($e); 
	  }
		unset($stmt);
		return $result;
   }
   /* 
   * 2015-02-20 defined but not using at this time
   * todo, 2015-05-11 updates database post image processing 
   */
   public function dbupdate($query,$array) {
   /*
		$DB = (!isset($DB)) ? $this->conn() : $DB;
		//optimize query statement
		$stmt = $DB->prepare($query);
		//execute the transaction
		$stmt->execute(array_values($array));
		//return UI feedback on success or failure
		$success = ($stmt->rowCount()==1) ? "Success! Record Updated." : "Not Updated! Refresh List View.";		
		unset($stmt);
		return $success;
  */
   }
   
} //CLOSE DATAPULL CLASS
/**
*
* HELPER CLASS DEV LOG
* @usage $errObj = new devlog();
* @usage $errObj->report($mes);
* @param array $mesa, prototype array([0]=>mixed mesage 1, [1]=>mixed message 2, ...)
* @return void
*/
class devlog {
	var $mesa;							//array, of messages to log
	
	public function report ($mesa) {
	  require_once 'GAL_CONF.php';		//DEV_LOG path to client root
	  $lev = 3;							//send messages to user defined log
	  $cnt = count($mesa);
	  foreach($mesa as $value) {
		error_log($value."\n",$lev,DEV_LOG);		
	  }
	}
}
/**
*
* PUBLIC FACING ACCESSWRAPPER
* @api
* @return void
* instantiates the sub class passed from viewer
* see header coments for usage description
* 
* @param array $params, [ticket] subclass name
*/
class accesswrapper {
	public static function factory($params) {
		
		$ticket = $params['ticket'];
		
		//compare category name to table short names
		switch($ticket){
			case $ticket=="lightbox":
				$inst = new lightbox($params);
			break;
				case $ticket=="gallery";
				$inst = new gallery($params);
			break;
			case $ticket=="image";	//2015-05-11 defined, but not in use
				$inst = new image($params);
			break;
		}
		
		if ($inst instanceof pforeman) {
			return $inst;
		} else {
			return void;
		}
	}
}
?>