<?php

namespace LT;

/**
 * The class for access configurations
 */
class Config {

    /**
     * @var array globals configurations
     */
    static $_g = array();

    /**
     * @var array user configurations
     */
    static $_u = array();

    /**
     * @var array instance array
     */
    static $_o = array();

    /**
     * @var string scope (key prefix)
     */
    private $_s = 'LT';

    /**
     * load the configuration file
     */
    public static function init() {

        $name = self::_configName();      
//        var_dump($name);exit;
        
        
        $file = LT_CFG_DIR . $name . DIRECTORY_SEPARATOR . 'config.php';
        if (!file_exists($file)) {
            Exception::config(sprintf('config file not found (%s)', $file));
        }
//        var_dump($file);exit;
        require $file;
    }

    /**
     * Register globals configurations
     * 
     * @param string $namespace The value scope (key prefix)
     * @param array $configs
     */
    public static function register($namespace, array $configs) {

//        $ns = strtoupper($namespace);
        $ns = $namespace;
        if (!isset(self::$_g[$ns])) {
            self::$_g[$ns] = array();
        }
        foreach ($configs as $k => $v) {
//            $k = strtoupper($k);

            self::$_g["$ns.$k"] = $v;
            self::$_g[$ns][$k]  = &self::$_g["$ns.$k"];
        }
    }

    /**
     * Get value from configs (User Config > Globals Config > NULL)
     * 
     * @param string $key
     * @param mixed $default
     * @return null
     */
    public static function value($key, $default = NULL) {
        return self::_get($key, $default);
    }

    /**
     * Get value from configs (User Config > Globals Config > NULL)
     * 
     * @param string $key
     * @param mixed $default
     * @return null
     */
    protected static function _get($key, $default = NULL) {
//        $k = strtoupper($key);
        $k = $key;
        if (isset(self::$_u[$k])) {
            return self::$_u[$k];
        } elseif (isset(self::$_g[$k])) {
            return self::$_g[$k];
        }
        if (0 === stripos($k, 'LT.')) {
            return $default;
        }
        return self::_get('LT.' . $k);
    }

    /**
     * retrieve the config name from domain/hostname, invalid chars(e.g. :) will be auto replaced by underscore
     * @return string
     */
    protected static function _configName() {
        foreach (array('HTTP_HOST', 'SERVER_NAME') as $n) {
            $v = filter_input(INPUT_SERVER, $n);
            if (!empty($v)) {
                return strtolower($v);
            }
        }

        return str_replace(array(':'), '_', gethostname());
    }

}
