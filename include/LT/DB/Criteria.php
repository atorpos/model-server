<?php

namespace LT\DB;

class Criteria {
    
    /**
     *
     * @var string statement
     */
    protected $_s;

    /**
     *
     * @var [] argument list
     */
    protected $_a = [];

    public function __construct($statement, $args = []) {
        $this->_s = $statement;
        $this->_a = $args;
    }
    
    public function args(){
        return $this->_a;
    }


    public function __toString() {
        return $this->_s;
    }
}
