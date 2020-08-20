<?php

namespace LT\Form\Element;

class Checkboxes extends Radios {

	public function __construct($name = NULL, $label = NULL, $defaultValue = NULL) {
		parent::__construct($name, $label, $defaultValue);

		$this->set('tag', 'div')
				->addClass('lt-form-element lt-form-element-checkboxes');
	}

	public function getSubmittedValue() {
		return filter_input(INPUT_POST, $this->getName(), FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
	}

	public function validate($value) {

		if ($this->get('required') && (empty($value))) {
			return $this->_error(':label: is mandatory');
		}

		$options = $this->get('options', array());

		if (!empty($options)) {
//            $_vs = $this->get('multi') ? $value : array($value);
			if (is_array($value)) {
				foreach ($value as $_v) {
					if (!isset($options[$_v])) {
						return $this->_error(':label: contain invalid value');
					}
				}
				unset($_v);
			}
		}
		return TRUE;
	}

	protected function _updateOptions() {
		$options = $this->get('options');
		if (empty($options)) {
			return;
		}
		$selected	 = $this->get('value', array());
		$elements	 = array();
		foreach ($options as $value => $label) {
			$elements[] = $this->_option($value, $label, $selected);
		}
		$this->inner($elements);
	}

	public function _option($value, $label, $selected = array()) {

		$attrs = [
			'type'	 => 'checkbox',
			'value'	 => $value,
			'name'	 => $this->getName() . '[]',
		];
		if (in_array((string) $value, $selected)) {
			$attrs['checked'] = 'checked';
		}
		$option = Base::tag('label', [], [
					Base::tag('input', $attrs, $label)
		]);

		return $option;
	}

}
