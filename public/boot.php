<?
/*
 * define domain
 */
define( "DOMAIN" , $_SERVER['HTTP_HOST']);
/*
 * define framework's base path
 */
$basepath = $_SERVER["REQUEST_URI"];
if( array_key_exists( "PATH_INFO", $_SERVER ) && isset( $_SERVER["PATH_INFO"] ) ) 
	$basepath = str_replace($_SERVER["PATH_INFO"],"/",$basepath );
if( array_key_exists( "QUERY_STRING", $_SERVER ) && isset( $_SERVER["QUERY_STRING"] ) ) 
	$basepath = str_replace("?".$_SERVER["QUERY_STRING"],"",$basepath );
define( "BASE_PATH" ,  $basepath );

/*
 * define framework's physical paths
 */
define( "REAL_PATH", realpath( dirname(__FILE__) . DIRECTORY_SEPARATOR . ".." ) );
define( "FW_PATH", REAL_PATH.DIRECTORY_SEPARATOR."framework".DIRECTORY_SEPARATOR );
define( "APP_PATH", REAL_PATH.DIRECTORY_SEPARATOR."app".DIRECTORY_SEPARATOR );
define( "CONTROLLERS_PATH", REAL_PATH.DIRECTORY_SEPARATOR."app".DIRECTORY_SEPARATOR."controllers".DIRECTORY_SEPARATOR );
define( "MODELS_PATH", REAL_PATH.DIRECTORY_SEPARATOR."app".DIRECTORY_SEPARATOR."models".DIRECTORY_SEPARATOR );
define( "VIEWS_PATH", REAL_PATH.DIRECTORY_SEPARATOR."app".DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR );
define( "DB_PATH", REAL_PATH.DIRECTORY_SEPARATOR."db".DIRECTORY_SEPARATOR );
define( "LIB_PATH", REAL_PATH.DIRECTORY_SEPARATOR."lib".DIRECTORY_SEPARATOR );
define( "PLUGIN_PATH", REAL_PATH.DIRECTORY_SEPARATOR."plugin".DIRECTORY_SEPARATOR );
define( "PUBLIC_PATH", REAL_PATH.DIRECTORY_SEPARATOR."public".DIRECTORY_SEPARATOR );
define( "UPLOAD_PATH", PUBLIC_PATH."upload".DIRECTORY_SEPARATOR );
/*
 * run app
 */
include_once FW_PATH."autoload.php";
include_once FW_PATH."functions.php";
include_once FW_PATH."plugins.php";
include_once FW_PATH."config.php";
include_once FW_PATH."application.php";

Application::run();
?>