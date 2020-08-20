<?php

namespace LT\DB\Mysql;

class DeleteBuilder extends \LT\DB\DeleteBuilder {

	use CommonBuilderMethods;

	public function toSQL() {
//DELETE [LOW_PRIORITY] [QUICK] [IGNORE] FROM tbl_name
//    [PARTITION (partition_name,...)]
//    [WHERE where_condition]
//    [ORDER BY ...]
//    [LIMIT row_count]

		$sql = 'DELETE ' . " \n";
		$sql .= 'FROM ' . $this->_options['table'] . " \n";

		if (isset($this->_options['where'])) {
			$sql .= 'WHERE ' . $this->_where($this->_options['where']) . " \n";
		}

		if (!empty($this->_options['order'])) {
			$sql .= 'ORDER BY ' . $this->_order($this->_options['order']) . " \n";
		}

		if (isset($this->_options['limit'])) {
			$sql .= 'LIMIT ' . $this->_options['limit'];
		}
		return $sql;
	}

}
