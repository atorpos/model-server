<?php

/**
 * LT Framework - PHP development framework
 * NOTE: Requires PHP version 5.2 or later
 * @package LT
 * @copyright 2009 - 2013 LT Workshop Limited
 * @license http://creativecommons.org/licenses/by-nc-nd/3.0/ Attribution-NonCommercial-NoDerivs 3.0 Unported
 */
class LT_Form_Field {

    /**
     * @var array Field Settings 
     */
    protected $_f         = array(
        'type'    => 'text',
        'name'    => '',
        'default' => NULL,
        'value'   => NULL,
        'attrs'   => array(
            'class' => '',
        ),
        'class'   => array(),
    );
    protected $_inputType = INPUT_POST;
    protected $_loaded    = FALSE;

    public function __construct($name, $title = NULL, $default = NULL) {
        $this->setName($name);
        $this->setValue($default);
        $this->_f['default'] = $default;
        $this->_f['title']   = is_null($title) ? ucfirst($title) : $title;
        return $this;
    }

    private function _type($val) {
        $this->_f['type'] = $val;
        return $this;
    }

    private function _attr($name, $val) {
        $this->_f['attrs'][$name] = $val;
        return $this;
    }

    public function setName($val) {
        $this->_f['name'] = $val;
        $this->_attr('id', $val);
        $this->_attr('name', $val);
        return $this;
    }

    public function setID($val) {
        return $this->_attr('id', $val);
    }

    public function name() {
        return $this->_f['name'];
    }

    public function setValue($val) {
        $this->_f['value'] = $val;
        return $this->_attr('value', $val);
    }

    public function value() {
        return $this->_f['value'];
    }

    public function loadSubmit($type = INPUT_POST) {
        if ($this->_loaded) {
            return;
        }
        $this->_loaded = TRUE;
        if (isset($this->_f['multi']) && $this->_f['multi']) {
            $v = filter_input($type, $this->_f['name'], FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
			if (!$v) {
				$v = filter_input($type, $this->_f['name']);
			}
        } else {
            $v = filter_input($type, $this->_f['name']);
        }
        if (isset($this->_f['case']) && is_string($v)) {
            switch ($this->_f['case']) {
                case 'upper':
                    $v = strtoupper($v);
                    break;
                case 'lower':
                    $v = strtolower($v);
                    break;
            }
        }
		if (is_string($v) && isset($this->_f['explode']) && $this->_f['explode']) {
			$v = explode($this->_f['explode'], $v);
		}
        if ($this->_f['type'] == 'onoff') {
            $v = is_null($v) ? 0 : $v;
        }
		
		// @add condition by Sing, 2015-05-12 10:20am
		if($v === '') {
			$v = NULL;
		} else if(filter_var($v, FILTER_VALIDATE_INT)) {
			$v = (int)$v;
		} else if(filter_var($v, FILTER_VALIDATE_FLOAT)) {
			$v = (float)$v;
		}
		// -- end
		
        $this->setValue($v); 
    }

    public function addAttr($name, $val) {
        $this->_f['attrs'][$name] = $val;
        return $this;
    }

    public function removeAttr($name) {
        unset($this->_f['attrs'][$name]);
        return $this;
    }

    public function addClass($val) {
        $this->_f['class'][] = $val;
        return $this->_attr('class', implode(' ', $this->_f['class']));
    }

    public function removeClass($val) {
        if (($k = array_search($val, $this->_f['class'])) !== false) {
            unset($this->_f['class'][$k]);
        }
        return $this->_attr('class', implode(' ', $this->_f['class']));
    }

    public function placeholder($val) {
        return $this->_attr('placeholder', $val);
    }

    public function checkbox($multi = TRUE) {
        $this->_f['multi'] = $multi;
        return $this->_type('checkbox');
    }

    public function dropdown() {
        return $this->_type('dropdown');
    }

    public function radio() {
        return $this->_type('radio');
    }

    public function onoff() {
        return $this->_type('onoff');
    }

    public function date() {
        $this->maxLength(10);
        return $this->_type('date');
    }

    public function time() {
        $this->maxLength(8);
        return $this->_type('time');
    }

    public function datetime() {
        $this->maxLength(19);
        return $this->_type('datetime');
    }

    public function month() {
        $this->range(12, 1);
        return $this->_type('month');
    }

    public function week() {
        $this->range(52, 1);
        return $this->_type('week');
    }

    public function number() {
        return $this->_type('number');
    }

    public function color() {
        $this->length(7, 7);
        return $this->_type('color');
    }

    public function tel() {
        if (!isset($this->_f['maxlength'])) {
            $this->maxLength(20);
        }
        return $this->_type('tel');
    }

    public function url() {
        return $this->_type('url');
    }

    public function file() {
        return $this->_type('file');
    }

    public function password() {
        if (!isset($this->_f['maxlength'])) {
            $this->maxLength(20);
        }
        if (!isset($this->_f['minlength'])) {
            $this->minLength(6);
        }
        return $this->_type('password');
    }
	
    public function hidden() {
        return $this->_type('hidden');
    }

    public function text() {
        return $this->_type('text');
    }

    public function textarea($rows = 3) {
        return $this->_type('textarea')->addAttr('rows', $rows);
    }

    public function richtext() {
        return $this->_type('richtext');
    }

    public function ip() {
        $this->maxLength(17);
        return $this->_type('ip');
    }

    public function login() {
        $this->length(20, 3);
        return $this->_type('login');
    }

    public function email() {
        $this->maxLength(100);
        return $this->_type('email');
    }

    public function nozero() {
        $this->_f['number'] = 'nozero';
        return $this;
    }

    public function positive() {
        $this->_f['number'] = 'positive';
        return $this;
    }

    public function negative() {
        $this->_f['number'] = 'negative';
        return $this;
    }

    public function amount() {
        $this->number();
        $this->_f['validate'] = 'amount';
        return $this;
    }

    public function digits() {
        $this->number();
        $this->_f['validate'] = 'digits';
        return $this;
    }

    public function alnum() {
        $this->_f['validate'] = 'alnum';
        return $this;
    }

    public function noHTML() {
        $this->_f['validate'] = 'nohtml';
    }

    public function required() {
        $this->_f['required'] = TRUE;
        return $this;
    }

    public function multi() {
        $this->_f['multi'] = TRUE;
		return $this;
    }

    public function range($max, $min = 0) {
        $this->_f['max'] = $max;
        $this->_f['min'] = $min;
        return $this;
    }

    public function max($val) {
        $this->_f['max'] = $val;
        return $this;
    }

    public function min($val) {
        $this->_f['min'] = $val;
        return $this;
    }

    public function length($max, $min = 0) {
        return $this->maxLength($max)->minLength($min);
    }

    public function maxLength($val) {
        $this->_f['maxlength'] = $val;
        return $this->addAttr('maxlength', $val);
    }

    public function minLength($val) {
        $this->_f['minlength'] = $val;
        return $this;
    }

    public function lower() {
        $this->_f['case'] = 'lower';
        return $this;
    }

    public function upper() {
        $this->_f['case'] = 'upper';
        return $this;
    }
	
	public function explode($delimiter = ',') {
        $this->_f['explode'] = $delimiter;
        return $this;
	}

	public function options($items) {
        $this->_f['options'] = $items;
        if (!in_array($this->_f['type'], array('dropdown', 'checkbox', 'radio'))) {
            $this->_f['type'] = 'dropdown';
        }
        return $this;
    }

    public function toArray() {
        return $this->_f;
    }

    public function toString() {
        return var_export($this->_f, TRUE);
    }

    public function __toString() {
        return $this->_f['name'];
    }

}
