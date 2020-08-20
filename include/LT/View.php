<?php

namespace LT;

class View {

	protected $_data		 = array();
	protected $_template	 = NULL;
	protected $_noOutput	 = FALSE;
	protected static $_paths = array();

	public function __construct($template = NULL, $data = array()) {

		$this->_template = $template;
		$this->_data	 = $data;

		$this->breadcrumbs(self::_defaultBreadcrumbItems());
	}

	/**
	 * Assign variable to view
	 * 
	 * @param string $name
	 * @param mixed $value
	 */
	public function assign($name, $value) {
		$this->_data[$name] = $value;
	}

	/**
	 * Setup bread crumb items
	 * @param array $items
	 */
	public function breadcrumbs(array $items = array()) {
		$this->_data['PAGE_BREADCRUMBS'] = $items;
	}

	/**
	 * Format variable (for view variable modifiers)
	 * 
	 * @param mixed $var
	 * @param string $args
	 * @return string
	 */
	public static function format($var, $args = '') {
		$args = explode('|', $args);
		foreach ($args as $arg) {
			$cmd = $ps	 = '';
			if (strpos($arg, ':')) {
				list($cmd, $ps) = explode(':', $arg, 2);
			} else {
				$cmd = $arg;
			}
			if ($ps === '') {
				$ps = array();
			} elseif ($ps[0] === '{') {
				$ps = json_decode($ps, TRUE);
			}
			switch (strtolower($cmd)) {
				case 'date':
					$_f = 'Y-m-d';
				case 'time':
					if (!isset($_f)) {
						$_f = 'H:i:s';
					}
				case 'datetime':
					if ($ps) {
						$_f = $ps;
					} elseif (!isset($_f)) {
						$_f = 'Y-m-d H:i:s';
					}
					if (!is_numeric($var)) {
						$var = strtotime($var);
					}
					$var	 = ($var	 = intval($var)) > 0 ? date($_f, $var) : '';
					unset($_f);
					break;
				case 'ago':
					$temp	 = ($var % 60) . 's';
					if ($var > 59) {
						$temp = floor($var / 60) . 'm';
					}
					if ($var > 3599) {
						$temp = floor($var / 3600) . 'h';
					}
					if ($var > 86399) {
						$temp = floor($var / 86400) . 'd';
					}
					$var = $temp;
					break;
				case 'nlbr':
					$var = nl2br($var);
					break;
				case 'empty':
					$var = $var ? $var : '-';
					break;
				case 'json':
					if (is_object($var)) {
						$var = (array) $var;
					}
					if (is_array($var)) {
						$_options = JSON_PRETTY_PRINT;
						if ($ps == 'num') {
							$_options = $_options | JSON_NUMERIC_CHECK;
						}
						$var = json_encode($var, $_options);
					} else {
						$var = '';
					}
					break;
				case 'url':
					$var = rawurlencode($var);
					break;
				case 'urlc':
					$var = rawurlencode(str_replace('/', ' ', $var));
					break;
				case 'ucwords':
					$var = ucwords($var);
					break;
				case 'ucfirst':
					$var = ucfirst($var);
					break;
				case 'upper':
					$var = strtoupper($var);
					break;
				case 'lower':
					$var = strtolower($var);
					break;
				case 'nospace':
					$var = str_replace(' ', '', $var);
					break;
				case 'space':
					$var = str_replace(' ', '&nbsp;', str_pad($var, (int) $ps, ' ', STR_PAD_LEFT));
					break;
				case 'abs':
					if (is_numeric($var)) {
						$var = abs($var);
					}
					break;
				case 'price':
				case 'amount':
					if (is_numeric($var)) {
						if (count($ps) === 0) {
							$ps = 2;
						}
						$var = number_format(round($var, (int) $ps), (int) $ps, '.', ',');
					}
					break;
				case 'drcr':
					if (is_numeric(str_replace(",", "", $var)) && (str_replace(",", "", $var) < 0)) {
						$var = '(' . str_replace('-', '', $var) . ')';
					}
					break;
				case 'cr':
					if (is_numeric(str_replace(",", "", $var))) {
						$var = '(' . str_replace('-', '', $var) . ')';
					}
					break;
				case 'round':
					$var = round($var, $ps);
					break;
				case 'filesize':
					if (!is_array($ps) && ($ps !== '')) {
						$ps = array('precision' => intval($ps));
					}
					$ps = array_merge(array('precision' => 0), $ps);
					if (ctype_digit($var)) {
						$_Us = array('B', 'KB', 'MB', 'GB', 'TB');

						$_b	 = max($var, 0);
						$_p	 = min(floor(($_b ? log($_b) : 0) / log(1024)), count($_Us) - 1);

						// Uncomment one of the following alternatives
						// $_b /= pow(1024, $_p);   
						$_b /= (1 << (10 * $_p));

						$var = round($_b, $ps['precision']) . ' ' . $_Us[$_p];
						unset($_Us, $_b, $_p);
					}
					break;
				case 'repeat':
					$var = str_repeat($var, $ps);
					break;
				case 'first':
					if (is_array($var)) {
						$var = current($var);
					}
					break;
				case 'last':
					if (is_array($var)) {
						$var = end($var);
					}
					break;
				case 'count':
					if (is_array($var)) {
						$var = count($var);
					} else {
						$var = strlen($var);
					}
					break;
				case 'list':

					if (is_object($var) && (get_class($var) === 'PDOStatement')) {
						$var = $var->fetch(PDO::FETCH_ASSOC);
					}

					if (is_array($var)) {
						$rs = array();
						foreach ($var as $_k => $_r) {
							$_k		 = ucwords(str_replace('_', ' ', $_k));
							$rs[$_k] = $_r;
						}
						$var = $rs;

						$view	 = new View(':list', array(
							'data' => $var
						));
						$var	 = $view->html();
						$args[]	 = 'html';
					}

					break;
				case 'comma':
					$var = is_array($var) ? implode(',', $var) : $var;
					break;
				case 'table':

					// convert pdo resultset to array
					if (is_object($var) && (get_class($var) === 'PDOStatement')) {
						$rs = array();
						foreach ($var as $_r) {
							$rs[] = $_r;
						}
						$var = $rs;
					}
					if ((is_array($var) && !empty($var))) {
						$headers	 = array();
						$firstRow	 = current($var);
						if (is_object($firstRow)) {
							foreach (array_keys(get_object_vars($firstRow)) as $_key) {
								$headers[] = $_key;
							}
						} else {
							foreach (array_keys($firstRow) as $_key) {
								$headers[] = $_key;
							}
						}
						$view	 = new View(':table', array(
							'header' => $headers,
							'data'	 => $var
						));
						$var	 = $view->html();
						$args[]	 = 'html';
					}
					break;
				case 'raw':
				case 'html':
//                    return $var;
					break;
				default:
					Exception::minor('unsupported format pattern');
					break;
			}
		}
		if (is_array($var)) {
			$var = '[Array]';
		}
		if (in_array(end($args), array('raw', 'html'))) {
			return $var;
		}
		return htmlentities($var);
	}
	
	
	public static function allow($rules) {
		if (empty($rules)) {
			return FALSE;
		}
//		$user	 = \LT\RBAC\User::current();
		$user = \PA\User::current();
		$rules	 = strtoupper($rules);
		foreach (explode('|', $rules) as $_rule) {
			$_rule = trim($_rule);
			if (Network\Client::isIPv4($_rule)) {
				$_rule .= '/32';
			}
			$b = FALSE;
			if (Network\Client::isCIDR($_rule)) {
				$b = Network\Client::inCIDR($_rule);
			} elseif ($user) {
				$b = $user->canRead($_rule);
			}
			if ($b) {
				return TRUE;
			}
		}
		return FALSE;
	}


