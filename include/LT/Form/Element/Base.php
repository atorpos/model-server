<?php

namespace LT\Form\Element;

abstract class Base implements \ArrayAccess {

	protected $_lastError	 = array();
	protected $_settings	 = array(
		'col'			 => 4,
		'tag'			 => NULL,
		'name'			 => NULL,
		'attrs'			 => array(),
		'class'			 => array(),
		'default_value'	 => NULL,
		'inner'			 => '',
	);
	protected $_http		 = 'post';

	/**
	 *
	 * @var \LT\Form\Theme\Base
	 */
	protected $_theme = NULL;

	public static function create($name = NULL, $label = NULL, $default = NULL) {
		$element = new static($name, $label, $default);
		return $element;
	}

	public function __construct($name, $label = NULL, $default = NULL) {

		if (is_null($name)) {
			// TODO random name
		}
		$this->_settings['name'] = $name;

		if (is_null($label)) {
			$label = ucwords(strtolower(str_replace(array('_', '-'), ' ', $name)));
		}

		$this->label($label);
		$this->defaultValue($default);
		$this->value($default);
	}

	public function theme($theme) {
		$this->_theme = $theme;
	}

	/**
	 * 
	 * @return static
	 */
	public function getFields() {
		return array($this->getName() => $this);
	}

	public function getName() {
		return $this->get('name');
	}

	public function getSubmittedValue() {
		if ($this->_http == 'get') {
			return filter_input(INPUT_GET, $this->getName());
		}
		return filter_input(INPUT_POST, $this->getName());
	}

	public function httpGet() {
		$this->_http = 'get';
	}

	/**
	 * Get settings value by name
	 * 
	 * @param string $name       settings name
	 * @param string $default    default value
	 * @return string
	 */
	public function get($name, $default = NULL) {
		if (isset($this->_settings[$name])) {
			return $this->_settings[$name];
		}
		if (isset($this->_settings['attrs'][$name])) {
			return $this->_settings['attrs'][$name];
		}
		return $default;
	}

	/**
	 * Update settings value by name
	 * 
	 * @param string $name      settings name
	 * @param string $value     value
	 * @return static
	 */
	public function set($name, $value) {
		$this->_settings[$name] = $value;
		return $this;
	}

	/**
	 * 
	 * @param string $name
	 * @param string $value
	 * @return static
	 */
	public function attr($name, $value) {
		$this->_settings['attrs'][$name] = $value;
		return $this;
	}

	/**
	 * 
	 * @param string $name
	 * @param string $value
	 * @return static
	 */
	public function data($name, $value) {
		$this->_settings['attrs']['data-' . $name] = $value;
		return $this;
	}

	public function inner($elements) {
		return $this->set('inner', $elements);
	}

	/**
	 * 
	 * @param string $value
	 * @return static
	 */
	public function defaultValue($value) {
		$this->value($value);
		return $this->set('default_value', $value);
	}

	/**
	 * 
	 * @param string $value
	 * @return static
	 */
	public function value($value) {
		return $this->attr('value', $value);
	}

	public function validate($value) {
		return isset($value);
	}

	/**
	 * Update class
	 * 
	 * @param string $class CSS Class Name
	 * @return static
	 */
	public function setClass($class) {
		if (is_string($class)) {
			$class = explode(' ', $class);
		}

		$this->_settings['class'] = $class;
		$this->attr('class', $class);
		return $this;
	}

	/**
	 * Add a custom class
	 *
	 * @param string $class CSS Class Name
	 * @return static
	 */
	public function addClass($class) {
		if (is_string($class)) {
			$class = explode(' ', $class);
		}
		$class = array_unique(array_merge($this->_settings['class'], $class));

		return $this->setClass($class);
	}

	/**
	 * Set field label
	 * 
	 * @param string $text
	 * @return static
	 */
	public function label($text) {
		return $this->set('label', $text);
	}

	/**
	 * Set help text/html
	 * @param string $text
	 * @return static
	 */
	public function help($text) {
		return $this->set('help', $text);
	}

	/**
	 * @return static
	 */
	public function noLabel() {
		return $this->set('no-label', 1);
	}

