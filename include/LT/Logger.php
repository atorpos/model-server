<?php

namespace LT;

class Logger {

	private $_folder;

	public function __construct() {
		if (!file_exists(LT_APP_DIR . 'logs')) {
			mkdir(LT_APP_DIR . 'logs') ?: exit('Cannot create log folder.');
		}

		$this->_folder = LT_APP_DIR . 'logs' . DIRECTORY_SEPARATOR;
	}

	/**
	 * 
	 * @staticvar NULL|\PA\Logger $o
	 * @return \PA\Logger
	 */
	public static function shared() {
		static $o = NULL;
		if (is_null($o)) {
			$o = new Logger();
		}
		return $o;
	}

	public static function dump() {
		self::shared()->_write('dump', func_get_args());
	}

	public static function error() {
		self::shared()->_write('error', func_get_args());
	}

	public static function critical() {
		self::shared()->_write('critical', func_get_args());
	}

	public static function custom($filename, $type) {
		$data = func_get_args();
		array_shift($data);
		array_shift($data);
		self::shared()->_write($type, $data, $filename);
	}

	private function _write($type, $data = [], $filename = NULL) {
		$time = time();
		if (is_null($filename)) {
			$f = sprintf("%s%s.%s.log", $this->_folder, $type, date('Ymd'));
		} else {
			$f = sprintf("%s%s.log", $this->_folder, $filename);
		}
		$fp = fopen($f, 'a');
		fwrite($fp, json_encode([
					'rid'	 => Action::$request_id,
					'time'	 => date('Y-m-d H:i:s', $time),
					'type'	 => $type,
					'data'	 => $data
				]) . PHP_EOL);
		fclose($fp);
		chmod($f, 0777);
	}

}