	/**
	 * Get view output
	 * 
	 * @return html
	 */
	public function html($template = NULL, $data = array()) {
		ob_start();
		$this->output($template, $data);
		return ob_get_clean();
	}

	public static function js($path) {
		static $s	 = array();
		static $b	 = NULL;
		static $e	 = ".js\"></script>\n";
		if (is_null($b)) {
			if (substr($path, 1, 4) === 'http') {
				$b = '<script type="text/javascript" src="';
			} else {
				$b = '<script type="text/javascript" src="' . \LT\Config::value('web.base');
			}
		}
		$r = '';
		if ($path === '!') {
			foreach ($s as $p) {
				$r .= $b . $p . $e;
			}
			$s = array();
		} elseif ($path[0] === '!') {
			$s[] = substr($path, 1);
		} else {
			$r = $b . $path . $e;
		}
		return $r;
	}

	/**
	 * Disable view output
	 */
	public function noOutput() {
		$this->_noOutput = TRUE;
	}

	/**
	 * Output view to browser
	 * 
	 * @param string $template
	 * @param array $data
	 */
	public function output($template = NULL, $data = array()) {
		if ($this->_noOutput) {
			return;
		}
		if (is_null($template)) {
			$template = $this->_template;
		}
		if (is_array($data)) {
			extract($data);
		}
		if (isset($this->_data) && is_array($this->_data)) {
			extract($this->_data);
		}
		include self::tpl($template);
	}

