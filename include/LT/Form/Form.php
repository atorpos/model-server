<?php

namespace LT;

/**
 * @property-read array $elements   form elements
 */
class Form implements \ArrayAccess {

	/**
	 * @var \LT\Form\Element\Base 
	 */
	protected $_elements = array();

	/**
	 *
	 * @var \LT\Form\Theme\Base
	 */
	protected $_theme	 = NULL;
	protected $_http	 = 'post';

	public function __construct(array $elements, $theme = 'Metronic') {
		if (is_string($theme)) {
			$themeClass		 = '\\LT\\Form\\Theme\\' . $theme;
			$this->_theme	 = new $themeClass();
		} elseif (is_subclass_of($theme, '\\LT\\Form\\Theme\\Base')) {
			$this->_theme = $theme;
		} else {
			Exception::general('invalid form theme');
		}
		$this->setElements($elements);
		$this->applyDefaultValues();
	}

	/**
	 * 
	 * @param \LT\Form\Element\Base $element
	 */
	public function setElement($element) {
		$name					 = $element->get('name');
		$element->theme($this->_theme);
		$this->_elements[$name]	 = $element;
	}

	public function setElements(array $elements) {
		foreach ($elements as $element) {
			$this->setElement($element);
		}
	}

	public function setValues($data) {
		$fields = $this->getFields();
		foreach ($fields as $element) {
			$name = $element->getName();
			if (isset($data[$name])) {
				$element->value($data[$name]);
			}
		}
	}

	public function applyDefaultValues() {
		foreach ($this->_elements as $element) {
//            $element->value($element->get('default_value'));
		}
	}

	public function getValidationErros() {
		$errors = array();
		foreach ($this->getFields() as $element) {
			$name	 = $element->getName();
			$value	 = $element->getSubmittedValue();
			if (!$element->validate($value)) {
				$errors[$name] = $element->lastError();
			}
		}
		if (empty($errors)) {
			return FALSE;
		}
		return $errors;
	}

	protected function _getTrimmedName($name, $trim) {
		if (is_string($trim) && (FALSE !== ($p = strpos($name, $trim)))) {
			return substr($name, strlen($trim));
		}
		return $name;
	}

	public function getSubmittedValues($trim = FALSE) {
		$values = array();
		foreach ($this->getFields() as $element) {
			if ($this->_http == 'get') {
				$element->httpGet();
			}
			$name			 = $this->_getTrimmedName($element->getName(), $trim);
			$value			 = $element->getSubmittedValue();
//            $value = filter_input(INPUT_POST, $name);
			$values[$name]	 = $value;
		}
		return $values;
	}

	public function getValues() {
		$values = array();
		foreach ($this->getFields() as $element) {
			$name			 = $element->getName();
			$value			 = $element->get('value');
			$values[$name]	 = $value;
		}
		return $values;
	}

	public function offsetSet($offset, $value) {
		if (!is_null($offset)) {
			$this->_elements[$offset] = $value;
		}
	}

	public function offsetExists($offset) {
		return isset($this->_elements[$offset]);
	}

	public function offsetUnset($offset) {
		unset($this->_elements[$offset]);
	}

	public function offsetGet($offset) {
		if (isset($this->_elements[$offset])) {
			return $this->_elements[$offset];
		}
		return NULL;
	}

	public function __get($name) {
		if ($name == 'elements') {
			return $this->_elements;
		}
	}

	/**
	 * 
	 * @return \LT\Form\Element\Base
	 */
	public function getFields() {
		$fields = array();
		foreach ($this->_elements as $element) {
			$fields += $element->getFields();
		}
		return $fields;
	}

	public function httpGet() {
		$this->_http = 'get';
	}

	public function __toString() {
		return var_export($this->_elements, TRUE);
	}

}

