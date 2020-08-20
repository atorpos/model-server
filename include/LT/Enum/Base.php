<?php

namespace LT\Enum;

class Base {

    public static function isValidCode($code) {
        return array_search(strtoupper($code), self::all());
    }

    public static function all() {
        $class     = get_called_class();
        static $_c = NULL;
        if (!isset($_c[$class]) || is_null($_c[$class])) {
            $o          = new \ReflectionClass(get_called_class());
            $_tmp       = $o->getConstants();
            $_c[$class] = array_combine(array_keys($_tmp), array_values($_tmp));
        }
        return $_c[$class];
    }

    public static function options() {
        $class     = get_called_class();
        static $_c = NULL;
        if (!isset($_c[$class]) || is_null($_c[$class])) {
            $o          = new \ReflectionClass(get_called_class());
            $_tmp       = $o->getConstants();
            $_c[$class] = array_combine(array_values($_tmp), array_keys($_tmp));
        }
        return $_c[$class];
    }
    public static function findByValue($V){
        $class = get_called_class();
        return $class;
    }


}