	protected static function _getPaths($name = NULL, $referenceDir = NULL) {

		$_ps = explode('/', $name);
		if (FALSE !== ($_p	 = strpos(end($_ps), '.'))) {
			$_tmp	 = pathinfo(end($_ps));
			$ext	 = '.' . $_tmp['extension'];

			if ($_tmp['filename'] == '') {
				$n = Core::$SCRIPT_PATH . $ext;
			} else {
				$n = $name;
			}
		} else {
			$n	 = $name . '.tpl';
			$ext = '.tpl';
		}
		unset($_ps, $_p);
		$cacheBaseDir	 = LT_APP_DIR . 'cache' . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR;
		$cacheBaseDir	 .= strtoupper(Core::$SCRIPT_ENTRY) . DIRECTORY_SEPARATOR;

		if ($n[0] == '/') {

			$n			 = substr($n, 1);
			$tplPath	 = Core::$SCRIPT_ENTRY_DIR . $n;
			$cachePath	 = $cacheBaseDir . $n . '.php';
		} elseif ($n[0] == ':') {

			$n			 = substr($n, 1);
			$tplPath	 = LT_TPL_DIR . $n;
			$cachePath	 = $cacheBaseDir . 'lt' . DIRECTORY_SEPARATOR . $n . '.php';
//        } elseif (empty($referenceDir)) {
//
//            $rP = '';
//            if (FALSE !== ($p  = strrpos(Core::$SCRIPT_PATH, '/'))) {
//                $rP = substr(Core::$SCRIPT_PATH, 0, $p) . '/';
//            }
//            $tplPath   = Core::$SCRIPT_DIR . $n;
//            $cachePath = $cacheBaseDir . $n . '.php';
		} else {
			if (empty($referenceDir)) {
				$referenceDir = Core::$SCRIPT_DIR;
			}

			$tplPath = $referenceDir . $n;
			if (0 === ($_p		 = strpos($referenceDir, Core::$SCRIPT_ENTRY_DIR))) {
				$referenceDir = substr($referenceDir, strlen(Core::$SCRIPT_ENTRY_DIR));
			}
			$cachePath = $cacheBaseDir . $referenceDir . $n . '.php';
		}

		$tplPath	 = self::_canonicalize($tplPath);
		$cachePath	 = self::_canonicalize($cachePath);
		$cacheDir	 = dirname($cachePath);
		$cacheDir	 = self::_canonicalize($cacheDir);
		return array(
			'template'		 => $tplPath,
			'template_ext'	 => $ext,
			'cache'			 => $cachePath,
		);
	}

	public static function tplPath($name = NULL, $referenceDir = NULL) {
		$key = $referenceDir . '::' . $name;
		if (!isset(static::$_paths[$key])) {
			static::$_paths[$key] = self::_getPaths($name, $referenceDir);
		}
		return static::$_paths[$key]['template'];
	}

