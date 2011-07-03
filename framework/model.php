<?
class Model extends ActiveRecord{
	
	public $_required_attributes=array();
	
	final public function is_required( $field ){
		return in_array($field,$this->_required_attributes) ? " required" : null;
	}
		
	final public function index( $options ){
		$query = "SELECT * FROM $this->_table_name";
		if( $options ){
			if (isset($options['group'])) $query .= ' GROUP BY ' . $options['group'];
			if (isset($options['having'])) $query .= ' HAVING ' . $options['having'];
			if (isset($options['order'])) {
				if( $options['order']=="id")  $options['order'] = $this->_primary_key;
				$query .= ' ORDER BY ' . $options['order'];
			}
			if (isset($options['limit'])) $this->_connection->add_limit_offset($query, $options['limit'], $options['offset']);
		}
		return $this->query($query,$options);
	}
	final public function retrive( $id ){
		
		$query = "SELECT * FROM $this->_table_name WHERE ".$this->_primary_key." = ? LIMIT 1";	
		$list =  $this->query($query, array( $id ) );
		return empty($list) ? $list : $list[0];
	}
	
	
}
?>