<?php

namespace LT\Security;

class Misc {

    public static function random($length, $seed = '23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ') {
        $L = strlen($seed);
        $o = '';
        for ($i = 0; $i < $length; $i++) {
            $o .= $seed[rand(0, $L - 1)];
        }
        return $o;
    }

}
