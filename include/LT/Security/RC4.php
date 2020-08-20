<?php

namespace LT\Security;

/**
 * @link http://en.wikipedia.org/wiki/RC4 wikipedia
 */
class RC4 {

	public static function decrypt($data, $key) {
		return self::encrypt($data, $key);
	}

	public static function encrypt($data, $key) {
		$dL	 = strlen($data);
		$kL	 = strlen($key);
		$k	 = array();
		$B	 = range(0, 255);
		$r	 = '';

		for ($i = 0; $i < 256; $i++) {
			$k[$i] = ord($key[$i % $kL]);
		}

		for ($i = $j = 0; $i < 256; $i++) {
			$j		 = ($j + $B[$i] + $k[$i]) % 256;
			$t		 = $B[$i];
			$B[$i]	 = $B[$j];
			$B[$j]	 = $t;
		}

		for ($i = $a	 = $j	 = 0; $i < $dL; $i++) {
			$a		 = ($a + 1) % 256;
			$j		 = ($j + $B[$a]) % 256;
			$t		 = $B[$a];
			$B[$a]	 = $B[$j];
			$B[$j]	 = $t;
			$r		 .= chr(ord($data[$i]) ^ ($B[($B[$a] + $B[$j]) % 256]));
		}

		return $r;
	}

}
