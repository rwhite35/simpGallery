<?php
/**
*
* FUNCTION PREG_GREP_KEYS
* Allows postchange function to dynamicaly set 
* the id field name by inverting the post array 
* so preg_grep can match keys rather than values.
* @return string $input, field name that matched $pattern ie. ct_id
*/
function preg_grep_keys($pattern, $input, $flags = 0) {
    return array_intersect_key($input, array_flip(preg_grep($pattern, array_keys($input), $flags)));
}
/**
* 
* FUNCTION SVALIDATE
* servside validation for anonymous from input
* checks for empty input field, then sanitizes input data.
* on fail, pass array back to viewer for UI reporting
* @version 2.5, 2015-02-02
* @since 1.0, 2014-04-08
* @param array $formpost, users form input from $_POST array
* @param int $mode, level of sanitizing to apply 1 - highest, 2 - lowest
* @return array $returnArray, sanitized form input or [exit] on fail
*/
function svalidate($formpost,$mode) {
	$date = date('Y-m-d H:i:s', time());
	$returnArray = array();
	switch ($mode) {
		case 1:
			$sanitizeLevel = "FILTER_FLAG_STRIP_HIGH"; //bytes > 127
		break;
		case 2:
			$sanitizeLevel = "FILTER_FLAG_STRIP_LOW"; //bytes < 32
		break;
		default:
			$sanitizeLevel = "FILTER_FLAG_STRIP_HIGH";
	}
	/* Check for empty fields */
	foreach($formpost as $k=>$v) {
		if(empty($v)) {
			$mes = $date." svalidate said: ".$k." field requires input!<br>\n";
			$returnArray['err'][] = $mes;
			$returnArray['exit'] = 1;
		} else {
			$formpost[$k] = trim($v); //remove white space from ends
		}		
	}
	/* Sanitize form input */
	foreach($formpost as $k=>$v) {
		$fvalue = filter_var($v, FILTER_SANITIZE_SPECIAL_CHARS, $sanitizeLevel);
		if( $fvalue!=false ) {
			$returnArray[$k] = $fvalue;
		} else {
			$mes = $date." svalidate said: ".$k." failed to sanitize.<br>\n";
			$returnArray['err'][] = $mes;
			$returnArray['exit'] = 1; //overloads on multiple errors
		}
	}
	return $returnArray; //pass feedback to viewer
}
?>