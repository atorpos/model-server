<?php

namespace LT\RBAC;

class User extends \PA\Model {

	protected static $_table = 'rbac_user';

	/**
	 *
	 * @var static 
	 */
	protected static $_current = NULL;
//	public $id;
//	public $app_id;
//	public $merchant_id;
//	public $name;
//	public $email;
//	public $mobile_prefix;
//	public $mobile;
//	public $country;
//	public $lang;
//	public $username;
//	public $password;
//	public $salt;
//	public $token;
//	public $cidr;
//	public $status;
//	public $email_activated;
//	public $last_login_ip;
//	public $last_login_time;
//	public $verified_time;
//	public $created_time;
//	public $updated_time;
	    
        public $id;
	public $merchant_id;
	public $username;
	public $password;
	public $salt;
	public $name;
	public $token;
	public $is_developer;
	public $verified_time;
	public $enabled = '1';
	public $expiry_time;
	public $app_id;
	public $created_time;
	public $updated_time;

	/**
	 * Get current user
	 * @return static
	 */
	public static function current() {
		return static::$_current;
	}

	/**
	 * Set current user
	 * @param static $user
	 */
	public static function setCurrentUser($user) {
		static::$_current = $user;
	}

	public static function logout() {
		\LT\Session::destroy();
	}

	protected static function _hash($password, $salt) {
		return hash('sha512', $password . $salt);
	}

	protected static function _beforeQuery($opts) {
		parent::_beforeQuery($opts);

//		$opts['where']['app_id'] = \LT\Config::value('rbac.app_id');

		return $opts;
	}

	protected function _beforeCreate() {
		parent::_beforeCreate();
		
		/**
		 * disabled by stephen.yu 2019-09-09
		 */
//		$t					 = time();
//		$this->token		 = \LT\Security\UUID::gen();
//		$this->created_time	 = $t;
//		$this->updated_time	 = $t;
//		if (is_null($this->country)) {
//			$this->country = 'HK';
//		}
//		if (is_null($this->lang)) {
//			$this->lang = 'en';
//		}
//		$this->status	 = 1;
//		$this->app_id	 = \LT\Config::value('rbac.app_id');
//		$user			 = \LT\RBAC\User::current();
//		if ($user) {
//			$this->merchant_id = $user->merchant_id;
//		}
	}

	protected function _beforeUpdate() {
		parent::_beforeUpdate();
//		$this->country		 = strtoupper($this->country);
		$this->updated_time	 = time();
	}

	protected function _beforeSave() {
		parent::_beforeSave();
#
#		if (empty($this->mobile)) {
#			$this->mobile = NULL;
#		}
#
#		if (empty($this->password)) {
#			$this->salt	 = $this->getRawValue('salt');
#			$this->password	 = $this->getRawValue('password');
#		} else {
#			$this->salt	 = \LT\Security\Misc::random(16);
#			$this->password	 = self::_hash($this->password, $this->salt);
#		}
#
#		if ($this->verified_time && !ctype_digit($this->verified_time)) {
#			$this->verified_time = strtotime($this->verified_time);
#		}
	}

	public function isCorrectPassword($password) {
		$rs = ($this->password === self::_hash($password, $this->salt));
		return $rs;
	}

