<?php

namespace LT\RBAC;

class Role extends \PA\Model {

	protected static $_table = 'rbac_role';
	public $id;
	public $app_id;
	public $merchant_id;
	public $parent_id		 = 0;
	public $name;
	public $description;
	public $default_home;
	public $created_time;
	public $updated_time;

	protected static function _beforeQuery($opts) {
		parent::_beforeQuery($opts);

//		$opts['where']['app_id'] = \LT\Config::value('rbac.app_id');
//		$user					 = \LT\RBAC\User::current();
//		if ($user) {
			/**
			 * disabled by stephen.yu 2019-09-09
			 */
//			$opts['where']['merchant_id'] = array($user->merchant_id, 0);
//			$opts['where']['merchant_id'] = $user->merchant_id;
//		}
		return $opts;
	}

	protected function _beforeCreate() {
		parent::_beforeCreate();
		$t					 = time();
		$this->created_time	 = $t;
		$this->updated_time	 = $t;
		$this->app_id		 = \LT\Config::value('rbac.app_id');

		if (!$this->default_home) {
			$this->default_home = '/';
		}

		$user = \LT\RBAC\User::current();
		if ($user) {
			$this->merchant_id = $user->merchant_id;
		}
	}

	protected function _beforeUpdate() {
		parent::_beforeUpdate();
		$this->updated_time = time();
	}
	
	protected function _afterDelete() {
		parent::_afterDelete();
		
		// delete related records
		RolePermission::quickDelete(['role_id' => $this->id]);
	}

	public static function dropdownOptions() {
		return static::select()->getKeyValue('name');
	}

	/**
	 * 
	 * @param int $userID
	 * @return static
	 */
	public static function findByUserID($userID) {
		$roleIDs = \LT\RBAC\UserRole::getRoleIDsByUserID($userID);
		if (empty($roleIDs)) {
			return array();
		}
		$rs = static::findMatches('id', $roleIDs);
		
		return $rs;
	}

	
	/**
	 * 
	 * @param string $name
	 * @return static
	 */
	public static function findByName($name,$app_id) {
		return static::findOne([
			    'name'	 => $name,
			    'app_id' => $app_id
		]);
	}
}
