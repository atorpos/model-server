<?php

namespace LT;

/**
 * @property string[] $fields Fields
 */
abstract class Model implements \ArrayAccess {

	static $_connnectionName = 'db.default';

	/**
	 * @var array Database table fields 
	 */
	protected $_fields = array();

	/**
	 * @var string Database table primary key
	 */
	protected $_pk;

	/**
	 * @var \LT\DB\Connection Database object
	 */
	protected $_db = NULL;

	/**
	 * @var bool This record is loaded from database? 
	 */
	protected $_load = FALSE;
	protected $_raw	 = array();

	const ACTION_CREATE	 = 'create';
	const ACTION_UPDATE	 = 'update';
	const ACTION_DELETE	 = 'delete';

	public function __construct($data = NULL) {


		// cache public properties
		if (empty($this->_fields)) {
			$vars = get_object_vars($this);
			foreach (array_keys($vars) as $k) {
				if ($k[0] != '_') {
					$this->_fields[] = $k;
				}
			}
		}

		// get primary key from the list of fields
		if (is_null($this->_pk) && isset($this->_fields[0])) {
			$this->_pk = $this->_fields[0];
		}

		// get database connection
		if (is_null($this->_db)) {
			$this->_db = \LT\DB::conn();
		}

		// init data
		if (is_array($data) && !empty($data)) {
			$this->_setData($data);
		}
	}

//    public function __get($name) {
//        if ('fields' === $name) {
//            return $this->_fields;
//        }
//        $trace = debug_backtrace();
//        trigger_error(
//                'Undefined property via __get(): ' . $name .
//                ' in ' . $trace[0]['file'] .
//                ' on line ' . $trace[0]['line'], E_USER_NOTICE);
//    }

	/**
	 * Get class name
	 * @return string
	 */
	public static function classname() {
		return get_called_class();
	}

	/**
	 * Load data into model(s)
	 * 
	 * @param array $data
	 * @param array $options
	 * @return static
	 */
	public static function load($data, $options = array()) {

		if (isset($options['no_key_field']) && $options['no_key_field']) {
			unset($options['key_field']);
		}

		if ((get_class($data) != 'PDOStatement') && !is_array($data)) {
			Exception::general('unsupported data type');
		}

		if (is_array($data) && ctype_digit(key($data))) {
			$data = array($data);
		}

		$k = 0;
		if (isset($options['key_field'])) {
			$k = $options['key_field'];
		}

		$o = array();
        foreach ($data as $r) {
            $r['_load'] = TRUE;
            if (isset($r[$k])) {
                $o[$r[$k]] = new static($r);
            } else {
                $o[] = new static($r);
            }
        }


//		for ($i =0; $i < count($data); $i++) {
//            $data[$i]['_load'] = TRUE;
//            if (isset($data[$i][$k])) {
//                $o[$data[$i][$k]] = new static($data[$i][$k]);
//            } else {
//                $o[] = new static($data[$i]);
//            }
//        }


		return $o;
	}

	/**
	 * 
	 * @param array $opts
	 * @return int
	 */
	public static function findCount($opts) {
		if (is_string($opts) || (is_array($opts) && !isset($opts['where']))) {
			$opts = array(
				'where' => $opts,
			);
		}
		$opts['fields'] = 'COUNT(*) AS `count`';

		$rs = static::findOne($opts);

		if ($rs && $rs->count) {
			return (int) $rs->count;
		}
		return 0;
	}

	/**
	 * 
	 * @param array $opts
	 * @return int
	 */
	public static function findMax($column, $opts) {
		if (is_string($opts) || (is_array($opts) && !isset($opts['where']))) {
			$opts = array(
				'where' => $opts,
			);
		}
		$opts['fields'] = 'Max(`' . $column . '`) AS `max`';

		$rs = static::findOne($opts);

		if ($rs && $rs->max) {
			return (int) $rs->max;
		}
		return 0;
	}

	/**
	 * @param array $opts
	 * @return static
	 */
	public static function findOne($opts) {
		if (is_array($opts) && !isset($opts['where'])) {
			$opts = array(
				'where' => $opts,
			);
		}
		if (is_array($opts) && !isset($opts['limit'])) {
			$opts['limit'] = 1;
		}
		$rs = static::find($opts);
		if (is_array($rs) && !empty($rs)) {
			return current($rs);
		}
		return NULL;
	}