	/**
	 * 
	 * @param int $num
	 * @return static
	 */
	public function col($num = 4) {
		return $this->set('col', $num);
	}

	/**
	 * @return static
	 */
	public function required() {
		return $this->set('required', 1);
	}

	public function translatable() {
		return $this->set('translatable', 1);
	}

	public function offsetSet($offset, $value) {
		if (!is_null($offset)) {
			$this->_settings[$offset] = $value;
		}
	}

	public function offsetExists($offset) {
		if ($offset === 'field') {
			return TRUE;
		}
		return isset($this->_settings[$offset]);
	}

	public function offsetUnset($offset) {
		unset($this->_settings[$offset]);
	}

	public function offsetGet($offset) {
		if ($offset === 'field') {
			return $this->field();
		}
		if (isset($this->_settings[$offset])) {
			return $this->_settings[$offset];
		}
		return NULL;
	}

	public function field() {

		if (is_array($this->_settings['inner'])) {
			foreach ($this->_settings['inner'] as $element) {
				if (is_subclass_of($element, '\\LT\Form\\Element\\Base')) {
					/* @var $element \LT\Form\Element\Base */
					$element->theme($this->_theme);
				}
			}
		}
		$this->_theme->beforeRender($this);

		$html = $this->_theme->renderField($this);

		return $html;
	}

	/**
	 * HTML
	 *
	 * @return string
	 */
	public function html() {

		if (is_array($this->_settings['inner'])) {
			foreach ($this->_settings['inner'] as $element) {
				if (is_subclass_of($element, '\\LT\Form\\Element\\Base')) {
					/* @var $element \LT\Form\Element\Base */
					$element->theme($this->_theme);
				}
			}
		}
		$this->_theme->beforeRender($this);

		$html = $this->_theme->render($this);

		return $html;
	}

	public function __toString() {
		return $this->html();
	}

	public function lastError() {
		if (empty($this->_lastError)) {
			return FALSE;
		}
		$msg = $this->_lastError['message'];
		foreach ($this->_lastError['data'] as $k => $v) {
			$msg = str_replace(":$k:", $v, $msg);
		}
		return $msg;
	}

	protected function _error($message, array $data = array()) {

		$data = [
			'label' => $this->get('label'),
		];

		$this->_lastError = array(
			'message'	 => $message,
			'data'		 => $data,
		);
		return FALSE;
	}

	public static function tag($name, $attrs = array(), $inner = '') {
		return array(
			'tag'	 => $name,
			'attrs'	 => $attrs,
			'inner'	 => $inner,
		);
	}

}

//abstract class Base implements \ArrayAccess {
//
//    protected $_customAttrs = array(
//        'lt-form-element',
//        'data-toggle',
//        'data-onchange',
//        'data-required'
//    );
//
//    /**
//     *
//     * @var [] Settings key-value array
//     */
//    protected $_settings = array();
//
//    /**
//     *
//     * @var string Error message
//     */
//    protected $_error;
//    protected $_namespace         = 'field_';
//    protected $_translatableLangs = array();
//
//    /**
//     * @param string $name
//     * @return static
//     */
//    public function __construct($name = NULL, $label = NULL) {
//        if ($name) {
//            $this->name($name);
//        }
//
//        $this
//                ->setTag('input')
//                ->setClass('form-control')
//                ->required(0)
//                ->column(9)
//                ->skip(0)
//                ->noprint(0)
//                ->_label($label);
//
//        return $this;
//    }
//

//

//
//    /**
//     *
//     * @param string $elementType
//     * @return static
//     */
//    protected function _setElementType($elementType) {
//        $this->_settings['lt-form-element'] = $elementType;
//        return $this;
//    }
//
//    /**
//     *
//     * @return boolean
//     */
//    public function isMultiple() {
//        return defined('static::MULTIPLE') ? static::MULTIPLE : FALSE;
//    }
//

