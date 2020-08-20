<?php

namespace LT;

class Session {

	protected static function _autoClose() {
		if (FALSE) {
			self::close();
		}
	}

	public static function _start() {
		if (version_compare(phpversion(), '5.4.0', '<')) {
			if (session_id() == '') {
				session_start();
			}
		} else {
			if (session_status() == PHP_SESSION_NONE) {
				session_start();
			}
		}
	}

	public static function close() {
		if (version_compare(phpversion(), '5.4.0', '<')) {
			if (session_id() != '') {
				session_write_close();
			}
		} else {
			if (session_status() != PHP_SESSION_NONE) {
				session_write_close();
			}
		}
	}

	public static function destroy() {
		self::_start();
		foreach ($_SESSION as $_k => $_v) {
			unset($_SESSION[$_k]);
		}
		session_destroy();
	}

	public static function remove($name) {
		self::_start();
		unset($_SESSION[$name]);
		self::_autoClose();
	}

	public static function set($name, $value) {
		self::_start();
		$_SESSION[$name] = $value;
		self::_autoClose();
	}

	public static function get($name, $default = NULL) {
		self::_start();
		if (isset($_SESSION[$name])) {
			$v = $_SESSION[$name];
		} else {
			$v = $default;
		}
		self::_autoClose();
		return $v;
	}

}
