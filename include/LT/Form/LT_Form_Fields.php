<?php
/**
 * LT Framework - PHP development framework
 * NOTE: Requires PHP version 5.2 or later
 * @package LT
 * @copyright 2009 - 2013 LT Workshop Limited
 * @license http://creativecommons.org/licenses/by-nc-nd/3.0/ Attribution-NonCommercial-NoDerivs 3.0 Unported
 */

class LT_Form_Fields {
    
    private $_fs = array();
    
    private $_token = '';


    /**
     * @var LT_Form 
     */
    private $_form = NULL;
    
    public function __construct($token = '') {
        $this->_token = $token;
    }


    /**
     * Add new field
     * 
     * @param string $name Field Name
     * @param string $label Field Label
     * @param string $defaultValue Default Value
     * @return LT_Form_Field
     */
    public function add($name, $label = NULL, $default = '') {
        if ($this->_token === '') {
            $f = new LT_Form_Field($name, $label, $default);
        } else {
            $f = new LT_Form_Field($name . $this->_token, $label, $default);
            $f->rename($name);
        }
        return $this->_fs[$name . $this->_token] = $f;
    }
    
    /**
     * Add file field
     * 
     * @param string $name
     * @param string $label
     * @return LT_Form_Field
     */
    public function addFile($name, $label = NULL) {
        $f = new LT_Form_Field($name, $label, NULL);
        return $this->_fs[$name] = $f->file();
    }


    /**
     * Delete field by field name
     * 
     * @param string $name
     */
    public function delete($name) {
        unset($this->_fs[$name]);
    }
    
    /**
     * Get all field names
     * 
     * @return array
     */
    public function getNames() {
        return array_keys($this->_fs);
    }

    /**
     * Get all fields settings
     * 
     * @return array
     */
    public function getFields() {
        return $this->_fs;
    }
    
    /**
     * Replace current fields settings
     * 
     * @param mixed $fields
     */
    public function setFields($fields) {
        if (is_object($fields)) {
            $this->_fs = $fields->getFields();
        } else {
            $this->_fs = $fields;
        }
    }
    
    /**
     * Make the LT_Form from this settings
     * 
     * @param string $prefix
     * @param string $scope
     * @param mixed $default
     * @return LT_Form
     */
    public function toForm($prefix = '', $scope = NULL, $default = '') {
        if (is_null($this->_form)) {
            if (is_null($scope)) {
                $scope = 'PG';
                foreach ($this->_fs as $_f) {
                    if ($_f->isFile()) {
                        $scope = 'FPG';
                        break;
                    }
                }
            }
            $this->_form = new LT_Form($this, $prefix, $scope, $default);
        }
        return $this->_form;
    }
    

    /**
     * Get default values
     * @return array
     */
    public function getDefaultsValues() {
        return $this->toForm()->getDefaultsValues();
    }


    /**
     * Get submit values
     * @return array 
     */
    public function getValues($validate = TRUE) {
        return $this->toForm()->getValues($validate);
    }

    public function __toString() {
        return var_export($this->getFields(), TRUE);
    }
}