//
//    /**
//     * All general attributes with custom attrs
//     *
//     * @return string
//     */
//    public function allAttrs() {
//        $attrs = [];
//
//        if ($this->get('id', NULL) !== NULL) {
//            if ($this->get('id') !== '') {
//                $attrs['id'] = $this->get('id');
//            }
//        } else if ($this->getNameWithNamespace()) {
//            $attrs['id'] = $this->getNameWithNamespace();
//        }
//        if ($this->get('class') !== '') {
//            $attrs['class'] = $this->getClass();
//        }
//
//        if ($this->getNameWithNamespace()) {
//            $attrs['name'] = $this->getNameWithNamespace();
//        }
//        if ($this->get('value') !== '') {
//            $attrs['value'] = $this->_formatOutput($this->getValue());
//        }
//        if ($this->get('type') !== '') {
//            $attrs['type'] = strtolower($this->get('type'));
//        }
//        if ($this->get('placeholder') !== '') {
//            $attrs['placeholder'] = $this->get('placeholder');
//        }
//        if ($this->get('title') !== '') {
//            $attrs['title'] = $this->get('title');
//        }
//
//        if ($this->get('required') === 1) {
//            $attrs['required'] = 'required';
//        }
//        if ($this->get('disabled') === 1) {
//            $attrs['disabled'] = 'disabled';
//        }
//        if ($this->get('readonly') === 1) {
//            $attrs['readonly'] = 'readonly';
//        }
//        if ($this->get('hidden') === 1) {
//            $attrs['hidden'] = 'hidden';
//        }
//
//        foreach ($this->_customAttrs as $attrKey) {
//            if (($value = $this->get($attrKey)) !== '') {
//                if ($attrKey === 'data-toggle' && $value === 'display') {
//                    if ($this->get('required') === 1) {
//                        $attrs['data-required'] = 'true';
//                    }
//                }
//                if ($attrKey === 'data-onchange') {
//                    $attrs[$attrKey] = json_encode($value);
//                } else {
//                    $attrs[$attrKey] = $value;
//                }
//            }
//        }
//
//        return $attrs;
//    }
//
//    /**
//     * Format before insert to db
//     *
//     * @param string $value
//     * @param string $language
//     * @return string
//     */
//    public function format($value, $language = 'default') {
//        if (1 === $this->get('empty_to_null') && empty($value)) {
//            return NULL;
//        }
//        if (1 === $this->get('blank_to_null') && '' === $value) {
//            return NULL;
//        }
//
//        if (is_array($this->get('input_format'))) {
//            $i = $this->get('input_format');
//            $j = $i[1];
//            if (FALSE !== ($k = array_search('%1', $j))) {
//                $j[$k] = $value;
//            } else {
//                array_unshift($j, $value);
//            }
//            $value = call_user_func_array($i[0], $j);
//        }
//        return $value;
//    }
//
//    /**
//     * Show display format, this function is called for showing user friendly values
//     * The value returned for checkbox / radio like input will always be label
//     *
//     * @todo think multilanguage
//     * @param mixed $value Value From DB
//     * @param string $language Language
//     * @return mixed
//     */
//    public function displayFormat($value, $language = 'default') {
//        return $value;
//    }
//
//    /**
//     * Format before print to html
//     *
//     * @param string $value
//     * @return string
//     */
//    protected function _formatOutput($value) {
//
//        $i = $this->get('output_format');
//        if (is_array($i)) {
//            $j = $i[1];
//
//            if (FALSE !== ($k = array_search('%1', $j))) {
//                $j[$k] = $value;
//            } else {
//                array_unshift($j, $value);
//            }
//            $value = call_user_func_array($i[0], $j);
//        }
//        return $value;
//    }
//
//    /**
//     * Set input format before saving to db, such as strtotime
//     *
//     * @todo Think if value is not the first param of method,
//     * such as strtotime($value) is ok, but date('y-m-d', $value) isn't
//     * suppose %1 specified in params array can be used instead, eg
//     * ['y-m-d', '%1']
//     *
//     * @param string $method
//     * @param array $params
//     * @return static
//     */
//    protected function _inputFormat($method, array $params = []) {
//        $this->_settings['input_format'] = [$method, $params];
//        return $this;
//    }
//
//    /**
//     * Set output format before printing to html
//     *
//     * @param string $method
//     * @param array $params
//     * @return static
//     */
//    protected function _outputFormat($method, array $params = []) {
//        $this->_settings['output_format'] = [$method, $params];
//        return $this;
//    }
//
//    /**
//     * Validate input
//     *
//     * @param string $value Value
//     * @param string $language Language
//     * @return boolean
//     */
//    public function validate($value, $language = 'default') {
//        if ('default' === $language) {
//            if ($this->get('data-toggle') !== 'display' && $this->get('required') && (is_null($value) || '' === $value)) {
//                // MUI
//                $this->_error = 'Empty Value ' . $this->get('label');
//                return FALSE;
//            }
//        }
//        return TRUE;
//    }
//
//    /**
//     * Get settings
//     *
//     * @param string $key
//     * @return string
//     */
//    public function get($key, $default = '') {
//        return array_key_exists($key, $this->_settings) ? $this->_settings[$key] : $default;
//    }
//
//    /**
//     * Get field name
//     *
//     * @return string
//     */
//    public function getName() {
//        return $this->get('name');
//    }
//
//    /**
//     * Get field name with namespace
//     *
//     * @return string
//     */
//    public function getNameWithNamespace() {
//        return $this->_namespace . $this->getName();
//    }
//
//    /**
//     * Get submitted value
//     *
//     * @param string $method method, PGC etc
//     * @param string $language Language
//     * @return string|boolean FALSE = problemetic value
//     */
//    public function getSubmittedValue($method = 'P', $language = 'default') {
//        $m      = [
//            'P' => INPUT_POST,
//            'G' => INPUT_GET,
//            'C' => INPUT_COOKIE,
//            'S' => INPUT_SESSION,
//            'R' => INPUT_SERVER
//        ];
//        $filter = $this->get('filter') ? $this->get('filter') : FILTER_SANITIZE_STRING;
//        $flag   = $this->get('flag') | FILTER_FLAG_NO_ENCODE_QUOTES;
//        if ($this->get('translatable') === 1) {
//            $flag |= FILTER_REQUIRE_ARRAY;
//        }
//        $c = 0;
//        while (isset($method[$c])) {
//            if (($value = filter_input($m[$method[$c++]], $this->getNameWithNamespace(), $filter, $flag))) {
//                break;
//            }
//        }
//
//        if ($this->get('translatable') === 1) {
//            $value = $value[$language];
//        }
//
//        if (!$this->validate($value, $language)) {
//            return FALSE;
//        }
//        return $this->format($value, $language);
//    }
//
//    /**
//     *
//     * @return string Error message
//     */
//    public function getLastError() {
//        return $this->_error;
//    }
//
//    /**
//     *
//     * @param string $id
//     * @return \LT\Form\Element\Base
//     */
//    public function id($id) {
//        $this->_settings['id'] = $id;
//        return $this;
//    }
//
//    /**
//     * @param string $name
//     * @return static
//     */
//    public function name($name) {
//        $this->_settings['name'] = $name;
//        return $this;
//    }
//
//    /**
//     * @todo JS error will be found if namespace changed after the onChange method called.
//     * please contact author to fix it
//     *
//     * @param type $prefix
//     * @return static
//     */
//    public function setNamespace($prefix) {
//        $this->_namespace = $prefix;
//        return $this;
//    }
//

