<?php

class Database {

	final public function __construct($config) {
		$path = LIB_PATH . 'active_data' . DIRECTORY_SEPARATOR . 'adapters' . DIRECTORY_SEPARATOR . $config['adapter'] . '.php';

		if (file_exists($path)) {
			require_once $path;

			$adapter = '_' . $config['adapter'];

			$this->database = new $adapter($config);
		}
		else
			throw new Exception('Invalid database adapter');
	}

	final public function add_limit_offset(&$sql, $offset, $values) {
		$this->database->_add_limit_offset($sql, $offset, $values);
	}

	final public function begin_transaction() {
		return $this->database->_begin_transaction();
	}

	final public function columns_on_query($query, $values = array()) {
		return $this->database->_columns_on_query(trim($query), $values);
	}

	final public function execute_transaction() {
		return $this->database->_execute_transaction();
	}

	final public function rollback_transaction() {
		return $this->database->_rollback_transaction();
	}

	final public function create_savepoint($name) {
		return $this->database->_create_savepoint($name);
	}

	final public function rollback_to_savepoint($name) {
		return $this->database->_rollback_to_savepoint($name);
	}

	final public function release_savepoint($name) {
		return $this->database->_relesase_savepoint($name);
	}

	final public function cache_on() {
		return $this->database->_cache_on();
	}

	final public function cache_off() {
		return $this->database->_cache_off();
	}

	final public function query($query, $values = array()) {
		$time = microtime(true);

		$data = $this->database->_query($query, $values);

		$this->database->_time += microtime(true) - $time;

		$this->database->_queries++;

		return $data;
	}

	final public function tables() {
		return $this->database->_tables();
	}

    final public function create_database($name, $options = array()) {
		return $this->database->_create_database($name, $options);
	}

	final public function drop_database($name) {
		return $this->database->_drop_database($name);
	}

	final public function current_database() {
		return $this->database->_current_database();
	}

	final public function charset() {
		return $this->database->_charset();
	}

	final public function collation() {
		return $this->database->_collation();
	}

	final public function indexes($table_name, $name = false) {
		return $this->database->_indexes($table_name, $name);
	}

	final public function columns($table) {
		return $this->database->_columns($table);
	}

	final public function create_table($name, $fields = array(), $options = array()) {
		$cfields=array();
		
		$default_field_options = array(
			'default' => false,
			'type' => false,
			'limit' => false,
			'precision' => false,
			'scale' => false,
			'null' => false
		);

		foreach ($fields as $field_name => $field_options) {
			$field_options = is_string($field_options) ? array('type' => $field_options) : $field_options;
			$field_options = array_merge($default_field_options, $field_options);

			if ($field_options['type'] == 'polymorphic') {
				$cfields[$field_name . '_type'] = array_merge($field_options, array('type' => 'string'));

				$cfields[$field_name . '_id'] = array_merge($field_options, array('type' => 'integer'));
			}
			else
				$cfields[$field_name] = $field_options;
		}

		$options = array_merge(array(
			'primary' => true
		), $options);

		return $this->database->_create_table($name, $cfields, $options);
	}

	final public function drop_table($name) {
		return $this->database->_drop_table($name);
	}

	final public function truncate_table($name) {
		return $this->database->_truncate_table($name);
	}

	final public function rename_table($name, $new_name) {
		return $this->database->_rename_table($name, $new_name);
	}

	final public function add_column($table_name, $column_name, $type, $options = array()) {
		return $this->database->_add_column($table_name, $column_name, $type, $options);
	}

	final public function remove_column($table_name, $column_name) {
		return $this->database->_remove_column($table_name, $column_name);
	}

	final public function change_column_default($table_name, $column_name, $default) {
		return $this->database->_change_column_default($table_name, $column_name, $default);
	}

	final public function change_column($table_name, $column_name, $type, $options = array()) {
		return $this->database->_change_column($table_name, $column_name, $type, $options);
	}

	final public function rename_column($table_name, $column_name, $new_column_name) {
		return $this->database->_rename_column($table_name, $column_name, $new_column_name);
	}

	final public function add_index($table_name, $column_name, $options = array()) {
		return $this->database->_add_index($table_name, $column_name, $options);
	}

	final public function remove_index($table_name, $options) {
		return $this->database->_remove_index($table_name, $options);
	}

	final public function show_variable($name) {
		return $this->database->_show_variable($name);
	}

	final public function restore($data, $table) {
		$limit = ini_get('max_execution_time');

		set_time_limit(0);

		foreach ($data as $row) {
			$keys = array_keys($row);
			$values = array_values($row);
			$count = count($keys);
			$replaced_values = array_fill(0, $count, '?');

			$sql = 'insert into ' . $table . '(' . implode(', ', $keys) . ') values (' . implode(', ', $replaced_values) . ')';

			$this->database->_query($sql, $values);
		}

		set_time_limit($limit);
	}

	final static function type_to_sql($_native_database_types, $type, $limit = false, $precision = false, $scale = false) {
		$db_type = false;

		foreach ($_native_database_types as $data_type => $properties)
			if ($data_type == $type)
				$db_type = array_merge(array(
					'type' => false,
					'limit' => false,
					'precision' => false,
					'scale' => false
				), $properties);

		if ($type == 'decimal') {
			$precision = $precision ? $precision : $db_type['precision'];

			$scale = $scale ? $scale : $db_type['scale'];

			if (!empty($precision))
				return $db_type['type'] . '(' . $precision . ($scale ? ', ' . $scale : '') . ')';
		}
		else {
			$limit = $limit ? $limit : $db_type['limit'];

			return $db_type['type'] . ($limit ? '(' . $limit . ')' : '');
		}
	}

}

?>