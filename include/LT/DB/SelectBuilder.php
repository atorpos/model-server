<?php

namespace LT\DB;

abstract class SelectBuilder extends SQLBuilder {

	protected $_options = array(
		'fields'	 => '*',
		'where'		 => array(),
		'order'		 => array(),
		'key_field'	 => 'id',
	);

	/**
	 * 
	 * @param \LT\DB\Connection $conn
	 * @return static
	 */
	public static function factory($conn = NULL) {
		if (is_null($conn)) {
			$conn = \LT\DB::conn();
		}
		$type	 = ucfirst($conn->type());
		$class	 = "\\LT\\DB\\$type\\SelectBuilder";
		if (!class_exists($class)) {
			\LT\Exception::general('unknown class ' . $class);
		}
		$builder = new $class($conn);
		return $builder;
	}

	/**
	 * 
	 * @param string $fields
	 * @return static
	 */
	public function fields($fields) {
		$this->_options['fields'] = $fields;
		return $this;
	}

	/**
	 * 
	 * @param string $columns
	 * @return static
	 */
	public function groupBy($columns) {
		$this->_options['group'] = $columns;
		return $this;
	}

	/**
	 * 
	 * @param int $index
	 * @return static
	 */
	public function offset($index) {
		$this->_options['offset'] = $index;
		return $this;
	}

	/**
	 * Get PDO result set
	 * @return \PDOStatement
	 */
	public function query() {
		$rs = $this->_conn->query($this);
		return $rs;
	}

	/**
	 * Get all results in model
	 * @return \LT\Model
	 */
	public function get() {
		$rs = $this->_conn->query($this);
		if (isset($this->_options['model'])) {
			$class = $this->_options['model'];
			return $class::load($rs, $this->_options);
		}
		return $rs;
	}

	/**
	 * Get all results in array
	 * @return array
	 */
	public function getArray() {
		$rs = $this->_conn->query($this);
		return $rs->fetchAll(\PDO::FETCH_ASSOC);
	}

	/**
	 * Get single column in array
	 * @return array
	 */
	public function getCol($field) {
		$rs = $this->fields($field)->query();
		return $rs->fetchAll(\PDO::FETCH_COLUMN, 0);
	}

	/**
	 * Get number of records
	 * @return int
	 */
	public function getCount() {
		return $this->getValue('COUNT(*) AS `count`');
	}

	/**
	 * Get key-value pairs
	 * @param string $valueField
	 * @param string $keyField
	 * @return array
	 */
	public function getKeyValue($valueField, $keyField = 'id') {
		$rs = $this->fields("$keyField, $valueField")->query();
		if (!$rs) {
			return array();
		}
		$o = array();
		foreach ($rs as $r) {
			$o[$r[$keyField]] = $r[$valueField];
		}
		return $o;
	}

	/**
	 * Get single record in model
	 * @return \LT\Model
	 */
	public function getRow() {
		$rs = $this->offset(0)->limit(1)->get();
		if (count($rs)) {
			return current($rs);
		}
		return NULL;
	}

	/**
	 * Get single value
	 * @return string
	 */
	public function getValue($field) {
		$row = $this->fields($field)->offset(0)->limit(1)->getRow();
		if (is_array($row) && !empty($row)) {
			return current($row);
		}
		return NULL;
	}

	/**
	 * Get latest record in model
	 * @return \LT\Model
	 */
	public function getLatest($column = 'created_time') {
		return $this->orderBy($column, TRUE)->limit(1)->getRow();
	}

	/**
	 * Get first record in model
	 * @return \LT\Model
	 */
	public function getFirst($column = 'created_time') {
		return $this->orderBy($column, FALSE)->limit(1)->getRow();
	}

}
