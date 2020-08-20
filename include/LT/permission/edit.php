<?php

namespace Action\Admin\Settings\Permission;

use \LT\Form\Element;

class Edit extends \OK\Action\Admin {

	protected $_form;

	public function __construct() {
		parent::__construct();

		$this->_form = new \LT\Form([
			Element\Dropdown::create('parent_id', 'Parent Object')
				->options(\LT\RBAC\Permission::options())->addClass('select2')->defaultValue('1'),
			Element\Text::create('name', 'Object Key')->required(),
			Element\Text::create('label')->required(),
			Element\Textarea::create('description'),
		]);
	}

	public function get($id = NULL) {

		if ($id) {
			$data = \LT\RBAC\Permission::findOne($id);
			if ($data) {
				$this->_form->setValues($data);
			} else {
				$this->badRequest();
			}
		}

		$this->view->assign('form', $this->_form);
	}

	public function post($id = NULL) {

		$this->_formSubmit($this->_form, \LT\RBAC\Permission::classname(), $id);

		$this->okRedirect('index');
	}

}
