<?php

namespace Action\Admin\Settings\User;

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
	protected $_roleForm;
	protected $_roleOptions = array();

	public function __construct() {
		parent::__construct();
		$this->_form		 = new Form([
			Element\Text::create('name')->required(),
			Element\Text::create('username')->required(),
			Element\Text::create('password')->help("please leave the field blank, if you don't want to change the password"),
			Element\Text::create('email')->required(),
			Element\Text::create('mobile_prefix')->defaultValue('852'),
			Element\Text::create('mobile'),
			Element\Dropdown::create('country')->required()->options([
				'HK' => 'Hong Kong',
			]),
			Element\Dropdown::create('lang', 'Language')->required()->options([
				'zh-HK' => 'Chinese (Hong Kong)',
			]),
			Element\Textarea::create('cidr', 'IP Whitelist')->help('IP rules for this user'),
			Element\ToggleSwitch::create('email_activated')->defaultValue('1'),
			Element\DateTime::create('verified_time', 'Email Activated Time')->defaultNow()->dateFormatFull(),
			
		]);
		$this->_roleOptions	 = RBAC\Role::dropdownOptions();
		$this->_roleForm	 = new Form([
			Element\SectionTitle::create('User Role'),
			Element\Dropdown::create('role')->options(['' => 'Please select ...'] + $this->_roleOptions)->required(),
		]);
	}

	public function get($id = NULL) {
		if ($id) {
			$user = \OK\User::findOne($id);
			unset($user->password);
			$this->_form->setValues($user);
			$roles = $user->roles();
			if (!empty($roles)) {
				$this->_roleForm->setValues(['role' => key($roles)]);
			}
			
		}
		$this->view->assign('form', $this->_form);
		$this->view->assign('roleForm', $this->_roleForm);
	}

	public function post($id = NULL) {
		$id = $this->_formSubmit($this->_form, \OK\User::classname(), $id);

		RBAC\UserRole::quickDelete(['user_id' => $id]);

		$data = $this->_roleForm->getSubmittedValues();

		$userRole			 = new RBAC\UserRole();
		$userRole->user_id	 = $id;
		$userRole->role_id	 = $data['role'];
		$userRole->save();

		$this->okRedirect('index');
	}

}
