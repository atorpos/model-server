<?php

namespace LT\DB;

abstract class QueryBuilder {

	/**
	 * @var \LT\DB\Connection
	 */
	protected $_db;
	protected $_type	 = '';
	protected $_fields	 = array('*');
	protected $_from;
	protected $_joins	 = array();
	protected $_where	 = array();
	protected $_params	 = array();

	/**
	 * @param \LT\DB\Connection  $db
	 * @return \LT\DB\Query
	 */
	public static function factory($db, $params = array()) {
		$type	 = ucfirst($db->type());
		$class	 = "\\LT\\DB\\$type\\Query";
		if (class_exists($class)) {
			return new $class($db, $params);
		}
		exit('Error: unknown class ' . $class);
	}

	public function __construct($db, $params) {
		$this->_db		 = $db;
		$this->_params	 = $params;
		if (isset($params['from'])) {
			$this->_from = $params['from'];
		}
	}

	/**
	 * @return \LT\DB\Connection
	 */
	public function db() {
		return $this->_db;
	}

	/**
	 * @return \LT\DB\Base\Query
	 */
	abstract public function select();

	/**
	 * @return \LT\DB\Base\Query
	 */
	abstract public function insert($into);

	/**
	 * @return \LT\DB\Base\Query
	 */
	abstract public function replace($into);

	/**
	 * @return \LT\DB\Base\Query
	 */
	abstract public function update($table);

	/**
	 * @return \LT\DB\Base\Query
	 */
	abstract public function delete($from);

	/**
	 * @return \LT\DB\Base\Query
	 */
	public function from($table, $otherTables = NULL) {
		if (is_array($otherTables) && !empty($otherTables)) {
			$this->_tables = array($table) + $otherTables;
		} else {
			$this->_tables = array($table);
		}
		return $this;
	}

	/**
	 * @param string $table1
	 * @param string $method
	 * @param string $table2
	 * @return \LT\DB\Base\Query
	 */
	abstract protected function _join($table1, $method, $table2);

	/**
	 * @return \LT\DB\Base\Query
	 */
	abstract public function join($table, $conditions = NULL, $alias = NULL);

	/**
	 * @return \LT\DB\Base\Query
	 */
	abstract public function innerJoin($table, $conditions = NULL, $alias = NULL);

	/**
	 * @return \LT\DB\Base\Query
	 */
	abstract public function leftJoin($table, $conditions = NULL, $alias = NULL);

	/**
	 * @return \LT\DB\Base\Query
	 */
	abstract public function rightJoin($table, $conditions = NULL, $alias = NULL);

	/**
	 * @return \LT\DB\Base\Query
	 */
	abstract public function outerjoin($table, $conditions = NULL, $alias = NULL);

	/**
	 * @return \LT\DB\Base\Query
	 */
	abstract public function leftOuterJoin($table, $conditions = NULL, $alias = NULL);

	/**
	 * @return \LT\DB\Base\Query
	 */
	abstract public function rightOuterJoin($table, $conditions = NULL, $alias = NULL);

	/**
	 * @return \LT\DB\Base\Query
	 */
	abstract public function filter($conditions = NULL);

	/**
	 * @return \LT\DB\Base\Query
	 */
	abstract public function exclude($conditions = NULL);

	/**
	 * @return \LT\DB\Base\Criteria
	 */
	abstract public function where($field = NULL);

	/**
	 * @return \LT\DB\Base\Criteria
	 */
	abstract public function andWhere($field = NULL);

	/**
	 * @return \LT\DB\Base\Criteria
	 */
	abstract public function orWhere($field = NULL);

	/**
	 * @return string
	 */
	abstract public function sql();

	public function run() {
		if (isset($this->_params['no_key_field']) && $this->_params['no_key_field']) {
			unset($this->_params['key_field']);
		}

		$sql = $this->sql();
		\LT::debug($sql, TRUE);
		$rs	 = $this->db()->query($sql);
		if (FALSE === $rs) {
			\LT\Exception::core('SQL Error');
		}
//		$o = array();
		if (isset($this->_params['model'])) {
            
			$class = $this->_params['model'];
            
            return $class::load($rs, $this->_params);
            
//			if (isset($this->_params['key_field'])) {
//				$k = $this->_params['key_field'];
//				foreach ($rs as $r) {
//					$r['_load'] = TRUE;
//					if (isset($r[$k])) {
//						$o[$r[$k]] = new $class($r);
//					} else {
//						$o[] = new $class($r);
//					}
//				}
//			} else {
//				foreach ($rs as $r) {
//					$r['_load']	 = TRUE;
//					$o[]		 = new $class($r);
//				}
//			}
//			return $o;
		}
		return $rs;
	}

	public function debug($print = 'HTML') {
		$printHTML = ($print == 'HTML');

		$s = '';
		if ($printHTML) {
			$s = '<pre>';
		}
		$vars = get_object_vars($this);
		$s .= "Class: " . __CLASS__ . "\n";
		$s .= "Properties:";
		$s .= var_export($vars, TRUE);
		if ($printHTML) {
			$s .= '</pre>';
		}
		if ($print) {
			echo $s;
		}
		return $s;
	}

}
