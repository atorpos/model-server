<?php

namespace LT\Security;

/**
 * 算法/模式/填充                16字节加密后数据长度        不满16字节加密后长度
 * AES/CBC/NoPadding             16                          不支持
 * AES/CBC/PKCS5Padding          32                          16
 * AES/CBC/ISO10126Padding       32                          16
 * AES/CFB/NoPadding             16                          原始数据长度
 * AES/CFB/PKCS5Padding          32                          16
 * AES/CFB/ISO10126Padding       32                          16
 * AES/ECB/NoPadding             16                          不支持
 * AES/ECB/PKCS5Padding          32                          16
 * AES/ECB/ISO10126Padding       32                          16
 * AES/OFB/NoPadding             16                          原始数据长度
 * AES/OFB/PKCS5Padding          32                          16
 * AES/OFB/ISO10126Padding       32                          16
 * AES/PCBC/NoPadding            16                          不支持
 * AES/PCBC/PKCS5Padding         32                          16
 * AES/PCBC/ISO10126Padding      32                          16
 */
class AES {

    const MODE_CBC         = 'cbc';
    const MODE_CFB         = 'cfb';
    const MODE_ECB         = 'ecb';
    const MODE_OFB         = 'ofb';
    const MODULE_OPENSSL   = 'openssl';
    const MODULE_MCRYPT    = 'mcrypt';
    const PADDING_OFF      = 'off';
    const PADDING_PKCS7    = 'pkcs7';
    const PADDING_ISO10126 = 'iso10126';

    protected $_mode    = self::MODE_CBC;
    protected $_module  = self::MODULE_MCRYPT;
    protected $_padding = self::PADDING_PKCS7;
    protected $_key     = NULL;
    protected $_iv      = NULL;
    protected $_based64 = TRUE;

    public function __construct($key = NULL, $iv = NULL) {
        $this->key($key);
        $this->iv($iv);
    }

    public function key($key) {
        $this->_key = $key;
    }

    public function iv($iv) {
        $this->_iv = $iv;
    }

    public function mode($mode) {
        $this->_mode = $mode;
    }

    protected function _iv() {
        if (is_null($this->_iv)) {
            return mcrypt_create_iv($this->_ivSize(), MCRYPT_RAND);
        }
        return $this->_iv;
    }

    protected function _ivSize() {
        static $size = null;
        if (is_null($size)) {
            $size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, $this->_mcryptMode());
        }
        return $size;
    }

    protected function _blockSize() {
        return mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, $this->_mcryptMode());
    }

    protected function _mcryptMode($mode = NULL) {
        static $modes = array(
            self::MODE_CBC => MCRYPT_MODE_CBC,
            self::MODE_CFB => MCRYPT_MODE_CFB,
            self::MODE_ECB => MCRYPT_MODE_ECB,
            self::MODE_OFB => MCRYPT_MODE_OFB,
        );

        if (empty($mode)) {
            $mode = $this->_mode;
        }
        if (!isset($modes[$mode])) {
            \LT\Exception::general('unknown operation mode');
        }

        return $modes[$mode];
    }

    protected function _padding($data) {

        switch ($this->_padding) {

            case self::PADDING_PKCS7:
                return $this->_pkcs7Padding($data);

            case self::PADDING_ISO10126:
                return $this->_iso10126Padding();

            case self::PADDING_OFF:
                return $data;

            default:
                return $this->_pkcs7Padding($data);
        }
    }

    protected function _iso10126Padding($data) {
        return $data;
    }

    protected function _pkcs7Padding($data) {
        $bs = $this->_blockSize();
        $ps = $bs - (strlen($data) % $bs);
        return $data . str_repeat(chr($ps), $ps);
    }

    public function encrypt($data) {

        $var = $this->_padding($data);
        $iv  = $this->_iv();
        $k   = pack("H*", $this->_key);

        $cipher = $iv . mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $k, $var, MCRYPT_MODE_CBC, $iv);

        return $this->_based64 ? base64_encode($cipher) : $cipher;
    }

    public function decrypt($data) {
        $cipher = $this->_based64 ? base64_decode($data) : $data;
        $iv     = substr($cipher, 0, $this->_ivSize());
        $chunk  = substr($cipher, $this->_ivSize());
        $k      = pack("H*", $this->_key);

        $plain = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $k, $chunk, MCRYPT_MODE_CBC, $iv);
        $pad   = ord($plain[strlen($plain) - 1]);
        if ($pad >= $this->_blockSize()) {
            return $plain;
        }
        return rtrim($plain, chr($pad));
    }

    public static function quickEncrypt($key, $data) {
        return (new AES($key))->encrypt($data);
    }

    public static function quickDecrypt($key, $data) {
        return (new AES($key))->decrypt($data);
    }

}
