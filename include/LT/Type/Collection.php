<?php

namespace LT\Type;

class Collection {

	/**
	 * Convert the list to nested list by parent id
	 * 
	 * Input: [
	 * ['id' => 1, 'parent_id' => 0],
	 * ['id' => 2, 'parent_id' => 0],
	 * ['id' => 3, 'parent_id' => 1],
	 * ['id' => 4, 'parent_id' => 1],
	 * ]
	 * 
	 * Output: [
	 * ['id' => 1, 'parent_id' => 0, '_childs' => [
	 *     ['id' => 3, 'parent_id' => 1],
	 *     ['id' => 4, 'parent_id' => 1]
	 * ]],
	 * ['id' => 2, 'parent_id' => 0],
	 * ]
	 * 
	 * @param array $rs records
	 * @param string $parentKey the key stored parent node id
	 * @param string $recordKey the key stored node id
	 * @param string $childsKey the key will be stored the child nodes
	 * @return array
	 */
	public static function tree(array $rs, $parentKey = 'parent_id', $recordKey = 'id', $childsKey = '_childs') {
		$refs	 = array();
		$list	 = array();
		foreach ($rs as $row) {
			// get address reference
			$ref = &$refs[$row[$recordKey]];
			// clone values
			foreach ($row as $k => $v) {
				$ref[$k] = $v;
			}
			// linking
			if ($row[$parentKey] == 0) {
				$list[$row[$recordKey]] = &$ref;
			} else {
				$refs[$row[$parentKey]][$childsKey][$row[$recordKey]] = &$ref;
			}
		}
		return $list;
	}

	/**
	 * 
	 * @param array $rs
	 * @param type $excludes
	 * @param type $indent
	 * @param type $labelKey
	 * @param type $recordKey
	 * @param type $childsKey
	 * @return string
	 */
	public static function flattenTree(array $rs, $indent = '    ', $labelKey = 'name', $recordKey = 'id', $childsKey = '_childs') {
		$o = array();
		foreach ($rs as $r) {
//			if (in_array($k, $excludes)) {
//				continue;
//			}
			$o[$r[$recordKey]] = $r;
			if (isset($r[$childsKey])) {
				$rs2 = self::flattenTree($r[$childsKey], $indent, $labelKey, $recordKey);
				foreach ($rs2 as $k2 => $r2) {

					$r2[$labelKey] = $indent . $r2[$labelKey];

					$o[$k2] = $r2;
				}
			}
		}
		return $o;
	}

	/**
	 * Get key value pairs from 2d array by given element key
	 * 
	 * @param array $rs
	 * @param string $valueField
	 * @param string|null $keyField
	 * @return array
	 */
	public static function keyValue(array $rs, $valueField = 'name', $keyField = NULL) {
		$o = array();
		foreach ($rs as $_k => $_r) {
			if (is_null($keyField)) {
				$o[$_k] = $_r[$valueField];
			} else {
				$o[$_r[$keyField]] = $_r[$valueField];
			}
		}
		return $o;
	}

	public static function treeDropdown(array $rs, $indent = '    ', $labelKey = 'name', $parentKey = 'parent_id', $recordKey = 'id') {
		$tree	 = self::tree($rs, $parentKey, $recordKey);
		$list	 = self::flattenTree($tree, $indent, $labelKey, $recordKey);
		return self::keyValue($list, $labelKey, $recordKey);
	}

	/**
	 * Get the set of all ordered pairs from multiple sets
	 * A = {x,y}; B = {1,2}
	 * A × B = {x,y} × {1,2} = {(x,1), (x,2), (y,1), (y,2)}
	 * 
	 * @link http://en.wikipedia.org/wiki/Cartesian_product Wiki about Cartesian product
	 * @param array $arrays 2D array set
	 * @return array
	 */
	public static function cartesianProduct(array $arrays) {
		$result	 = array();
		$arrays	 = array_values($arrays);
		$sizeIn	 = sizeof($arrays);
		$size	 = $sizeIn > 0 ? 1 : 0;
		foreach ($arrays as $array) {
			$size = $size * sizeof($array);
		}
		for ($i = 0; $i < $size; $i ++) {
			$result[$i] = array();
			for ($j = 0; $j < $sizeIn; $j ++) {
				array_push($result[$i], current($arrays[$j]));
			}
			for ($j = ($sizeIn - 1); $j >= 0; $j --) {
				if (next($arrays[$j])) {
					break;
				} elseif (isset($arrays[$j])) {
					reset($arrays[$j]);
				}
			}
		}
		return $result;
	}

}
