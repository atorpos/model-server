<?php

namespace LT\DB;

abstract class SQLBuilder {

	protected $_options = array(
		'fields' => '*',
		'where'	 => array(),
		'order'	 => array(),
	);

	/**
	 * @var \LT\DB\Connection
	 */
	protected $_conn;


	public function __construct($conn = NULL) {
		if (is_null($conn)) {
			$conn = \LT\DB::conn();
		}
		$this->_conn = $conn;
	}


	/**
	 * 
	 * @param string $table
	 * @return static
	 */
	public function model($table) {
		$this->_options['model'] = $table;
		return $this;
	}

	/**
	 * 
	 * @param string $table
	 * @return static
	 */
	public function from($table) {
		$this->_options['table'] = $table;
		return $this;
	}

	/**
	 * 
	 * @param string $column
	 * @param string $value
	 * @return static
	 */
    public function where($column, $value = NULL, $operator = '=') {
        if (is_string($column)) {
            if (strpos($column, ' ')) {
                list($column, $operator) = explode(' ', $column, 2);
            }
            $operator = strtolower($operator);
            if ($operator == '=') {
                $column = $column . ' ' . $operator;
            } elseif (in_array($operator, array('>', '<', '>=', '<=', '!=', 'like', 'between'))) {
                $column = $column . ' ' . $operator;
            } else {
                \LT\Exception::general('unknown operator ', ['operator' => $operator]);
            }
            $this->_options['where'][$column] = $value;
        } elseif (is_array($column)) {
            foreach ($column as $_key => $value) {
                $this->where($_key, $value);
            }
        } elseif (is_null($column)) {
            $this->_options['where'][] = new \LT\DB\Criteria($value);
        }
        return $this;
    }


	/**
	 * 
	 * @param string $columns
	 * @return static
	 */
	public function orderBy($column, $desc = NULL) {
		if (is_array($column)) {
			foreach ($column as $r) {
				$this->_options['order'][] = $r;
			}
		} elseif (!is_null($desc)) {
			$this->_options['order'][] = array($column, $desc);
		} else {
			$this->_options['order'][] = $column;
		}
		return $this;
	}

	/**
	 * 
	 * @param string $column
	 * @return static
	 */
	public function asc($column = 'sort_order') {
		return $this->orderBy($column, FALSE);
	}

	/**
	 * 
	 * @param string $column
	 * @return static
	 */
	public function desc($column = 'sort_order') {
		return $this->orderBy($column, TRUE);
	}
	
	/**
	 * 
	 * @param int $maxNumOfResults
	 * @return static
	 */
	public function limit($maxNumOfResults) {
		$this->_options['limit'] = $maxNumOfResults;
		return $this;
	}
	abstract public function toSQL();

	public function __toString() {
		return $this->toSQL();
	}

}
