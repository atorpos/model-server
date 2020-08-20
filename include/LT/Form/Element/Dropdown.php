<?php

namespace LT\Form\Element;

class Dropdown extends Base {

    use Input;

    public function __construct($name = NULL, $label = NULL, $defaultValue = NULL) {
        parent::__construct($name, $label, $defaultValue);

        $this->set('tag', 'select')
                ->attr('name', $name)
                ->addClass('lt-form-element lt-form-element-dropdown');
    }

    public function validate($value) {

        if ($this->get('required') && (is_null($value) || ($value == ''))) {
            return $this->_error(':label: is mandatory');
        }

        $options = $this->get('options', array()) + $this->get('options_other', array());
        if (!empty($options)) {
            $_vs = $this->get('multi') ? $value : array($value);
            foreach ($_vs as $_v) {
                if (!isset($options[$_v])) {
                    return $this->_error(':label: contain invalid value');
                }
            }
            unset($_vs, $_v);
        }

        return TRUE;
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

    protected function _updateOptions() {
        $options = $this->get('options');
        if (empty($options)) {
            return;
        }
        $selected = $this->get('value');
        $elements = array();
        foreach ($options as $value => $label) {
            $elements[] = $this->_option($value, $label, $selected);
        }
        $this->inner($elements);
    }

    protected function _option($value, $label, $selected = NULL) {

        $attrs = array(
            'value' => $value,
        );
        if ((string) $value === (string) $selected) {
            $attrs['selected'] = 'selected';
        }
        return Base::tag('option', $attrs, $label);
    }

}
