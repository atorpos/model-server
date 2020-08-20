<?php

namespace LT\DB\Mysql;

defined('LT') or die('Access Denied');

class Connection extends \LT\DB\Connection {

    public function __construct($params) {

        parent::__construct($params);

        if (isset($params['charset'])) {
            $this->execute('SET NAMES ' . $params['charset']);
        }
    }

    protected function _fields($fs) {
        if (is_string($fs)) {
            return $fs = trim($fs);
//            if ($fs == '*') {
//                return '*';
//            }
//            $fs = explode(',', $fs);
        }
        if (is_array($fs)) {
            foreach ($fs as $_k => $_f) {
                $_f = trim($_f);
                if ($_f == '*') {
                    return '*';
                }
                $fs[$_k] = '`' . $_f . '`';
            }
            return implode(',', array_unique($fs));
        }
        return '*';
    }

    protected function _where($c, $prefix = ' WHERE ') {

        if (is_int($c) || ctype_digit($c)) {
            $sql = 'id = ' . $c;
        } elseif (is_null($c) || ($c === TRUE)) {
            return '';
        } elseif (is_array($c)) {
            $rs = array();
            foreach ($c as $k => $v) {
                $not = FALSE;
                if ($k[0] == '!') {
                    $not = TRUE;
                    $k   = substr($k, 1);
                }
                if ($k[0] == '%') {         // like
                    $k = substr($k, 1);
                    $s = ($not ? 'NOT ' : '') . 'LIKE ' . $this->quote($v);
                } elseif ($k[0] == '>') {         // >
                    $k = substr($k, 1);
                    $s = ($not ? '<= ' : '> ') . $this->quote($v);
                } elseif ($k[0] == '<') {         // <
                    $k = substr($k, 1);
                    $s = ($not ? '>= ' : '< ') . $this->quote($v);
                } elseif ($k[0] == '~') {   // between
                    $k = substr($k, 1);
                    $s = ($not ? 'NOT ' : '') . 'BETWEEN ' . $this->quote($v[0]) . ' AND ' . $this->quote($v[1]);
                } elseif (is_array($v)) {   // in array
                    foreach ($v as &$_t) {
                        $_t = $this->quote($_t);
                    }
                    $s = ($not ? 'NOT ' : '') . 'IN (' . implode(',', $v) . ')';
                } elseif (is_null($v)) {    // null value
                    $s = 'IS ' . ($not ? 'NOT ' : '') . 'NULL';
                } else {
//                    if (!is_int($v) && !ctype_digit($v)) { // not integer
//                        $v = $this->quote($v);
//                    }
                    $s = ($not ? '!' : '') . '= ' . $this->quote($v);
                }
                $rs[] = "`$k` " . $s;
            }
            $sql = implode(' AND ', $rs);
        } elseif (is_string($c)) {
            $sql = $c;
        } else {
            $sql = '0';
        }
        return $prefix . $sql;
    }

    protected function _order($list, $prefix = ' ORDER BY ') {
        if (is_null($list) || empty($list)) {
            return '';
        } elseif (is_string($list)) {
            if (strpos($list, ' ')) {
                $s = explode(' ', $list, 2);
            } else {
                $s = array($list, 'ASC');
            }
            $sql = (FALSE === strpos($s[0], '`') ? "`$s[0]`" : $s[0]) . ' ' . $s[1];
        } elseif (is_array($list)) {
            $rs = array();
            foreach ($list as $k => $v) {
                $rs[] = "`$k` " . ($v ? 'DESC' : 'ASC');
            }
            $sql = implode(',', $rs);
        }
        return $prefix . $sql;
    }

    public function where($where) {
        return $this->_where($where, '');
    }

    public function data($data) {
        foreach ($data as $k => $v) {
            if (is_null($v)) {
                $data[$k] = 'NULL';
            } elseif (is_array($v)) {
                $data[$k] = $this->quote(implode(',', $v));
            } else {
                $data[$k] = $this->quote($v);
            }
        }
        return $data;
    }

    public function insert($table, $data, $opts = array()) {
        $data = $this->data($data);
        $sql  = (isset($opts['replace']) && $opts['replace'] ? 'REPLACE' : 'INSERT')
                . ' INTO `' . $this->table($table) . '` (`'
                . implode('`,`', array_keys($data)) . '`)VALUES('
                . implode(',', array_values($data)) . ')';
        return $this->executeInsert($sql);
    }

    public function update($table, $data, $where, $opts = array()) {
        $t = array();
        foreach ($this->data($data) as $k => $v) {
            $t[] = "`$k`=" . $v;
        }
        $sql = 'UPDATE `' . $this->table($table)
                . '` SET ' . implode(',', $t) . $this->_where($where);
        if (isset($opts['limit'])) {
            $sql .= ' LIMIT ' . $opts['limit'];
        } elseif (is_int($where) || ctype_digit($where)) {
            $sql .= ' LIMIT 1';
        }
        return $this->executeUpdate($sql);
    }

    public function delete($table, $where, $opts = array()) {
        $sql = 'DELETE FROM `' . $this->table($table) . '` ' . $this->_where($where);
        if (isset($opts['limit']) && $opts['limit']) {
            $sql .= ' LIMIT ' . $opts['limit'];
        }
        return $this->executeDelete($sql);
    }

    public function drop($table) {
        return $this->execute("DROP TABLE IF EXISTS `$table`");
    }
}