	/**
	 * Get complied template file
	 * 
	 * @param string $name template path
	 * @param string $referenceDir base reference path
	 * @return string
	 */
	public static function tpl($name = NULL, $referenceDir = NULL, $options = array()) {
		/*
		  $_ps = explode('/', $name);
		  if (FALSE !== ($_p	 = strpos(end($_ps), '.'))) {
		  $_tmp	 = pathinfo(end($_ps));
		  $ext	 = '.' . $_tmp['extension'];

		  if ($_tmp['filename'] == '') {
		  $n = Core::$SCRIPT_PATH . $ext;
		  } else {
		  $n = $name;
		  }
		  } else {
		  $n	 = $name . '.tpl';
		  $ext = '.tpl';
		  }
		  unset($_ps, $_p);
		  $cacheBaseDir	 = LT_APP_DIR . 'cache' . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR;
		  $cacheBaseDir	 .= strtoupper(Core::$SCRIPT_ENTRY) . DIRECTORY_SEPARATOR;

		  if ($n[0] == '/') {

		  $n			 = substr($n, 1);
		  $tplPath	 = Core::$SCRIPT_ENTRY_DIR . $n;
		  $cachePath	 = $cacheBaseDir . $n . '.php';
		  } elseif ($n[0] == ':') {

		  $n			 = substr($n, 1);
		  $tplPath	 = LT_TPL_DIR . $n;
		  $cachePath	 = $cacheBaseDir . 'lt' . DIRECTORY_SEPARATOR . $n . '.php';
		  //        } elseif (empty($referenceDir)) {
		  //
		  //            $rP = '';
		  //            if (FALSE !== ($p  = strrpos(Core::$SCRIPT_PATH, '/'))) {
		  //                $rP = substr(Core::$SCRIPT_PATH, 0, $p) . '/';
		  //            }
		  //            $tplPath   = Core::$SCRIPT_DIR . $n;
		  //            $cachePath = $cacheBaseDir . $n . '.php';
		  } else {
		  if (empty($referenceDir)) {
		  $referenceDir = Core::$SCRIPT_DIR;
		  }

		  $tplPath = $referenceDir . $n;
		  if (0 === ($_p		 = strpos($referenceDir, Core::$SCRIPT_ENTRY_DIR))) {
		  $referenceDir = substr($referenceDir, strlen(Core::$SCRIPT_ENTRY_DIR));
		  }
		  $cachePath = $cacheBaseDir . $referenceDir . $n . '.php';
		  }

		  $tplPath	 = self::_canonicalize($tplPath);
		  $cachePath	 = self::_canonicalize($cachePath);
		  $cacheDir	 = dirname($cachePath);
		  $cacheDir	 = self::_canonicalize($cacheDir);

		 */
//            echo Core::$SCRIPT_DIR;
//            echo '<hr>';
//            echo $name;
//            echo '<hr>';
//            echo $cacheBaseDir;
//            echo '<hr>';
//            echo $referenceDir;
//            echo '<hr>';
//            echo $cachePath;
//            echo '<hr>';
//            echo $tplPath;
//            exit;

		$paths		 = self::_getPaths($name, $referenceDir);
		$tplPath	 = $paths['template'];
		$ext		 = $paths['template_ext'];
		$cachePath	 = $paths['cache'];
		$cacheDir	 = dirname($cachePath);
		$cacheDir	 = self::_canonicalize($cacheDir);


		if (!is_dir($cacheDir) && !mkdir($cacheDir, 0775, true)) {
			Exception::config('cannot initialize the template cache space', ['path' => $cacheDir]);
		}
		if (file_exists($cachePath) && !is_writable($cachePath)) {
			Exception::config('unable to create the template cache', ['path' => $cachePath]);
		}
		if (!file_exists($tplPath)) {
			if (in_array($ext, array('.css', '.js'))) {
				return LT_TPL_DIR . 'empty.php';
			}
			Exception::general('template file not found', ['path' => $tplPath]);
		}

		clearstatcache();
		if (true || !file_exists($cachePath) || (filemtime($tplPath) > filemtime($cachePath))) {
			$html = self::tplP($tplPath);
//            if (isset($options['source_code']) && $options['source_code']) {
//                $html = htmlentities($html);
//            }
//            $callby = explode('.', current(debug_backtrace())['file']);
//            if ($callby[sizeof($callby) - 2] === 'js') {
//                $tpl = '*/' . $tpl . '/*';
//            } else
//            var_dump($ext);
//            exit;
			if ($ext == '.css') {
				$html = '<style type="text/css">' . $html . '</style>';
			} elseif ($ext == '.js') {
				$html = '<script type="text/javascript">' . $html . '</script>';
			}
			file_put_contents($cachePath, $html);
		}
		return $cachePath;
	}

