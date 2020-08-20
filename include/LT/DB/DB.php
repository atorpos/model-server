<?php

namespace LT;

defined('LT') or die('Access Denied');

if (!class_exists('PDO')) {
    exit('Error: PHP PDO module not exist.');
}

abstract class DB {

    protected static $_conns = array();

    /**
     * Get database connection by name
     * 
     * @param string $name
     * @return \LT\DB\Connection
     */
    public static function conn($name = 'db.default') {
        if (!isset(self::$_conns[$name])) {
            $params = Config::value($name);
            $class  = '\LT\DB\\' . ucfirst(strtolower($params['type'])) . '\\Connection';
            if (class_exists($class)) {
                self::$_conns[$name] = new $class($params);
            } else {
                Exception::config('unknown connection type');
            }
        }
        return self::$_conns[$name];
    }

}

//abstract class DB_old extends \PDO {
//
//	protected static $_conns = array();
//	protected $_isOpen		 = FALSE;
//	protected $_cfg			 = array(
//		'type'		 => 'MySQL',
//		'charset'	 => 'utf8',
//		'host'		 => 'localhost',
//		'user'		 => 'root',
//		'pw'		 => '',
//		'schema'	 => '',
//		'prefix'	 => '',
//		'autoopen'	 => FALSE,
//		'options'	 => NULL,
//	);
//	protected $_sql			 = '';
//	protected $_helpers		 = array();
//
//	public function __construct($dsn, $username = '', $passwd = '', $options = NULL) {
//		if (is_array($dsn)) {
//			$this->config($dsn);
//		} else {
//			parent::__construct($dsn, trim($username), trim($passwd), $options);
//		}
//	}
//
//	/**
//	 * Get database type
//	 * 
//	 * @return string
//	 */
//	public function type() {
//		return strtolower($this->_cfg['type']);
//	}
//
//	public function helper($name) {
//		if (!isset($this->_helpers[$name])) {
//			$class = 'LT_DB_Helper_' . ucfirst(strtolower($name));
//			if (class_exists($class)) {
//				$this->_helpers[$name] = new $class($this);
//			} else {
//				LT::error('Unable to load library ' . $class);
//			}
//		}
//		return $this->_helpers[$name];
//	}
//
//	/**
//	 * @param array $config Connection Config
//	 * @return LT_DB
//	 */
//	public static function create(array $config = array()) {
//		$n = "LT_DB_{$config['type']}";
//		if (class_exists($n)) {
//			$obj = new $n($config);
//			$obj->config($config);
//			$obj->_autoOpen();
//			return $obj;
//		}
//		exit('Error: unsupported database type (' . $config['type'] . ').');
//	}
//
//	/**
//	 * @param string $name Connection Name
//	 * @return LT_DB
//	 */
//	public static function conn($name = NULL, $config = NULL) {
//		if (is_null($name)) {
//			$name = 0;
//		}
//		if (isset(self::$_conns[$name]) && is_object(self::$_conns[$name])) {
//			return self::$_conns[$name];
//		}
//		if (!is_null($config)) {
//			$config['name']		 = $name;
//			return self::$_conns[$name] = self::create($config);
//		}
//		return NULL;
//	}
//
//	/**
//	 * @param string $config Connection Config
//	 * @return LT_DB
//	 */
//	public static function shared(array $config = NULL) {
//		if (is_null($conn = self::conn())) {
//			if (is_null($config)) {
//				$config = LT::config('LT_DB');
//			}
//			$conn = self::conn(NULL, $config);
//		}
//		return $conn;
//	}
//
//	protected function config(array $config) {
//		$this->_cfg = array_merge($this->_cfg, $config);
//	}
//
//	/**
//	 * @return LT_DB
//	 */
//	abstract public function open();
//
//	protected function _autoOpen() {
//		if ($this->_cfg['autoopen']) {
//			$this->open();
//		}
//	}
//
//	public function close() {
//		if (isset($this->_cfg['name'])) {
//			if (($conn = self::conn($this->_cfg['name']))) {
//				unset(self::$_conns[$this->_cfg['name']]);
//			}
//		}
//	}
//
//	public function prefix() {
//		return $this->_cfg['prefix'];
//	}
//
//	public function table($name) {
//		return $this->_cfg['prefix'] . $name;
//	}
//
//	/**
//	 * (PHP 5 &gt;= 5.1.0, PECL pdo &gt;= 0.2.0)<br/>
//	 * Executes an SQL statement, returning a result set as a PDOStatement object
//	 * @link http://php.net/manual/en/pdo.query.php
//	 * @param string $statement <p>
//	 * The SQL statement to prepare and execute.
//	 * </p>
//	 * <p>
//	 * Data inside the query should be properly escaped.
//	 * </p>
//	 * @return PDOStatement <b>PDO::query</b> returns a PDOStatement object, or <b>FALSE</b>
//	 * on failure.
//	 */
//	public function query($sql, $mode = PDO::FETCH_ASSOC) {
//		$this->_sql = $sql;
//		return parent::query($sql, $mode);
//	}
//
//	/**
//	 * (PHP 5 &gt;= 5.1.0, PECL pdo &gt;= 0.1.0)<br/>
//	 * Execute an SQL statement and return the number of affected rows
//	 * @link http://php.net/manual/en/pdo.exec.php
//	 * @param string $sql <p>
//	 * The SQL statement to prepare and execute.
//	 * </p>
//	 * <p>
//	 * Data inside the query should be properly escaped.
//	 * </p>
//	 * @return int <b>PDO::exec</b> returns the number of rows that were modified
//	 * or deleted by the SQL statement you issued. If no rows were affected,
//	 * <b>PDO::exec</b> returns 0.
//	 * </p>
//	 * This function may
//	 * return Boolean <b>FALSE</b>, but may also return a non-Boolean value which
//	 * evaluates to <b>FALSE</b>. Please read the section on Booleans for more
//	 * information. Use the ===
//	 * operator for testing the return value of this
//	 * function.
//	 * <p>
//	 * The following example incorrectly relies on the return value of
//	 * <b>PDO::exec</b>, wherein a statement that affected 0 rows
//	 * results in a call to <b>die</b>:
//	 * <code>
//	 * $db->exec() or die(print_r($db->errorInfo(), true));
//	 * </code>
//	 */
//	public function execute($sql) {
//		$this->_sql = $sql;
//		return $this->exec($sql);
//	}
//
//	public function executeInsert($sql) {
//		return $this->execute($sql) ? $this->lastInsertId() : FALSE;
//	}
//
//	public function executeUpdate($sql) {
//		return $this->execute($sql);
//	}
//
//	public function executeDelete($sql) {
//		return $this->execute($sql);
//	}
//
//	public function getInsertID() {
//		return $this->lastInsertId();
//	}
//
//	public function getLastSQL() {
//		return $this->_sql;
//	}
//
//	public function getOne($sql) {
//		if (FALSE !== ($rs = $this->query($sql))) {
//			return $rs->fetch(PDO::FETCH_COLUMN);
//		}
//		return FALSE;
//	}
//
//	public function getCol($sql) {
//		if (FALSE !== ($rs = $this->query($sql))) {
//			return $rs->fetchAll(PDO::FETCH_COLUMN);
//		}
//		return FALSE;
//	}
//
//	public function getRow($sql) {
//		if (FALSE !== ($rs = $this->query($sql))) {
//			return $rs->fetch(PDO::FETCH_ASSOC);
//		}
//		return FALSE;
//	}
//
//	public function getArray($sql, $keyField = FALSE, $valueField = FALSE) {
//		if (FALSE === ($rs = $this->query($sql))) {
//			return FALSE;
//		}
//		if (!($k = $keyField)) {
//			return $rs->fetchAll(PDO::FETCH_ASSOC);
//		}
//		$o	 = array();
//		if (FALSE === ($v	 = $valueField)) {
//			foreach ($rs as $r) {
//				$o[$r[$k]] = $r;
//			}
//		} else {
//			foreach ($rs as $r) {
//				$a	 = $r[$k];
//				$b	 = $r[$v];
//
//				if (isset($o[$a])) {
//					if (!is_array($o[$a])) {
//						$o[$a] = array($o[$a]);
//					}
//					$o[$a][] = $b;
//				} else {
//					$o[$a] = $b;
//				}
//			}
//		}
//		return $o;
//	}
//
//	abstract public function where($where);
//
//	abstract public function data($v);
//
//	abstract public function insert($table, $data, $opts = array());
//
//	public function inserts($table, $data2D, array $common = array(), $opts = array()) {
//		$rs	 = array();
//		$c	 = is_array($common) && !empty($common);
//		foreach ($data2D as $data) {
//			if ($c) {
//				$data = array_merge($common, $data);
//			}
//			$rs[] = $this->insert($table, $data, $opts);
//		}
//		return $rs;
//	}
//
//	public function replace($table, $data, $opts = array()) {
//		$opts['replace'] = TRUE;
//		return $this->insert($table, $data, $opts);
//	}
//
//	public function replaces($table, $data2D, array $common = array(), $opts = array()) {
//		$opts['replace'] = TRUE;
//		return $this->inserts($table, $data2D, $common, $opts);
//	}
//
//	abstract public function update($table, $data, $where, $opts = array());
//
//	abstract public function delete($table, $where, $opts = array());
//
//	public function deleteOne($table, $where) {
//		$opts['limit'] = 1;
//		return $this->delete($table, $where, $opts);
//	}
//
//	abstract public function select($table, $where = TRUE, $fields = '*', $order = NULL, $limit = 0, array $opts = array());
//
//	public function selectCount($table, $where = TRUE, $countField = '*') {
//		return $this->selectOne($table, $where, 'COUNT(' . $countField . ')');
//	}
//
//	public function selectMax($table, $where = TRUE, $countField = 'id') {
//		return $this->selectOne($table, $where, 'MAX(' . $countField . ')');
//	}
//
//	public function selectMin($table, $where = TRUE, $countField = 'id') {
//		return $this->selectOne($table, $where, 'MIN(' . $countField . ')');
//	}
//
//	public function selectOne($table, $where, $field = 'id', $order = NULL, $offset = 0) {
//		if (($row = $this->selectRow($table, $where, $field, $order, $offset))) {
//			return current($row);
//		}
//		return NULL;
//	}
//
//	public function selectCol($table, $where = TRUE, $field = 'id', $order = NULL, $limit = 0, array $opts = array()) {
//		$sql = $this->selectSQL($table, $where, $field, $order, $limit, $opts);
//		return $this->getCol($sql);
//	}
//
//	public function selectRow($table, $where = TRUE, $fields = '*', $order = NULL, $offset = 0, array $opts = array()) {
//		$rs = $this->select($table, $where, $fields, $order, array($offset, 1), $opts);
//		if ($rs === FALSE) {
//			return FALSE;
//		}
//		if (count($rs) == 1) {
//			return $rs[0];
//		}
//		return array();
//	}
//
//	public function selectMap($table, $where = TRUE, $order = NULL, $keyField = 'id', $fields = '*', $limit = NULL, array $opts = array()) {
//		return $this->select($table, $where, array('[KEY]' => $keyField, '[DATA]' => $fields), $order, $limit, $opts);
//	}
//
//	abstract public function copy($from, $to, $where, $fields = '*');
//
//	public function move($from, $to, $where, $fields = '*') {
//		if (($count = $this->copy($from, $to, $where, $fields))) {
//			$this->delete($from, $where);
//		}
//		return $count;
//	}
//
//	public function isDuplicate() {
//		return $this->errorCode() == 1062;
//	}
//
//	/**
//	 * 
//	 * @return \LT\DB\Base\Query
//	 */
//	public function queryBuilder($table) {
//		return \LT\DB\Base\Query::create($this)->from($table);
//	}
//
//	public function __toString() {
//		return __CLASS__;
//	}
//
//}
