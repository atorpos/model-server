<?php

namespace LT\Form\Theme;

class Base {

    const LAYOUT_DEFAULT = 'default';

    protected $_layout = '';

    public function __construct($layout = self::LAYOUT_DEFAULT) {
        $this->layout($layout);
    }

    public function layout($layout) {
        $this->_layout = $layout;
    }

    /**
     * 
     * @param \LT\Form\Element\Base $element
     */
    public function beforeRender($element) {
        
    }

    public function renderField($element) {
        return $this->_tag($element->get('tag'), $element->get('attrs'), $element->get('inner'));
    }
    
    /**
     * 
     * @param \LT\Form\Element\Base $element
     */
    public function render($element) {
        $html = $this->renderField($element);

        return $html;
    }

    /**
     *
     * @staticvar array $ST
     * @param string $name
     * @param array $attrs
     * @param string $inner
     * @return string HTML
     */
    protected function _tag($name, array $attrs = array(), $inner = NULL) {
        // single tag without end tag (e.g. <input ... />)
        static $ST = array(
            'input',
            'hr',
        );

        if (is_array($name)) {
            return $this->_tag($name['tag'], $name['attrs'], $name['inner']);
        }

        $tag  = strtolower($name);
        $html = '<' . $tag;
        foreach ($attrs as $_k => $_v) {
            if (($_k == 'class') && is_array($_v)) {
                $_v = implode(' ', $_v);
            }
            $html .= ' ' . $_k . '="' . htmlentities($_v) . '"';
        }

        if (is_array($inner)) {
            $elements = $inner;
            $inner    = '';
            foreach ($elements as $element) {
                if (is_array($element)) {
                    $inner .= $this->_tag($element);
                } else {
                    $inner .= (string) $element;
                }
            }
        }

        if (in_array($tag, $ST)) {
            $html .= " /> {$inner}";
        } else {
            if (is_array($inner)) {
                $inner = implode('', $inner);
            }
            $html .= " >{$inner}</{$tag}>";
        }
        return $html;
    }

}

//
//class Base {
//
//    protected $_f    = array();
//    protected $_echo = TRUE;
//
//    public function __construct($fields) {
//        $this->_f = $fields;
//    }
//
//    public function addIDPrefix($prefix) {
//        foreach ($this->_f as $k => $v) {
//            $v['attrs']['id'] = $prefix . $v['attrs']['id'];
//            $this->_f[$k]     = $v;
//        }
//    }
//
//    protected function _choose($f) {
//        $f['attrs']['class'] = trim('lt-choose ' . $f['attrs']['class']);
//        $h                   = '';
//        foreach ($f['options'] as $k => $v) {
//            $v = str_replace(' ', '&nbsp;', htmlentities($v, ENT_COMPAT, 'UTF-8'));
//            $h .= '<label><input type="' . $f['type']
//                    . '" name="' . $f['name']
//                    . '" value="' . $f['value'] . '"'
//                    . (strval($f['value']) === strval($k) ? ' checked="checked"' : '') . ' />'
//                    . $v . '</label>';
//        }
//        return $h;
//    }
//
//    protected function _dropdown($f) {
//        $f['attrs']['class'] = trim('lt-dropdown ' . $f['attrs']['class']);
//        $h                   = '';
//        foreach ($f['options'] as $k => $v) {
//            $v = str_replace(' ', '&nbsp;', htmlentities(LT::text($v), ENT_COMPAT, 'UTF-8'));
//            $h .= '<option value="' . $k . '"';
//            if (isset($f['multi'])) {
//                if ($f['value'] && in_array(strval($k), $f['value'])) {
//                    $h .= ' selected="selected"';
//                }
//            } else if (strval($f['value']) === strval($k)) {
//                $h .= ' selected="selected"';
//            }
//            $h .= '>' . $v . "</option>\n";
//            //LT::debug($k);
//        }
//        $result = '';
//        if (isset($f['multi'])) {
//            $f['attrs']['multiple']                  = 'multiple';
//            $f['attrs']['data-header']               = 'Toggle All / None';
//            $f['attrs']['data-selected-text-format'] = 'count>1';
//            $f['attrs']['data-size']                 = '5';
//
//            $result             .= '<input name="' . $f['attrs']['name'] . '" type="hidden" value="-" />';
//            $f['attrs']['name'] = '';
//        }
//        if (isset($f['attrs']['value']) && is_array($f['attrs']['value'])) {
//            unset($f['attrs']['value']);
//        }
//        $result .= $this->tag('select', $f['attrs'], "\n" . $h);
//        return $result;
//    }
//
//    protected function _field($f) {
//        if (in_array($f['type'], array('textarea', 'richtext'))) {
//            $value = htmlspecialchars($f['value']);
//            $attrs = $f['attrs'];
//            if (isset($attrs['value'])) {
//                unset($attrs['value']);
//            }
//            return $this->tag('textarea', $attrs, $value);
//        }
//
//        if (in_array($f['type'], array('password', 'hidden'))) {
//            $f['attrs']['type'] = $f['type'];
//        } else {
//            $f['attrs']['type'] = 'text';
//        }
//        $f['attrs']['value'] = $f['value'];
//        $f['attrs']['class'] = trim('lt-field ' . $f['attrs']['class']);
//
//        return $this->tag('input', $f['attrs']);
//    }
//
//    public function echoMode() {
//        $this->_echo = TRUE;
//    }
//
//    public function returnMode() {
//        $this->_mode = FALSE;
//    }
//
//    public function tag($name, array $attrs = array(), $inner = '') {
//        $h = '<' . $name;
//        foreach ($attrs as $k => $v) {
//            $h .= ' ' . $k . '="' . htmlspecialchars($v) . '"';
//        }
//        if (in_array($name, array('input', 'br', 'hr'))) {
//            $h .= ' />';
//        } else {
//            $h .= '>' . $inner . '</' . $name . '>';
//        }
//        return $h;
//    }
//
//    public function label($name) {
//        $f    = $this->_f[$name];
//        $html = '<label for="' . htmlspecialchars($f['attrs']['id']) . '">' . LT::text($f['title']) . '</label>';
//        if ($this->_echo) {
//            echo $html;
//        }
//        return $html;
//    }
//
//    public function hidden($name) {
//        $f         = $this->_f[$name];
//        $f['type'] = 'hidden';
//        $h         = $this->_field($f);
//        if ($this->_echo) {
//            echo $h;
//        }
//        return $h;
//    }
//
//    public function input($name) {
//        $f = $this->_f[$name];
//        if ($f['type'] == 'dropdown') {
//            $h = $this->_dropdown($f);
//        } elseif (in_array($f['type'], array('checkbox', 'radio'))) {
//            $h = $this->_choose($f);
//        } else {
//            $h = $this->_field($f);
//        }
//        if ($this->_echo) {
//            echo $h;
//        }
//        return $h;
//    }

//}
