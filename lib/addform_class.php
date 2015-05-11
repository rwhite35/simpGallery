<?php
/**
* ADD FORM CLASS
* @author Ron White rwhite@october-design.com <rwhite@october-design.com>
* @version 2.2 2015-05-26
* @since 1.0, 2013
* 
* output forms for adding new galliers and images called from addform.php viewer
* @usage $formObj = new form($tables);
* @usage $formObj->formrequest();
* @return stream html, output add record form
*
* @param array $tables, table names listed in order of category precident
* prototype - Array([0]=>fist_tbl_toquery, [1]=>second_tbl_toquery)
*
* @return html stream either add gallery or add image
*/
class form {
	var $tables;
	var $token;
	
	function __construct($tablelist,$rrtoken) {
		$this->tables = $tablelist;
		$this->token = $rrtoken;
	}
	/*
	* @api
	* PUBLIC INTERFACE METHOD
	* called from viewer
	* buildform method requires db result
	* @return html stream, output form
	*/
	function formrequest() {
		$qresult = $this->prepared();
		$galclist = $qresult[0];
		$fields = $qresult[1];
		$this->buildform($fields,$galclist);
	}
	/*
	* PREPARED METHOD	
	* describe each category table, class property $table determines table process order
	* select all gallery id and names for form select option menu
	* for a prototype of the datapull::dbquery object, see gallery_addform_c.pages
	* @param array $queue, query string and binder array for each table desciption.
	* prototype - $queue Array([0]=>([q]=>first_tbl_desc,[b]=>binder_array),
							   [1]=>([q]=>second_tbl_desc, [b]=>binder_array),
							   [2]=>([q]=>select_galc_string, [b]=>binder_array))
	* @return array $handle, first array - active tables, second array= - field list
	* prototype - $handle Array([1]=>Array([0]=>(galc_id=>int,galc_name=>string),[1]=>(galc_id=>int,galc_name=>string),
								[0]=>Array([0]=>([0]=>galc_id,[1]=>galc_name,...)[1]=>([0]=>gali_id,[1]=>galc_id,...))
	*/
	protected function prepared() {
		$queue = array();	//transaction queue
		$handle = array();	//processed results
		$i=0;
		foreach($this->tables as $value) {
			$q = "DESCRIBE `$value`";
			$queue[$i]['q'] = $q;
			$queue[$i]['b'] = null;
			unset($q);
			$i++;
		}
		/* get all active or inactive gallery names for image upload process */
		$qcnt = count($queue);	//should be third query for gallery implementation
		$qcnt++;
		$queue[$qcnt]['q'] = "SELECT galc_id, galc_name FROM galc_cat WHERE galc_active=?";
		$queue[$qcnt]['b'] = "Y";
		/* 
		* instantiate datapull class and run the queue, dbquery requires public visibility
		* return array $resarray, class property $table determine which table is first
		*/
		$resObj = new datapull();
		$resarray = $resObj->dbquery($queue);
		if(empty($resarray)) {
			$mes = "L65: form::formrequest::datapull->dbquery returned empty result! Exiting here.";
			error_log($mes);
			unset($resObj);
			exit();
		} else {
		/*
		* pre process $rearray object so only the gallery list and form field list
		* are passed to buildform method, for additional comment see gallery_addform_c.pages			
		*/
			$galclist = array_pop($resarray);
			$handle[0] = $galclist;
			$e=0;
			foreach($resarray as $array) {
			  foreach($array as $value) {				
				  if (isset($value['Field'])) { $keys[$e][] = $value['Field']; } 
			  }
			  $handle[1]=$keys;
			  $e++;
			}
			return $handle;
		}
	}
	/*
	* BUILDFORM METHOD
	* output form to UI viewer
	* @param array $fields, multidim field list for each table, listed by primary table
	* prototype - Array([0]=>Array([0]=>gali_id,[1]=>galc_id,[2]=>gali_name,...),
	*				    [1]=>Array([0]=>galc_id,[1]=>galc_name,[2]=>galc_desc,...))
	* @param array $galclist, multidim of active or inactive galleries
	* prototype - Array([0]=>Array(galc_id=>int,galc_name=>string),[1]=>Array(galc_id=>int,galc_name=string))
	* @return obj html stream, ouput form html
	*/
	function buildform($fields,$galclist) {
		$ftbl = $this->tables[0]; //first listed table name
		$tbl_trigger = ( $ftbl == 'galc_cat' ) ? 1 : 2;
		echo '<table class="galleryTable">'."\n";
		echo '<form id="galleryForm" name="galleryForm" action="addform_control.php" method="post" enctype="multipart/form-data">'."\n";
		/* outer loop splits field list array */
		foreach($fields as $farray) {
		 $fld_order_trigger = ( $farray[0] == "galc_id" ) ? 1 : 2 ; //define if gallery or image fields are list first
		 $fset = $this->getfieldset($fld_order_trigger);
		 if( $fset!=false ){ echo $fset[0]; } //form header string
		  /* inner loop output each form field element */
		  foreach( $farray as $value ) {
		    $parts = explode("_",$value);
		    /* first, ouput hidden input fields with default values */
		    if ( $parts[1]=="id" || $parts[1]=="date" || $parts[1]=="active" ) {
		  	  switch($parts[1]) {
			  	case "id" :
			  		echo '<input type="hidden" name="'.$value.'" value="'.$value.'">'."\n";	//id
			  	break;
			  	case "date" :
			  		$date = date('Y-m-d', time());
			  		echo '<input type="hidden" name="'.$value.'" value="'.$date.'">'."\n"; //date
			  	break;
			  	case "active" :
			  		echo '<input type="hidden" name="'.$value.'" value="Y">'."\n";  //active
			  	break;
		  	  }
		    /* when uploading an image, omitt gallery name and description */
		    } elseif ( $tbl_trigger==2 && $parts[0]=="galc" && $parts[1]=="name" ) {
		      		echo "\n";
		    } elseif ( $tbl_trigger==2 && $parts[0]=="galc" && $parts[1]=="desc" ) {
		      		echo "\n";
		    /* output fields in order of remaining field list */
		    } else {
		      		echo '<tr><td class="galleryCell">'.$fset[2]." ".ucwords($parts[1]).'</td>'."\n";
			  		echo '<td class="galleryCell"><input type="text" name="'.$value.'" size="42" maxlength="42"></td></tr>'."\n";
		    }
		  } //close inner loop
		}  //close outter look
		/* 
			* required - when uploading an image to a gallery, show gallery names in select menu 
		*/
		if( $tbl_trigger==2 ) {
			  echo '<tr><td class="galleryCell">Select A Gallery</td>'."\n";
			  echo '<td class="galleryCell"><select name="galc_fkey">'."\n";
			  echo '<option value="0" selected>Select One</option>'."\n";
			  foreach ($galclist as $array) {				     
				  echo '<option value="'.$array['galc_id'].'">'.$array['galc_name'].'</option>'."\n";
			  }
			  echo '</select></td></tr>'."\n";
		}
		/* 
			* required - file upload when adding a new gallery or an image to a pre existing gallery 
		*/		
		echo '<tr><td class="galleryCell">Upload File&nbsp;(jpeg or png)</td><td class="galleryCell"><input type="file" name="img_file">';
		echo '<input type="hidden" name="token" value="'.$this->token.'">';
		echo '</td></tr>';
		echo '<tr><td class="galleryCell" align="center"><button class="button buttontxt" id="btnsave">Submit</button></td>';
		echo '<td align="center"><a class="button buttontxt" id="btnclose" href="javascript:window.history.back()">Close Form</a></td></tr>';
		echo "</form></table>\n";
	} //close buildhtml
	/*
	* define extra feilds by table name
	* return array $array, [0]=>html frameset, [1]=>html file upload, [2]=>string name
	*/
	function getfieldset($t) {
		if ($t==1) {
		  	$thead = '<tr><th class="galleryHead" colspan=2>Gallery Properties</th></tr>';
		  	$fileupload = null;
		  	$label = "Gallery";
		} elseif ($t==2) {
			$thead = '<tr><th class="galleryHead" colspan=2>Image Properties: <span style="font-size:0.75em">&#042;&nbsp;Must upload image with new gallery.</th></tr>';
			$fileupload = null;
			$label = "Image";
		} else {
		 	return false;
		} 
			$array = array($thead,$fileupload,$label);
		  	return $array;
	} //close getfieldset

} 	//CLOSE FORM CLASS
/**
* INSERT NEW RECORD
* @param array $params, prototype array([gal_name]=> string Some Name, [gal_desc]=> string A description, 
      [gal_active]=> string Y, [gal_date]=> date YYYY-mm-dd)
* @param array $table, names of tables to insert
* @return string $mes, UI feedback on success (1) or fail (0)
*/
class dbrecord {
	var $params;			//post data, sanitized and formatted
	var $table;				//insert table name
	
