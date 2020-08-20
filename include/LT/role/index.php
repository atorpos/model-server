<?php

namespace Action\Admin\Settings\Role;

class Index extends \OK\Action\Admin {

	public function get() {

		$roles = \LT\RBAC\Role::all();
		$this->view->assign('roles', (array) $roles);
	}

}
