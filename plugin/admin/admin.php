<?
class Admin extends Model{
	public static $table_prefix = "plugin_admin_";
	
	public $_record_timestamps = true;
	protected $_table_name = "plugin_admin_admins";
	
	public function before_create( ){
		if(isset($_SESSION["admin_auth"]) && $_SESSION["admin_auth"]["role"]!="root" && $this->role=="root")
			$this->role=$_SESSION["admin_auth"]["role"];
		if ( $this->password ){
			$this->password = md5( $this->password );
		}	

	}
	public function before_update( ){
		if(isset($_SESSION["admin_auth"]) && $_SESSION["admin_auth"]["role"]!="root" && $this->role=="root")
			$this->role=$_SESSION["admin_auth"]["role"];
		if ( $this->password ){
			$this->password = md5( $this->password );
		}	
	}
	public function before_delete( ){
		if(isset($_SESSION["admin_auth"]) && $_SESSION["admin_auth"]["role"]!="root" && $this->role=="root"){
			error( array("Error"=>"only root users can delete other root users") );
		}	
	}
}
?>