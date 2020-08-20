<?php

namespace LT\Form\Element;

class Textarea extends Base {

    use Input;

    public function __construct($name = NULL, $label = NULL, $defaultValue = NULL) {
        parent::__construct($name, $label, $defaultValue);

        $this->set('tag', 'textarea')
                ->attr('type', 'textarea')
                ->attr('name', $name)
                ->addClass('lt-form-element lt-form-element-textarea');
    }

    public function validate($value) {
        if ($this->get('required') && (is_null($value) || ($value == ''))) {
            return $this->_error(':label: is mandatory');
        }

        return TRUE;
    }

    public function value($value) {
        $this->set('value', $value);
        $this->inner(htmlentities($value));

        return $this;
    }

    public function rows($number) {
        $this->attr('rows', $number);

        return $this;
    }

}
