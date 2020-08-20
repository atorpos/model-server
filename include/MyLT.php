<?php

class MyLT extends LT {

    protected static function _onBeforeRoute() {

//        $page   = 'PUBLIC';
//        $cookie = filter_input(INPUT_COOKIE, 'user');
//        if (!empty($cookie)) {
//            $key  = \LT\Config::value('web.cookie_secret');
//            $data = \LT\Security\AES::quickDecrypt($key, $cookie);
//            $user = json_decode($data, TRUE);
//            if (json_last_error() === JSON_ERROR_NONE) {
//                if ($user['role'] === '1') {
//                    $page = 'ADMIN';
//                } else if ($user['role'] === '2') {
//                    $page = 'CONTRACTOR';
//                } else if ($user['role'] === '3') {
//                    $page = 'SUPPLIER';
//                }
//            }
//        }
//        
//        define('LT_ENTRY', $page);
//        define('LT_DEFAULT_ENTRY', 'SHARE');
    }

}
