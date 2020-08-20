<?php

namespace LT\DB\Mysql;

class SelectBuilder extends \LT\DB\SelectBuilder {

	use CommonBuilderMethods;

	/**
	 * 
	 * @return string
	 * @throws \Exception
	 */
	public function toSQL() {

//SELECT
//[ALL | DISTINCT | DISTINCTROW ]
//  [HIGH_PRIORITY]
//  [MAX_STATEMENT_TIME = N]
//  [STRAIGHT_JOIN]
//  [SQL_SMALL_RESULT] [SQL_BIG_RESULT] [SQL_BUFFER_RESULT]
//  [SQL_CACHE | SQL_NO_CACHE] [SQL_CALC_FOUND_ROWS]
//select_expr [, select_expr ...]
//[FROM table_references
//  [PARTITION partition_list]
//[WHERE where_condition]
//[GROUP BY {col_name | expr | position}
//  [ASC | DESC], ... [WITH ROLLUP]]
//[HAVING where_condition]
//[ORDER BY {col_name | expr | position}
//  [ASC | DESC], ...]
//[LIMIT {[offset,] row_count | row_count OFFSET offset}]
//[PROCEDURE procedure_name(argument_list)]
//[INTO OUTFILE 'file_name'
//    [CHARACTER SET charset_name]
//    export_options
//  | INTO DUMPFILE 'file_name'
//  | INTO var_name [, var_name]]
//[FOR UPDATE | LOCK IN SHARE MODE]]

		$sql = 'SELECT ' . $this->_options['fields'] . " \n";
		$sql .= 'FROM ' . $this->_options['table'] . " \n";

		if (isset($this->_options['where'])) {
			$sql .= 'WHERE ' . $this->_where($this->_options['where']) . " \n";
		}

		if (isset($this->_options['group'])) {
			$sql .= 'GROUP BY ' . $this->_options['group'] . " \n";
		}

		if (!empty($this->_options['order'])) {
			$sql .= 'ORDER BY ' . $this->_order($this->_options['order']) . " \n";
		}

		if (isset($this->_options['limit'])) {
			$sql .= 'LIMIT ';
			if (isset($this->_options['offset'])) {
				$sql .= $this->_options['offset'] . ',';
			}
			$sql .= $this->_options['limit'];
		}

		return $sql;



//        if (!isset($this->_params['fields'])) {
//            $this->_params['fields'] = $this->_fields;
//        } elseif (is_string($this->_params['fields'])) {
//            $this->_params['fields'] = [$this->_params['fields']];
//        }
//
//        $sql = 'SELECT ' . implode(',', $this->_params['fields']) . " \n";
//        $sql .= 'FROM `' . $this->_from . "` \n";
//
//        foreach ($this->_joins as $join) {
//            $sql .= "\t$join->method $join->table $join->alias ON $join->conditions \n";
//        }
//
//        if (isset($this->_params['join'])) {
//            $sql .= ' JOIN ' . $this->_params['join'];
//        }
//
//        if (isset($this->_params['leftJoin'])) {
//            $sql .= ' LEFT JOIN ' . $this->_params['leftJoin'];
//        }
	}


}
