<?
class UserController extends Controller{
	public function addhabtm(){
		$User = new User();
		$find = $User->find( 'first' );
		
		$tagto = new Tag(array("tag"=>"tag"));
		$tagto->save();
		
		$find->tags = array_merge(
			$find->tags, 
			array( $tagto) 
		);
		
		echo "<pre>";
		print_r($find->tags);
		echo "</pre>";		
		
		exit;		
	}
	public function updatehabtm(){
		$User = new User();
		$find = $User->find( 'first' );
		
		$tagto = new Tag(array("tag"=>"tag"));
		$tagto->save();
		
		$find->tags = array( $tagto) ;
		
		echo "<pre>";
		print_r($find->tags);
		echo "</pre>";		
		
		exit;		
	}
	public function addhasone(){
		$User = new User();
		$find = $User->find( 'first' );
		
		$prof = new Prof();
		
		$find->prof = $prof->create( array( 'bio'=>'short bio') );
		//$find->save();
		
		echo "<pre>";
		print_r($find->prof);
		echo "</pre>";
		
		exit;
	}
	public function updatehasone(){
		$User = new User();
		$find = $User->find( 'first' );
		
		$prof = new Prof();
		
		$find->prof = $prof->find( 1 );
		//$find->save();
		
		echo "<pre>";
		print_r($find->prof);
		echo "</pre>";
		
		exit;
	}
	public function addhasmany(){
		$User = new User();
		$find = $User->find( 'first' );
		
		$post = new Post();
		$post->attributes( array("title"=>"new post"));
		
		$find->posts = array_merge($find->posts,array($post));
		//$find->save();
		
		echo "<pre>";
		print_r($find->posts);
		echo "</pre>";
		
		exit;
	}
	public function updatehasmany(){
		$User = new User();
		$find = $User->find( 'first' );
		
		$post = new Post();
		$post->attributes( array("title"=>"new post"));
		
		$find->posts = array($post);
		//$find->save();
		
		echo "<pre>";
		print_r($find->posts);
		echo "</pre>";
		
		exit;
	}
	public function create(){
		$User = new User();
		$User->create( array('name'=>'yalco') );
		
		echo "<pre>";
		print_r($User);
		echo "</pre>";
		exit;
	}
	public function retrive(){
		$User = new User();
		$find = $User->find( ID );
		echo "<pre>";
		print_r($find->prof->user->posts[0]->user->tags);
		echo "</pre>";
	}
	

}
?>