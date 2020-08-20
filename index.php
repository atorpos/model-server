<?php
    define('LT', '3.0');
    define('LT_APP_DIR', __DIR__ . DIRECTORY_SEPARATOR);
    define('LT_INC_DIR', LT_APP_DIR . 'include' . DIRECTORY_SEPARATOR);
    define('LT_CFG_DIR', LT_APP_DIR . 'config' . DIRECTORY_SEPARATOR);
    define('LT_TPL_DIR', LT_APP_DIR . 'template' . DIRECTORY_SEPARATOR);
    define('LT_DIR', LT_INC_DIR . 'LT' . DIRECTORY_SEPARATOR);
    define('LT_CACHE_DIR', LT_APP_DIR . 'cache' . DIRECTORY_SEPARATOR);
    define('LT_LOG_DIR', LT_APP_DIR . 'logs' . DIRECTORY_SEPARATOR);
    define('LT_STORAGE_DIR', LT_APP_DIR . 'storage' . DIRECTORY_SEPARATOR);

    require LT_DIR . 'LT.php';

    try {
        \MyLT::run();
    } catch (\AW\Exception $e) {
        if ((\LT\Config::value('core.mode') == 'web') && in_array($e->getCode(), array('404'))) {
            (new \LT\View(':error/' . $e->getCode()))->output();
        } else {
            \LT\Response::code($e->getCode(), $e->getMessage(), $e->getData());
        }
    } catch (\LT\Exception $e) {
        \LT\Logger::error($e);
        \LT\Response::badRequest($e->getMessage());
    } catch (\Exception $e) {
        \LT\Logger::error($e);
        \LT\Response::badRequest($e->getMessage());
    //	echo '<pre>', var_dump($e), '</pre>';
        exit;
        echo '<pre>', var_dump($e->getMessage()), '</pre>';
        echo '<pre>', var_dump($e->getTrace()), '</pre>';
    //    \LT::error($e->getMessage());
    }