	/**
	 * @param array $opts
	 * @return static Model data
	 */
	public static function find($opts = []) {
		if (!is_array($opts) && ctype_digit((string) $opts)) {
			$opts = [
				'where'	 => ['id' => $opts],
				'limit'	 => 1,
			];
		}
		if (is_array($opts) && !isset($opts['where'])) {
			$opts = [
				'where' => $opts,
			];
		}
		if (!isset($opts['key_field'])) {
			$opts['key_field'] = 'id';
		}
//        if (is_string($opts)) {
//            $opts = [
//                'where' => $opts,
//            ];
//        }
		if (FALSE === ($opts = static::_beforeQuery($opts))) {
			return FALSE;
		}

		$rs = static::_afterQuery(static::query($opts)->run());
		return $rs;
	}

	/**
	 * @return static[]|static
	 */
	public static function findMatches($field, $values) {
		return static::find(array(
					'where' => array($field => $values),
		));
	}

	/**
	 * @param int $id
	 * @return static
	 */
	public static function findByID($id) {
		$opts = array('where' => array('id' => (int) $id));
		return static::findOne($opts);
	}

	/**
	 * 
	 * @return \LT\DB\SelectBuilder
	 */
	public static function select() {
		return DB\SelectBuilder::factory()->from(static::_getTableName())->model(get_called_class());
	}

	/**
	 * @param string $where
	 * @return \LT\DB\DeleteBuilder
	 */
	public static function prepareDelete($where) {
		return DB\DeleteBuilder::factory()->from(static::_getTableName())->where($where);
	}

	/**
	 * 
	 * @param string $where
	 * @param int $limit
	 * @return int number of rows deleted
	 */
	public static function quickDelete($where, $limit = NULL) {
		$deleteBuilder = static::prepareDelete($where);
		if ($limit) {
			$deleteBuilder->limit($limit);
		}
		return $deleteBuilder->execute();
	}

	protected function _setData($data) {

		foreach ($data as $k => $v) {
			$this->$k = $v;
		}
		if (isset($data['_load'])) {
			$this->_raw	 = $data;
			$this->_load = $data['_load'];
		}
	}

	protected function _isChanged($field) {
		if (isset($this->_raw[$field])) {
			return ($this->_raw[$field] != $this->$field);
		}
		return FALSE;
	}

	protected function _getData() {
		$data = array();
		foreach ($this->_fields as $f) {
			$data[$f] = $this->$f;
		}
		return $data;
	}

	protected static function _getTableName($class = NULL) {
		static $tables = array();

		if (is_null($class)) {
			$class = get_called_class();
		}
		if (!isset($tables[$class])) {
			if (property_exists($class, '_table')) {
				$tables[$class] = static::$_table;
			} else {
				// parse class name to database table name
				$ps				 = explode('\\', $class);
				$tables[$class]	 = substr(strtolower(preg_replace('/([A-Z])/', '_$1', end($ps))), 1);
			}
		}
		return $tables[$class];
	}

	/**
	 * Data as array
	 * 
	 * @return []
	 */
	public function getData() {
		$data = $this->_getData();
		return $data;
	}

	public function getRawData() {
		return $this->_raw;
	}

	public function getRawValue($field) {
		return isset($this->_raw[$field]) ? $this->_raw[$field] : NULL;
	}

	public function lastSQL() {
		return $this->_db->lastSQL();
	}

	public function errors() {
		return $this->_db->errorInfo();
	}

	public function errorMessage() {
		$errors = $this->errors();
		if (is_array($errors) && (isset($errors[2]) || is_null($errors[2]))) {
			return $errors[2];
		}
		return 'unknown error';
	}

	public function dump() {
		\LT::dump($this);
	}

	public function table() {
		return $this->_db->table(static::_getTableName());
	}

	public function key() {
		return $this->{$this->_pk};
	}

	/**
	 * @return static[]|static
	 */
	public static function all() {
		$opts = array();
		return static::find($opts);
	}

	/**
	 * Get model name by a table name
	 * 
	 * @param string $table Table name
	 * @return string
	 */
	public static function getModelNameByTableName($table) {
		return str_replace(' ', '', ucwords(str_replace('_', ' ', $table)));
	}

