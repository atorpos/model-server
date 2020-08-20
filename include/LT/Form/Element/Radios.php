<?php

namespace LT\Form\Element;

class Radios extends Base {

    public function __construct($name = NULL, $label = NULL, $defaultValue = NULL) {
        parent::__construct($name, $label, $defaultValue);

        $this->set('tag', 'div')
                ->addClass('lt-form-element lt-form-element-radios');
    }

    public function getSubmittedValue() {
        return filter_input(INPUT_POST, $this->getName(), FILTER_DEFAULT);
    }
    
    /**
     * 
     * @param array $items
     * @return static
     */
    public function options(array $items) {
        $this->set('options', $items);
        $this->_updateOptions();
        return $this;
    }

    /**
     * 
     * @param string $value
     * @return static
     */
    public function value($value) {
        $this->set('value', $value);
        $this->_updateOptions();
        return $this;
    }

    public function validate($value) {

        if ($this->get('required') && (is_null($value) || ($value == ''))) {
            return $this->_error(':label: is mandatory');
        }

        $options = $this->get('options', array());
        if (!isset($options[$value])) {
            return $this->_error(':label: contain invalid value');
        }

        return TRUE;
    }

    protected function _updateOptions() {
        $options = $this->get('options');
        if (empty($options)) {
            return;
        }
        $selected = $this->get('value', NULL);
        $elements = array();
        foreach ($options as $value => $label) {
            $elements[] = $this->_option($value, $label, $selected);
        }
        $this->inner($elements);
    }

    public function _option($value, $label, $selected = NULL) {

        $attrs = [
            'type'  => 'radio',
            'value' => $value,
            'name'  => $this->getName(),
        ];
        if ($value == $selected) {
            $attrs['checked'] = 'checked';
        }
        $option = Base::tag('label', [], [
                    Base::tag('input', $attrs, $label)
        ]);

        return $option;
    }

}
