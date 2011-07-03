<?
/*
 * include plugins in plugin directory
 */
$files = dir_files( PLUGIN_PATH , true);
function include_plugins( $files ){
	foreach($files as $file){
		if(gettype($file)=="string"){
			if( strpos( $file, "include.php" ) ) {
				require_once $file;
			}
		}	
		else include_plugins( $file );
	};
}
include_plugins( $files );
?>