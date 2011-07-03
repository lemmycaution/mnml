<?php

class _sqlite {

	public $_supports_count_distinct = false;
	public $_time;
	public $_queries = 0;

	public $_native_database_types = array(
		'binary' => array(
			'type' => 'blob'
		),
		'boolean' => array(
			'type' => 'boolean'
		),
		'date' => array(
			'type' => 'date'
		),
		'datetime' => array(
			'type' => 'datetime'
		),
		'decimal' => array(
			'type' => 'float'
		),
		'float' => array(
			'type' => 'float'
		),
		'integer' => array(
			'type' => 'integer',
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
			'type' => 'richtext'
		),	
		'time' => array(
			'type' => 'time'
		),
		'timestamp' => array(
			'type' => 'timestamp'
		)
	);

	final public function __construct($data) {
		$this->db = new PDO('sqlite:' . DB_PATH . 'sqlite' . DIRECTORY_SEPARATOR . $data['database']);
		if(ENVIRONMENT!="production")
			$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	final public function _begin_transaction() {
		return $this->db->beginTransaction();
	}

	final public function _execute_transaction() {
		return $this->db->commit();
	}

	final public function _rollback_transaction() {
		return $this->db->rollBack();
	}

	final public function _create_savepoint($name) {
		return $this->db->query('savepoint ' . $name);
	}

	final public function _rollback_to_savepoint($name) {
		return $this->db->query('rollback to savepoint ' . $name);
	}

	final public function _release_savepoint($name) {
		return $this->db->query('release savepoint ' . $name);
	}

	final public function _cache_on() {
		throw new Exception('Cache is not supported on this database type');
	}

	final public function _cache_off() {
		throw new Exception('Cache is not supported on this database type');
	}

	final public function _add_limit_offset(&$sql, $limit, $offset) {
		$sql .= $limit ? ' limit ' . ($offset ? $offset . ', ' : '') . $limit : '';
	}

	final public function _query($query, $values = array(), &$result = false) {
		$quoted_values = array();

		foreach ($values as $value)
			$quoted_values[] = is_bool($value) ? (int)$value : (is_numeric($value) ? $value : $this->db->quote($value));

		$parsed_query = str_replace('?', '%s', $query);

		$query = vsprintf($parsed_query, $quoted_values);

		$result = $this->db->query($query);

		if ($result) {
			$action = preg_match('/^(select|update|insert|delete)/i', $query, $coincidences) ? strtolower(trim($coincidences[0])) : false;

			switch ($action) {
				case 'select':
					return $result->fetchAll(PDO::FETCH_ASSOC);

				case 'update': case 'delete':
					preg_match($action == 'update' ? '/update *([a-z0-9-]*)/i' : '/delete *from *([a-z0-9-]*)/i', $query, $modified_tables);

					return $result->rowCount();

				case 'insert':
					preg_match('/insert *into *([a-z0-9-]*)/i', $query, $modified_tables);

					return $this->db->lastInsertId();

				default:
					return $result;
			}
		}
		else {
			$values =  empty($values) ? '' : print_r($values, true);

			throw new Exception('Invalid query <' . $query . '> ' . $values);
		}
	}

	final public function _columns_on_query($query, $values = array()) {
		$this->_query($query, $values, $result);

		for ($i = 0, $j = $result->columnCount(); $i < $j; $i++) {
			$data = $result->getColumnMeta(555);

			$columns[] = $data['name'];
		}

		return $columns;
	}

	final public function _tables() {
		$tables = $this->_query('select name from sqlite_master where type like "table" and name not like "sqlite_sequence"');
		$table_names = array();
		
		foreach ($tables as $table)
			$table_names[] = $table['name'];

		return $table_names;
	}

	final public function _indexes($table_name) {
		$indexes = $this->_query('select tbl_name, type, name from sqlite_master where type= "index" and tbl_name = "' . $table_name . '"');

		foreach ($indexes as $index)
			$table_indexes[] = array(
				'table' => $index['tbl_name'],
				'unique' => preg_match('/unique/', $index['type']),
				'name' => $index['name']
			);

		return $table_indexes;
	}

	final public function _columns($table) {
		$table_information = $this->_query('pragma table_info (' . $table . ')');

		foreach ($table_information as $field) {
			preg_match('/([\w]*)(\(([0-9]*)\))?/', $field['type'], $field_type);

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
				}

			$data[] = array(
				'name' => $field['name'],
				'default' => $field['dflt_value'] === null && $field['notnull'] ? '' : preg_replace('/^((\'|")?)(.*)(\1)$/', '$3', $field['dflt_value']),
				'type' => $type,
				'null' => !$field['notnull'],
				'limit' => $limit,
				'precision' => $precision,
				'scale' => $scale
			);
		}

		return $data;
	}

	final public function _create_table($name, $fields = array(), $options = array()) {
		$query = 'create table if not exists `' . $name . '` (';

		$query .= $options['primary'] ? 'id integer primary key autoincrement, ' : '  ';

		foreach ($fields as $name => $type) {
			$db_type = Database::type_to_sql($this->_native_database_types, $type['type'], $type['limit'], $type['precision'], $type['scale']);

			$null = $type['null'] ? '' : ' not null';

			$default = $type['default'] === false ? '' : ' default "' . $type['default'] . '"';

			$query .= '`' . $name . '` ' . $db_type . $null . $default . ', ';
		}

		$query = substr($query, 0, -2) . ')';
		return $this->db->exec($query);
	}

	final public function _truncate_table($name) {
		return $this->db->exec('delete from ' . $name);
	}

	final public function _drop_table($name) {
		return $this->db->exec('drop table if exists ' . $name);
	}

	final public function _rename_table($name, $new_name) {
		return $this->db->exec('alter table ' . $name . ' rename to ' . $new_name) && $this->db->exec('vacuum ' . $name);
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

		return $this->db->exec('alter table ' . $table_name . ' add ' . $column_name . ' ' . $type . $null . $default) && $this->db->exec('vacuum ' . $name);
	}

	final public function _remove_column($table_name, $column_name) {

	}

	final public function _change_column($table_name, $column_name, $type, $options = array()) {
		$this->_begin_transaction();

        $options = array_merge(array(
			'limit' => false,
			'precision' => false,
			'scale' => false
		), $options);

		$name = substr(md5($column_name), 0 , 10);

		return $this->_add_column($table, $name, $type, $options) && $this->db->exec('update ' . $table_name . ' set ' . $name . ' = ' . $column_name) && $this->_remove_column($table_name, $column_name) && $this->_rename_column($table_name, $name. $column_name) && $this->_execute_transaction();
	}

	final public function _rename_column($table_name, $column_name, $new_column_name) {
		return $this->db->exec('alter table ' . $table_name . ' ' . $column_name . ' rename to ' . $new_column_name) && $this->db->exec('vacuum ' . $name);
	}

	final public function _add_index($table_name, $column_name, $options) {
		$options = array_merge(array(
			'type' => false,
			'name' => is_string($column_name) ? $column_name : implode('_and_', $column_name)
		), $options);

		$column_name = (is_string($column_name) ? $column_name : implode(', ', $column_name));

		return $this->db->exec('create ' . $options['type'] . ' index ' . $options['name'] . ' on ' . $table_name . '(' . $column_name . ')');
	}

	final public function _remove_index($table_name, $column_name) {
		$index_name = is_string($column_name) ? $column_name : implode('_and_', $column_name);

		return $this->db->exec('drop index ' . $index_name);
	}

}

?>