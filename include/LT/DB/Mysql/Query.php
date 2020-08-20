<?php

namespace LT\DB\Mysql;

defined('LT') or die('Access Denied');

class Join {

    public $method;
    public $table;
    public $conditions;
    public $alias;

    public function __construct($method, $table, $conditions = NULL, $alias = NULL) {
        $this->method     = $method;
        $this->table      = $table;
        $this->conditions = $conditions;
        $this->alias      = $alias;
    }

}



class Query extends \LT\DB\QueryBuilder {

    protected $_fields = array('*');
    protected $_tables = array();

    /**
     * @var \LT\DB\Driver\Mysql\Join[]
     */
    protected $_joins = array();

    /**
     * @var \LT\DB\Driver\Mysql\Criteria[]
     */
    protected $_where = array();

    public function select() {
        if (func_get_args() > 0) {
            $this->_fields = func_get_args();
        } else {
            $this->_fields = array('*');
        }
        return $this;
    }

    public function insert($into) {
        $this->_type   = 'INSERT';
        $this->_tables = array($into);
        return $this;
    }

    public function replace($into) {
        $this->_type   = 'REPLACE';
        $this->_tables = array($into);
        return $this;
    }

    public function update($table) {
        $this->_type   = 'UPDATE';
        $this->_tables = array($table);
        return $this;
    }

    public function delete($from) {
        $this->_type   = 'DELETE';
        $this->_tables = array($from);
        return $this;
    }

    public function from($table, $otherTables = NULL) {
        if (is_array($otherTables) && !empty($otherTables)) {
            $this->_tables = array($table) + $otherTables;
        } else {
            $this->_tables = array($table);
        }
        return $this;
    }

    public function join($table, $conditions = NULL, $alias = NULL) {
        return $this->_join('JOIN', $table, $conditions, $alias);
    }

    public function innerJoin($table, $conditions = NULL, $alias = NULL) {
        return $this->_join('INNER JOIN', $table, $conditions, $alias);
    }

    public function leftJoin($table, $conditions = NULL, $alias = NULL) {
        return $this->_join('LEFT JOIN', $table, $conditions, $alias);
    }

    public function rightJoin($table, $conditions = NULL, $alias = NULL) {
        return $this->_join('RIGHT JOIN', $table, $conditions, $alias);
    }

    public function outerjoin($table, $conditions = NULL, $alias = NULL) {
        return $this->_join('OUTER JOIN', $table, $conditions, $alias);
    }

    public function leftOuterJoin($table, $conditions = NULL, $alias = NULL) {
        return $this->_join('LEFT OUTER JOIN', $table, $conditions, $alias);
    }

    public function rightOuterJoin($table, $conditions = NULL, $alias = NULL) {
        return $this->_join('RIGHT OUTER JOIN', $table, $conditions, $alias);
    }

    public function filter($conditions = NULL) {
        //preg_match('/^[a-zA-Z_]+[a-zA-Z0-9_]*$/', $c)
//        $c = $conditions;
//        if (is_int($c) || ctype_digit($c)) {
//            $this->_where[] = (new Criteria('id'))->equal($c);
//        } elseif (is_array($c)) {
//            foreach ($c as $f => $v) {
//                $this->_where[] = (new Criteria($f))->equal($v);
//            }
//        } elseif (is_string($c)) {
//            $this->_where[] = (string) $c;
//        }
        return $this;
    }

    public function exclude($conditions = NULL) {

        return $this;
    }

    public function where($field = NULL) {
        $c = new Criteria($field, $this);

        $this->_where[] = $c;
        return $c;
    }

    public function andWhere($field = NULL) {
        $this->_where[] = 'AND';
        return $this->where($field);
    }

    public function orWhere($field = NULL) {
        $this->_where[] = 'OR';
        return $this->where($field);
    }

    public function orderBy($field) {

        return $this;
    }

    public function sql() {

        $firstWhere = TRUE;
        
        if (!isset($this->_params['fields'])) {
            $this->_params['fields'] = $this->_fields;
        } elseif (is_string($this->_params['fields'])) {
            $this->_params['fields'] = [$this->_params['fields']];
        }

        $sql = 'SELECT ' . implode(',', $this->_params['fields']) . " \n";
        $sql.= 'FROM `' . $this->_from . "` \n";

        foreach ($this->_joins as $join) {
            $sql .= "\t$join->method $join->table $join->alias ON $join->conditions \n";
        }
		
		if (isset($this->_params['join'])) {
            $sql .= ' JOIN ' . $this->_params['join'];
        }
		
		if (isset($this->_params['leftJoin'])) {
            $sql .= ' LEFT JOIN ' . $this->_params['leftJoin'];
        }

        if (!empty($this->_where)) {
            $sql .= "WHERE \n";
        }
        foreach ($this->_where as $where) {
            if (is_string($where) && in_array($where, array('OR', 'AND'))) {
                $sql .= "\t" . $where;
            } else {
                $sql .= "\t" . (string) $where . " \n";
            }
        }

        if (isset($this->_params['where'])) {
            $where = $this->_params['where'];
            if (is_array($where)) {
                $cs = array();
                foreach ($where as $_k => $_v) {
                    if (is_null($_v)) {
                        $cs[] = "`$_k` IS NULL";
                    } elseif (is_object($_v) && ($_v instanceof \LT\DB\Criteria)) {
                        $_a = $_v->args();
                        foreach ($_a as $_k2 => $_a2) {
                            $_a[$_k2] = $this->_db->quote($_a2);
                        }
                        $cs[] = vsprintf($_v, $_a);
                    } elseif (ctype_digit($_k)) {
                        $cs[] = $_v;
                    } elseif (is_array($_v)) {
                        //TODO IN
                        $tmp = [];
                        foreach ($_v as $_v2) {
                            if (is_object($_v2) && is_subclass_of($_v2, '\LT\Model')) {
                                /* @var $_v2 \LT\Model */
                                $_v2 = $_v2->key();
                            }
                            $tmp[] = $this->_db->quote($_v2);
                        }
                        if (empty($tmp)) {
                            throw new \Exception('invalid criteria');
                        }
                        $cs[] = "`$_k` IN (" . implode(', ', $tmp) . ')';
                    } else {
                        $cs[] = "`$_k` = " . $this->_db->quote($_v);
                    }
                }
                $where = implode(' AND ', $cs);
            }
            $sql .= ' WHERE ' . (empty($where) ? ' 1=1 ' : $where);
        }
        
        if (isset($this->_params['group'])) {
            $sql .= ' GROUP BY ' . $this->_params['group'];
        }
        
        if (isset($this->_params['order'])) {
            $sql .= ' ORDER BY ' . $this->_params['order'];
        }

        if (isset($this->_params['limit'])) {
            $sql .= ' LIMIT ' . $this->_params['limit'];
        }
        return $sql;
    }

    protected function _join($method, $table, $conditions = NULL, $alias = NULL) {
        $join = new Join($method, $table, $conditions, $alias);

        $this->_joins[] = $join;
        return $this;
    }

}
