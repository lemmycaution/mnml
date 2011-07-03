<?
class Post extends Model{
	//public $_record_timestamps = true;
	//public $_has_and_belongs_to_many = array("tags"=>array("join_table"=>"users_tags"));
	public $_belongs_to = array("user");	
	public $_required_attributes=array("title","body");
}
?>