	public function isAllowedIP($ip = NULL) {
		if (is_null($ip)) {
			$ip = \LT\Network\Client::ip();
		}
		if (empty($this->cidr)) {
			return TRUE;
		}
		if (\LT\Network\Client::inCIDRs($this->cidr)) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * 
	 * @return \LT\RBAC\Role
	 */
	public function roles() {
		$roles = \LT\Session::get('rbac_roles');
		if (empty($roles)) {
			$roles	 = array();
			$rs	 = Role::findByUserID($this->id);
			foreach ($rs as $_r) {
				$roles[$_r->id] = $_r->name;
			}
			\LT\Session::set('rbac_roles', $roles);
		}
		return $roles;
	}

	public function home() {
		$roleID	 = key($this->roles());
		$role	 = Role::findByID($roleID);
		if ($role && $role->default_home) {
			return $role->default_home;
		}
		return '/';
	}

	public function permissions() {
		$permissions	 = \LT\Session::get('rbac_permissions');
		$permissions	 = NULL;
		if (is_null($permissions)) {
			$roles = $this->roles();
			foreach (array_keys($roles) as $_roleID) {
				$permissions = RolePermission::getPermissionsLevelByRoleID($_roleID);
			}
			\LT\Session::set('rbac_permissions', $permissions);
		}
		return $permissions;
	}

	/**
	 * Get permission level by permission key
	 * 
	 * @staticvar array $map The mapping of permission key and permission id
	 * @param string $permissionKey
	 * @return string
	 */
	public function permission($permissionKey) {
		static $map = NULL;
		if (is_null($map)) {
			$rs = Permission::allWithKeys();
			foreach ($rs as $_r) {
				$map[$_r->key] = $_r->id;
			}
		}

		$permissions	 = $this->permissions();
		$ps		 = explode('_', $permissionKey);
		do {
			$key = implode('_', $ps);
			array_pop($ps);
			if (isset($map[$key])) {
				$permissionID = $map[$key];
				if (isset($permissions[$permissionID]) && ($permissions[$permissionID] !== '-')) {
					return $permissions[$permissionID];
				}
			}
		} while (count($ps) > 0);
		$rootID = Permission::getRootID();
		if (!isset($permissions[$rootID])) {
			\LT\Exception::config('missing root permission');
		}
		return $permissions[$rootID];
	}

	public function canRead($permissionKeyOrID) {
		$permission = $this->permission($permissionKeyOrID);
		if (($permission == 'r') || ($permission == 'w')) {
			return TRUE;
		}
		return FALSE;
	}

	public function canEdit($permissionKeyOrID) {
		$permission = $this->permission($permissionKeyOrID);
		if ($permission == 'w') {
			return TRUE;
		}
		return FALSE;
	}

	public function read($permissionKeyOrID) {
		if (!$this->canRead($permissionKeyOrID)) {
			\LT\Exception::general('permission deny');
		}
	}

	public function edit($permissionKeyOrID) {
		if (!$this->canEdit($permissionKeyOrID)) {
			\LT\Exception::general('permission deny');
		}
	}

	public function connectFacebook(\Facebook\GraphNodes\GraphUser $user, \Facebook\Authentication\AccessToken $accessToken = NULL) {


		if (!($socialAccount = \OK\RbacUserSocialAccount::findByUserIDAndFacebookASID($this->id, $user->getId()))) {

			$socialAccount			 = new \OK\RbacUserSocialAccount;
			$socialAccount->user_id		 = $this->id;
			$socialAccount->platform_type	 = 'facebook';
			$socialAccount->platform_uid	 = $user->getId();
			$socialAccount->platform_uid_int = $user->getId();
		} else {
			
		}

		if ($user->getName()) {
			$socialAccount->name = $user->getName();
		}

		if ($user->getEmail()) {
			$socialAccount->email = $user->getEmail();
		}

		if ($user->getGender()) {
			$socialAccount->gender = $user->getGender();
		}

		if ($user->getPicture()) {
			$socialAccount->profile_picture = $user->getPicture()->getField('url');
		}

		if ($accessToken) {
			$socialAccount->access_token	 = $accessToken->getValue();
			$expiresAt			 = $accessToken->getExpiresAt();
			if ($expiresAt) {
				$socialAccount->access_token_expiry_time = $accessToken->getExpiresAt()->getTimestamp();
			}

			\LT\Session::set('facebook_access_token', $socialAccount->access_token);
			$s = \LT\Session::get('facebook_access_token');
			var_dump($s);
		}

		if (FALSE === $socialAccount->save()) {
			var_dump($socialAccount);
			echo __METHOD__ . 'Error: ' . $socialAccount->errorMessage();
		}
	}

}
