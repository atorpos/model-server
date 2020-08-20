<?php

namespace LT\Type;

class Image {

	private $_s	 = NULL; // source image handle
	private $_sMT;   // source image time
	private $_sF = '';  // source image load from
	private $_sT; // source image type
	private $_sW = 0;   // source image width
	private $_sH = 0;   // source image height
	private $_sTCI;  // source image transparent color index
	private $_sTC;   // source image transparent color
	private $_p	 = NULL; // current image
	private $_w	 = 0; // image width
	private $_h	 = 0; // image height

	public function __construct($source = NULL) {
		if (!extension_loaded('gd')) {
			LT::error('GD library is not installed');
		}
		if (!is_null($source)) {
			$this->open($source);
		}
	}

	/**
	 * Check supported file type
	 * 
	 * @param string $type
	 * @return bool
	 */
	public static function isSupported($type) {
		return in_array(strtolower($type), array('image/gif', 'image/jpeg', 'image/pjpeg', 'image/png'));
	}

	/**
	 * @param int $width
	 * @param int $height
	 * @param string $bgColor   (e.g. NULL: transparency; #FFFFFF: white)
	 * @return resource an image resource identifier
	 */
	public function blank($width, $height, $bgColor = '#FFFFFF') {

		$img = imagecreatetruecolor((int) $width <= 0 ? 1 : (int) $width, (int) $height <= 0 ? 1 : (int) $height);

		if (($this->_sT == IMAGETYPE_PNG) && is_null($bgColor)) {
			// keep transparency
			imagealphablending($img, FALSE);
			imagesavealpha($img, TRUE);
			$tC = imagecolorallocatealpha($img, 255, 255, 255, 127);
			imagefill($img, 0, 0, $tC);
		} elseif (($this->_sT == IMAGETYPE_GIF) && is_null($bgColor) && ($this->_sTCI >= 0)) {

			$tC = imagecolorallocate($img, $this->_sTC['red'], $this->_sTC['green'], $this->_sTC['blue']);
			imagefill($img, 0, 0, $tC);
			imagecolortransparent($img, $tC);
		} else {

			if (is_null($bgColor)) {
				$bgColor = '#FFFFFF';
			}
			$rgb	 = $this->_hex2rgb($bgColor);
			$bgColor = imagecolorallocate($img, $rgb['r'], $rgb['g'], $rgb['b']);
			imagefill($img, 0, 0, $bgColor);
		}

		return $img;
	}

	/**
	 * Load image from (file path/gd resource/object/upload)
	 * 
	 * @param string $source
	 * @param string $type
	 * @return static
	 */
	public function open($source, $type = NULL) {
		if (is_null($type)) { // auto detect source type
			if ($source == '') {
				//$type = 'string';
			} elseif (file_exists($source)) {
				$this->_sF = 'file';
				return $this->openFile($source);
			} elseif ((is_resource($source) && get_resource_type($source) == 'gd')) {
				$this->_sF = 'handle';
			} elseif (is_object($source)) {
				$this->_sF = 'object';
			} elseif ((isset($_FILES[$source]) && isset($_FILES[$source]['tmp_name']))) {
				$this->_sF = 'upload';
			} else {
				// search first $binLength bytes (at a maximum) for ord<32 characters (binary image data)
				$bL	 = 64;
				$sL	 = strlen($source);
				$mL	 = ($sL > $bL) ? $bL : $sL;
				for ($i = 0; $i < $mL; $i++) {
					if (ord($source[$i]) < 32) {
						$this->_sF = 'string';
						break;
					}
				}
			}

			if (is_null($type)) {
				return $this;
			}
		}
		return $this;
	}

	/**
	 * Open image file
	 * 
	 * @param string $path Image file path
	 * @return boolean
	 * @throws Exception
	 */
	public function openFile($path) {
		$this->_sF = 'file';
		if (!file_exists($path) || !is_readable($path)) {
			return FALSE;
		}
		list($this->_sW, $this->_sH, $this->_sT) = getimagesize($path);
		$this->_w	 = $this->_sW;
		$this->_h	 = $this->_sH;
		switch ($this->_sT) {
			case IMAGETYPE_GIF:
				$this->_s	 = imagecreatefromgif($path);
				// get the index of the transparent color (if any)
				if (($this->_sTCI = imagecolortransparent($this->_s)) >= 0) {
					$this->_sTC = imagecolorsforindex($this->_s, $this->_sTCI);
				}
				break;

			case IMAGETYPE_JPEG:
				$this->_s = imagecreatefromjpeg($path);
				break;

			case IMAGETYPE_PNG:
				$this->_s = imagecreatefrompng($path);
				imagealphablending($this->_s, FALSE);   // disable blending
				break;

			default:
				throw new Exception('unsupported file type', 4);
			//return FALSE;
		}
		$this->_sMT = filemtime($path);
		return $this->reset();
	}

