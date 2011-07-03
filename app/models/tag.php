<?
class Tag extends Model{
	public $_record_timestamps = false;
	public $_has_and_belongs_to_many = array("users"=>array("join_table"=>"users_tags"));

}
?>