	function __construct($params,$table) {
		$this->params = $params;
		$this->table = $table;
	}
	
	function addrecord() {
		$table = $this->table;
		
		$keys = array_keys($this->params);
		$fields = '`'.implode('`, `',$keys).'`'; //glue the field names together with ","
		$placeholders = substr(str_repeat('?,', count($keys)),0,-1);

		$q = "INSERT INTO `$table`($fields) VALUES($placeholders)";
		
		$insertObj = new datapull();
		$success = $insertObj->dbinsert($q,$this->params);
		
		if($success==1) {
		 		$mes = "Success! A new gallery had been added.";
		 } elseif($success==0) {
			 	$mes = "L180: recordchg::updaterec::datapull->dbupdate Record NOT Updated! Exiting here.";
			 	error_log($mes);
		 }
		 return $mes;
	}
}
/**
* DATAPULL CLASS
* serves db connection
*/
class datapull {
	var $query;
	var $result;
	var $bind;
	
	protected function conn() {
	  try {
		$DB = new PDO(DB_HOST, DB_UNAME, DB_UPWORD, array(PDO::ATTR_PERSISTENT => true));
	  }catch( Exception $e ) {
		$mes = $date.": addform_class.php Caught Exception ---"."\n";
		$mes .= $e->getMessage()."\n";
		error_log($mes); //uses system error report, check apache or mysql error logs 
	  }
	  return $DB;
   }
   /*
   * dbquery method runs queries from queue
   * return object $result, prototype Obj([query]=>,[result]=>array([0]=>([Field]=>$field_name,[type]=>$data_type,...),[bind]=>
   */
   public function dbquery($qqueue) {
		$DB = (!isset($DB)) ? $this->conn() : $DB;
	  
	  try {
	  	$DB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Set Errorhandling to Exception
		$DB->beginTransaction();
		
		/* optimize and execute each query */
		$resi = 1;
		foreach($qqueue as $query) {
		  $stmt = $DB->prepare($query['q']);
		  if ($query['b']!=null) {
			  $stmt->bindParam(1,$query['b']); //only one bind param passed, ever
		  }
		  $stmt->execute();
		  $result[$resi] = $stmt->fetchAll(PDO::FETCH_ASSOC);
		  $resi++;
		}
		
		/* commit transactions to process */
		$DB->commit();
	  
	  }catch(Exception $e){
		$DB->rollBack();
		echo "Failed: " . $e->getMessage();		
		error_log($e); 
	  }
		unset($stmt);
		return $result;
   }
   /*
   * SINGLE TABLE dbinsert method for add image process
   * addform_control call at L230-232
   * returns int $insertid, last record added prime key
   */
   public function dbinsert($qqueue) {
		$DB = (!isset($DB)) ? $this->conn() : $DB;
		
		try {
		  //optimize query statement
		  $q = $qqueue[0]['q'];
		  $stmt = $DB->prepare($q);

		  //execute the insert statement
		  if($stmt->execute()) {
			$insertid = $DB->lastInsertId();
		  } else {
			  throw new Exception("Error in Image insert: ");
		  }
		
		} catch(Exception $e) {
		$DB->rollBack();
		echo "Failed: " . $e->getMessage();		
		error_log($e); 
		}

		unset($stmt);
		return $insertid;
   }
   /*
   * MULTI TABLE dbinsert method for adding galleries
   * addform_control call this method at L190-195
   * param array $queue, proto Array([0]=>Array([q]=>the first query,[b]=>Array([0]=>id,[1]=>fid,[2]=>name,...)),
   * 								 [1]=>Array([q]=>the second query,[b]=>null if no field list))
   * return array $insertid, prototype Array([0]=>gal_cat.gal_id,[1]=>gal_img.img_id)   
   */
   public function dbmultii($queue) {
		$DB = (!isset($DB)) ? $this->conn() : $DB;
	  
	  try {
	  	$DB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Set Errorhandling to Exception
		$DB->beginTransaction();
		
		foreach($queue as $query) {
		  $stmt = $DB->prepare($query['q']);
		  if($query['b']!=null) { //has a field list of values that match column list
		  	$stmt->execute(array_values($query['b']));
		  } else {
			$stmt->execute();
		  }
		  $insertid[] = $DB->lastInsertId();
		}
		/* commit transactions to process */
		$DB->commit();
	  
	  }catch(Exception $e){
		$DB->rollBack();
		echo "Failed: " . $e->getMessage();		
		error_log($e); 
	  }
		unset($stmt);
		return $insertid;
   }
}