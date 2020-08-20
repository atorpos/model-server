<?php

namespace LT;

/**
 * override standard php exception to provide more support for logging, notification and levels
 */
class Exception extends \Exception {

	/**
	 * the list of exception levels
	 */
	const LEVEL_CORE			 = 1;   // the error in framework, must be reported to framework admin
	const LEVEL_CRITICAL		 = 2;   // the error must be handled immediately
	const LEVEL_GENERAL		 = 4;   // the error must be logged and handle as soon as possible
	const LEVEL_MINOR			 = 8;   // the error must be logged
	const LEVEL_CONFIG		 = 16;  // the error in configuration, maybe a invalid value
	const LEVEL_BAD_REQUEST	 = 32;

	/**
	 * @var int listed levels will throw exception
	 */
	protected static $_throwLevels = 55; // LEVEL_CORE | LEVEL_CRITICAL | LEVEL_GENERAL | LEVEL_CONFIG | LEVEL_BAD_REQUEST

	/**
	 * @var int exception level
	 */
	protected $_level = 4; // LEVEL_GENERAL

	/**
	 * @var array extra data for this exception
	 */
	protected $_data = array();

	/**
	 * update default exception thrown level
	 * @param int $levels
	 */
	public static function throwLevels($levels) {
		if (!is_int($levels)) {
			static::config('try to set invalid throw levels');
		}
		static::$_throwLevels = $levels;
	}

	/**
	 * the error in configuration, maybe a invalid value
	 * 
	 * @param string $message
	 * @param $data
	 */
	public static function config($message, $data = array()) {
		self::_throw($message, 500, $data, static::LEVEL_CONFIG);
	}

	/**
	 * the error in framework, must be reported to framework admin
	 * 
	 * @param string $message
	 * @param $data
	 */
	public static function core($message, $data = array()) {
		self::_throw($message, 500, $data, static::LEVEL_CORE);
	}

	/**
	 * the error must be handled immediately
	 * 
	 * @param string $message
	 * @param $data
	 */
	public static function critical($message, $data = array()) {
		self::_throw($message, 500, $data, static::LEVEL_CRITICAL);
	}

	/**
	 * the error must be logged and handle as soon as possible
	 * 
	 * @param string $message
	 * @param $data
	 */
	public static function general($message, $data = array()) {
		self::_throw($message, 500, $data, static::LEVEL_GENERAL);
	}

	/**
	 * the error in configuration, maybe a invalid value
	 * 
	 * @param string $message
	 * @param $data
	 */
	public static function minor($message, $data = array()) {
		self::_throw($message, 500, $data, static::LEVEL_MINOR);
	}

	/**
	 * the error in configuration, maybe a invalid value
	 * 
	 * @param string $message
	 * @param $data
	 */
	public static function badRequest($data = array()) {
		self::_throw('Bad Request', 400, $data, static::LEVEL_BAD_REQUEST);
	}

	/**
	 * handle the exception
	 * 
	 * @param string $message
	 * @param $data
	 * @param int $level
	 * @throws \LT\Exception
	 */
	protected static function _throw($message, $code = 0, $data = array(), $level = self::LEVEL_GENERAL) {
		if (is_string($data)) {
			$data = array('details' => $data);
		}
		if (!($level & self::LEVEL_MINOR)) {
			Logger::error($code, $level, $message, $data);
		}

		$exception = new static($message, $code);
		$exception->level($level);
		$exception->data($data);
		if (static::$_throwLevels & $level) {
			throw $exception;
		}
	}

	/**
	 * set this exception level
	 * 
	 * @param int $level
	 */
	public function level($level) {
		$this->_level = $level;
	}

	/**
	 * set data
	 * @param $data
	 */
	public function data($data) {
		$this->_data = $data;
	}

	public function getData() {
		return $this->_data;
	}

}
