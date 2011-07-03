<?
class HomeController extends Controller{
	public function index(){
		// Sample data 
		$array = array('foo'=>'bar','bar'=>'foo'); 
		$string = 'this is a string'; 

		$c = new Cookie(); 

		/*   
		    Create encrypted cookie with an array  
		*/ 
		echo '<h3>Encrypted array</h3>'; 

		$start = microtime(true); 

		$c->setName('Example') // our cookie name 
		  ->setValue($array,true)   // second parameter, true, encrypts data 
		  ->setExpire('+1 hours')   // expires in 1 hour 
		  ->setPath('/')            // cookie path 
		  ->setDomain('')  // set for localhost 
		  ->createCookie(); 

		$cookie = $c->getCookie('Example',true); 
		$cookie = unserialize($cookie); 
		
		if(!$cookie){
			echo $c->getErrors();
		}
		
		$bench = sprintf('%.8f',(microtime(true)-$start)); 

		echo print_r($cookie,true).'<br />'.$bench.' seconds<hr />'; 

		/* 
		    Destroy Example Cookie 
		    Note: Domain and path may need to be set if they differ from the defaults,  
		          but they're already initialized above  
		*/ 
		//$c->destroyCookie('Example'); 


		/*   
		    Create cookie with a string that expires when the browser closes (default) 
		*/ 
		echo '<h3>Regular unencrypted string</h3>'; 
		$start = microtime(true); 
		$c->setName('Example1234') 
		  ->setValue($string) // Second param could be set to false here 
		  ->setExpire(0) 
		  ->setPath('/') 
		  ->setDomain('') 
		  ->createCookie(); 

		$cookie = $c->getCookie('Example1234'); 

		$bench = sprintf('%.8f',(microtime(true)-$start)); 

		echo print_r($cookie,true).'<br />'.$bench.' seconds';
		exit;
	}
	public function fbtest(){
		if(facebook_check_signed_request()){
			echo "yes fb";
		}else{
			echo "no fb";
		}
		
	}
	public function test(){
		$this->data= array("data"=>VERB.":".LANG.":".CONTROLLER.":".ACTION.":".ID);
		$this->render_view();
	}
	public function rendertest(){
	/*
		$this->data = array(
			array("hello xml", "key"=>"value", "array_key"=>array("my key"=>"my value2") ),
			array("hello xml", "key"=>"value", "array_key"=>array("my key"=>"my value2") )
			);
	*/
	/*
		$this->data = "sanple";
	*/
	
	$user = new User();
	$this->data = objects_to_array( $user->find('all') ) ;

	}
	/*
	* renders with 'default' layout
	*/
	public function render( $layout=null ){
		$layout = $layout==CONTROLLER ? DEFAULT_LAYOUT : $layout;
		parent::render( $layout );
	}	
}
?>