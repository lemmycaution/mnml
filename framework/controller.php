<?
class Controller{
	/*
	* stores render state
	*/
	public $rendered = false;
	
	/*
	* renders action
	*/
	public function render( $layout=null, $view=null )
	{
		if($this->rendered) return false;
		
		switch(FORMAT){
			case "xml":
				$this->render_xml();
			break;
			case "json":
				$this->render_json();
			break;
			default:
				$this->render_html( $layout, $view );
			break;
		}
	}
	
	/*
	* renders view file
	*/
	public function render_view( $layout = null, $view = null )
	{
		if($this->rendered) return false;
		
		$view = $view ? $view : ACTION;
		
		if(!$layout){
			$view_file = VIEWS_PATH . CONTROLLER . DIRECTORY_SEPARATOR . $view . "." . FORMAT . ".php";
		}elseif($layout=="plugin"){
			$view_file = PLUGIN_PATH . CONTROLLER . DIRECTORY_SEPARATOR . $view . "." . FORMAT . ".php";					
		}else{
			$view_file = VIEWS_PATH . CONTROLLER . DIRECTORY_SEPARATOR . $layout . DIRECTORY_SEPARATOR . $view . "." . FORMAT . ".php";
		}
		if( file_exists( $view_file ) ){
			foreach(get_object_vars($this) as $key=>$value)
				${$key}=$value;
			require_once( $view_file );	
			$this->rendered = true;
		}else{
			error( array( "error"=>"View file not found", "file"=>$view_file ) );
		}
	}
	
	/*
	* renders html
	*/
	protected function render_html( $layout = null, $view = null )
	{
		$view = $view ? $view : ACTION;

		if($layout=="plugin"){
			$layout_file = PLUGIN_PATH.CONTROLLER.DIRECTORY_SEPARATOR."layout.".FORMAT.".php";
			$view_file = PLUGIN_PATH.CONTROLLER.DIRECTORY_SEPARATOR.$view.".".FORMAT.".php";			
		}else{
			$layout_file = VIEWS_PATH."layouts".DIRECTORY_SEPARATOR.($layout ? $layout : CONTROLLER ).".".FORMAT.".php";
			$view_file = VIEWS_PATH.CONTROLLER.DIRECTORY_SEPARATOR.$view.".".FORMAT.".php";			
		}
		
		if( file_exists( $layout_file ) ){
			require_once( $layout_file );
		}elseif( file_exists( $view_file ) ){
			$this->render_view( $layout, $view );
		}else{
			error( array( "error"=>"Layout and View file not found", "layout file"=>$layout_file, "view file"=>$view_file ) );
		}
	}
	
	/*
	* renders xml
	*/
	protected function render_xml()
	{
		header("Content-type: application/xml; charset=utf8");
		$this->rendered = true;
		$data = array();
		
		if( isset( $this->data )  ){
			$data = is_array($this->data) ? $this->data : array( $this->data );
		}
		send_xml($data);
	}
	
	/*
	* renders json
	*/
	protected function render_json()
	{			
		header("Content-type: application/javascript");
		$this->rendered = true;
		$data = array();
		
		if( isset( $this->data )  ){
			$data = is_array($this->data) ? $this->data : array( $this->data );
		}
		
		send_json($data);
	}
}
?>