	/**
	 * 
	 * @return \LT\DB\QueryBuilder
	 */
	public static function query($options = array()) {
		if (!isset($options['from'])) {
			$options['from'] = static::_getTableName();
		}

		if (!isset($options['model'])) {
			$options['model'] = get_called_class();
		}

		return \LT\DB::conn()->queryBuilder($options);
	}

	/**
	 * Convert data to key value pair array
	 * 
	 * @param static[] $data Data array
	 * @param string $keyField Key field, multicolumn is supported by // 
	 * @param string $valueField Value field
	 * @return [] Key value pair array
	 */
	public static function keyValue($data, $keyField, $valueField) {
		$output = [];
		if (is_array($data)) {
			foreach ($data as $_data) {
				$_kf = explode('//', $keyField);
				if (count($_kf) > 1) {
					$_k = [];
					foreach ($_kf as $_f) {
						$_k[] = $_data->$_f;
					}
					$k = implode('//', $_k);
				} else {
					$k = $_data->$keyField;
				}
				$output[$k] = $_data->$valueField;
			}
		}
		return $output;
	}

	/**
	 * Change collection key
	 * 
	 * @param static[] $data Data array
	 * @param string $keyField Key field, multicolumn is supported by // 
	 * @return static[]|static Key value pair array
	 */
	public static function changeKey($data, $keyField) {
		return Utilities\ArrayHelper::changeKey($data, $keyField);
	}

	/**
	 * 
	 * @param mixed $data
	 * @return int|boolean FALSE = error.  int = affected row or id depending on update or insert
	 */
	public function save($data = NULL) {
		if ($this->_load) {
			return $this->update($data);
		} else {
			return $this->create($data);
		}
	}

	public function saveAs($replaceData = array()) {
		$this->_load		 = FALSE;
		$this->{$this->_pk}	 = NULL;
		if (!empty($replaceData)) {
			foreach ($replaceData as $k => $v) {
				$this->$k = $v;
			}
		}
		return $this->create();
	}

	public function create($data = NULL) {

		if (is_array($data)) {
			$this->_setData($data);
		}

		if (FALSE === $this->_beforeCreate()) {
			return FALSE;
		}
		if (FALSE === $this->_beforeSave()) {
			return FALSE;
		}
		if (FALSE === $this->_beforeExecute()) {
			return FALSE;
		}

		$rs = $this->_db->insert($this->table(), $this->_getData());

		if ($rs) {
			$this->_load		 = TRUE;
			$this->{$this->_pk}	 = ctype_digit($rs) ? intval($rs) : $rs;
		}
		$this->_afterExecute(self::ACTION_CREATE, $rs);
		if ($rs) {
			$this->_afterCreate();
			$this->_afterSave();
		}



		return $rs;
	}

	public function update($data = NULL) {

		if (is_array($data)) {
			$this->_setData($data);
		}

		if (FALSE === $this->_beforeUpdate()) {
			return FALSE;
		}
		if (FALSE === $this->_beforeSave()) {
			return FALSE;
		}

		if ($this->{$this->_pk}) {

			if (FALSE === $this->_beforeExecute()) {
				return FALSE;
			}

			if (($changeData = $this->getChangedData())) {
				$affectedRow = $this->_db->update($this->table(), $changeData, array($this->_pk => $this->{$this->_pk}));
			} else {
				$affectedRow = 0;
			}

			$this->_afterExecute(self::ACTION_UPDATE, $affectedRow);
			if (FALSE !== $affectedRow) {
				$this->_afterUpdate();
				$this->_afterSave();
				$this->_raw = $this->_getData();
			}

			return $affectedRow;
		}
		return FALSE;
	}

	/**
	 * @todo check delete status to fire after delete
	 * @return boolean
	 */
	public function delete() {

		$this->_beforeDelete();

		if ($this->{$this->_pk}) {

			$this->_beforeExecute();

			$rs = $this->_db->delete($this->table(), array($this->_pk => $this->{$this->_pk}));

			$this->_load = FALSE;

			$this->_afterExecute(self::ACTION_DELETE, $rs);
			$this->_afterDelete();

			return $rs;
		}
		return FALSE;
	}

	public function getChangedData($forLogging = FALSE) {
		$changed = $this->_getData();

		foreach ($changed as $field => $value) {
			if (array_key_exists($field, $this->_raw) && $this->_raw[$field] === $value) {
				unset($changed[$field]);
			}
		}

		if ($forLogging && !empty($this->_skipLogChangeFields)) {
			$changed = array_diff_key($changed, array_flip($this->_skipLogChangeFields));
		}

		return $changed;
	}

