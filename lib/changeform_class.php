<?php
/**
* CHANGE FORM MODEL
* @version 1.0 2015-05-11
* @since 1.0, 2014-07-31
*
* accepts params from each category list params assign in chgForm.php head
* produce form with pre-populated fields
*/
/*
* GET RECORD CLASS
* $dataObj Object ( 
*  [query]=>, 
*  [result]=>array([0]=>array([field_id]=int,[field_name]=>string,[field_date]=>date,...),[1]=>array([join_id]=>int,[join_name]=>string),...)),
*  [bind]=>,
*  [idfld]=>string,
*  [nmfld]=>string)			
*/
class record {
	var $params;
	
	function __construct($params) {
		$this->params = $params;
	}
	
	public function recordrequest() {
		$qresObj = $this->prepared();
		$this->buildhtml($qresObj);
	}
	/*
	* prepared method 
	* constructs the query string, instantiates datapull class
	* uses datapull::dbquery, class for handling all db transactions
	* return object $dataObj, see class comments for object description
	*/
	public function prepared() {
		/* define table to query, instantiate new db object */
		$table = $this->params['table']; //set in changeform L43		
		$dataObj = new datapull();
		
		/* 
		* uses different tables this class::method
		* !!-- field list must be primary key (id), name, date, active... --!!
		*/
		switch($table){
			case $table == "galc_cat" :
				$query = "SELECT * FROM $table WHERE galc_id=? LIMIT 1";
					$dataObj->dbquery($query,$this->params['bindparam']['recid']);
					unset($query);
			break;
			case $table == "gali_img" :
				$query = "SELECT * FROM $table WHERE gali_id=? LIMIT 1";
					$dataObj->dbquery($query,$this->params['bindparam']['recid']);
					unset($query);
			break;
			
		}	
		
		if(!empty($dataObj->result)) {
			return($dataObj);
		} else {
			$mes = "L51: record::recordrequest::datapull->dbquery returned empty result! Exiting now. \n";
			$mes .= "L50: Query string: ".$query."\n";
			error_log($mes);
			exit();
		}
	}
	/*
	* Format data and ouput html stream to viewer
	*/
	public function buildhtml($resObj) {
		if($resObj->fkey) {
			$fk = $resObj->fkey;
			$fn = $resObj->fname;
		}
		$record = $resObj->result[0];
		$akeys = array_keys($record);  		//prototype array([0]=>field_id,[1]=>field_name,[2]=>field_date,[3]=>feild_active,...)
		$parent = $record[$fn];
		unset($record[$fn]);				//not changable at this time
		unset($record[$fk]);				//not changable at this time
		
		echo '<table class="galleryTable" width="315px">';
		echo '<form id="galleryForm" name="galleryForm" method="post" action="changeform.php">'."\n";
		
		/* loop through each record and output html */
		foreach($record as $k=>$v) {
			echo "<tr>\n";
			$fname = explode("_", $k); 		//$fname[0]=prefix,$fname[1]=string: used to trigger code blocks
			
			if ( $fname[1] == "id" || $fname[1] == "date" ) { //make id and date readonly fields
			   $idlabel = ( $fname[0]=="galc" ) ? "Galley" : "Image" ;
			   echo '<td width="30%" align="right">'.$idlabel." ".ucwords($fname[1]).'</td>';
			   echo '<td class="galleryCell" width="70%" align="left">&nbsp;<input type="text" name="'.$k.'" value="'.$v.'" readonly="readonly" /></td>';
			   echo "</tr>\n"; 
			} elseif ( $fname[1] == "active" ) { //print select option list for Active field 
			   echo '<td width="30%" align="right">'.ucwords($fname[1]).'</td>';
			   echo '<td class="galleryCell" width="70%" align="left">&nbsp;<select class="'.$fname[1].'" name="'.$k.'">'."\n";			     
				   if($record[$k]=="Y") {
					   echo '<option value="Y" selected>Yes</option>'."\n";
					   echo '<option value="N">No</option>'."\n";
				   } else {
					   echo '<option value="N" selected>No</option>'."\n";
					   echo '<option value="Y">Yes</option>'."\n";
				   }
			   echo '</select></td>'."\n";
			} else { //output the rest of the fields
			   echo '<td width="30%" align="right">'.ucwords($fname[1]).'</td>';
			   echo '<td class="galleryCell" width="70%" align="left">&nbsp;<input type="text" name="'.$k.'" value="'.$v.'" /></td>'."\n";
			   echo "</tr>\n";
			}
		}	//close foreach loop L18
		
		echo '<tr><td align="left"><input class="button buttontxt" type="submit" name="submit" value="Submit" /></td>';
		echo '<td align="right"><button class="button buttontxt" type="button" onclick=location.href="javascript:window.close()">Close</button></td></tr>'."\n";
		echo '</form>';
		echo '</table>';
	}
}
/*
*
* RECORDS CHANGE CLASS
* version 1.2
* since 1.0, 2014-04-08
*
* update record data from user input
* called when $_POST['submit']==true
*
* param array $clean, result of svalidate function
* param string $tblname, table name set from session var in viewer script
* param string $pattern, table primary key - appended field name ie gal_id, 
*  where grep matched the "id" string
*
* postchange is table and field agnostic, HOWEVER
* datapull has to pull the record as is in the database so that
* the table field names are also the forms input names
*/
class recordchg {
 	var $params;
	
