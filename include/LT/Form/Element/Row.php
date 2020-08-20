<?php

namespace LT\Form\Element;

class Row extends Base {

    protected static $_rowNo = 1;

    public function __construct($name, $label = NULL, $elements = NULL) {
        if (is_null($name)) {
            $name = 'lt_form_row_' . static::$_rowNo++;
            if (is_null($label)) {
                $label = '';
            }
        }
        parent::__construct($name, $label, NULL);
        $this->inner($elements);
        $this->set('tag', 'div')
                ->addClass('row');
    }

    public function html() {

        if (is_array($this->_settings['inner'])) {
            foreach ($this->_settings['inner'] as $element) {
                if (is_subclass_of($element, '\\LT\Form\\Element\\Base')) {
                    /* @var $element \LT\Form\Element\Base */
                    $element->noLabel();
                }
            }
        }
        return parent::html();
    }

    public function validate($value) {
        return TRUE || $value;
    }

    public function getFields() {
        $names = array();
        if (is_array($this->_settings['inner'])) {
            foreach ($this->_settings['inner'] as $element) {
                if (is_subclass_of($element, '\\LT\Form\\Element\\Base')) {
                    /* @var $element \LT\Form\Element\Base */
                    $names[$element->getName()] = $element;
                }
            }
        }
        return $names;
    }

//    public function __construct($elements, $label = NULL, $name = NULL) {
//
//        $this->inner($elements);
//        $this->_settings['name'] = $name;
//        $this->label($label);
////        $this->defaultValue($default);
//        $this->set('tag', 'div');
//    }
}
