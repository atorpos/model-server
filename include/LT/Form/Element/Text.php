<?php

namespace LT\Form\Element;

class Text extends Base {

    use Input;

    public function __construct($name = NULL, $label = NULL, $defaultValue = NULL) {
        parent::__construct($name, $label, $defaultValue);

        $this->set('tag', 'input')
                ->attr('type', 'text')
                ->attr('name', $name)
                ->addClass('lt-form-element lt-form-element-text');
    }

    public function validate($value) {
        if ($this->get('required') && (is_null($value) || ($value == ''))) {
            return $this->_error(':label: is mandatory');
        }

        return TRUE;
    }

}