	/**
	 * Convert template file to PHP
	 * 
	 * @staticvar string $P_VAR
	 * @staticvar string $P_CONST
	 * @staticvar string $P_SPACHE
	 * @param string $file
	 * @param string $referenceDir
	 * @return string
	 */
	public static function tplP($file, $referenceDir = NULL) {

		static $P_VAR	 = "((\\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)(\[[a-zA-Z0-9_\-\.\"\'\[\]\$\x7f-\xff]+\])*)";
		static $P_CONST	 = "([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)";
		static $P_SPACHE = '([\n\r\t]*)';

		$tpl = file_get_contents($file);

		$referenceDir = "'" . dirname($file) . DIRECTORY_SEPARATOR . "'";

		// parse const, variables, functions
		$patterns	 = array(
			// remove comments
			"#{\*(.+?)\*}#is"											 => '', //{*bahbahbah*}
			"#{$P_SPACHE}{if\s+(!*)ajax}{$P_SPACHE}#is"					 => '<?php if(\2\\LT\\Core::isAJAX()) { ?>', // {if ajax}  {if !ajax}
			"#{$P_SPACHE}{if\s+(!*)https}{$P_SPACHE}#is"				 => '<?php if(\2\\LT\\Core::isHTTPS()) { ?>', // {if https}  {if !https}
			// sub templates
			"#{tpl\s+$P_VAR}#is"										 => '<?php include \LT\View::tpl(\1, ' . $referenceDir . ');?>', // {tpl $variable}
			'#{tpl\s+([A-Za-z0-9:\._/\-]+)}#is'							 => '<?php include \LT\View::tpl("\1", ' . $referenceDir . ');?>', // {tpl \bahbah\sdfsdf}
			'#{tpl\s+(\S+)}#is'											 => '<?php include \LT\View::tpl("\1", ' . $referenceDir . ');?>', // {tpl asdasd}
			'#{code\s+(\S+)}#is'										 => '<?php echo htmlentities(file_get_contents(\LT\View::tplPath("\1", ' . $referenceDir . ')));?>', // {code exmaple/ajax}
			// mui text
			"#\{\{$P_VAR\}\}#is"										 => '<?php echo htmlentities(\LT\Lang::text(isset(\1) ? \1 : ""));?>', // {{$multilanguagevar}}
			"/\{\{$P_VAR\|(.+?)\}\}/is"									 => '<?php echo \LT\Lang::text(isset(\1) ? \LT\View::format(\1, "\4") : \'\');?>', // {{multilanguagekey|formattext}}
			"/\{\{(.+?)\}\}/is"											 => '<?php echo htmlentities(\LT\Lang::text("\1"));?>', // {{$multilanguagetext}}
			// echo statements
			/*     "/\{$P_VAR}/is" => '<? echo isset(\1) ? \1 : \'\';?>', */// format output
			"/\{config\.(.+?)}/i"										 => '<?php echo \LT\Config::value("\1");?>', // {config.bahbah}
			"/\{$P_VAR}/i"												 => '<?php if (isset(\1)) {echo htmlentities(\1);}?>', // {$varable}
			"/\{$P_VAR\.([A-Za-z0-9_]+)\|(.+?)}/is"						 => '<?php if (isset(\1)) echo \1->\4("\5");?>', // {$object.key|param}
			"#{{$P_VAR}\.([A-Za-z0-9_]+)\(\)}#is"						 => '<?php if (isset(\1)) echo \1->\4();?>', // {$object.function()}
			"#{{$P_VAR}\.([A-Za-z0-9_]+)}#is"							 => '<?php if (isset(\1->\4)) echo \1->\4;?>', // {$object.variable}
			"/\{$P_VAR\s*=[^=]\s*(.+?)\}/i"								 => '<?php \1 = \4;?>', // {$variable = assignvalue}
			"/\{$P_VAR\|debug}/is"										 => '<?php echo "<pre class=\"debug\">"; if (isset(\1)) { ?>\1 = <? var_export(\1); } else { ?> \1 undefined. <? } echo "</pre>";?>', // {var|debug}
			"/\{$P_VAR\|(.+?)}/i"										 => '<?php if (isset(\1)) { echo \LT\View::format(\1, "\4"); };?>', // {$value|format}
			"/\{$P_VAR\|raw(.*?)}/i"									 => '<?php if (isset(\1)) { echo htmlentities(\LT\View::format(\1, "\4")); };?>', // {$value|format}
			'#{if allow\s+(.+?)}#i'										 => '<?php if (\LT\View::allow(\'\1\')) { ?>', // {if allow ip_address/permission_key}
			'#{theme\s+(.+?)}#i'										 => '<?php echo LT::theme("\1");?>', // {theme bahbah}
			'#{js\s+(.+?)}#i'											 => '<?php echo \LT\View::js(\'\1\');?>', // {js bahbah}
			'#{css\s+(.+?)}#i'											 => '<link type="text/css" rel="stylesheet" href="<?php echo LT::css(\'\1\');?>" />', // {css bahbah}
			// action url
			'#{url}#is'													 => '<?php echo \LT::url()?>', // {url}
			"#{url\s+$P_VAR}#is"										 => '<?php echo \LT::url(\1)?>', // {url $var}
			"#{url\s+([\$\'\]\[\{\}A-Za-z0-9-_.\/]+)}#is"				 => '<?php echo \LT::url("\1")?>', // {url any string}
			"#{url\s+$P_VAR\s+$P_VAR}#is"								 => '<?php echo \LT::url(\1, \4)?>', // {url $var $var}
			"#{url\s+([A-Za-z0-9-_.\/]+)\s+$P_VAR}#is"					 => '<?php echo \LT::url("\1", \2)?>', // {url text $var}
			"#{url\s+([A-Za-z0-9-_.\/]+)\s+(.+?)}#is"					 => '<?php echo \LT::url("\1", \2)?>', // {url text text}
//			"#{url\s+([A-Za-z0-9-_.\/]+)}#ies"							 => '\LT::url("\1")',
//			"#{url\s+([A-Za-z0-9-_.\/]+)\s+(.+?)}#ies"					 => '\LT::url("\1", "\2")',
			//"/\{url\s+([A-Za-z0-9-]+)\s+([A-Za-z0-9-]+)}/is" => '{LT_ACTION_BASE}\1/\2/',
			// convert if, elseif, else statement
			"/([\n\r\t]*)\{if\s+$P_VAR\s+in\s+$P_VAR\s*\}/i"			 => '\1<?php if(isset(\2, \5) && is_array(\5) && in_array(\2, \5)) { ?>', // {if $var in $var}
			"/([\n\r\t]*)\{if\s+(\!?)$P_VAR\}/is"						 => '\1<?php if(\2(isset(\3) && \3)) { ?>', // {if !$var}
			"/([\n\r\t]*)\{if\s+(.+?)\}([\n\r\t]*)/is"					 => '\1<?php if(\2) { ?>\3', // {if bahbah}
			"/([\n\r\t]*)\{elseif\s+(\!?)$P_VAR\}/is"					 => '\1<?php } elseif(\2(isset(\3) && \3)) { ?>', // {elseif $var}
			"/([\n\r\t]*)\{elseif\s+(.+?)\}([\n\r\t]*)/is"				 => '\1<?php } elseif(isset(\2) && \2) { ?>\3', // {elseif bahbah}
			"/\{else\}/i"												 => "<?php } else { ?>", // {else}
			"/\{\/if\}/i"												 => "<?php } ?>", // {/if}
			// convert const
			"/\{$P_CONST}/is"											 => '\1', //{CONSTANT}
			// implode
			"/[\n\r\t]*\{implode\|+(\S+)\|+(\S+)\}[\n\r\t]*/i"			 => '<?php echo implode("\2", \1); ?>', // {implode|glue|pieces}
			// convert foreach
			"/[\n\r\t]*\{foreach\s+(\S+)\s+(\S+)\s+(\S+)\}[\n\r\t]*/i"	 => '<?php if(is_array(\1)) foreach(\1 as \2 => \3) { ?>',
			"/[\n\r\t]*\{foreach\s+(\S+)\s+(\S+)\}[\n\r\t]*/i"			 => '<?php if(is_array(\1)) foreach(\1 as \2) { ?>',
			"/[\n\r\t]*\{foreach\s+$P_VAR\}[\n\r\t]*/i"					 => '<?php if(is_array(\1)) foreach(\1 as $_k => $_v) { ?>',
			"/[\n\r\t]*\{foreach\s+(\S+)\}[\n\r\t]*/i"					 => '<?php if(is_array(\1)) foreach(\1 as $_k => $_v) { ?>',
			"/([\n\r\t]*)\{for\s+(.+?)\}([\n\r\t]*)/is"					 => '\1<?php for(\2) { ?>\3',
			"/\{\/foreach\}/i"											 => "<?php } ?>", // {/foreach}
			"/\{\/for\}/i"												 => "<?php } ?>", // {/for}
			// convert php short tags
			"/\<\?(\s{1})/is"											 => "<?php\\1", // <?
			"/\<\?\=(.+?)\?\>/is"										 => "<?php echo \\1;?>", // <?=$bahbah
		);
		/*
		  "/\{foreach\s+$P_VAR\s+$P_VAR\s*\}/i" => '<?php if(isset(\1) && is_array(\1)) foreach(\1 as \2) { ?>',
		  "/\{foreach\s+$P_VAR\s+$P_VAR\s+$P_VAR\s*\}/i" => '<?php if(isset(\1) && is_array(\1)) foreach(\1 as \2 => \3) { ?>',
		 */
		$s			 = preg_replace(array_keys($patterns), array_values($patterns), $tpl);
		unset($tpl);

		// parse multi language text
		$matches = NULL;
		if (preg_match_all("/{:[^}]*}/is", $s, $matches, PREG_SET_ORDER)) {
			foreach ($matches as $match) {
				$s = str_replace($match[0], substr($match[0], 3, -1), $s);
			}
		}
		//$tpl = preg_replace_callback("/\{\(text|l):(.+?)\\}/is", "text", $tpl);

		return $s;
	}

