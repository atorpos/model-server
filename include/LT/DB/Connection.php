<?php

namespace LT\DB;

defined('LT') or die('Access Denied');

abstract class Connection extends \PDO {

	protected $_configs		 = array();
	protected $_sql			 = '';
	protected $_profile		 = array(
		'execute_count'	 => 0,
		'query_count'	 => 0,
		'details'		 => array(),
	);
	protected $_profiling	 = FALSE;

	public function __construct($params) {
		$dns = strtolower($params['type']) . ':host=' . $params['host'];
		if ($params['schema']) {
			$dns .= ';dbname=' . $params['schema'];
		}

		try {
			parent::__construct($dns, $params['user'], $params['password'], $params['options']);
		} catch (PDOException $e) {
			\LT::error('unable to connect to database server. ' . $e->getMessage());
		}

		unset($params['user'], $params['password']);
		$this->_configs = $params;
	}

	/**
	 * Get database type
	 * 
	 * @return string
	 */
	public function type() {
		return strtolower($this->_configs['type']);
	}

	/**
	 * Get table name prefix
	 * 
	 * @return string
	 */
	public function prefix() {
		return $this->_configs['prefix'];
	}

	/**
	 * Get table name with prefix
	 * 
	 * @param string $name table name without prefix
	 * @return string
	 */
	public function table($name) {
		return $this->_configs['prefix'] . $name;
	}

	/**
	 * Insert single row into data table
	 */
	abstract public function insert($table, $data, $opts = array());

	/**
	 * Insert dataset into data table
	 */
	public function inserts($table, $data, $opts = array()) {
		foreach ($data as $row) {
			$this->insert($table, $row, $opts);
		}
	}

	/**
	 * Update records by criteria
	 */
	abstract public function update($table, $data, $where, $opts = array());

	/**
	 * Delete records by criteria
	 */
	abstract public function delete($table, $where, $opts = array());

	abstract public function drop($table);

	/**
	 * (PHP 5 &gt;= 5.1.0, PECL pdo &gt;= 0.1.0)<br/>
	 * Execute an SQL statement and return the number of affected rows
	 * @link http://php.net/manual/en/pdo.exec.php
	 * @param string $sql <p>
	 * The SQL statement to prepare and execute.
	 * </p>
	 * <p>
	 * Data inside the query should be properly escaped.
	 * </p>
	 * @return int <b>PDO::exec</b> returns the number of rows that were modified
	 * or deleted by the SQL statement you issued. If no rows were affected,
	 * <b>PDO::exec</b> returns 0.
	 * </p>
	 * This function may
	 * return Boolean <b>FALSE</b>, but may also return a non-Boolean value which
	 * evaluates to <b>FALSE</b>. Please read the section on Booleans for more
	 * information. Use the ===
	 * operator for testing the return value of this
	 * function.
	 * <p>
	 * The following example incorrectly relies on the return value of
	 * <b>PDO::exec</b>, wherein a statement that affected 0 rows
	 * results in a call to <b>die</b>:
	 * <code>
	 * $db->exec() or die(print_r($db->errorInfo(), true));
	 * </code>
	 */
	public function execute($sql) {
		$this->_sql					 = $sql;
		if ($this->_profiling === true) {
                    $p							 = array(
                            'method'	 => 'Execute',
                            'statement'	 => $sql,
                    );
                    $p['start_time']			 = microtime(TRUE);
                }
                $rs							 = $this->exec($sql);
		
                if ($this->_profiling === true) {
                    $p['end_time']				 = microtime(TRUE);
                    $p['duration']				 = $p['end_time'] - $p['start_time'];
                    $this->_profile['details'][] = $p;
                    $this->_profile['execute_count'] ++;
                }
                
		return $rs;
	}

