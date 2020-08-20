<?php

namespace LT\DB;

abstract class DeleteBuilder extends SQLBuilder {

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
		$class	 = "\\LT\\DB\\$type\\DeleteBuilder";
		if (!class_exists($class)) {
			\LT\Exception::general('unknown class ' . $class);
		}
		$builder = new $class($conn);
		return $builder;
	}

	/**
	 * Execute delete statement
	 * 
	 * @return int returns the number of rows that were modified
	 * or deleted by the SQL statement you issued.
	 * If no rows were affected, returns 0.
	 */
	public function execute() {
		$rs = $this->_conn->executeDelete($this);
		return $rs;
	}

}