 	function __construct($params){
	 	$this->params = $params;
	 }
 
	 function updaterec() {
		 if($this->params['pattern']) {
		 	 $pattern = '/'.$this->params['pattern'].'/';	//looking for galc_id or gali_id
			 $pkfield = $this->preg_grep_keys($pattern, $this->params['clean'], $flags = 0);
			 $pkkey = key($pkfield); //proto pkfield[gal_id]=>3
			 $id = $pkfield[$pkkey];
		 } else {
			 error_log("Error: the pattern ".$this->params['pattern']." didn't return a result");
		 }
		 
		 $array = $this->params['clean'];
		 $table = $this->params['table'];				//set in changeform L48
		 $akeys = array_keys($array);
		 $fields = '`'.implode('`=?, `',$akeys).'`'; 	//glue the field names together, append the placeholder
		 $fields = $fields."=?";					 	//append extra placeholder on last field		 
		 
		 /* build the query statement, instatniate db object */
		 $query = "UPDATE `$table` SET $fields WHERE `$pkkey`='$id' LIMIT 1";
		 
		 $updateObj = new datapull();
		 $success = $updateObj->dbupdate($query,$array);
		 
		 if($success==1) {
		 		$mes = "Success! Record ID $id Updated.";
		 } elseif($success==0) {
			 	$mes = "L180: recordchg::updaterec::datapull->dbupdate Record NOT Updated! Exiting here.";
			 	error_log($mes);
		 }
		 return $mes;
    }
	/*
	* FUNCTION PREG_GREP_KEYS
	*
	* Allows postchange function to dynamicaly set 
	* the id field name by inverting the post array 
	* so preg_grep can match keys rather than values. Called on L134
	* return string $input, field name that matched $pattern ie. ct_id
	*/
	function preg_grep_keys($pattern, $input, $flags = 0) {
    	return array_intersect_key($input, array_flip(preg_grep($pattern, array_keys($input), $flags)));
	}
}
/**
*
* DATAPULL CLASS
* serves db connection
* NOTE: query success or fail reporting handled by method calling 
* this working.  The only error reporting in this class is if
* there is a connection error.
*/
class datapull {
	var $query;
	var $result;
	var $bind;
	
	protected function conn() {
	  try {
		$DB = new PDO(DB_HOST, DB_UNAME, DB_UPWORD);
		$DB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	  }catch(Exception $e){
		$mes = $date.":L223 changeform_class.php Caught Exception ---"."\n";
		$mes .= $e->getMessage()."\n";
		error_log($mes);  
	  }
	  return $DB;
   }
   /*
   * dbquery method pulls records
   */
   public function dbquery($query,$bind) {
		$DB = (!isset($DB)) ? $this->conn() : $DB;
		$stmt = $DB->prepare($query);
		/*
		* bind params if not null, there are parameters in the query
		* if more than one, loop though each, binding params in order of placeholds
		* else, one parameter to bind
		*/
		if ($bind!=null) {
		  $cnt = count($bind);
		  if ($cnt>1){
		    $i=1;
		    foreach ($bind as $key=>$value) {
			  $stmt->bindParam($i,$value);
			  $i++;
		    }
		  } else {
			  $stmt->bindParam(1,$bind);
		  }
		}
		if($stmt->execute()) {
		  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
	        $this->result[] = $row;
		  }
		}
		unset($stmt);
		return $this->result;
   }
   /*
   * dbupdate method updates records
   */
   public function dbupdate($query,$array) {
		$DB = (!isset($DB)) ? $this->conn() : $DB;
		$stmt = $DB->prepare($query);
		$stmt->execute(array_values($array));
		$success = ($stmt->rowCount()==1) ? 1 : 0;		
		unset($stmt);
		return $success;
   }
}
?>