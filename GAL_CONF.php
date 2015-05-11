<?php
/**
* 
* DISTRO SPECIFIC CONFIGURATION SETTINGS
* local variables for application configuration. You may want to move some of these
* variables outside the gallery root directory, specifically database credentials.
*/
/*
* token gets hashed on each page that processes form input to ensure each 
* request is unique and local to the application.
*/
$token = "One World";
/*
* homepage is used for navigation and sets the websites root director
* name. homepage can be the fully qualified domain - http://www.examp.com
*/
$homepage = "localhost";
/*
* THIS SCRIPT IS NOT INCLUDED in the project distro, its up to 
* the developer to create their own error reporting mechanism.  
* required in router.php (L53), index_admin.php (L23), dashboard.php (L19)
*/
$errorfile = "error.php";
/*
* THIS SCRIPT IS NOT INCLUDED in the project distro, its up to 
* the developer to create their own logout mechanism.  
* required in dashboard.php (L50)
*/
$logoutfile = "logout.php";
/*
* DB CREDENTIALS
* this is the database user, not an unprivileged user account (admin_joe).
* DB_UNAME privileged superuser with grant permissions
* DB_UPWORD privileged superuser with grant permissions
* BD_HOST string is set for mysql::PDO driver
*/
define("DB_NAME","gallery_dev");
define("DB_UNAME","mydbsupuser");
define("DB_UPWORD","mydummypass123");
define("DB_HOST","mysql:host=localhost;dbname=".DB_NAME);
/*
*
* GALLERY CONFIGURATION VARS
* These constant variables define the location of resources and 
* sets parameters for image file size and resolution.
*
* CONST defines dev log text file, path to client dev root
* text file used for debugging installations, comment out if
* not using. 
*/
define("DEV_LOG","/Users/ronwhite/Sites/gallery/dev_mes.txt");
/* 
* CONST define the image file size allowed for form and php.ini reference
*/
define("MAX_FILE_SIZE", 2000000);
/*
* CONST file system path used by httpd for moving image files from /tmp to the galleries rouseouce folders.
* example file systems /var/www/vhosts/domain_root/httpdocs/gallery or /Users/username/Sites/gallery
*/
define("GAL_PARENT_DIR","/Users/ronwhite/Sites/gallery");
/*
* CONST define image upload originals directory
*/
define("ORIG_DIR_NAME","originals");
/*
* CONST define temporary images directory
*/
define("IMG_DIR_NAME","images");
/*
* CONST define lightbox thumbnails directory
*/
define("THUMB_DIR_NAME","thumb");
/*
* CONST define gallery live area max width
*/
define("VIEWER_MAX_WIDTH", 768);
/*
* CONST define gallery maximum image width
*/
define("IMG_MAX_WIDTH", 800);
/*
* CONST define gallery viewer thumbnail width
*/
define("THUMB_MAX_WIDTH", 250);
/*
* CONST define database table name for category data.
* while database name is flexible, the table name is not.
* the app expects certain prefix/suffix combinations.
*/
define("GAL_TBL", "galc_cat");
/*
* CONST define database table name for image data.
* while database name is flexible, the table name is not.
* the app expects certain prefix/suffix combinations.
*/
define("IMG_TBL", "gali_img");
?>