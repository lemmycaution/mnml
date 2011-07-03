<?
class PostMigration {

	function __construct(){
		$this->connection = DatabaseManager::get("");
	}

	final public function up() {
		$this->connection->create_table('posts', array(
			"title"=>"string",
			"body"=>"richtext",			
			"user_id"=>"integer",			
			"created_at"=>"datetime",
			"updated_at"=>"datetime",
		//), array( "engine"=>"engine=MyISAM") );
		));
		
	}

	final public function down() {
		$this->connection->drop_table('posts');

	}

}
?>