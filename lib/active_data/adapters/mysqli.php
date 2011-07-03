<?php

class _mysqli extends mysqli {

	public $_supports_count_distinct = true;
	public $_time;
	public $_queries = 0;
	
	public $_native_database_types = array(
		'binary' => array(
			'type' => 'blob',
			'limit' => 16
		),
		'boolean' => array(
			'type' => 'tinyint',
			'limit' => 1
		),
		'date' => array(
			'type' => 'date'
		),
		'datetime' => array(
			'type' => 'datetime'
		),
		'decimal' => array(
			'type' => 'decimal',
			'precision' => 11,
			'scale' => 4
		),
		'float' => array(
			'type' => 'float'
		),
		'integer' => array(
			'type' => 'int',
			'limit' => 11
		),
		'string' => array(
			'type' => 'varchar',
			'limit' => 255
		),
		'text' => array(
			'type' => 'text'
		),
		'richtext' => array(
			'type' => 'longtext'
		),
		'time' => array(
			'type' => 'time'
		),
		'timestamp' => array(
			'type' => 'timestamp'
		)
	);

	final public function __construct($data) {
		$port = empty($data['port']) ? '3306' : $data['port'];
		$socket = empty($data['socket']) ? false : $data['socket'];

		$this->connect($data['host'], $data['username'], $data['password'], $data['database'], $port, $socket);
		if( !empty( $data['encoding'] ) )
		$this->query("set names '" . $data['encoding'] . "'");
	}

	final public function _begin_transaction() {
		return $this->autocommit(false);
	}

	final public function _execute_transaction() {
		$result = $this->commit();

		$this->autocommit(true);

		return $result;
	}

	final public function _rollback_transaction() {
		$result = $this->rollback();

		$this->autocommit(true);

		return $result;
	}

	final public function _create_savepoint($name) {
		return $this->query('savepoint ' . $name);
	}

	final public function rollback_to_savepoint($name) {
		return $this->query('rollback to savepont ' . $name);
	}

	final public function release_savepoint($name) {
		return $this->query('release savepoint ' . $name);
	}

	final public function _cache_on() {
		return $this->query('set session query_cache_type = ON');
	}

	final public function _cache_off() {
		return $this->query('set session query_cache_type = OFF');
	}

	final public function _add_limit_offset(&$sql, $limit, $offset) {
		$sql .= $limit ? ' limit ' . ($offset ? $offset . ', ' : '') . $limit : '';
	}

	final public function _query($query, $values = array(), &$result = false) {
		
		$quoted_values = array();

		foreach ($values as $value)
			$quoted_values[] = is_bool($value) ? (int)$value : (is_numeric($value) ? $value : "'" . $this->real_escape_string($value) . "'");

		$parsed_query = str_replace('?', '%s', $query);
		


		$query = vsprintf($parsed_query, $quoted_values);
		$result = $this->query($query);

		if ($result) {
			$action = preg_match('/^(describe|select|show|update|insert|delete)/i', $query, $coincidences) ? strtolower(trim($coincidences[0])) : false;

			switch ($action) {
				case 'describe': case 'select': case 'show':
					$data = $select_tables = array();

					$fields = $result->fetch_fields();

					$i = 0;

					while ($row = $result->fetch_assoc()) {
						foreach ($fields as $field)
							$data[$i][$field->name] = $row[$field->name];

						$i++;
					}

					$result->free_result();

					return $data;

				case 'update': case 'delete':
					preg_match($action == 'update' ? '/update *([a-z0-9-]*)/i' : '/delete *from *([a-z0-9-]*)/i', $query, $modified_tables);

					return $this->affected_rows;

				case 'insert':
					preg_match('/insert *into *([a-z0-9-]*)/i', $query, $modified_tables);

					return $this->insert_id;

				default:
					return $result;
			}
		}
		else {
			$values =  empty($values) ? '' : print_r($values, true);
			throw new Exception('Invalid query <' . $query . '> ' . $values);
		}
	}

	final public function _columns_on_query($query, $values) {
		$data = $this->_query($query, $values)->result_metadata()->fetch_field();
		$columns = array();
		
		foreach ($data as $column)
			$columns[] = $column->name;

		return $columns;
	}

	final public function _tables() {
		$tables = $this->_query('show tables');
		$table_names = array();
		
		foreach ($tables as $table)
			$table_names[] = array_shift($table);

		return $table_names;
	}

    final public function _create_database($name, $options = array()) {
		$options = array_merge(array(
			'charset' => 'utf8',
			'collate' => false
		), $options);

		$collate = empty($options['collate']) ? ' collate "' . $options['collate'] . '"' : '';

		return $this->query('create database "' . $name . '" default character set "' . $options['charset'] . '"' . $collate);
	}

	final public function _drop_database($name) {
		return $this->query('drop database if exists ' . $name);
	}

	final public function _current_database() {
		$database = $this->_query('select database()');

		return array_shift(array_shift($database));
	}

	final public function _charset() {
		$charset = $this->_show_variable('character_set_database');

		return array_shift($charset);
	}

	final public function _collation() {
		$collation = $this->_show_variable('collation_database');

		return array_shift($collation);
	}

