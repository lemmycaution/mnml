<?
class TagMigration {

	function __construct(){
		$this->connection = DatabaseManager::get("");
	}

	final public function up() {
		$this->connection->create_table('tags', array(
			"tag"=>"string",
		//), array( "engine"=>"engine=MyISAM") );
		));
	}

	final public function down() {
		$this->connection->drop_table('tags');
	}

}
?>