	/**
	 * (PHP 5 &gt;= 5.1.0, PECL pdo &gt;= 0.2.0)<br/>
	 * Executes an SQL statement, returning a result set as a PDOStatement object
	 * @link http://php.net/manual/en/pdo.query.php
	 * @param string $statement <p>
	 * The SQL statement to prepare and execute.
	 * </p>
	 * <p>
	 * Data inside the query should be properly escaped.
	 * </p>
	 * @return PDOStatement <b>\PDO::query</b> returns a PDOStatement object, or <b>FALSE</b>
	 * on failure.
	 */
	public function query($sql, $mode = \PDO::FETCH_ASSOC) {
		$this->_sql					 = $sql;
		if ($this->_profiling === true) {
                    $p							 = array(
                            'method'	 => 'Query',
                            'statement'	 => $sql,
                    );
                    $p['start_time']			 = microtime(TRUE);
                }
                $rs							 = parent::query($sql, $mode);
                if ($this->_profiling === true) {
                    $p['end_time']				 = microtime(TRUE);
                    $p['duration']				 = $p['end_time'] - $p['start_time'];
                    $this->_profile['details'][] = $p;
                    $this->_profile['query_count'] ++;
                }
		if (FALSE === $rs) {
			\LT\Exception::critical('query error',$sql);
//			exit('SQL Error: ' . $sql);
		}
		return $rs;
	}

	/**
	 * 
	 * @return \LT\DB\SelectBuilder
	 */
	public static function select() {
		return DB\SelectBuilder::factory();
	}

	/**
	 * 
	 * @return \LT\DB\Base\Query
	 */
	public function queryBuilder($params) {
		return \LT\DB\QueryBuilder::factory($this, $params);
	}

	/**
	 * Execute insert sql and return last insert id
	 * 
	 * @param string $sql
	 * @return int|false
	 */
	public function executeInsert($sql) {
		return $this->execute($sql) ? $this->lastInsertId() : FALSE;
	}

	/**
	 * Execute update sql and return affected rows
	 * 
	 * @param string $sql
	 * @return int
	 */
	public function executeUpdate($sql) {
		return $this->execute($sql);
	}

	/**
	 * Execute delete sql and return affected rows
	 * 
	 * @param string $sql
	 * @return int
	 */
	public function executeDelete($sql) {
		return $this->execute($sql);
	}

	/**
	 * Get last inserted id
	 * 
	 * @return int|false
	 */
	public function lastID() {
		return $this->lastInsertId();
	}

	/**
	 * Get last executed sql in current connection
	 * 
	 * @return string
	 */
	public function lastSQL() {
		return $this->_sql;
	}

	/**
	 * Query and return only a value
	 * 
	 * @param string $sql
	 * @return boolean
	 */
	public function getOne($sql) {
		if (FALSE !== ($rs = $this->query($sql))) {
			return $rs->fetch(\PDO::FETCH_COLUMN);
		}
		return FALSE;
	}

	/**
	 * Query and return only a single column from the result set.
	 * 
	 * @param string $sql
	 * @return boolean
	 */
	public function getCol($sql) {
		if (FALSE !== ($rs = $this->query($sql))) {
			return $rs->fetchAll(\PDO::FETCH_COLUMN);
		}
		return FALSE;
	}

	/**
	 * Query and return a row
	 * 
	 * @param string $sql
	 * @return boolean
	 */
	public function getRow($sql) {
		if (FALSE !== ($rs = $this->query($sql))) {
			return $rs->fetch(\PDO::FETCH_ASSOC);
		}
		return FALSE;
	}

	/**
	 * Query and return each row as an array indexed
	 * 
	 * @param string $sql
	 * @return boolean
	 */
	public function getArray($sql) {
		if (FALSE !== ($rs = $this->query($sql))) {
			return $rs->fetchAll(\PDO::FETCH_ASSOC);
		}
		return FALSE;
	}

	/**
	 * Get profiling result
	 * 
	 * @param bool $raw
	 * @return array
	 */
	public function profile($raw = FALSE) {
		if ($raw) {
			return $this->_profile;
		}
		$rs				 = $this->_profile;
		$rs['details']	 = array();
		foreach ($this->_profile['details'] as $r) {
			list($sec, $usec) = explode('.', round($r['start_time'] - ((int) $r['start_time']), 6), 2);
			$r['start_time'] = date('H:i:s.', (int) $r['start_time']) . $usec;
			list($sec, $usec) = explode('.', round($r['end_time'] - ((int) $r['end_time']), 6), 2);
			$r['end_time']	 = date('H:i:s.', (int) $r['end_time']) . $usec;
			$r['duration']	 = round(($r['duration'] * 1000), 6) . 'ms';

			$rs['details'][] = $r;
		}
		return $rs;
	}

	/**
	 * Enable/disable performance profiling
	 * 
	 * @param bool $enable
	 */
	public function profiling($enable = TRUE) {
		$this->_profiling = $enable;
	}

	public function __toString() {
		return __CLASS__;
	}

}
