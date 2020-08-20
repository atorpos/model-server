<?php

namespace LT;

class Network {

	/**
	 * Check the given IP address is within CIDR or not
	 * 
	 * @param string $cidrs
	 * @return boolean
	 */
	public static function inCIDR($cidr, $ip = NULL) {
		if (is_null($ip)) {
			$ip = Network\Client::ip();
		}
		list($subnet, $mask) = explode('/', $cidr);
		$b = (ip2long($ip) & ~((1 << (32 - $mask)) - 1)) == ip2long($subnet);
		return $b;
	}

	/**
	 * Simple validation for CIDRs
	 * 
	 * @param array $cidrs
	 * @return boolean
	 */
	public static function inCIDRs($cidrs, $ip = NULL) {
		if (is_string($cidrs)) {
			$cidrs = explode("\n", $cidrs);
		}
		if (is_null($ip)) {
			$ip = Network\Client::ip();
		}
		foreach ($cidrs as $cidr) {
			if (static::inCIDR($cidr, $ip)) {
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * Simple validation for CIDR
	 * 
	 * @param string $cidr
	 * @return boolean
	 */
	public static function isCIDR($cidr) {
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

	/**
	 * Check the given value is MAC address or not
	 * 
	 * @param string $str
	 * @return boolean
	 */
	public static function isMAC($str) {
		return filter_var($str, FILTER_VALIDATE_MAC); // Available as of PHP 5.5.0
	}

	/**
	 * Check the given value is IP address or not
	 * 
	 * @param string $str
	 * @return boolean
	 */
	public static function isIP($str) {
		return filter_var($str, FILTER_VALIDATE_IP);
	}

	/**
	 * Check the given value is IPv4 or not
	 * 
	 * @param string $str
	 * @return boolean
	 */
	public static function isIPv4($str) {
		return filter_var($str, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
	}

	/**
	 * Check the given value is IPv6 or not
	 * 
	 * @param string $str
	 * @return boolean
	 */
	public static function isIPv6($str) {
		return filter_var($str, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
	}

	/**
	 * Gets the host name for the local machine.
	 * 
	 * @return string Returns a string with the hostname on success, otherwise FALSE is returned.
	 */
	public static function hostname() {
		if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
			return gethostname();
		} else {
			return php_uname('n');
		}
	}

	/**
	 * Get the IPv4 address corresponding to a given Internet host name
	 * 
	 * @param string $domain
	 * @return string Returns the IPv4 address or a string containing the unmodified hostname on failure.
	 */
	public static function ipFromDomain($domain) {
		return gethostbyname($domain);
	}

}