	/**
	 * Destroy opened files and resources
	 */
	public function close() {
		if (isset($this->_p) && is_resource($this->_p)) {
			imagedestroy($this->_p);
		}
		if (isset($this->_s) && is_resource($this->_s)) {
			imagedestroy($this->_s);
		}
	}

	/**
	 * Created new base
	 *
	 * @param int $left
	 * @param int $top
	 * @param int $right
	 * @param int $bottom
	 * @return static
	 */
	public function base($w = 0, $h = 0) {

		$this->_p	 = $this->blank($w, $h, NULL);
		$this->_w	 = $w;
		$this->_h	 = $h;
		return $this;
	}

	/**
	 * Crop image
	 * 
	 * @param int $left
	 * @param int $top
	 * @param int $right
	 * @param int $bottom
	 * @return static
	 */
	public function crop($left = 0, $top = 0, $right = 0, $bottom = 0) {

		$w			 = $right - $left;
		$h			 = $bottom - $top;
		$new		 = $this->blank($w, $h, NULL);
		imagecopyresampled($new, $this->_p, 0, 0, $left, $top, $w, $h, $w, $h);
		imagedestroy($this->_p);
		$this->_p	 = $new;
		$this->_w	 = $w;
		$this->_h	 = $h;
		return $this;
	}

	public function cropWH($width, $height) {
		$left	 = intval(($this->_w - $width) / 2);
		$top	 = intval(($this->_h - $height) / 2);
		$right	 = max(0, $this->_w - $left);
		$bottom	 = max(0, $this->_h - $top);
		return $this->crop($left, $top, $right, $bottom);
	}

	public function cropSquare($size = NULL) {
		if (is_null($size)) {
			$size = min($this->_w, $this->_h);
		}
		return $this->cropWH($size, $size);
	}

	/**
	 * Resize image
	 *
	 * @param int $width Target width
	 * @param int $height Target height
	 * @param bool $keepRatio keep size ratio?
	 * @return static
	 */
	public function resize($width, $height, $keepRatio = TRUE) {
		if ($keepRatio) {
			if ($this->_w >= $this->_h) {
				$this->resizeToWidth($width);
			} else {
				$this->resizeToHeight($height);
			}
		} else {
			$new		 = $this->blank($width, $height, NULL);
			imagecopyresampled($new, $this->_p, 0, 0, 0, 0, $width, $height, $this->_w, $this->_h);
			imagedestroy($this->_p);
			$this->_p	 = $new;
			$this->_w	 = $width;
			$this->_h	 = $height;
		}
		return $this;
	}

	/**
	 * Resize image (keep ratio & auto height)
	 *
	 * @param int $width Target width
	 * @return static
	 */
	public function resizeToWidth($width) {
		if ($this->_w == 0) {
			echo '<pre>', var_export($this);
			echo debug_print_backtrace();
			exit;
		}
		return $this->resize($width, intval($this->_h * ($width / $this->_w)), FALSE);
	}

	/**
	 * Resize canvas
	 * @param int $width
	 * @param int $height
	 * @param string $background RGB color code
	 * @return static
	 */
	public function resizeCanvas($width, $height, $background = '#FFFFFF') {
		$canvas		 = $this->blank($width, $height, $background);
		$x			 = intval(($width - $this->_w) / 2);
		$y			 = intval(($height - $this->_h) / 2);
		imagecopyresampled($canvas, $this->_p, $x, $y, 0, 0, $this->_w, $this->_h, $this->_w, $this->_h);
		imagedestroy($this->_p);
		$this->_p	 = $canvas;
		$this->_w	 = $width;
		$this->_h	 = $height;
		return $this;
	}

	/**
	 * Resize image (keep ratio & auto width)
	 *
	 * @param int $height Target height
	 * @return static
	 */
	public function resizeToHeight($height) {
		$nw = intval($this->_w * ($height / $this->_h));
		return $this->resize($nw, $height, FALSE);
	}

	/**
	 * Rotate image
	 *
	 * @param Image $src
	 * @param       $x
	 * @param       $y
	 *
	 * @return $this
	 * @internal param Image $dst
	 */
	public function rotate($angle) {

		$this->_p	 = imagerotate($this->_p, -$angle, -1);
		$this->_w	 = imagesx($this->_p);
		$this->_h	 = imagesy($this->_p);
		return $this;
	}

	/**
	 * copy whole source image to destination image with pos x and y, with no resize
	 *
	 * @param Image $src
	 * @param       $x
	 * @param       $y
	 *
	 * @return $this
	 * @internal param Image $dst
	 */
	public function paste($src, $x, $y, $w, $h) {
		imagecopyresampled($this->_p, $src->_p, $x, $y, 0, 0, $w, $h, $src->_w, $src->_h);

		return $this;
	}