	public function offsetSet($offset, $value) {
		if (is_null($offset)) {
//			$this->container[] = $value;
		} else {
			$this->$offset = $value;
		}
	}

	public function offsetExists($offset) {
		return isset($this->$offset);
	}

	public function offsetUnset($offset) {
		unset($this->$offset);
	}

	public function offsetGet($offset) {
		return isset($this->$offset) ? $this->$offset : NULL;
	}

	public function __isset($name) {
		if ('fields' === $name) {
			return TRUE;
		}
		return FALSE;
	}

	public function __sleep() {
		return $this->_getData();
	}

	/**
	 * Modfiy query options before query
	 * 
	 * @param [] $opts
	 * @return [] query options
	 */
	protected static function _beforeQuery($opts) {
		return $opts;
	}

	/**
	 * @param static[] $rs
	 * @return static[]
	 */
	protected static function _afterQuery($rs) {
		return $rs;
	}

	protected function _beforeExecute() {
		return TRUE;
	}

	protected function _beforeSave() {
		return TRUE;
	}

	protected function _beforeCreate() {
		return TRUE;
	}

	protected function _beforeUpdate() {
		return TRUE;
	}

	protected function _beforeDelete() {
		return TRUE;
	}

	protected function _afterExecute($action, $result) {
		return TRUE;
	}

	protected function _afterSave() {
		return TRUE;
	}

	protected function _afterCreate() {
		return TRUE;
	}

	protected function _afterUpdate() {
		return TRUE;
	}

	protected function _afterDelete() {
		return TRUE;
	}

}

//
//class ModelS {
//
//	protected $_table	 = NULL;
//	protected $_fields	 = array();
//	protected $_pk		 = NULL;
//	protected $_load	 = FALSE;
//
//	/**
//	 * @var \LT\Database 
//	 */
//	protected $_db;
//
//	public function toArray() {
//		$o = array();
//		foreach ($this->_fields as $f) {
//			$o[$f] = $this->$f;
//		}
//		return $o;
//	}
//
//	/**
//	 * Get first record
//	 * 
//	 * @param mixed $where
//	 * @return static
//	 */
//	public static function findFirst($where = TRUE) {
//		$rs = static::find($where);
//		if (count($rs) > 0) {
//			return current($rs);
//		}
//		return NULL;
//	}
//
//	/**
//	 * @param string $where
//	 * @param string $order
//	 * @param string $indexBy {columnname}
//	 * @param string $indexByType
//	 * @return static[]
//	 */
////  public static function find($where = TRUE, $order = NULL,$indexBy = NULL, $indexByType = NULL) {
//	public static function find($where = TRUE, $order = NULL, $limit = 0, $indexBy = NULL, $indexByType = NULL) {
//		$class = get_called_class();
//
//		/* @var $o \LT\Model */
//		$o	 = new $class();
//		$pk	 = $o->primaryKey();
//		if (is_array($where) && isset($where['__PK__'])) {
//			$where[$pk] = $where['__PK__'];
//			unset($where['__PK__']);
//		}
//		$op = array();
//
//		/* Limit */
//		if (FALSE !== ($rs = $o->db()->select($o->table(), $where, '*', $order, $limit))) {
//			foreach ($rs as $r) {
//				$r['_load'] = TRUE;
//				if ($indexBy === NULL) {
//					$op[] = new $class($r);
//				} else {
//					$index = preg_replace_callback('/{(\w+)}/', function($matches) use ($r) {
//						if (isset($r[$matches[1]])) {
//							return $r[$matches[1]];
//						} else {
//							exit("Error Column Name.");
////                        return $matches[1];
//						}
//					}, $indexBy);
//					if ($indexByType === 'array') {
//						$op[$index][] = new $class($r);
//					} else {
//						$op[$index] = new $class($r);
//					}
//				}
//			}
//		}
//
//		return $op;
//	}
//
//	/**
//	 * @param int $id
//	 * @return static
//	 */
//	public static function findByID($id, $order = NULL, $indexBy = NULL, $indexByType = NULL) {
//		$rs = static::find(array('__PK__' => $id), $order, $indexBy, $indexByType);
//		return current($rs);
//	}
//
//}
