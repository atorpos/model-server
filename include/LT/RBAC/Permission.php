<?php

namespace LT\RBAC;

/**
 * @property-read string $key Permission Key (e.g. PAYMENT_TRANSACTION)
 */
class Permission extends \PA\Model {

	protected static $_table = 'rbac_permission';
	public $id;
	public $app_id;
	public $parent_id;
	public $type;
	public $name;
	public $label;
	public $description;
	public $sort_order;
	public $created_time;
	public $updated_time;

	protected static function _beforeQuery($opts) {
		parent::_beforeQuery($opts);

		$opts['where']['app_id'] = \LT\Config::value('rbac.app_id');

		return $opts;
	}

	protected function _beforeCreate() {
		parent::_beforeCreate();
		$t					 = time();
		$this->type			 = 1;
		$this->created_time	 = $t;
		$this->updated_time	 = $t;
		$this->sort_order	 = 1000;
	}

	protected function _beforeUpdate() {
		parent::_beforeUpdate();
		$this->updated_time = time();
	}

	protected function _beforeSave() {
		parent::_beforeSave();
		if (!is_null($this->name)) {
			$this->name = strtoupper($this->name);
		}
		if ($this->id == '1') {
			$this->parent_id = NULL;
		}
	}

	protected static function _getPermissionKey($parentID, &$permissions) {
		$rootID	 = static::getRootID();
		$key	 = '';
		if (isset($permissions[$parentID])) {
			if ($parentID == $rootID) {
				return $permissions[$parentID]['name'];
			}
			$key = $permissions[$parentID]['name'];
			if ($permissions[$parentID]['parent_id'] != $rootID) {
				$parentKeys = static::_getPermissionKey($permissions[$parentID]['parent_id'], $permissions);
				if ($parentKeys) {
					$key = $parentKeys . '_' . $key;
				}
			}
		}
		return $key;
	}

	public static function getRootID() {
		static $id = NULL;
		if (is_null($id)) {
			$rs = self::findOne(['parent_id' => 0]);
			if ($rs) {
				$id = $rs->id;
			} else {
				$id = 0;
			}
		}
		return $id;
	}

	public static function options() {
		$rs = static::all();
		return \LT\Type\Collection::treeDropdown($rs, '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', 'label');
	}

	/**
	 * 
	 * @return static
	 */
	public static function allWithKeys() {
		$rs = static::select()->asc()->get();
		foreach ($rs as &$_r) {
			$_r->key = static::_getPermissionKey($_r->id, $rs);
		}
		return $rs;
	}

}
