<?php
/*
* Test the PDO driver configuration to make sure its working.
* Most database connection/queries require PDO
*/
include '../GAL_CONF.php';
try {
		$DB = new PDO(DB_HOST, DB_UNAME, DB_UPWORD);
		$DB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Set Errorhandling to Exception
		$tables = array();
		$result = $DB->query("SHOW TABLES");
		while ($row = $result->fetch(PDO::FETCH_NUM)) {
			$table[] = $row[0];
		}
	  } catch( Exception $e ) {
		$mes = "Error: test_pdo.php Caught Exception --- ";
		$mes .= $e->getMessage();  
		echo $mes;
	}
	if (!empty($table)) {
		echo "Table names in the database ".DB_NAME."<br>";
		var_dump($table);
	} else {
		echo "DB connection made but returned empty set.";
	}
?>
