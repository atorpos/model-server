<?php

namespace LT\Form\Element;

class Hidden extends Base {

    use Input;

    public function __construct($name = NULL, $label = NULL, $defaultValue = NULL) {
        parent::__construct($name, $label, $defaultValue);

        $this->set('tag', 'input')
                ->attr('type', 'hidden')
                ->attr('name', $name)
                ->set('no-label', 1);
    }

    public function validate($value) {
        if ($this->get('required') && (is_null($value) || ($value == ''))) {
            return $this->_error(':label: is mandatory');
        }

        return TRUE;
    }

}