	/**
	 * Get view output
	 * 
	 * @return html
	 */
	public function __toString() {
		return $this->html();
	}

	protected static function _canonicalize($address) {
		$address = explode('/', $address);
		$keys	 = array_keys($address, '..');

		foreach ($keys AS $keypos => $key) {
			array_splice($address, $key - ($keypos * 2 + 1), 2);
		}

		$address = implode('/', $address);
		$address = str_replace('./', '', $address);
		return $address;
	}

	/**
	 * Get default breadcrumb items from script path
	 * 
	 * @staticvar array $items
	 * @return array
	 */
	protected static function _defaultBreadcrumbItems() {

		static $items = NULL;

		if (is_null($items)) {
			$items	 = array(
				array(
					'action' => '/',
					'label'	 => 'Home',
					'icon'	 => 'fa fa-home',
				)
			);
			$action	 = '';
			$ps		 = explode('/', \LT\Core::$SCRIPT_PATH);
			$len	 = count($ps);
			$i		 = 0;
			foreach ($ps as $p) {

				if (++$i === $len) {

					if ($p == 'index') {
						continue;
					}
					$action	 .= '/' . $p;
					$items[] = array(
						'action' => $action,
						'label'	 => ucwords(str_replace(array('-', '_'), ' ', $p)),
						'icon'	 => 'fa fa-circle',
					);
				} else {

					$action	 .= '/' . $p;
					$items[] = array(
						'action' => $action . '/',
						'label'	 => ucwords(str_replace(array('-', '_'), ' ', $p)),
						'icon'	 => 'fa fa-circle',
					);
				}
			}
		}

		return $items;
	}

}
