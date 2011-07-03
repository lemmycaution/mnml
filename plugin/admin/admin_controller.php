<?
class AdminController extends Controller{

	public function __construct(){
		$this->connection = DatabaseManager::get('');
	}
	public function install(){
		if(!in_array(Admin::$table_prefix."admins",$this->connection->tables())){
			
			$this->connection->create_table(Admin::$table_prefix."admins",array(
				"email"=>"string",
				"password"=>"string",
				"created_at"=>"datetime",
				"updated_at"=>"datetime",
				"role"=>"string"
			));
			
			$Admin = new Admin();
			$Admin->create( array( 
				"email"=>"root@root.com",
				"password"=>"root",
				"role"=>"root"
				) );
			die ( "Admin plugin install successfuly</br><a href=\"".url_for("admin/index")."\">start using</a>" );
		}else{
			die( "Admin plugin already installed, please uninstall first" );
		}
	}
	public function uninstall(){
		$this->connection->drop_table(Admin::$table_prefix."admins");		
		die ( "Admin plugin uninstall successfuly" );
	}
	public function home(){
		admin_check_auth();
	}
	public function login(){
		if( VERB=="POST" ){
			$Admin = new Admin();
			$email = $_POST["email"];
			$password = md5($_POST["password"]);
			$admin = $Admin->query("SELECT * FROM ".Admin::$table_prefix."admins WHERE email = ? AND password = ? LIMIT 1",array($email,$password));
			if(empty($admin)){
				flash( array("type"=>"error", "text"=>"wrong email or password!" ) );
			}else{
				$_SESSION["admin_auth"] =  $admin[0] ;
				redirect( url_for( "admin/home") );
			}
		}
	}
	public function logout(){
		clear_session("admin_auth");
		redirect( url_for( "admin/home") );
	}
	public function migrations(){
		admin_check_auth();
		
		$this->migrations = dir_files( DB_PATH."migrations" );
		$this->count = count($this->migrations);
	}
	public function index(){
		admin_check_auth();

		if( !empty( $_GET['table']) ){
			$this->table = $_GET['table'];
			$this->page = $_GET['page'];
			$this->limit = $_GET['limit'];
			$this->order = $_GET['order'];
			$Model =  Inflector::classify($this->table);
			$this->model = new $Model();
			$this->count = $this->model->count();
			$options = array( "order"=>$this->order, "limit"=>$this->limit , "offset"=>$this->page*$this->limit );
			$this->data = $this->model->index( $options );
		}else redirect( url_for("admin/home") );
	}
	public function migrate(){
		admin_check_auth();
		
		if(ID){
			if( isset($_GET['approve']) ){
				$class_name = $_GET["class_name"];
				$migration = new $class_name();
				switch(ID){
					case "up":
					$this->output=$migration->up();
					break;
					case "down":
					$this->output=$migration->down();			
					break;
				}
			}
		}else redirect( url_for("admin/home") );
		
		$this->render_view( "plugin" );
	}
	public function create(){
		admin_check_auth();
		
		if( !empty( $_GET['table'] ) ){
			$this->table = $_GET['table'];
			$Model =  Inflector::classify($this->table);
			$this->model =  new $Model();
			if(VERB=="POST"){
				$this->before_save();
				$this->data = $this->model->create($_POST);
				$this->model = $this->data;					
				$this->after_save();
			}	
				
		}else redirect( url_for("admin/home") );
		
		$this->render_view( "plugin" );
	}
	public function retrive(){
		admin_check_auth();
		
		if( !empty( $_GET['table'] ) && ID ){
			$this->table = $_GET['table'];
			$Model =  Inflector::classify($this->table);
			$this->model = new $Model();
			$this->data = $this->model->retrive(ID);
		}else redirect( url_for("admin/home") );
		
		$this->render_view( "plugin" );
	}
	public function update(){
		admin_check_auth();
		
		if( !empty( $_GET['table'] ) && ID ){
			$this->table = $_GET['table'];
			$Model =  Inflector::classify($this->table);
			$this->model = new $Model();
			if(VERB=="POST"){
				$this->before_save();
				$this->data = $this->model->update(ID, $_POST);
				$this->data = $this->data[0];				
				$this->model = $this->data;				
				echo "before";
				$this->after_save();
				echo "after";
			}else{
				$this->data = $this->model->retrive(ID);
				$this->model = $this->model->find(ID);
			}	
		}else redirect( url_for("admin/home") );
		
		$this->render_view( "plugin" );
	}
	public function destroy(){
		admin_check_auth();
		
		if( !empty( $_GET['table'] ) && ID ){
			$this->table = $_GET['table'];
			$Model =  Inflector::classify($this->table);
			$this->model = new $Model();
			if(VERB=="POST")
				$this->data = $this->model->destroy(ID);
			else
				$this->data = $this->model->retrive(ID);
		}else redirect( url_for("admin/home") );
		
		$this->render_view( "plugin" );
	}
	public function search(){
		admin_check_auth();
		
		if( !empty( $_GET['term'] ) ){
			$term = $_GET["term"];
			$this->data = array();
			$this->count = 0;
			foreach($this->connection->tables() as $table){
				$model_name=admin_get_modelname_from_tablename($table);
				try{
					$Model =  Inflector::classify($model_name);
					$model = new $Model();
					$query = "SELECT * FROM $table WHERE ";
					$values = array();
					foreach($model->attributes_info() as $attr){
						if($attr["type"]=="string" || $attr["type"]=="text"){
							$query.=$attr["name"]." LIKE ? OR ";
							$values[]="%".$term."%";
						}
					}
					$query = substring( $query, 0, strlen($query)-4 );
					$search = $model->query($query,$values);
					$this->count+=count($search);				
					if(!empty($search))$this->data[]=array("table"=>$table,"model"=>$model,"list"=>$search);
				}catch(Exception $c){
					//model not found
					//id table habtm rel?
				}
			}
			
		}else redirect( url_for("admin/home") );
		
	}	
	
	
	private $td=array();
	
	public function before_save(){

		$this->get_rel_data("_has_one");
		$this->get_rel_data("_has_many");		
		$this->get_rel_data("_has_and_belongs_to_many");
	}
	
	
	public function after_save(){

		$this->put_rel_data("_has_one");
		$this->put_rel_data("_has_many");		
		$this->put_rel_data("_has_and_belongs_to_many");
				
	}
	
	private function get_rel_data($rel){

		foreach($this->model->$rel as $key=>$value){
			if(is_numeric($key))$key=$value;

			if(isset($_POST[$key])){				

				$this->td[$rel][$key]=$_POST[$key];

				unset( $_POST[$key] );
			}else{
				$this->td[$rel][$key]=null;
			}
		}


	}
	private function put_rel_data($rel){
		if(isset($this->td[$rel])){


		foreach($this->td[$rel] as $key=>$value){

						
			if($value){
				$m = Inflector::classify($key);

				$m = new $m;				
				$this->model->$key = $m->find( $value );
			}else{
				
				$this->model->$key = $rel=="_has_one" ? null :array();
			}
		}
				
		}
		
	}
}
?>