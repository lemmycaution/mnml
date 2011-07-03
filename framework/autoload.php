<?
/*
 * autoloads required classes
 * nested classes can't be autolaoded use : require_once(LIB_PATH."dir_name/ClassName.php");
 */
function __autoload($class_name) 
{
	//define include paths
	$include_paths = array(
		"framework"=>FW_PATH,
		"lib"=>LIB_PATH,
		"plugin"=>PLUGIN_PATH,						
		"app"=>APP_PATH,
		"migrations"=>DB_PATH."migrations".DIRECTORY_SEPARATOR,		
	);
	
	//singular underscore inflected class_name	
	$class_name_mnml = strtolower(preg_replace('/([A-Z]+)([A-Z])/','\1_\2', preg_replace('/([a-z\d])([A-Z])/','\1_\2', strval($class_name))));
	
	//search include paths
	foreach($include_paths as $path){

		if( file_exists("$path$class_name_mnml.php") ){
			include_once ( "$path$class_name_mnml.php" );
			return true;
		}	
		
		if ( check_dir($path, $class_name_mnml) ) return true;
	}

	//search libraries
	if( file_exists($include_paths['lib']."$class_name.php") ){
		include_once ( $include_paths['lib']."$class_name.php" );
		return true;
	}	
	
	if ( check_dir($include_paths['lib'], $class_name) ) return true;

	throw new Exception( "Unable to load class : $class_name" );
}
/*
 * checks given class is in given path
 */
function check_dir( $path, $class_name )
{
	$iterator = new DirectoryIterator( $path );
	while($iterator->valid()) {
		$i = $iterator->current();
		$dir = $i=="." || $i==".." ? "" : $path.$i.DIRECTORY_SEPARATOR;
		
		if(is_dir($dir)){
			if( file_exists("$dir$class_name.php") ){
				include_once ( "$dir$class_name.php" );
				return true;
			}	
			if ( check_dir( $dir, $class_name ) ) return true;
		}
		$iterator->next();
	}	
	return false;
}
?>