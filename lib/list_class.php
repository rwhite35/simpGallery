<?php 
/**
*
* GALLERY FACTORY FOR LIST SERVICES
* @version 1.0
* @since 1.0, 2014-05-08
*
* @param array $params, [cat] Category name; [orderparam] Sort by; [bindparam] Query bindParams
* @usage $cObj = accessfinal::factory($params);
* @usage $cObj->listrequest();
*/
interface listmanager {
	function listrequest();
}
/**
*
* GALLERY MODEL
* @version 1.0
* @since 1.0, 2014-05-08
* @param array $params, passed to constructor holds class properties
* @return html stream, output a list of gallerys
*/
class vlist implements listmanager {
	public $params;
	
	function __construct($params) {
		$this->params = $params;
	}
	/*
	* interface method returns html stream
	* param array $qresult, m_gallery table records 
	*/
	function listrequest(){
		$qresult = $this->prepared();
		if(!empty($qresult[0])) {
			$parhtml['qarray'] = $qresult;
			$parhtml['ltable'] = $this->params['ltable'];	//prefix_suffix
			$parhtml['ltable2'] = $this->params['ltable2'];  //called L200 html::buildhtml
			$parhtml['collabel'] = $this->params['collabel'];
			
			$htmlObj = new html($parhtml);
			$htmlObj->buildhtml();
		} else {
			echo '<h2 class="adminstr" style="font-size:0.90em"><br>&nbsp;&nbsp;Your query returned an empty result! Nothing to change.</h2><br>';
		}
	}
	/* 
	* return array $qresult, called from listrequest method L35 
	*/
	function prepared() {
		/* build table name */
		$prefix = explode("_",$this->params['ltable']);		//ex [0]=>galc,[1]=>cat
		
		/* build order by field name */
		$odbystr = $prefix[0]."_".$this->params['orderby']; //could be _id (default), _name, _date
		
		if($odbystr==$prefix[0]."_name") {
			$orderby = $odbystr." ASC"; 					//names ascend form A to Z
		} else {
			$orderby = $odbystr." DESC"; 					//numbers and dates decend from highest to lowest
		}

		/* build the query statement */
		$table = $this->params['ltable'];
		$activecol = $prefix[0]."_active";
		$q = "SELECT * FROM $table WHERE $activecol=? ORDER BY $orderby";
		
		/* assign execute parameters to array */
		$qbindparam = $this->params['bindparam'];			//$qbindparam=array([active]=>"Y")
		
		/* instantiate the query object, run the db query */
		$queryObj = new datapull($q,$qbindparam);
		$queryObj->dbquery(); 								//return object, properties include [result] the tbl records, 
															//[query] the query ran, and [bind] the bindParam sent in
		/* 
		* assign results object property to array variable and return query result 
		* object prototype: queryObj[result]=>array([0]=>array([fld_name]=>value,...)) 
		*/
		$qresult = $queryObj->result;		
		return $qresult;
	} //close prepared()
} //CLOSE gallery CLASS ---
/**
*
* HTML OUTPUT CLASS
* @version 1.5, 2015-02-11
* @since 1.0, 2014-05-08
* @param array $parhtml, passed to constructor from Gallery Class
* prototype - 
* @return html stream, output a list of gallerys
*/ 
class html {
	/* class properties */
	public $catstring;
	public $ltable;
	public $ltable2;
	public $collabel;
	public $records;  //prototype $resArr=array([0]=>array([field_name]=>$value,..))
	
	function __construct($params) {
		$this->catstring = $params['cat'];
		$this->ltable = $params['ltable'];
		$this->ltable2 = $params['ltable2'];
		$this->collabel = $params['collabel'];
		$this->records = $params['qarray'];
	}

