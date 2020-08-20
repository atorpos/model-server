<?php

namespace LT;

class Response {

    /**
     * Encode all string to UTF-8
     * 
     * @param mixed $value
     * @return mixed
     */
    protected static function _utf8encode($value) {
		return $value;
//        if (is_string($value)) {
//            return utf8_encode($value);
//        } elseif (is_array($value)) {
//            return array_map([get_called_class(), '_utf8encode'], $value);
//        } else {
//            return $value;
//        }
    }

    /**
     * JSON Response
     * 
     * @param array $array Raw response contact in array
     */
    public static function json($array) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(self::_utf8encode($array), JSON_PRETTY_PRINT);
        exit;
    }

    public static function code($code, $message = NULL, $payload = array()) {
        $code = (string)$code;
        if (is_string($payload)) {
            $message = $payload;
            $payload = array();
        }
        $o = array(
            'request'  => array(
                'id'   => \LT\Action::$request_id,
                'time' => (string)\LT\Action::$request_time,
            ),
            'response' => array(
                'code'    => $code,
                'message' => $message,
                'time'    => (string)time(),
            ),
//			'payload'	 => gettype($payload) === 'array' ? $payload : [get_class($payload) => get_object_vars($payload)],
            'payload'  => gettype($payload) === 'array' ? $payload : get_object_vars($payload),
        );



        switch (Config::value('core.mode')) {
            case 'web-api':
                //http_response_code($code);
                self::json($o);
                break;

            case 'web':
                //http_response_code($code);
                if (in_array($code, array('404'))) {
                    (new View(':error/' . $code))->output();
                } else {
                    self::json($o);
                }

                break;

            default:
                break;
        }
    }

    public static function ok($payload = array(), $message = 'OK') {
        self::code(200, $message, $payload);
    }

    public static function badRequest($payload = array(), $message = 'Bad Request') {
        self::code(400, $message, $payload);
    }

    public static function unauthorized($payload = array(), $message = 'Unauthorized') {
        self::code(401, $message, $payload);
    }

    public static function forbidden($payload = array(), $message = 'Forbidden') {
        self::code(403, $message, $payload);
    }

    public static function notFound($payload = array(), $message = 'Not Found') {
        self::code(404, $message, $payload);
    }

    public static function internalServerError($payload = array(), $message = 'Internal Server Error') {
        self::code(500, $message, $payload);
    }

    public static function notImplemented($payload = array(), $message = 'Not Implemented') {
        self::code(501, $message, $payload);
    }

    public static function badGateway($payload = array(), $message = 'Bad Gateway') {
        self::code(502, $message, $payload);
    }

    public static function serviceUnavailable($payload = array(), $message = 'Service Unavailable') {
        self::code(503, $message, $payload);
    }

}
