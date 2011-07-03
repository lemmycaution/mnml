<?
/*
 * define environment
 */
define( "ENVIRONMENT" , "development");
/*
 * define error statement
 */
switch (ENVIRONMENT){
	case "production":
	// Turn off all error reporting
	error_reporting(0);
	break;
	case "test": case "development":
	// Turn on all error reporting:
	error_reporting(E_ALL);	
	break;
}
/*
 * define is htaccess enabled
 */
define( "USE_HTACCESS" , true);
/*
 * define is sessions enabled
 */
define( "SESSIONS" , true);
/*
 * define default controller,action and id
 */
define( "DEFAULT_CONTROLLER" , "home" );
define( "DEFAULT_ACTION" , "index" );
define( "DEFAULT_ID" , null );
/*
 * define default layout
 */
define( "DEFAULT_LAYOUT" , "desktop");
/*
 * define is mobile enabled
 */
define( "MOBILE_READY" , false);
define( "DEFAULT_MOBILE_LAYOUT", "mobile" );
$_mobile_layouts = array("unknown"=>"wap","ipad"=>DEFAULT_LAYOUT);
/*
 * define default and other languages
 * load locale files (public/i18n)
 */
define( "DEFAULT_LANG" , "en" );
I18n::$languages = array( "en", "tr" );
foreach(I18n::$languages as $lang){
	require_once PUBLIC_PATH."i18n".DIRECTORY_SEPARATOR."$lang.php";
}
/*
 * define database environments
 * 
 * mysql example
	array(
     'adapter' => 'mysqli',
		'database' =>  '',
		'username' => '',
		'password' => '',
		'host' => '',
		'port' => '3306',
		'socket' => '',
		'encoding' => ''
 	)
 * sqlite example
 	array(
        'adapter' => 'sqlite',
        'database' => 'sqlitedb_prod'
    )
 */
$_database_environments = array(
    'development'=>array(
     'adapter' => 'mysqli',
		'database' =>  'mnml_dev',
		'username' => 'root',
		'password' => '',
		'host' => 'localhost',
		'port' => '3306',
		'socket' => '',
		'encoding' => ''
 	),
    'test' => array(
        'adapter' => 'sqlite',
        'database' => 'sqlite_test.sqlite'
    ),
    'production' => array(
        'adapter' => 'sqlite',
        'database' => 'sqlite_prod.sqlite'
    )
);
define( "MYSQL_CACHE" , "ON" );
/*
 * define is stmp enabled
 * /
define( "USE_SMTP" , "true");
$_smtp = array(
	"host"=>"smtp.example.com",
	"auth"=>true,
	"username"=>"username",
	"password"=>"password"
);
*/

/* 
 * mapping routes
 */
Application::map( ":controller/:action" );
Application::map( ":controller/:action/:id" );
?>