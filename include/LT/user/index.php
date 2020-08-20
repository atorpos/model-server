<?php

namespace Action\Admin\Settings\User;

use \LT\RBAC;

class Index extends \OK\Action\Admin {

	public function get() {
		$users		 = RBAC\User::select()->asc('name')->get();
		$userRoles	 = RBAC\UserRole::select()->where(['user_id' => array_keys($users)])->getKeyValue('role_id', 'user_id');

		$roles = RBAC\Role::find(['id' => $userRoles]);
		foreach ($users as $_user) {
			if (isset($userRoles[(int)$_user->id])) {
				$_user->role_id		 = $userRoles[$_user->id];
				$_user->role_name	 = $roles[$_user->role_id]->name;
				$users[$_user->id] = $_user;
			}
		}

		$this->view->assign('users', $users);
	}

}
