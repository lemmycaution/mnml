<?
class DumperController extends Controller{
	public function __construct(){
		$this->connection = DatabaseManager::get('');
	}
	public function index(){
		$this->tables = $this->connection->tables();
	}
	public function dump(){
		//this function included admin/include.php
		admin_check_auth();
		if(VERB=="POST"){
			$tables=null;
			switch($_POST['table']){
				case "all":
				$tables = $this->connection->tables();
				break;
				default:
				$tables = array( $_POST['table'] );
				break;
			}
			switch($_POST['type']){
				case "sql":
				header('Content-type: text/plain');
				header('Content-Disposition: attachment; filename=dump_'.date('YmdHis').'.sql');
				dumper_dump_sql($this->connection,$tables);
				break;
				case "csv":
				ob_start("ob_gzhandler");
				header("Content-type: text/csv");				
				header('Content-Disposition: attachment; filename=dump_'.date('YmdHis').'.csv');
				dumper_dump_csv($this->connection,$tables);
				header("Content-Length: ".ob_get_length());
				ob_end_flush();
				break;
			}
		}
		$this->rendered=true;
	}
}
?>