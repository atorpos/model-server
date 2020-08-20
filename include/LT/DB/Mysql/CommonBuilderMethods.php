<?php

namespace LT\DB\Mysql;

trait CommonBuilderMethods {

    protected function _order($orders) {
        $rs = array();
        foreach ($orders as $r) {
            if (is_array($r)) {
                $r = $r[0] . ' ' . ($r[1] ? 'DESC' : 'ASC');
            }
            $rs[] = $r;
        }
        return implode(', ', $rs);
    }

    protected function _where($conditions) {

        $where = '';

        if (is_array($conditions)) {
            $cs = array();
            foreach ($conditions as $_k => $_v) {

                // explode operator

                if (is_int($_k) || ctype_digit($_k)) {

                } elseif (ctype_alnum($_k)) {
                    $_o = '=';
                } else {
                    foreach (array('>=', '<=', '>', '<', '!=') as $_c) {
                        $_count = 0;
                        $_k     = str_replace($_c, ' ' . $_c, $_k, $_count);
                        if ($_count) {
                            break;
                        }
                    }
                    if (!strpos($_k, ' ')) {
                        \LT\Exception::general('invalid column name ' . $_k, ['name' => $_k]);
                    }
                    list($_k, $_o) = explode(' ', $_k, 2);
                    $_o = trim($_o);
                }
                $_k = trim($_k);

                if (is_null($_v)) {

                    $cs[] = "`$_k` IS NULL";
                } elseif (is_object($_v) && ($_v instanceof \LT\DB\Criteria)) {

                    $_a = $_v->args();
                    if (empty($_a)) {
                        $cs[] = $_v;
                    } else {
                        foreach ($_a as $_k2 => $_a2) {
                            $_a[$_k2] = $this->_conn->quote($_a2);
                        }
                        $cs[] = vsprintf($_v, $_a);
                    }
                } elseif (ctype_digit($_k)) {

                    $cs[] = $_v;
                } elseif (is_array($_v)) {
                    $tmp = [];
                    foreach ($_v as $_v2) {
                        if (is_object($_v2) && is_subclass_of($_v2, '\LT\Model')) {
                            /* @var $_v2 \LT\Model */
                            $_v2 = $_v2->key();
                        }
                        $tmp[] = $this->_conn->quote($_v2);
                    }
                    if (empty($tmp)) {
                        \LT\Exception::general('invalid criteria');
                    }
                    $_o = ($_o == '!=') ? ' NOT IN ' : ' IN ';

                    $cs[] = "`$_k` $_o (" . implode(', ', $tmp) . ')';
                } else {
                    $cs[] = "`$_k` $_o " . $this->_conn->quote($_v);
                }
            }
            $where = implode("\n  AND ", $cs);
        }

        if ($where == '') {
            return '1=1';
        }
        return $where;
    }

}