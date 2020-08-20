<?php

namespace LT;

/**
 * @property string $scriptDirectory 
 */
class Runtime implements \ArrayAccess {

    protected $_vars = array();

    /**
     * 
     * @staticvar \LT\Runtime $o
     * @return \static
     */
    public static function current() {
        static $o = NULL;
        if (is_null($o)) {
            $o = new static;
        }
        return $o;
    }

    /**
     * 
     * @param string $name
     * @return bool
     */
    public function __isset($name) {
        return isset($this->_vars[$name]);
    }

    /**
     * 
     * @param string $name
     * @return mixed
     */
    public function __get($name) {
        if (isset($this->_vars[$name])) {
            return $this->_vars[$name];
        }
        return NULL;
    }

    /**
     * 
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value) {
        $this->_vars[$name] = $value;
    }

    /**
     * 
     * @param string $name
     */
    public function __unset($name) {
        if (!isset($this->_vars[$name])) {
            return;
        }
        unset($this->_vars[$name]);
    }

    /**
     * 
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset) {
        return isset($this->_vars[$offset]);
    }

    /**
     * 
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset) {
        if (isset($this->_vars[$offset])) {
            return $this->_vars[$offset];
        }
        return NULL;
    }

    /**
     * 
     * @param string $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value) {
        $this->_vars[$offset] = $value;
    }

    /**
     * 
     * @param string $offset
     */
    public function offsetUnset($offset) {
        if (!isset($this->_vars[$offset])) {
            return;
        }
        unset($this->_vars[$offset]);
    }

}
