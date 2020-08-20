<?php

namespace Action\Admin\Settings\Role;

use LT\Form;
use LT\Form\Element;
use LT\RBAC;

class Edit extends \OK\Action\Admin {

	/**
	 * @var \LT\Form 
	 */
	protected $_form;

	/**
	 * @var \LT\Form 
	 */
	protected $_permissionsForm;
	protected $_permissions = array();

	public function __construct() {
		parent::__construct();
		$this->_form		 = new Form([
			Element\Text::create('name')->required(),
			Element\Textarea::create('description'),
		]);
		$_permissions		 = RBAC\Permission::all();
		$_permissionsTree	 = \LT\Type\Collection::tree($_permissions);
		$this->_permissions	 = \LT\Type\Collection::treeFlatten($_permissionsTree, ' + ', 'label');

		$_permissionsOptions = RBAC\Level::options();
		$_permissionsFields	 = array(
			Element\SectionTitle::create('Permissions')
		);
		foreach ($this->_permissions as $_permission) {
			$_permissionsFields[] = Element\Radios::create('permission_' . $_permission['id'], $_permission['label'])
							->options($_permissionsOptions)
							->defaultValue(RBAC\Level::INHERIT)->required();
		}
		$this->_permissionsForm = new Form($_permissionsFields);
	}

	protected function _getRolePermissions($roleID) {
		return \LT\RBAC\RolePermission::select()->where(['role_id' => $roleID])->get();
	}

	public function get($id = NULL) {
		if ($id) {
			$role = RBAC\Role::findOne($id);
			$this->_form->setValues($role);

			$rolePermissions = $this->_getRolePermissions($id);
			foreach ($rolePermissions as $_rolePermission) {
				$this->_permissionsForm['permission_' . $_rolePermission['permission_id']]->value($_rolePermission['level']);
			}
		}
		$this->view->assign('form', $this->_form);
		$this->view->assign('permissions', $this->_permissions);
		$this->view->assign('permissionsForm', $this->_permissionsForm);
	}

	public function post($id = NULL) {

		$id = $this->_formSubmit($this->_form, RBAC\Role::classname(), $id);

		RBAC\RolePermission::quickDelete(['role_id' => $id]);

		$data = $this->_permissionsForm->getSubmittedValues('permission_');
		foreach ($data as $_permissionID => $_permissionLevel) {
			if ($_permissionLevel == '-') {
				continue;
			}
			$rolePermission					 = new RBAC\RolePermission();
			$rolePermission->role_id		 = $id;
			$rolePermission->permission_id	 = $_permissionID;
			$rolePermission->level			 = $_permissionLevel;
			$rolePermission->save();
		}

		$this->okRedirect('index');
	}

}