	final public function _indexes($table_name) {
		$indexes = $this->_query('show keys from ' . $table_name);

        foreach ($indexes as $index)
			if ($index['Key_name'] != 'PRIMARY')
				$table_indexes[] = array(
					'table' => $index['Table'],
					'unique' => !$index['Non_unique'],
					'name' => $index['Key_name']
				);

		return $table_indexes;
	}

	final public function _columns($table) {
		$table_information = $this->_query('describe ' . $table);

		foreach ($table_information as $field) {
			preg_match('/([\w]*)(\(([0-9]*)\))?/', $field['Type'], $field_type);

			foreach ($this->_native_database_types as $database_type => $native_database_type)
				if ($native_database_type['type'] == $field_type[1])
					$type = $database_type;

			$limit = $precision = $scale = false;

			if (isset($field_type[3]))
				switch ($type) {
					case 'decimal':
						list($precision, $scale) = explode(',', $field_type[3]);

						break;

					default:
						$limit = $field_type[3];

						break;
				}

			$data[] = array(
				'name' => $field['Field'],
				'default' => $field['Default'],
				'type' => $type,
				'null' => $field['Null'] == 'YES',
				'limit' => $limit,
				'precision' => $precision,
				'scale' => $scale
			);
		}

		return $data;
	}

	final public function _create_table($name, $fields = array(), $options = array()) {
		$options = array_merge(array(
			'primary' => false,
			'engine' => 'engine=InnoDB'
		), $options);

		$query = 'create table if not exists `' . $name . '` (';

		$query .= $options['primary'] ? 'id integer(11) auto_increment primary key, ' : '  ';

		foreach ($fields as $name => $type) {
			$db_type = Database::type_to_sql($this->_native_database_types, $type['type'], $type['limit'], $type['precision'], $type['scale']);

			$null = $type['null'] ? '' : ' not null';

			$default = $type['default'] === false ? '' : ' default "' . $type['default'] . '"';

			$query .= '`' . $name . '` ' . $db_type . $null . $default . ', ';
		}

		$query = substr($query, 0, -2) . ') ' . $options['engine'];
		
		return $this->query($query);
	}

	final public function _truncate_table($name) {
		return $this->query('truncate table ' . $name);
	}

	final public function _drop_table($name) {
		return $this->query('drop table if exists ' . $name);
	}

	final public function _rename_table($name, $new_name) {
		return $this->query('rename table ' . $name . ' to ' . $new_name);
	}

	final public function _add_column($table_name, $column_name, $type, $options) {
		$options = array_merge(array(
			'limit' => false,
			'precision' => false,
			'scale' => false,
			'null' => false,
			'default' => false
		), $options);

		$type = Database::type_to_sql($this->_native_database_types, $type, $options['limit'], $options['precision'], $options['scale']);

		$null = $options['null'] ? '' : ' not null';

		$default = $options['default'] ? ' default "' . $options['default'] . '"' : '';

		return $this->query('alter table ' . $table_name . ' add ' . $column_name . ' ' . $type . $null . $default);
	}

	final public function _remove_column($table_name, $column_name) {
		return $this->query('alter table ' . $table_name . ' drop ' . $column_name);
	}

	final public function _change_column_default($table_name, $column_name, $default) {
		$columns = $this->_query('show columns from ' . $table_name . ' like "' . $column_name . '"');

		$current_type = array_shift($columns);

		return $this->query('alter table ' . $table_name . ' change ' . $column_name . ' ' . $column_name . ' ' . $current_type['Type'] . ' default "' . $default . '"');
	}

	final public function _change_column($table_name, $column_name, $type, $options = array()) {
        $options = array_merge(array(
			'limit' => false,
			'precision' => false,
			'scale' => false
		), $options);

		$columns = $this->_query('show columns from ' . $table_name . ' like "' . $column_name . '"');

		$current_type = array_shift($columns);

		$type = Database::type_to_sql($this->_native_database_types, $type, $options['limit'], $options['precision'], $options['scale']);

		return $this->query('alter table ' . $table_name . ' change ' . $column_name . ' ' . $new_column_name . ' ' . $type . ' default "' . $current_type['Default'] . '"');
	}

	final public function _rename_column($table_name, $column_name, $new_column_name) {
		$columns = $this->_query('show columns from ' . $table_name . ' like "' . $column_name . '"');

		$current_type = array_shift($columns);

		return $this->query('alter table ' . $table_name . ' change ' . $column_name . ' ' . $new_column_name . ' ' . $current_type['Type']);
	}

	final public function _add_index($table_name, $column_name, $options) {
		$options = array_merge(array(
			'type' => false,
			'name' => is_string($column_name) ? $column_name : implode('_and_', $column_name)
		), $options);

		$column_name = is_string($column_name) ? $column_name : implode(', ', $column_name);

		return $this->query('create ' . $options['type'] . ' index ' . $options['name'] . ' on ' . $table_name . '(' . $column_name . ')');
	}

	final public function _remove_index($table_name, $column_name) {
        $index_name = is_string($column_name) ? $column_name : implode('_and_', $column_name);

        return $this->query('drop index ' . $index_name . ' on ' . $table_name);
	}

	final public function _show_variable($name) {
		$variables = $this->_query('show variables like "' . $name . '"');

		foreach ($variables as $variable) {
			$variable_name = $variable['Variable_name'];

			$data[$variable_name] = $variable['Value'];
		}

		return $data;
	}

}

?>