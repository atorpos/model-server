<?php

namespace LT\Type;

/**
 * @property-read string $content Get file content
 * @property-read string $dir Get directory path
 * @property-read string $extension Get file extension
 * @property-read string $filename Get file name
 * @property-read string $name Get file name without extension
 * @property-read string $mime Get MIME type
 * @property-read long $size Get file size in bytes
 */
class File {

	protected $_path;

	public function __construct($filename) {
		$this->_path = $filename;
	}

	public function __get($name) {
		switch ($name) {
			case 'content':
			case 'dir':
			case 'extension':
			case 'filename':
			case 'name':
			case 'mime':
			case 'size':
				return self::$name($this->_path);
		}
		return NULL;
	}

	/**
	 * Get file content
	 * @param string $filename
	 * @return string|false
	 */
	public static function content($filename) {
		if (file_exists($filename)) {
			return file_get_contents($filename);
		}
		return FALSE;
	}

	/**
	 * Get directory path from file path
	 * @param string $path
	 * @return string
	 */
	public static function dir($path) {
		return pathinfo($path, PATHINFO_DIRNAME);
	}

	/**
	 * Get file extension
	 * @param string $path
	 * @return string
	 */
	public static function extension($path) {
		return pathinfo($path, PATHINFO_EXTENSION);
	}

	/**
	 * Get file name from path
	 * @param string $path
	 * @return string
	 */
	public static function filename($path) {
		return pathinfo($path, PATHINFO_FILENAME);
	}

	/**
	 * Get file name without extension from path
	 * @param string $path
	 * @return string
	 */
	public static function name($path) {
		return pathinfo($path, PATHINFO_BASENAME);
	}

	/**
	 * Get MIME type by given path
	 * @staticvar array $MIMES
	 * @param string $path
	 * @return string
	 */
	public static function mime($path) {
		static $MIMES = array(
			'txt'	 => 'text/plain',
			'htm'	 => 'text/html',
			'html'	 => 'text/html',
			'php'	 => 'text/html',
			'css'	 => 'text/css',
			'js'	 => 'application/javascript',
			'json'	 => 'application/json',
			'xml'	 => 'application/xml',
			'swf'	 => 'application/x-shockwave-flash',
			'flv'	 => 'video/x-flv',
			// images
			'png'	 => 'image/png',
			'jpe'	 => 'image/jpeg',
			'jpeg'	 => 'image/jpeg',
			'jpg'	 => 'image/jpeg',
			'gif'	 => 'image/gif',
			'bmp'	 => 'image/bmp',
			'ico'	 => 'image/vnd.microsoft.icon',
			'tiff'	 => 'image/tiff',
			'tif'	 => 'image/tiff',
			'svg'	 => 'image/svg+xml',
			'svgz'	 => 'image/svg+xml',
			// archives
			'zip'	 => 'application/zip',
			'rar'	 => 'application/x-rar-compressed',
			'exe'	 => 'application/x-msdownload',
			'msi'	 => 'application/x-msdownload',
			'cab'	 => 'application/vnd.ms-cab-compressed',
			// audio/video
			'mp3'	 => 'audio/mpeg',
			'qt'	 => 'video/quicktime',
			'mov'	 => 'video/quicktime',
			// adobe
			'pdf'	 => 'application/pdf',
			'psd'	 => 'image/vnd.adobe.photoshop',
			'ai'	 => 'application/postscript',
			'eps'	 => 'application/postscript',
			'ps'	 => 'application/postscript',
			// ms office
			'doc'	 => 'application/msword',
			'rtf'	 => 'application/rtf',
			'xls'	 => 'application/vnd.ms-excel',
			'ppt'	 => 'application/vnd.ms-powerpoint',
			// open office
			'odt'	 => 'application/vnd.oasis.opendocument.text',
			'ods'	 => 'application/vnd.oasis.opendocument.spreadsheet',
		);


		//$ext = strtolower(array_pop(explode('.', $this->_fn)));
		$ext = self::extension($path);
		if (isset($MIMES[$ext])) {
			$mime = $MIMES[$ext];
		} elseif (function_exists('finfo_open') && is_file($path)) {
			$_f		 = finfo_open(FILEINFO_MIME);
			$mime	 = finfo_file($_f, $path);
			finfo_close($_f);
		} else {
			$mime = 'application/octet-stream';
		}
		return $mime;
	}

	/**
	 * Get file size in bytes
	 * @param string $filename
	 * @return string
	 */
	public static function size($filename) {
		return filesize($filename);
	}

}
