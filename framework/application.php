<?
class Application{
	
	/*
	 * stores mapped routes
	 */
	protected static $routes = array();
	
	/*
	 * stores response formats
	 */	
	protected static $response_formats = array("xml","json","html");
	
	/*
	 * $route : uri pattern, string
	 * $options: uri options, array, optional
	 */
	public static function map( $route, $options=array() ){
		//add lang pattern if missed
		if( strpos($route,":lang") === false )
			self::map( ":lang/".$route, $options);
			
		//add route if isn't duplicate
		if( !array_key_exists( $route , self::$routes) )
			self::$routes[$route]=$options;
	}
	
	/*
	 * starts application
	 */
	public static function run(){
		//test request and run if passed
		foreach(self::$routes as $route=>$options)
			self::dispatch($route,$options);
		//otherwise go 404	
		http_error(404);
	}
	
	/*
	 * dispatchs requested uri via mapped routes
	 */
	protected static function dispatch( $route, $options=array() ){
		//return false ofcourse
		if( defined("CONTROLLER") ) return false;

		//parse request if exists
		if ( array_key_exists( "PATH_INFO", $_SERVER ) ){
			$request = substring( $_SERVER["PATH_INFO"] ,1, strlen($_SERVER["PATH_INFO"]));
			$request = preg_split( "/[\?]/u", $request, -1 );
			//$parts_request = explode( '/', $request[0] );
			$parts_request = preg_split( "/[\/]/u", $request[0], -1, PREG_SPLIT_NO_EMPTY );
			$parts_request_count = count($parts_request);
			$format_request = preg_split( "/[\.]/u", $parts_request[$parts_request_count-1], -1, PREG_SPLIT_NO_EMPTY );
			if( count($format_request)>1 && in_array( $format_request[count($format_request)-1], self::$response_formats ) )
				$parts_request[$parts_request_count-1] = array_shift($format_request);
			$is_parts_request_empty=true;	
			foreach($parts_request as $p){
				if($p!=null){
					$is_parts_request_empty=false;
					break;
				}
			}
		}
		
		//define output format
		$fmt = isset($options[':default_format'])?$options[':default_format']:"html";
		$format_response =	!isset($format_request) || empty( $format_request ) ? $fmt : 
							( in_array( $format_request[0], self::$response_formats ) ? $format_request[0] : $fmt );
							
		//initialize response array
		$response = array( ":lang"=>"", ":controller"=>"", ":action"=>"", ":id"=>"" );

		//set defaults if there is no path request
		if( empty( $parts_request ) || $is_parts_request_empty ){
			$response=array( ":lang"=>DEFAULT_LANG, ":controller"=>DEFAULT_CONTROLLER, ":action"=>DEFAULT_ACTION, ":id"=>DEFAULT_ID );
			$check = check_controller_file( DEFAULT_CONTROLLER );
			if(!$check) return false;
		//parse path request	
		}else{
			//parse route pattern
			$parts_route = explode( '/' , $route);
			$parts_route_count = count($parts_route);
			$params_route = array_key_exists( ":params", $options ) ? $options[":params"] : array() ;
			$requirements_route = array_key_exists( ":requirements", $options ) ? $options[":requirements"] : array() ;

			//test pattern and request is matching
			$matches = array();

			if( $parts_route_count != $parts_request_count ) return false;
			
			for($i=0;$i<$parts_route_count;$i++){
				$v1=$parts_route[$i];
				$v2=$parts_request[$i];
				//check named parts
				if( strpos($v1,':')===false && $v1!=$v2) return false;
				//check requiriments
				if( $requirements_route && 
					array_key_exists($v1,$requirements_route) && 
					!preg_match($requirements_route[$v1], $v2) ) return false;	
				$matches[$v1]=$v2;
			}
			
			//fill response array
			//lang
			$response[":lang"] = array_key_exists( ":lang", $params_route ) ? $params_route[":lang"] :
			 					( isset( $matches[":lang"] ) ? ( in_array( $matches[":lang"], I18n::$languages) ? $matches[":lang"] : null) : 
								DEFAULT_LANG ) ;
			if( !$response[":lang"] ) return false;
								
			//controller
			$response[":controller"] = array_key_exists( ":controller", $params_route ) ? $params_route[":controller"] : $matches[":controller"];
			$check = check_controller_file( $response[":controller"] );
			if(!$check) return false;
			//action
			$response[":action"] = array_key_exists( ":action",$params_route ) ? $params_route[":action"] : $matches[":action"];
			//id
			$response[":id"] =  array_key_exists( ":id", $params_route ) ? $params_route[":id"] : 
							   ( array_key_exists( ":id", $matches ) ? $matches[":id"] : null );
			 		
		}					
		
		//deal with controller and action
		$controllerClassName = Inflector::classify( $response[":controller"]."Controller" );
		try{
			$controllerInstance = new $controllerClassName;
		}catch(Exception $e){
			//error($e);
			return false;
		}
		
		if( in_array( $response[":action"], get_class_methods($controllerInstance) ) ){
			define( "VERB" , $_SERVER["REQUEST_METHOD"] );
			define( "LANG" , $response[":lang"] );
			define( "CONTROLLER" , $response[":controller"] );
			define( "ACTION" , $response[":action"] );
			define( "FORMAT" , $format_response );
			define( "ID" , $response[":id"] );
			
			if( defined("MOBILE_READY") && MOBILE_READY ){
				$mobile_type=null;
				if( is_mobile( $mobile_type ) ){
					define( "MOBILE", $mobile_type );
					global $_mobile_layouts;
					$check = ( array_key_exists( $mobile_type, $_mobile_layouts ) ) ? $_mobile_layouts[$mobile_type] : DEFAULT_MOBILE_LAYOUT;
				}
			}
			
			if( defined("SESSIONS") && SESSIONS ) session_start();
			
			$controllerInstance->{$response[":action"]}();
			$controllerInstance->render( $check );
			exit;
		}else{
			//error(array("Error"=>"action could not found","controller"=>$controllerClassName,"action" => $response[":action"]));
			return false;
		}
		
	}
	
}
?>