//class Form {
//
//    /**
//     * @var array The array of LT_Form_Field 
//     */
//    protected $_fs = array();
//
//    /**
//     * Form ID
//     * 
//     * @var string 
//     */
//    protected $_id;
//
//    /**
//     * @var string The random token for anti-robot
//     */
//    protected $_token = '';
//
//    /**
//     * @var \LT\Form\Validator
//     */
//    protected $_validator;
//
//    /**
//     * @var \LT\Form\Generator
//     */
//    protected $_generator;
//    protected $_method;
//
//    /**
//     * 
//     * @param string $id
//     */
//    public function __construct($id = NULL) {
//        $this->_id = $id;
//    }
//
//    public function __clone() {
//        foreach ($this->_fs as $k => $v) {
//            $this->_fs[$k] = clone $v;
//        }
//    }
//
//    /**
//     * Add new field
//     * 
//     * @param string $name Field Name
//     * @param string $label Field Label
//     * @param string $default Default Value
//     * @return LT_Form_Field
//     */
//    public function add($name, $label = NULL, $default = NULL) {
//        if ($this->_token === '') {
//            $f = new LT_Form_Field($name, $label, $default);
//        } else {
//            $f = new LT_Form_Field($name . $this->_token, $label, $default);
//            $f->rename($name);
//        }
//        if ($this->_id) {
//            $f->setID($this->_id . '_' . $name);
//        }
//        return $this->_fs[$name . $this->_token] = $f;
//    }
//
//    /**
//     * Check form is submitted or not
//     * 
//     * @return boolean
//     */
//    public function isSubmit($flag = NULL) {
//
//        if (!is_null($flag)) {
//            return (LT::arg('LT_ACTION') == $flag);
//        }
//
//        if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') == 'POST') {
//            return TRUE;
//        }
//        if (LT::arg('LT_ACTION')) {
//            return TRUE;
//        }
//
//        return FALSE;
//    }
//
//    /**
//     * Get validate errors in array
//     * 
//     * @return array
//     */
//    public function errors() {
//        return $this->validator()->errors();
//    }
//
//    /**
//     * Validate form via form validator
//     * 
//     * @return boolean
//     */
//    public function validate() {
//        return $this->validator()->validate();
//    }
//
//    /**
//     * Get form validator instance
//     * 
//     * @return LT_Form_Validator
//     * 
//     */
//    public function validator() {
//        if (!$this->_validator) {
//            $this->loadSubmit();
//            $this->_validator = new LT_Form_Validator($this->fieldsArray());
//        }
//        return $this->_validator;
//    }
//
//    /**
//     * Get form generator instance
//     * 
//     * @return LT_Form_Generator
//     */
//    public function generator() {
//        if (!$this->_generator) {
//            $this->loadSubmit();
//            $this->_generator = new LT_Form_Generator($this->fieldsArray());
//        }
//        return $this->_generator;
//    }
//
//    /**
//     * Set Token
//     * 
//     * @param string $token
//     */
//    public function setToken($token) {
//        $this->_token = $token;
//    }
//
//    /**
//     * Set HTTP method
//     * 
//     * @param string $method
//     */
//    public function setMethod($method = 'post') {
//        $m = strtolower($method);
//        if ($m == 'post') {
//            $this->_method = INPUT_POST;
//        } elseif ($m == 'get') {
//            $this->_method = INPUT_GET;
//        }
//    }
//
//    /**
//     * Set value to instance
//     * 
//     * @param string $key
//     * @param mixed $val
//     */
//    public function setValue($key, $val) {
//        if (isset($this->_fs[$key])) {
//            $this->_fs[$key]->setValue($val);
//        }
//    }
//
//    /**
//     * Set values to instance
//     * 
//     * @param array $vals
//     */
//    public function setValues(array $vals) {
//        foreach ($vals as $k => $v) {
//            if (isset($this->_fs[$k])) {
//                $this->_fs[$k]->setValue($v);
//            }
//        }
//    }
//
//    /**
//     * Get submitted/default values, need call loadSubmit() before 
//     * 
//     * @return array
//     */
//    public function values() {
//        $rs = array();
//        foreach ($this->_fs as $f) {
//            $rs[$f->name()] = $f->value();
//        }
//        return $rs;
//    }
//
//    /**
//     * Load submitted values into instance variables, if submit flag detected.
//     * 
//     * @param bool $force Dismiss flag detection, force load the values
//     */
//    public function loadSubmit($force = FALSE) {
//        if ($force || $this->isSubmit()) {
//            foreach ($this->_fs as $f) {
//                $f->loadSubmit($this->_method);
//            }
//        }
//    }
//
//    /**
//     * Get fields settings in array
//     * 
//     * @return array
//     */
//    public function fieldsArray() {
//        $r = array();
//        foreach ($this->_fs as $k => $v) {
//            $r[$k] = $v->toArray();
//        }
//        return $r;
//    }
//
//}
