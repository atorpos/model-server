<?php

namespace LT\Form\Element;

class Amount extends Text {

    public function __construct($name = NULL, $label = NULL, $defaultValue = NULL) {
        parent::__construct($name, $label, $defaultValue);

        $this->setClass('lt-form-element lt-form-element-amount');
    }

    public function validate($value) {
        parent::validate($value);

        // not mandatory
        if ($value === '') {
            return TRUE;
        }

        // check amount format
        if (!preg_match("/^-?[0-9]+(?:\.[0-9]{1,2})?$/", $value)) {
            return $this->_error(':label: is not a valid value');
        }

        return TRUE;
    }

}
