<?php

namespace LT\RBAC;

class RolePermission extends \PA\Model {

	protected static $_table = 'rbac_role_permission';
	public $id;
	public $role_id;
	public $permission_id;
	public $level;
	public $created_time;
	public $updated_time;

	protected function _beforeCreate() {
		parent::_beforeCreate();
		$t					 = time();
		$this->created_time	 = $t;
		$this->updated_time	 = $t;
	}

	protected function _beforeUpdate() {
		parent::_beforeUpdate();
		$this->updated_time = time();
	}

	/**
	 * @param int $roleID
	 * @return static
	 */
	public static function findByRoleID($roleID) {
		return static::select()->where('role_id', $roleID)->get();
	}

	/**
	 * @param array $roleIDs
	 * @return static
	 */
	public static function findByRoleIDs(array $roleIDs) {
		return static::select()->where('role_id', $roleIDs)->get();
	}

	/**
	 * 
	 * 
	 * @param int $roleID
	 * @return array
	 */
	public static function getPermissionsLevelByRoleID($roleID) {
		return static::select()->where('role_id', $roleID)->getKeyValue('level', 'permission_id');
	}

}
