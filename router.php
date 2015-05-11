<?php
/**
 * ADMIN LOGIN PROCESS
 * @author Ron D. White rwhite@october-design.com <rwhite@october-design.com>
 * @version 5.0, 2015-05-27
 * @since 1.0, 2015
 *
 *@param string $user_name, $_POST var, admins user name
 *@param string $user_pass, $_POST var, admins password
 *@param int $pageid, $_POST var used for routing control to different pages
 *@param string $token, $_POST var, string hash for form post authentication
 *
 *@param string $errorfile, defined in GAL_CONF, path to custom error reporting.  
 * reports errors back to the web user without exposing system information.  
 * error reporting script was not included with project distro, either create one 
 * or comment out the header line (L53, 57) and handle failed checks your way.
 *
 *@param array $_SESSION[staff][{}], carries admin credentials for user check
 * prototype - array(staff(uname=>string, uid=>int, token=>string))
 *
 *@return void
*/
session_start();
error_reporting(E_ERROR | E_PARSE);
/* 
* --- LOCAL VARS ---
*/
include 'GAL_CONF.php';
$parent = basename(GAL_PARENT_DIR); //parent directory name defined in GAL_CONF
$rrtoken = hash("md5",$token); //hash a token for authentication challenges
/*
* --- LOCAL FUNCTIONS ---
*/
/* 
* function testmatch ($uname[string], $upass[string], $qresult[array])
* test login credentials against user db records without exposing form input to database. 
* return array $passed, prototype array(name=user_name,uid=user_id) assigned to session 
*/
function testmatch($uname,$upass,$qresult) {
	$passed = array('name'=>null,'uid'=>null);
	for($i=0;$i<=count($qresult);$i++) {
		if ( $qresult[$i]['u_name'] == $uname ) $passed['name'] = $uname;
		if ( $qresult[$i]['u_pwd'] == $upass ) $passed['uid'] = $qresult[$i]['u_id'];
	}
	return $passed;
}
/* 
* --- CHECK USER INPUT BEFORE PROCEEDING --- 
* break point - if post vars are empty or token doesn't match, send to error page
*/
if (empty($_POST['user_name']) || empty($_POST['user_pass']))  { 
	$erno = 5;
	header("Location: ../$errorfile?erno=$erno&page=index.php"); 
	exit();
} elseif ($_POST['token']!==$rrtoken) {
	$erno = 3;
	header("Location: ../$errorfile?erno=$erno&page=index.php");
	exit();
} else {  //passed, filter input - removes special characters
	  $user_name = filter_var($_POST['user_name'],FILTER_SANITIZE_SPECIAL_CHARS,FILTER_FLAG_STRIP_LOW);
	  $user_pass = filter_var($_POST['user_pass'],FILTER_SANITIZE_SPECIAL_CHARS,FILTER_FLAG_STRIP_LOW);
	  $pageid = filter_var($_POST['pageid'],FILTER_SANITIZE_NUMBER_INT);
	  unset($_POST['token']);
/* 
* --- DATA PULL --- 
*/
try {
	$dbh = new PDO(DB_HOST, DB_UNAME, DB_UPWORD);
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
	print("Error on PDO Connect: ".$e->getMessage()."<br>\n");
	die();
} 
/* 
* pull all active staff records and assign result to array.
* for security purpose, no user input is required for this query.
*
* The user table and column labels will most likely require changing. 
* change the SELECT statement to your tables structure.
*/
try {
	$staffar = array();
	$q = "SELECT u_id,u_name,u_pwd FROM users WHERE u_active='Y'";
	$stmt = $dbh->prepare($q);
	$stmt->execute();
	while( $row = $stmt->fetch(PDO::FETCH_ASSOC)) { $staffar[] = $row; } 
} catch (PDOException $e) {
	print "Error!: ".$e->getMessage()."<br>";
	die();
}
/*
* define the parent directory name
* @param int $pageid, used to route to different subdirectories
*/
switch ($pageid) {
	case 1:
	$ppath = $parent;
	break;
	default:
	$parent = "../index.html";	//send to home page if empty
}
/* 
* break point - if test failed because input didn't match any database records, 
* send to error page and exit 
*/ 
$usercheck = testmatch($user_name,$user_pass,$staffar);
if ( $usercheck['name'] == null || $usercheck['uid'] == null ) {
 	$erno = 1;
	header("Location: ../$errorfile?erno=$erno&page=$parent"); 
	exit();
}
/*
*
* --- ASSIGN STAFFAR TO SESSION --- 
* param string [name], authenticated user name
* param int [uid], authenticated user id
* param string [token], hash for subsiquent form post 
*/
if ($usercheck['name']!=null)
$_SESSION['staff']['uname'] = $usercheck['name'];
$_SESSION['staff']['uid'] = $usercheck['uid'];
$_SESSION['staff']['token'] = $rrtoken;
/*
*
* -- ROUTE USER TO SUBSECTION ADMIN HOME --
* return process to subsection viewer
*/
?>
    <script language="javascript">
      location.replace('<?php echo "../".$ppath."/index_admin.php"; ?>');
    </script>
<?php
	exit();
}
?>