//
//    /**
//     * Set <sup></sup> text for label
//     *
//     * @param string $text
//     * @param staring $class CSS class
//     * @return static
//     */
//    public function labelSup($text, $class = '') {
//        $this->_settings['label_sup']       = $text;
//        $this->_settings['label_sup_class'] = $class;
//        return $this;
//    }
//
//    /**
//     * Set column
//     *
//     * @param int $col Column number default 9
//     * @return static
//     */
//    public function column($col = 9) {
//
//        $this->_settings['column'] = $col;
//        return $this;
//    }
//
//    /**
//     * Set addon
//     *
//     * @param string $arg Addon text or add on icon class name, eg fa fa-calendar
//     * @param string $type Type of addon, default = icon, another option is text and icon-button
//     * @param string $pendTo Append location, append or prepend
//     * @return static
//     */
//    public function addon($arg, $type = 'icon', $pendTo = 'prepend') {
//        if ($this->get('addon') === '') {
//            $this->_settings['addon'] = array();
//        }
//        array_push($this->_settings['addon'], get_defined_vars());
//        return $this;
//    }
//
//    /**
//     * Set addon
//     *
//     * @param string $arg Addon text or add on icon class name, eg fa fa-calendar
//     * @param string $pendTo Append location, append or prepend
//     * @param string $buttonStyle button style
//     * @return static
//     */
//    public function addonIconButton($arg, $pendTo = 'prepend', $buttonStyle = '') {
//        $type = 'icon-button';
//        if ($this->get('addon') === '') {
//            $this->_settings['addon'] = array();
//        }
//        array_push($this->_settings['addon'], get_defined_vars());
//        return $this;
//    }
//
//    /**
//     * @param int $required Required? 1 = required, 0 = not required
//     * @return static
//     */
//    public function required($required = 1) {
//        if ($required) {
//            $this->_settings['required'] = $required;
//        } else {
//            unset($this->_settings['required']);
//        }
//        return $this;
//    }
//
//    /**
//     * This Field is required
//     * @return boolean
//     */
//    public function isRequired() {
//        if(isset($this->_settings['required']) && $this->_settings['required'] === 1){
//            return true;
//        }
//        return false;
//    }
//
//    /**
//     * @param int Skip? 1 = skip, 0 = not skip
//     * @return static
//     */
//    public function skip($skip = 1) {
//        $this->_settings['skip'] = $skip;
//        return $this;
//    }
//
//    /**
//     * @return int disabled? 1 = $disabled, 0 = not $disabled
//     * @return static
//     */
//    public function disabled($disabled = 1) {
//        $this->_settings['disabled'] = $disabled;
//        return $this;
//    }
//
//    public function hidden($hidden = 1) {
//        $this->_settings['hidden'] = $hidden;
//        return $this;
//    }
//
//    /**
//     *
//     * @return static
//     */
//    public function readOnly($readonly = 1) {
//        $this->_settings['readonly'] = $readonly;
//        return $this;
//    }
//
//    /**
//     * Set this form element translatable
//     *
//     * @param int $translate
//     * @return static
//     */
//    public function translatable($translate = 1) {
//        $this->_settings['translatable'] = $translate;
//        return $this;
//    }
//
//    /**
//     * Is this field translatable
//     *
//     * @return boolean
//     */
//    public function isTranslatable() {
//        return 1 === $this->get('translatable');
//    }
//
//    /**
//     *
//     * @param type $langs
//     * @return \LT\Form\Element\Base
//     */
//    public function setTranslatableLangs($langs) {
//        if (is_array($langs)) {
//            $this->_translatableLangs = $langs;
//        } else {
//            $this->_translatableLangs = explode(',', $langs);
//        }
//        return $this;
//    }
//
//    /**
//     * return null instead of empty
//     * @return static
//     */
//    public function emptyToNull() {
//        $this->_settings['empty_to_null'] = 1;
//        return $this;
//    }
//
//    /**
//     * return null instead of blank (0 is not blank)
//     * @return static
//     */
//    public function blankToNull() {
//        $this->_settings['blank_to_null'] = 1;
//        return $this;
//    }
//

