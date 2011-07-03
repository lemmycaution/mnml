<?
class User extends Model{
	public $_record_timestamps = true;
	public $_has_and_belongs_to_many = array("tags"=>array("join_table"=>"users_tags"));
	public $_has_one = array("prof");	
	public $_has_many = array("posts");		

}
?>