<?php

namespace LT\Form;

class Validator {

    protected $_cf;
    protected $_f      = array();
    protected $_errors = array();

    public function __construct($fields) {
        $this->_f = $fields;
    }


    
    
    
    
    /**
     * Validate form
     * 
     * @return boolean
     */
    public function validate() {

        $this->_errors = array();


        foreach ($this->_f as $f) {

            $this->_cf = $f;
            $value     = $f['value'];
            $type      = $f['type'];


            if (!empty($value) || (($value !== '') && !is_null($value))) {

                if (isset($f['options'])) {

                    if (!is_array($f['options'])) {
                        LT::error('field options must be array.');
                    }

                    if (!isset($f['other_options'])) {
                        $f['other_options'] = array();
                    }

                    if (isset($f['multi']) && $f['multi']) {
                        $value = array_filter($value);
                        if (!is_array($value)) {
                            LT::debug($value);
                        }
                        foreach ($value as $value2) {
                            if (!isset($f['options'][$value2]) && !in_array($value2, $f['other_options'])) {
                                $this->_addError("Please select {title}");
                                continue 2;
                            }
                        }
                    } elseif (!isset($f['options'][$value]) && !in_array($value, $f['other_options'])) {
                        $this->_addError("Please select {title}");
                        continue;
                    }
                }
                if (($type == 'date') && !self::isDate($value)) {
                    $this->_addError('Please enter a valid {title}, format shoud be 2010-10-30');
                    continue;
                }
                if ($type == 'time') {
                    if (isset($f['maxlength']) && ($f['maxlength'] == 5)) {
                        if (!self::isTime($value, 'hm')) {
                            $this->_addError('Please enter a valid {title}, format shoud be 12:59');
                            continue;
                        }
                    } else {
                        if (!self::isTime($value)) {
                            $this->_addError('Please enter a valid {title}, format shoud be 12:59:59');
                            continue;
                        }
                    }
                }
                if ($type == 'password') {
                    if (!preg_match("#[0-9]+#", $value)) {
                        $this->_addError('Password must contain Number');
                        continue;
                    }
                    if (!preg_match("#[a-z]+#", $value)) {
                        $this->_addError('Password must contain Letter');
                        continue;
                    }
                    if (!preg_match("#[A-Z]+#", $value)) {
                        $this->_addError('Password must contain Capital Letter');
                        continue;
                    }
                }
                if (($type == 'datetime') && !self::isDateTime($value)) {
                    $this->_addError('Please enter a valid {title}, format shoud be 2010-10-30 12:59:59');
                    continue;
                }
                if (($type == 'email') && !self::isEmail($value)) {
                    $this->_addError('Please enter a valid {title}');
                    continue;
                }
                if (($type == 'color') && !self::isColor($value)) {
                    $this->_addError('Please enter a valid {title}');
                    continue;
                }
                if (($type == 'ip') && !self::isIP($value)) {
                    $this->_addError('Please enter a valid {title}');
                    continue;
                }
                if (($type == 'url') && !self::isURL($value)) {
                    $this->_addError('Please enter a valid {title}');
                    continue;
                }
                if (($type == 'login') && !self::isAlphaDash($value)) {
                    $this->_addError('Please enter a valid {title}');
                    continue;
                }
                if (($type == 'file') && file_exists($value['tmp_name'])) {
                    $this->_addError('Please select a valid file in {title}');
                    continue;
                }
                if (isset($f['validate']) && ($validate = $f['validate'])) {

                    if (($validate == 'digits') && !ctype_digit((string) $value)) {
                        $this->_addError("{title} must contain digits only");
                        continue;
                    }
                    if (($validate == 'amount') && !self::isAmount($value)) {
                        $this->_addError("{title} must contain amount only");
                        continue;
                    }
                    if (($validate == 'alnum') && !ctype_alnum($value)) {
                        $this->_addError("{title} must contain letters and numbers only");
                        continue;
                    }
                    if (($validate == 'nohtml') && ($value != strip_tags($value))) {
                        $this->_addError("{title} cannot contains HTML");
                        continue;
                    }
                }
                if (isset($f['min']) && ($f['min'] > $value)) {
                    $this->_addError("{title} cannot less than {min}", array('{min}' => $f['min']));
                    continue;
                }
                if (isset($f['max']) && ($f['max'] < $value)) {
                    $this->_addError("{title} cannot greater than {max}", array('{max}' => $f['max']));
                    continue;
                }
                if (isset($f['minlength']) && (strlen($value) < $f['minlength'])) {
                    $this->_addError("{title} cannot less than {minlength} characters", array('{minlength}' => $f['minlength']));
                    continue;
                }
                if (isset($f['maxlength']) && (strlen($value) > $f['maxlength'])) {
                    $this->_addError("{title} cannot more than {maxlength} characters", array('{maxlength}' => $f['maxlength']));
                    continue;
                }
                if (isset($f['number'])) {
                    if (($f['number'] == 'nozero') && ($value == '0')) {
                        $this->_addError("{title} cannot be zero");
                    } elseif (($f['number'] == 'positive') && ($value <= '0')) {
                        $this->_addError("{title} must contain positive value only");
                    } elseif (($f['number'] == 'negative') && ($value >= '0')) {
                        $this->_addError("{title} must contain negative value only");
                    }
                }
            } elseif (isset($f['required']) && $f['required']) {

                $this->_addError('Please enter a valid {title}');
                continue;
            }
        }
        return empty($this->_errors);
    }

    private function _addError($msg, $vals = array()) {
        $msg = LT::text($msg);
        $f   = $this->_cf;
        if (is_array($f)) {
            $msg = str_replace(array('{name}', '{title}'), array(LT::text($f['name']), LT::text($f['title'])), $msg);
            if (!empty($vals)) {
                $msg = str_replace(array_keys($vals), array_values($vals), $msg);
            }
        }
        $this->_errors[$f['name']] = $msg;
    }

    /**
     * Get errors in array
     * 
     * @return array
     */
    public function errors() {
        return $this->_errors;
    }

}