//
//    /**
//     * Get class
//     *
//     * @return type
//     */
//    public function getClass() {
//        return $this->_settings['class'];
//    }
//
//    /**
//     *
//     * @param type $title
//     * @return \LT\Form\Element\Base
//     */
//    public function title($title) {
//        $this->_settings['title'] = $title;
//        return $this;
//    }
//
//    public function getTitle() {
//        return $this->_settings['title'];
//    }
//
//    /**
//     *
//     * @param type $tag
//     * @return static
//     */

//
//    /**
//     *
//     * @return type
//     */
//    public function getTag() {
//        return $this->_settings['tag'];
//    }
//
//    /**
//     * Add auto-submit
//     *
//     * @return static
//     */
//    public function autoSubmit() {
//        return $this->addClass('auto-submit');
//    }
//
//    /**
//     * Place holder
//     *
//     * @param string $value Value
//     * @return static
//     */
//    public function placeHolder($value) {
//        $this->_settings['placeholder'] = $value;
//        return $this;
//    }
//
//    /**
//     * Assign options
//     *
//     * @param [] $options Key-value array
//     * @return static
//     */
//    public function options($options) {
//        $this->_settings['options'] = $options;
//        return $this;
//    }
//
//    /**
//     * Add options
//     *
//     * @param string[] $options Key values array
//     */
//    public function addOptions(array $options){
//        $this->_settings['options'] = array_merge($this->_settings['options'], $options);
//    }
//
//    /**
//     * @param array $options Keys that to be removed
//     */
//    public function removeOptions(array $options){
//        foreach ($options as $option) {
//            if (array_key_exists($option, $this->get('options'))) {
//                unset($this->_settings['options'][$option]);
//            }
//        }
//    }
//
//    /**
//     *
//     * @param string $description Description
//     * @param boolean $entities HTML Entities during display, use this carefully in case of XSS
//     * @return static
//     */
//    public function description($description, $entities = TRUE) {
//        $this->_settings['description']          = $description;
//        $this->_settings['description_entities'] = $entities;
//        return $this;
//    }
//
//    /**
//     * @param array $settings
//     * @return static
//     */
//    public function loadSettings($settings) {
//        foreach ($settings as $k => $v) {
//            if (!is_null($v)) {
//                $this->loadSetting($k, $v);
//            }
//        }
//        return $this;
//    }
//
//    /**
//     * Load setting
//     *
//     * @param string $name
//     * @param mixed $value
//     * @return static
//     */
//    public function loadSetting($name, $value) {
//        $this->_settings[$name] = $value;
//        return $this;
//    }
//
//    /**
//     * Get formatted value, if the element is multiple, the return should be array
//     *
//     * @return mixed
//     */
//    public function getFormattedValue() {
//        return $this->getValue();
//    }
//
//    /**
//     * Set value
//     *
//     * @param string $value
//     * @return static
//     */
//    public function value($value) {
//        $this->_settings['value'] = $value;
//        return $this;
//    }
//
//    /**
//     * Get value
//     *
//     * @return string
//     */
//    public function getValue() {
//        return $this->get('value');
//    }
//
//    /**
//     *
//     * @param string $target
//     * @param string $value
//     * @param boolean $isReverse
//     * @return static
//     */
//    public function toggleDisplay($target, $value, $isReverse = FALSE) {
//        $this->_settings['data-toggle'] = 'display';
//
//        $js = $this->equalsConditions($value, $isReverse) .
//                "this.isHidden = !condition;" .
//                "if(typeof this.setRequired !== 'undefined') {" .
//                "if(this.isHidden) {" .
//                "this.setRequired(false);" .
//                "} else {" .
//                "this.setRequired(this.\$el.data('required'));" .
//                "}" .
//                "}" .
//                "this._gen();";
//
//        return $this->onChange($target, $js);
//    }
//
//    /**
//     *
//     * @param type $value
//     * @param type $isReverse
//     * @return type
//     */
//    public function equalsConditions($value, $isReverse = FALSE) {
//        $condition = !$isReverse ? '===' : '!==';
//        return "var condition = false;" .
//                "if(\$.isArray(value)) {" .
//                "condition = (value.indexesOf(" . (is_array($value) ? json_encode($value) : "'{$value}'") . ") {$condition} true);" .
//                "} else if(typeof value === 'object') {" .
//                "condition = (Object.equals(value, " . json_encode($value) . ") {$condition} true);" .
//                "} else {" . (
//                is_array($value) ?
//                        "condition = (" . json_encode($value) . ".indexesOf(value) {$condition} true);" :
//                        "condition = (value {$condition} '{$value}');"
//                ) .
//                "}";
//    }
//
//    /**
//     *
//     * @param type $js
//     * @return \LT\Form\Element\Base
//     */
//    public function onChange($target, $js) {
//        $id = !is_null($target) ? "{$this->_namespace}{$target}" : "{$this->getNameWithNamespace()}";
//        $js = "var value = \$('#{$id}').data('lt-form-element').getValue();" . $js;
//        if (!is_array($this->get('data-onchange'))) {
//            $this->_settings['data-onchange'] = array($id => array());
//        }
//        if (!isset($this->_settings['data-onchange'][$id])) {
//            $this->_settings['data-onchange'][$id] = array();
//        }
//        array_push($this->_settings['data-onchange'][$id], $js);
//        return $this;
//    }
//
//    /**
//     * No div block when getting html, return only input / textarea / select ...
//     *
//     * @param int $value 0|1 , 1= no block, 0 = show block
//     * @return static
//     */
//    public function noblock($value = 1) {
//        $this->_settings['noblock'] = $value;
//        return $this;
//    }
//
//    /**
//     * Return type of field
//     *
//     * @return string
//     */
//    public function type() {
//        $c = explode('\\', get_called_class());
//        return end($c);
//    }
//
//    /**
//     * To prevent element been generated
//     *
//     * @param type $noprint
//     * @return \LT\Form\Element\Base
//     */
//    public function noprint($noprint = 1) {
//        $this->_settings['noprint'] = $noprint;
//        return $this;
//    }
//

