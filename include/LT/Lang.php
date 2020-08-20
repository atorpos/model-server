<?php

namespace LT;

class Lang {

	protected static $_lang	 = NULL;
	protected static $_T	 = array();

	public static function load($dir, $stopDir = LT_APP_DIR) {
		$limit	 = 20;
		$b		 = $dir;
		$fs		 = array();
		while ($limit--) {
			$fs[] = str_replace('/', DIRECTORY_SEPARATOR, $b . '_lang/' . self::current() . '.php');
			if (realpath($b) == realpath($stopDir)) {
				break;
			}
			$b = dirname($b) . DIRECTORY_SEPARATOR;
		}
		foreach (array_reverse($fs) as $f) {
			if (file_exists($f)) {
				include $f;
			}
		}
		unset($f, $fs);
	}

	public static function current() {
		if (is_null(self::$_lang)) {
			foreach ([$_GET, $_POST, $_COOKIE] as $_method) {
				if (($l = isset($_method['LT_LANG']) ? $_method['LT_LANG'] : FALSE)) {
					break;
				}
			}
			self::change($l);
		}
		return self::$_lang ? self::$_lang : 'en';
	}

	public static function change($lang) {
		$replaces = Config::value('mui.replaces');
		if (isset($replaces[$lang])) {
			$lang = $replaces[$lang];
		}

		self::$_lang = $lang;
		if ($lang != filter_input(INPUT_COOKIE, 'LT_LANG')) {
			setcookie('LT_LANG', $lang, time() + 86400 * 365, \LT\Config::value('web.base'));
		}
	}

	public static function flag($lang = NULL) {
		if (is_null($lang)) {
			$lang = self::current();
		}
		$flags = Config::value('mui.flags');
		if (isset($flags[$lang])) {
			return $flags[$lang];
		}
		return $lang;
	}

	public static function register($texts, $lang = NULL) {
		if (is_null($lang)) {
			$lang = self::current();
		}
		if (!isset(self::$_T[$lang])) {
			self::$_T[$lang] = $texts;
		} else {
			self::$_T[$lang] = array_merge(self::$_T[$lang], $texts);
		}
	}

	public static function text($key, $lang = NULL) {
		if (is_null($lang)) {
			$lang = self::current();
		}
		if (isset(self::$_T[$lang][$key])) {
			return self::$_T[$lang][$key];
		}
		if (isset(self::$_T['default'][$key])) {
			return self::$_T['default'][$key];
		}
		return $key;
	}

}
