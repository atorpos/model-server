<?php

namespace LT\Network;

class Client {

	/**
	 * simple validation for CIDR
	 * 
	 * @param type $cidr
	 * @return boolean
	 */
	public static function isCIDR($cidr = NULL) {
		$matches = [];
		preg_match('#([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\/([0-9]{1,2})#', $cidr, $matches);
		if (count($matches) < 6) {
			return FALSE;
		}
		for ($i = 1; $i <= 4; $i++) {
			if ($matches[$i] < 0 || $matches[$i] > 255) {
				return FALSE;
			}
		}
		if (!in_array($matches[5], [0, 8, 16, 24, 32])) {
			return FALSE;
		}
		return TRUE;
	}

	public static function inCIDR($ip, $cidr) {
		list($subnet, $mask) = explode('/', $cidr);
        // ADD quote () for ip2long($ip) & ~((1 << (32 - $mask)) - 1)
		return (ip2long($ip) & ~((1 << (32 - $mask)) - 1) ) == ip2long($subnet);
	}

	/**
	 * Get client IP address
	 * 
	 * @staticvar string $ip
	 * @return string
	 */
	public static function ip() {
		static $ip = NULL;
		if (is_null($ip)) {
			foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $n) {
				$cur2ip = filter_input(INPUT_SERVER, $n);
				if (!$cur2ip) {
					continue;
				}
				$curip = explode('.', $cur2ip);
				if (count($curip) !== 4) {
					// if they've sent at least one invalid IP, break out
					break;
				}
				foreach ($curip as $sup) {
					if (($sup = intval($sup)) < 0 or $sup > 255) {
						break 2;
					}
				}
				$curip_bin = $curip[0] << 24 | $curip[1] << 16 | $curip[2] << 8 | $curip[3];
				foreach (array(
			// hexadecimal ip  ip mask
			array(0x7F000001, 0xFFFF0000), // 127.0..
			array(0x0A000000, 0xFFFF0000), // 10.0..
			array(0xC0A80000, 0xFFFF0000), // 192.168..
				) as $ipmask) {
					if (($curip_bin & $ipmask[1]) === ($ipmask[0] & $ipmask[1])) {
						break 2;
					}
				}
				return $ip = $cur2ip;
			}
			$ip = filter_input(INPUT_SERVER, 'REMOTE_ADDR');
		}

		return $ip;
	}
	
	public static function isIPv4($ip) {
		return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
	}

}
