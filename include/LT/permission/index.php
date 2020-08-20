<?php

namespace Action\Admin\Settings\Permission;

class Index extends \OK\Action\Admin {

	public function get() {
		$rs = \LT\RBAC\Permission::allWithKeys();
		$tree		 = \LT\Type\Collection::tree($rs);
		$permissions = \LT\Type\Collection::treeFlatten($tree, '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
		$this->view->assign('permissions', (array) $permissions);
	}

	public function post($action) {
		switch ($action) {
			case 'sorting':
				$this->_sorting();
				break;
			default :
				break;
		}

		$this->view->noOutput();
	}

	protected function _sorting() {
		$ids		 = filter_input(INPUT_POST, 'ids', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
		$newOrders	 = array();
		$i			 = 1;
		foreach ($ids as $id) {
			$newOrders[$id] = $i++;
		}
//		$this->dump($newOrders);

		$permissions = \LT\RBAC\Permission::find(['id' => $ids]);
		foreach ($permissions as $permission) {
			$permission->sort_order = $newOrders[$permission->id];
			$permission->save();
		}
		$this->ok('Sorting Updated');
	}

}