	function buildhtml() {
		$resArr = $this->records;
		$colcnt = count($resArr[0]);
		$cellwidth = round((100 / $colcnt), 2, PHP_ROUND_HALF_DOWN);
		$akeys = array_keys($resArr[0]);  //prototype array([0]=>field_name1,[1]=>feild_name2,...)
		$suffix = explode("_",$this->ltable);
		$suffix2 = $this->ltable2;
		$colstr=$this->collabel;
		$belongto = "belongs to";
	
		/* begin table ouput */
		echo '<table border="1px" class="glist">'."\n";
		$e=1;
		echo '<tr class="header">';
		foreach($akeys as $label) {
			$lname = explode("_", $label);
			$idLabel = ( $e==1 ) ? $this->catstring." ".$lname[1] : $lname[1];
			if ($lname[0]==$suffix[0] && $lname[1]=='name') {
				echo '<th class="label_'.$e.'" width="'.$cellwidth.'%" title="Sort Names ASC"> '.ucwords($colstr).'</th>';		
			} elseif ($lname[0]==$suffix2[0] && $lname[1]=='name') {
				echo '<th class="label_'.$e.'" width="'.$cellwidth.'%">'.ucwords($belongto).'</th>';
			}else {
				echo '<th class="label_'.$e.'" width="'.$cellwidth.'%" title="Sort Num DESC">'.ucwords($idLabel).'</th>';
			}
			$e++;
		}
		echo '<th width="'.$cellwidth.'%">&nbsp;Edit&nbsp;</th>';
		echo "</tr>\n";
		
		/* assemble each record */
		$i=0;
		foreach($resArr as $array) {  //prototype $array([pl_id]=>int,[pl_name]=>string,..)
		  $color = ($i%2) ? "#ffffff" : "#00ffcc";
		  echo '<tr bgcolor="'.$color.'">'."\n";
		  foreach($array as $k=>$v) {
		  	if($k==$akeys[0]) $rec_id=$v;  //sets change form $_GET[recid] from $rec_id, CLASS SPECIFIC!!!
		  		echo '<td width="'.$cellwidth.'%" align="center">';
		  		$part = explode("_", $k);
		  	if ($part[0]==$suffix[0] && $part[1]=='date') {
			 	echo $this->date = date('m-d-Y', strtotime($v));
			} else {
			 	echo $this->$part[1] = $v;  //table.field name to match class properties name i.e. m_gallery.ct_name = $this->name 
		    }
			echo "</td>\n";
		  }
		  /* edit icon column */
		  echo '<td width="'.$cellwidth.'%" align="center">';
		  echo '<a href="#" onClick="openpopup(\'changeform.php?recid='.$rec_id.'\', \'343\', \'400\')">'."\n";
		  echo '<img src="../img/edit_icon.png" height="20px" alt="Edit Record" tile="Edit Record" border=0/></a>'."\n";		  	  
		  echo "</td>\n";
		  unset($rec_id);
		  $i++;	
	    }
	    echo "</tr>\n";
		echo "</table>\n";
	} //close buildhtml()
}
/*
* BD CONNECTION QUERY CLASS
*/
class datapull {
	var $query;
	var $result;
	var $bind;
	
	function __construct($qry,$bind){
		$this->query = $qry;
		$this->bind = $bind;
	}
	
	function conn() {
	  try {
		$DB = new PDO(DB_HOST, DB_UNAME, DB_UPWORD);
		$DB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	  }catch(Exception $e){
		$mes = $date.": list_cLass.php Caught Exception ---"."\n";
		$mes .= $e->getMessage()."\n";
		error_log($mes);  
	  }
	  return $DB;
   }
	
   function dbquery() {
		$DB = $this->conn();
		$stmt = $DB->prepare($this->query);
		
		/* 
		* bind params if not null, $this->bind=array([field]=>$value)
		* note binding order must match query string placeholders
		*/
		if ($this->bind!=null) {
		  $i=1;
		  foreach ($this->bind as $key=>$value) {
			  $stmt->bindParam($i,$value);
			  $i++;
		  }
		}
		
		/* execute the transaction */
		if($stmt->execute()) {
		  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
	        $this->result[] = $row;
		  }
		}
		return $this->result;
   }
}
/**
* PUBLIC INTERFACE CLASS
* @api
*
* list factory called from client viewer
* @return void
*/
class accesswrapper {
	public static function factory($params) {
		$cat = $params['cat'];
		
		/* compara category name to table short names */
		switch($cat){
			case $cat == "gallery" :
				$inst = new vlist($params);
			break;
			case $cat == "images" :
				$inst = new vlist($params);
			break;
		}
		
		if ($inst instanceof listmanager) {
			return $inst;
		}
	}
}