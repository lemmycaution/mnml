<?
class UserMigration {

	function __construct(){
		$this->connection = DatabaseManager::get("");
	}

	final public function up() {
		$this->connection->create_table('users', array(
			"name_tr"=>"string",
			"name_en"=>"string",			
			"bod"=>"datetime",
			"active"=>"boolean",
			"bio_tr"=>"richtext",			
			"bio_en"=>"richtext",						
			"file"=>"string",			
			"created_at"=>"datetime",
			"updated_at"=>"datetime",
		//), array( "engine"=>"engine=MyISAM") );
		));
		$this->connection->create_table('users_tags', array(
			"user_id"=>"integer",
			"tag_id"=>"integer",
		),	array("primary"=>false)
		);		
	}

	final public function down() {
		$this->connection->drop_table('users');
		$this->connection->drop_table('users_tags');		
	}

}
?>