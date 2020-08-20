<?php

namespace LT\Form\Element;

class Raw extends Base {

    protected static $_rowNo = 1;

    /**
     * 
     * @param string $html
     * @return static
     */
    public static function content($html) {
        return static::create(NULL, NULL, $html);
    }

    public function __construct($name = NULL, $label = NULL, $elements = NULL) {
        if (is_null($name)) {
            $name = 'lt_form_raw_' . static::$_rowNo++;
            if (is_null($label)) {
                $label = '';
            }
        }
        parent::__construct($name, $label, NULL);

        $this->inner($elements);
        $this->set('tag', 'div');
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
        return array();
    }

}
