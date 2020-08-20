<?php

namespace LT\RBAC;

class UserRole extends \LT\Model {

	protected static $_table = 'rbac_user_role';
	public $id;
	public $user_id;
	public $role_id;
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
	 * 
	 * @param int $userID
	 * @return static
	 */
	public static function findByUserID($userID) {
		return static::findMatches('user_id', (int) $userID);
	}
	
	public static function getRoleIDsByUserID($userID) {
		return static::select()->where('user_id', $userID)->getCol('role_id');
	}
}
