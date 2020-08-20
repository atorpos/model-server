<?php

namespace LT;

/**
 * @property-read \LT\View $view view engine
 * 
 * @method void get() Description
 * @method void post() Description
 * @method void put() Description
 * @method void delete() Description
 * @method void cli() Description
 */
abstract class Action {

    public static $request_time = NULL;
    public static $request_id   = NULL;
    protected $_error           = [];
    protected $_request;
    protected $_response;
    protected $_view;

    public function __construct() {
        self::$request_time = time();
        self::$request_id   = \LT\Security\UUID::gen();
    }

    public function ok($payload = array(), $message = 'Success') {
        Response::code(200, $message, $payload);
    }

    public function okRedirectURL($url, $message = '') {
        $payload = array(
            'LT_REDIRECT' => $url,
        );
        Response::code(200, $message, $payload);
    }

    public function okRedirect($action, $message = '') {
        $this->okRedirectURL(Core::url($action), $message);
    }

    public function okReload($message = '') {
        $payload = array(
            'LT_RELOAD' => '1',
        );
        Response::code(200, $message, $payload);
    }

    public function fail($payload = array(), $message = 'Failure') {
        Response::code(400, $message, $payload);
    }
    
    public function validationErrors($errors, $payload = array()) {
        $message = implode('<br />', $errors);
        Response::code(400, $message, $payload);
    }

    public function error($payload = array(), $message = 'Error') {
        Response::code(500, $message, $payload);
    }

    public function badRequest($payload = array(), $message = 'Bad Request') {
        Response::code(400, $message, $payload);
    }

    public function dump($var) {
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $this->ok('<pre>' . json_encode($var, JSON_PRETTY_PRINT) . '</pre>');
        } else {
            echo '<pre>' . json_encode($var, JSON_PRETTY_PRINT) . '</pre>';
//            exit;
        }
    }

    public function redirectURL($url, $permanent = FALSE) {
        if ($permanent) {
            http_response_code(301);
        } else {
            http_response_code(302);
        }
        header('Location: ' . $url);
    }

    public function redirect($action, $permanent = FALSE) {
        $this->redirectURL(Core::url($action));
    }

//    public function get() {
//        Response::notImplemented();
//    }
//
//    public function post() {
//        Response::notImplemented();
//    }
//
//    public function put() {
//        Response::notImplemented();
//    }
//
//    public function delete() {
//        Response::notImplemented();
//    }


    public function __get($name) {
        if ($name === 'view') {
            if (is_null($this->_view)) {
                $this->_view = new View();
            }
            return $this->_view;
        }
    }

}