//
//    /**
//     *
//     * @return type
//     */
//    public function getType() {
//        return $this->get('type');
//    }
//

//
//    public function __toString() {
//        return $this->getHTML();
//    }
//
//    /**
//     *
//     * @param int $filter Filter
//     * @param int $flag Flag
//     * @return static
//     */
//    public function filter($filter = FILTER_SANITIZE_STRING, $flag = NULL) {
//        if (is_null($filter)) {
//            $filter = FILTER_SANITIZE_STRING;
//        }
//        $this->_settings['filter'] = $filter;
//        $this->_settings['flag']   = $flag;
//        return $this;
//    }
//
//    /**
//     * Format html block
//     *
//     * @param string $html
//     * @return string
//     */
//    protected function _block($html) {
//        if (1 === $this->get('noprint')) {
//            return '';
//        }
//        if (1 === $this->get('noblock')) {
//            if (is_array($html)) {
//                $html = implode('', $html);
//            }
//            return $html;
//        }
//        $before = '<div class="form-group">';
//        $after  = '';
//        if ($this->get('column') < 12 && FALSE !== $this->get('label')) {
//            $before .= '<label class="control-label col-md-' . (12 - $this->get('column')) . ' ' . $this->get('label_block_class') . '">';
//            if ($this->get('translatable') === 1) {
//                $before .= '<div class="inline-block translatable-flags">';
//                $before .= '<div class="flag flag-default inline-block hidden" title="default" data-toggle="tooltip"></div>';
//                foreach ($this->_translatableLangs as $lang) {
//                    $before .= '<div class="flag flag-' . $lang . ' inline-block hidden" title="' . $lang . '" data-toggle="tooltip"></div>';
//                }
//                $before .= '</div> ';
//            }
//            if ($this->get('required') === 1) {
//                $before .= '<span class="text-danger" >*</span> ';
//            }
//            $before .= '<span>' . $this->get('label') . '</span>' . ( $this->get('label_sup') ? '<sup class="' . $this->get('label_sup_class') . '">' . $this->get('label_sup') . '</sup>' : '' ) . '</label>';
//        } else {
//            $this->column(12);
//        }
//        $before .= '<div class="col-md-' . $this->get('column') . ' ' . $this->get('col_block_class') .  '">';
//
//        $after .= '<div class="after-input-placeholder"></div>';
//        if (($description = $this->get('description'))) {
//            $after .= '<span class="help-block">' . (TRUE === $this->get('description_entities') ? htmlentities($description) : $description ) . '</span>';
//        }
//        $after .= '</div></div>';
//
//        if (is_array($html)) {
//            $html = implode('', $html);
//        }
//
//        return $before . $html . $after;
//    }
//
//    public function getCustomAttrs() {
//        $o = [];
//        foreach ($this->_customAttrs as $attr) {
//            if (!empty($this->_settings[$attr])) {
//                $o = $this->_settings[$attr];
//            }
//        }
//        return $o;
//    }
//
//    public function setCustomAttr($key, $value) {
//        array_push($this->_customAttrs, $key);
//        $this->_settings[$key] = $value;
//        return $this;
//    }
//
//    public function setCustomAttrs($attrs) {
//        foreach ($attrs as $key => $value) {
//            $this->setCustomAttr($key, $value);
//        }
//        return $this;
//    }
//
//    /**
//     *
//     * @return \LT\Form
//     */
//    public static function getSettingsForm($namespace = 'LT_SETTINGS_', $action = 'submit') {
//        $form = new \LT\Form($namespace, $action);
//        return $form;
//    }
//
//    /**
//     * This function is called on form element edit page
//     *
//     * Since Sing's Tel insert object using javascript,
//     * the Tel Element's prefer countries can not be preset
//     *
//     * @param Form $form Form object
//     * @param mixed[] $values Values
//     * @return void
//     */
//    public static function prepareSettingsForm(\LT\Form $form, array $values){
//        return;
//    }
//
//
//    /**
//     * @param string $class
//     *
//     * @return $this
//     */
//    public function colBlockClass($class){
//        $this->_settings['col_block_class'] = $class;
//        return $this;
//    }
//
//    /**
//     * @param string $class
//     *
//     * @return $this
//     */
//    public function labelBlockClass($class){
//        $this->_settings['label_block_class'] = $class;
//        return $this;
//    }
//}


