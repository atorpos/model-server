<?php

namespace LT;

class Autoloader {

    /**
     * @var array the search paths of auto loader, default LT_INC_DIR
     */
    protected static $_searchPaths = array(
        LT_INC_DIR
    );

    /**
     * add custom include search path to autoloader
     * @param string $dir
     */
    public static function addSearchPath($path) {

        if (empty($path)) {
            Exception::minor('try to add empty include path to autoloader');
        }

        // convert incorrect directory separator, e.g. \(windows) => /(unix or linux)
        $p = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
        if (substr($p, -1) != DIRECTORY_SEPARATOR) {
            $p .= DIRECTORY_SEPARATOR;
        }

        // prevent duplicate search path
        if (!in_array($p, static::$_searchPaths)) {
            static::$_searchPaths[] = $p;
        }
    }

    /**
     * replace all existing search paths with new paths
     * @param array $paths
     */
    public static function setSearchPaths(array $paths) {

        static::$_searchPaths = array(LT_INC_DIR);

        foreach ($paths as $path) {
            static::addSearchPath($path);
        }
    }

    /**
     * try to include the script file and related language file
     * @param string $path
     * @return boolean
     */
    public static function tryInclude($path) {

        $mainFile = str_replace('\\', DIRECTORY_SEPARATOR, $path);
        $dir      = dirname($mainFile) . DIRECTORY_SEPARATOR;
        $lang     = Core::lang();
        $langFile = $dir . 'LT_LANG' . DIRECTORY_SEPARATOR . $lang . '.php';
        if (file_exists($langFile)) {
            include $langFile;
        }
        if (file_exists($mainFile)) {
            include $mainFile;
            return TRUE;
        }
        return FALSE;
    }

    /**
     * triggered by class or interface not found
     * 
     * the following line added on the end of file
     * spl_autoload_register(array('\LT\Autoloader', 'load'));
     * 
     * @link http://php.net/manual/en/function.spl-autoload-register.php about spl_autoload_register
     * 
     * @param string $class class name
     * @return boolean
     */
    public static function load($class) {

        $found = FALSE;

        // try to find the file in all search paths
        foreach (self::$_searchPaths as $iP) {

            // try class name delimiter \ and _
            foreach (array('\\', '_') as $d) {

                $ps = explode($d, $class);
                $sN = end($ps);

                // try possible files
                while (count($ps)) {

                    $rP = implode(DIRECTORY_SEPARATOR, $ps) . DIRECTORY_SEPARATOR;

                    if (($found = static::tryInclude($iP . $rP . $class . '.php'))) {
                        break;
                    }
                    if (($found = static::tryInclude($iP . $rP . $sN . '.php'))) {
                        break;
                    }
                    array_pop($ps);
                }

                // not found in folder, try upper level directory
                if (!$found) {
                    static::tryInclude($iP . $class . '.php');
                }

                return (class_exists($class, FALSE) || interface_exists($class, FALSE));
            }
        }

        return FALSE;
    }

}

spl_autoload_register(array('\LT\Autoloader', 'load'));
