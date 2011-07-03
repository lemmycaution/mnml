<?
class ProfMigration {

	function __construct(){
		$this->connection = DatabaseManager::get("");
	}

	final public function up() {
		$this->connection->create_table('profs', array(
			"user_id"=>"integer",
			"active"=>"boolean",
			"bio"=>"richtext",			

		));	
	}

	final public function down() {
		$this->connection->drop_table('profs');
	}

}
?>