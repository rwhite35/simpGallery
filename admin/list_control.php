<?php
/**
* CONTROLLER FOR DASHBOARD AND LIST VIEWS 
* version 1.0
* since 1.0, 2014-07-28
*
* return session $sort_by, string holds last column name i.e. 'name','id' or 'date'.
* define function assignSortOrder, returns var $sortByCol, change column name for session.
*/
include '../GAL_CONF.php';
/*
* assignSortOrder([th_field_id]) called through AJAX post event user clicks a list column label.
* param string th_filed_id, each th tag has a dynamic class name, JQUERY captures that name and
* initializes an AJAX request
* return string $sortByCol, column name appended to table field prefix i.e. ct_name. 
*/	
function assignSortOrder($labelClick) {
	$lpart = array();
	$th_id = "";
	$sortByCol = "";
	
	if(!empty($labelClick)) {
		$lpart = explode("_", $labelClick);
		$th_id = $lpart[1]; //id appended to label_
	}
	//todo 2014-06-08: column names are specific to media center
	//table structure. these should be dynamic  	
	switch($th_id) {
	  case 1:
		$sortByCol="id";
	  break;
	  case 2:
		$sortByCol="name";
	  break;
	  case 3:
		$sortByCol="date";
	  break;
	  default:
	  	$sortByCol="id";
	}
	return $sortByCol;
}
?>