//        $this->tag($tag)
//        
//        
//        
//        
//        
//        
//        
//        $value = NULL;
//        if (in_array($this->getTag(), ['textarea', 'label', 'select', 'div', 'span'])) {
//            $value = $this->getValue();
//            $this->value(NULL);
//        }
//        $before = $after  = '';
//        if (is_array($this->get('addon'))) {
//            $before .= '<div class="input-group">';
//            foreach ($this->get('addon') as $addon) {
//                switch ($addon['type']) {
//                    case 'icon-button':
//                        $s = '<span class="input-group-btn"><button class="btn btn-secondary ' . $addon['buttonStyle'] . '" type="button"><i class="'
//                                . $addon['arg']
//                                . '"></i></button></span>';
//                        break;
//                    case 'icon':
//                    case 'text':
//                    default:
//                        $s = '<span class="input-group-addon"><i class="'
//                                . ('icon' === $addon['type'] ? $addon['arg'] : '') . '"> '
//                                . ('text' === $addon['type'] ? $addon['arg'] : '')
//                                . '</i></span>';
//                        break;
//                }
//
//                if ($addon['pendTo'] === 'prepend') {
//                    $before .= $s;
//                } else if ($addon['pendTo'] === 'append') {
//                    $after .= $s;
//                }
//            }
//            $after .= '</div>';
//        }
//
//        $tag = array();
//        if ($this->get('translatable') === 1) {
//            foreach ($this->_translatableLangs as $lang) {
//                $attrs = $this->allAttrs();
//                if (isset($attrs['id'])) {
//                    $attrs['id'] .= "-{$lang}";
//                }
//                if (isset($attrs['name'])) {
//                    $attrs['name'] .= "[{$lang}]";
//                }
//                $attrs['class']        .= ' hidden';
//                $attrs['translatable'] = $lang;
//                unset($attrs['required']);
//                if (isset($attrs['value'])) {
//                    if (is_array($attrs['value'])) {
//                        $attrs['value'] = isset($attrs['value'][$lang]) ? $attrs['value'][$lang] : '';
//                    } else {
//                        unset($attrs['value']);
//                    }
//                }
//                array_push($tag, $this->_tag($this->getTag(), $attrs, is_array($value) ? (isset($value[$lang]) ? $value[$lang] : '') : NULL));
//            }
//            $attrs = $this->allAttrs();
//            if (isset($attrs['name'])) {
//                $attrs['name'] .= '[default]';
//            }
//            $attrs['translatable'] = 'default';
//            if (isset($attrs['value']) && is_array($attrs['value'])) {
//                $attrs['value'] = $attrs['value']['default'];
//            }
//            $html = $this->_tag($this->getTag(), $attrs, is_array($value) ? $value['default'] : $value);
//            array_push($tag, $html);
//            $tag  = $before . implode('', $tag) . $after;
//        } else {
//            array_push($tag, $before . $this->_tag($this->getTag(), $this->allAttrs(), $value) . $after);
//        }
//        return $this->_block($tag);
