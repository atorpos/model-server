<?php

namespace LT\Form\Theme;

use \LT\Form\Element;

class Metronic extends Base {

	const LAYOUT_HORIZONTAL_FORM = 'horizontal_form';

	/**
	 * 
	 * @return \static
	 */
	public static function horizontalForm() {
		$o = new static(static::LAYOUT_HORIZONTAL_FORM);
		return $o;
	}

	/**
	 * 
	 * @param \LT\Form\Element\Base $element
	 */
	public function beforeRender($element) {

		if (($element->get('tag') == 'input')) {
			if (in_array($element->get('type'), array('text', 'password'))) {
				$element->addClass('form-control');
			}
		}

		$ps = explode('\\', get_class($element));
		switch (strtolower(end($ps))) {
			case 'dropdown':
			case 'textarea':
				$element->addClass('form-control');
				break;
			case 'checkboxes':
				$element = $this->_checkboxes($element);
				break;
			case 'radios':
				$element = $this->_radios($element);
				break;
			case 'sectiontitle':
				$element->set('no-form-group', 1);
				$element->addClass('form-section');
				break;
//            case 'row':
//                $element = $this->_radios($element);
//                break;

			default:
				break;
		}
	}

	protected function _radios($element) {
		$element->addClass('mt-radio-inline');
		$items = array();
		foreach ($element->get('inner') as $item) {
			$item['attrs']['class']	 = 'mt-radio mt-radio-outline';
			$item['inner'][]		 = Element\Base::tag('span');

			$items[] = $item;
		}
		$element->inner($items);
		return $element;
	}

	protected function _checkboxes($element) {
		$element->addClass('mt-checkbox-list');
		$items = array();
		foreach ($element->get('inner') as $item) {
			$item['attrs']['class']	 = 'mt-checkbox mt-checkbox-outline';
			$item['inner'][]		 = Element\Base::tag('span');

			$items[] = $item;
		}
		$element->inner($items);
		return $element;
	}

	public function renderField($element) {

		$innerElements = array();
		if (is_subclass_of($element, '\\LT\Form\\Element\\Base') && is_array($element->get('inner'))) {
			foreach ($element->get('inner') as $_element) {
				if (is_subclass_of($_element, '\\LT\Form\\Element\\Base')) {
					/* @var $element \LT\Form\Element\Base */
					$innerElements[] = $this->_tag('div', ['class' => 'col-md-' . $_element->get('col')], $_element);
				} else {
					$innerElements[] = $_element;
				}
			}
		} else {
			$innerElements = $element->get('inner');
		}

		$tagName = $element->get('tag');
		$attrs	 = $element->get('attrs');

		if ($element->get('help')) {
			$helpBlock = '<span class="help-block">' . $element->get('help') . '</span>';
			if ((get_class($element) == 'LT\\Form\\Element\\Row')) {
				$helpBlock = $this->_tag('div', ['class' => 'col-md-12'], $helpBlock);
			}
			if ((get_class($element) != 'LT\\Form\\Element\\Textarea')) {
				$innerElements[] = $helpBlock;
			}
		}

		$html = $this->_tag($tagName, $attrs, $innerElements);

		if ($element->get('help')) {
			if ((get_class($element) == 'LT\\Form\\Element\\Textarea')) {
				$html .= $helpBlock;
			}
		}

		if ($element->get('translatable')) {
			
		}

		return $html;
	}

	/**
	 * 
	 * @param \LT\Form\Element\Base $element
	 */
	public function render($element) {

		$fieldHTML = $this->renderField($element);

//        var_dump(htmlentities($fieldHTML)); 
		if ($element->get('no-label')) {
			return $fieldHTML;
		}

		$labelText	 = $element->get('label');
		$labelClass	 = 'control-label col-md-2';
		if ($element->get('required')) {
			$labelText .= '<small class="required">*</small>';
//			$labelClass .= ' required';
		}

		$labelHTML = $this->_tag('label', ['class' => $labelClass], $labelText);

		if ($element->get('no-form-group')) {
			$html = $fieldHTML;
		} else {
			$html = $this->_formGroup($labelHTML, $fieldHTML, $element->get('col'));
		}


		return $html;
	}

	public function _formGroup($label, $element, $elementColumn = 4) {
		$fieldBlock = $this->_tag('div', ['class' => 'col-md-' . $elementColumn], $element);
		return $this->_tag('div', ['class' => 'form-group'], $label . $fieldBlock);
	}

	public function _form() {
		
	}

}

//    
//    const CLASS_NAME = 'LT_Form_Generator_Bootstrap';
//    
//    public $labelClass = 'control-label';
//
//    public function __construct($fields) {
//
//        if (is_object($fields) && is_a($fields, 'LT_Form')) {
//            $fields->loadSubmit();
//            $fields = $fields->fieldsArray();
//        }
//        
//        foreach ($fields as $k => $f) {
//            if (!in_array($f['type'], array('checkbox', 'radio'))) {
//                if (isset($f['attrs'], $f['attrs']['class'])) {
//                    $f['attrs']['class'] .= ' form-control';
//                } else {
//                    $f['attrs']['class'] = 'form-control';
//                }
//                $fields[$k] = $f;
//            }
//            if (isset($f['attrs']['placeholder'])) {
//                $fields[$k]['attrs']['placeholder'] = LT::text($f['attrs']['placeholder']);
//            }
//        }
//
//        parent::__construct($fields);
//    }
//
//    protected function _choose($f) {
//        $f['attrs']['class'] = trim('lt-choose ' . $f['attrs']['class']);
//        $h = '<div class="btn-group" data-toggle="buttons">';
//        foreach ($f['options'] as $k => $v) {
//            $v = str_replace(' ', '&nbsp;', htmlentities($v, ENT_COMPAT, 'UTF-8'));
//            $h .= '<label class="btn btn-default'
//                    . ($f['value'] == $k ? ' active' : '') . '">'
//                    . '<input type="' . $f['type']
//                    . '" name="' . $f['name']
//                    . '" value="' . $k . '"'
//                    . ($f['value'] == $k ? ' checked="checked"' : '') . ' />'
//                    . $v . '</label>';
//        }
//        $h .= '</div>';
//        return $h;
//    }
//
//    public function label($name) {
//        $f = $this->_f[$name];
//        $html = '<label for="' . htmlspecialchars($f['attrs']['id']) . '" class="' . $this->labelClass . '">' . LT::text($f['title']) . '</label>';
//        if ($this->_echo) {
//            echo $html;
//        }
//        return $html;
//    }
//}