	/**
	 * Image sharpen
	 * 
	 * @return \LT_Image
	 */
	public function sharpen() {
		if (file_exists('imageconvolution')) {
			$matrix	 = array(
				array(-1.2, -1, -1.2),
				array(-1, 20, -1),
				array(-1.2, -1, -1.2),
			);
			$divisor = array_sum(array_map('array_sum', $matrix));
			$offset	 = 0;
			imageconvolution($this->_p, $matrix, $divisor, $offset);
		}
		return $this;
	}

	/**
	 * Reset image to raw
	 * 
	 * @return static
	 */
	public function reset() {
		if (isset($this->_p) && is_resource($this->_p)) {
			imagedestroy($this->_p);
		}
		$this->_w	 = $this->_sW;
		$this->_h	 = $this->_sH;
		$this->_p	 = imagecreatetruecolor($this->_w, $this->_h);

		// keep transparency
		imagealphablending($this->_p, false);
		imagesavealpha($this->_p, true);

		imagecopy($this->_p, $this->_s, 0, 0, 0, 0, $this->_w, $this->_h);
		return $this;
	}

	/**
	 * Save as file
	 * 
	 * @param string $dest the target filename
	 * @param image_type $type 
	 * @return static
	 */
	public function save($dest, $type = NULL, $quality = NULL) {
		if (!$type) {
			$type = $this->_sT;
		}
		switch ($type) {
			case IMAGETYPE_JPEG:
				$r	 = imagejpeg($this->_p, $dest, $quality);
				break;
			case IMAGETYPE_PNG:
				$r	 = imagepng($this->_p, $dest, $quality);
				break;
			case IMAGETYPE_GIF:
				$r	 = imagegif($this->_p, $dest);
				break;
		}
		if (!$r && LT_DEBUG) {
			LT::error('unable to save image');
		}
		return $this;
	}

	/**
	 * Save as JPG format
	 * 
	 * @param string $dest
	 * @param int $quality [optional] <p>
	 * <i>quality</i> is optional, and ranges from 0 (worst
	 * quality, smaller file) to 100 (best quality, biggest file). The
	 * default is the default IJG quality value (about 75).
	 * </p>
	 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
	 */
	public function saveJPG($dest, $quality = 90) {
		$quality = is_null($quality) ? 90 : $quality;
		return imagejpeg($this->_p, $dest, $quality);
	}

	/**
	 * Save as PNG format
	 * 
	 * @param string $dest
	 * @param int $quality [optional] <p>
	 * Compression level: from 0 (no compression) to 9.
	 * </p>
	 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
	 */
	public function savePNG($dest, $quality = 0) {
		$quality = is_null($quality) ? 0 : $quality;
		return imagepng($this->_p, $dest, $quality);
	}

	/**
	 * Save as GIF format
	 * 
	 * @param string $dest
	 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
	 */
	public function saveGIF($dest) {
		return imagegif($this->_p, $dest);
	}

	/**
	 * Get the source image resource identifier
	 * 
	 * @return resource an image resource identifier
	 */
	public function source() {
		return $this->_s;
	}

	/**
	 * Get the image resource identifier
	 * 
	 * @return resource an image resource identifier
	 */
	public function image() {
		return $this->_p;
	}

	/**
	 * Get image width
	 * 
	 * @return int
	 */
	public function width() {
		return $this->_w;
	}

	/**
	 * Get image height
	 * 
	 * @return int
	 */
	public function height() {
		return $this->_h;
	}

	/**
	 *  Converts a hexadecimal representation of a color (i.e. #123456 or #AAA) to a RGB representation.
	 *
	 *  The RGB values will be a value between 0 and 255 each.
	 *
	 *  @param  string  $color              Hexadecimal representation of a color (i.e. #123456 or #AAA).
	 *
	 *  @param  string  $default   Hexadecimal representation of a color to be used in case $color is not
	 *                                      recognized as a hexadecimal color.
	 *
	 *  @return array                       Returns an associative array with the values of (R)ed, (G)reen and (B)lue
	 *
	 *  @access private
	 */
	private function _hex2rgb($color, $default = '#FFFFFF') {

		// if color is not formatted correctly, use the default color
		if (preg_match('/^#?([a-f]|[0-9]){3}(([a-f]|[0-9]){3})?$/i', $color) == 0) {
			$color = $default;
		}

		// trim off the "#" prefix from $background_color
		$color = ltrim($color, '#');

		// if color is given using the shorthand (i.e. "FFF" instead of "FFFFFF")
		if (strlen($color) == 3) {
			$tmp = '';
			for ($i = 0; $i < 3; $i++) {
				$tmp .= str_repeat($color[$i], 2);
			}
			$color = $tmp;
		}

		// decimal representation of the color
		$int = hexdec($color);

		// extract and return the RGB values
		return array(
			'r'	 => 0xFF & ($int >> 0x10),
			'g'	 => 0xFF & ($int >> 0x8),
			'b'	 => 0xFF & $int
		);
	}

}
