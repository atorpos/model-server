<?php

namespace LT;

class Core {

//    public static function initWithConfig($name = NULL) {
//        require $configFile;
//    }

	protected static $_initiated = FALSE;
	public static $SCRIPT_DIR;
	public static $SCRIPT_FILE;
	public static $SCRIPT_PATH;
	public static $SCRIPT_ENTRY;
	public static $SCRIPT_ENTRY_DIR;
	public static $SCRIPT_NAME;
	public static $SCRIPT_PARAMS = array();

	/**
	 * the common initial before action run
	 * 
	 * @staticvar boolean $_init
	 */
	public static function init() {
		if (static::$_initiated) {
			Exception::minor('cannot initialize the core again');
		}

		Config::init();

		date_default_timezone_set(Config::value('core.timezone', 'Asia/Hong_Kong'));
		session_name(Config::value('web.session_name', 'LT_APP'));

		static::$_initiated = TRUE;
	}

	public static function lang() {
		return 'default';
	}

	protected static function _route($routes, $uri = NULL) {

		$_urlComponents = parse_url($uri);

		$matches = NULL;

		foreach ($routes as $pattern => $target) {
			if (preg_match($pattern, $_urlComponents['path'], $matches)) {

				if (is_string($target)) {
					foreach ($matches as $key => $match) {
						$target = str_replace('$' . $key, $match, $target);
					}
					return $target;
				} else {
					Exception::config('unsupported route settings');
				}
			}
		}
		return $uri;
//        return $_urlComponents['path'];
	}

	public static function run($path = NULL, array $params = array()) {

		static::init();

		$_mode = Config::value('core.mode', 'web');
//                var_dump($_mode);exit;
		if (in_array($_mode, array('web', 'web-api'))) {
			$_base = Config::value('web.base', '/');
			if (is_null($path)) {

				$_rawURI = filter_input(INPUT_SERVER, 'REQUEST_URI');
				$_uri	 = self::_route(Config::value('route', array()), $_rawURI);

				$_urlComponents = parse_url($_uri);
				if (isset($_urlComponents['path'])) {
					$_uri = $_urlComponents['path'];
				}

				$_queryParams = array();
				if (isset($_urlComponents['query'])) {
					parse_str($_urlComponents['query'], $_queryParams);

					foreach ($_queryParams as $_k => $_v) {
						$_GET[$_k] = $_v;
					}
				}
				self::$SCRIPT_PARAMS = $_queryParams;
//                $components = parse_url($_uri);
				if (FALSE !== ($_p					 = strpos($_uri, '?'))) {
					$_uri = substr($_uri, 0, $_p);
				}
				unset($_p);
				$path = substr($_uri, strlen($_base));
			}
			if (empty($path) || (substr($path, -1) == '/')) {
				$path .= 'index';
			}
		} elseif ($_mode == 'cli') {
			if (is_null($path)) {
				$options = getopt('p:');
				if (!isset($options['p'])) {
					exit('usage: ' . $argv[0] . ' -p <page>' . PHP_EOL);
				}
				$path = $options['p'];
			}
		} else {
			Exception::config('unsupported core.mode: ' . $_mode);
		}

		if (isset($params['default_script_not_found'])) {
			self::$SCRIPT_ENTRY = Config::value('core.shared_entry');
		} else {
			self::$SCRIPT_ENTRY = Config::value('core.default_entry');
		}

		self::$SCRIPT_ENTRY_DIR	 = LT_APP_DIR . 'ACTION_' . strtoupper(self::$SCRIPT_ENTRY) . DIRECTORY_SEPARATOR;
		self::$SCRIPT_PATH		 = $path;
		self::$SCRIPT_FILE		 = self::$SCRIPT_ENTRY_DIR . str_replace('/', DIRECTORY_SEPARATOR, self::$SCRIPT_PATH) . '.php';
		self::$SCRIPT_DIR		 = dirname(self::$SCRIPT_FILE) . DIRECTORY_SEPARATOR;
		self::$SCRIPT_NAME		 = basename(self::$SCRIPT_FILE, '.php');

		if (true) { //TODO: on/off by settings
			\LT\Lang::load(self::$SCRIPT_DIR);
		}

		if (!file_exists(self::$SCRIPT_FILE)) {

			if (!isset($params['default_script_not_found']) && Config::value('core.shared_entry')) {
				$params['default_script_not_found'] = TRUE;
				static::run($path, $params);
			} else {
				Exception::minor('script not found', self::$SCRIPT_FILE);
				Response::notFound();
			}

			exit; // should be never executed
		}

		include self::$SCRIPT_FILE;

		if (true) {

			// convert path to class name
			$_className = "\\Action\\" . ucfirst(strtolower(self::$SCRIPT_ENTRY));
			foreach (explode('/', $path) as $_p) {
				$_className .= '\\' . ucfirst(strtolower($_p));
			}
			if (strpos($_className, '-')) {
				$_className = str_replace(' ', '', ucwords(str_replace('-', ' ', $_className)));
			}
			unset($_p);

			if (!class_exists($_className)) {
				Exception::general($_className);
			}

			$_handler = new $_className();

			if (in_array($_mode, array('web', 'web-api'))) {
				$_httpMethod = strtolower(filter_input(INPUT_SERVER, 'REQUEST_METHOD'));
				if (!method_exists($_handler, $_httpMethod)) {
					Exception::minor('method not found', $_className . '->' . $_httpMethod . '()');
					Response::notFound();
				}
				call_user_func_array(array($_handler, $_httpMethod), self::$SCRIPT_PARAMS);
//                $_handler->$_httpMethod();
				if (in_array($_mode, array('web'))) {
					$_handler->view->output(self::$SCRIPT_NAME);
				}
			} elseif ($_mode == 'cli') {
//            if (php_sapi_name() === 'cli') {
				$_handler->cli();
			}
		}
	}

	public static function test($var) {
		static::_onBeforeRoute($var);
	}

	public static function debug($vars = NULL, $is_hidden = FALSE) {
		static $lt_debugs = array();

		if (!is_null($vars)) {
			ob_start();
			var_dump($vars);
			$var_dump = ob_get_clean();

			$trace	 = debug_backtrace();
			$dump	 = "<div>" .
					"<div>File: <b> " . $trace[0]['file'] . "</b><br/>Line : <b>" . $trace[0]['line'] . "</b></div>" .
					'<div class="row">' .
					'<div class="col-md-6"><pre>' . $var_dump . '</pre></div>' .
					'<div class="col-md-6"><pre>' . print_r($vars, TRUE) . '</pre></div>' .
					'</div>' .
					'<hr />' .
					'</div>';
			array_push($lt_debugs, $dump);
		}

		if (!$is_hidden) {
//            self::assign('lt_debugs', $lt_debugs);
		}
	}

	public static function text($str, $lang = NULL) {
		return $str;
	}

	public static function url($action = NULL, $params = array()) {
		static $_baseURL = NULL;

		if (is_null($_baseURL)) {
			$_baseURL = '//' . filter_input(INPUT_SERVER, 'HTTP_HOST');
		}
		if (is_null($action)) {
			$url = $_baseURL . filter_input(INPUT_SERVER, 'REQUEST_URI');
		} else {

			if ($action[0] === '/') {
				$path = substr($action, 1);
			} else {
				$rP	 = '';
				if (FALSE !== ($p	 = strrpos(self::$SCRIPT_PATH, '/'))) {
					$rP = substr(self::$SCRIPT_PATH, 0, $p) . '/';
				}
				$path = $rP . $action;
			}
			$url = $_baseURL . Config::value('web.base') . $path;
		}

		if (empty($params)) {
			return $url;
		}

		$urlParts	 = parse_url($url);
		$urlParams	 = array();
		if (isset($urlParts['query'])) {
			parse_str($urlParts['query'], $urlParams);
		}
		$urlParams = array_merge($urlParams, $params);

		$urlParts['query']	 = http_build_query($urlParams);
		$url				 = '//' . $urlParts['host'];
		if (isset($urlParts['path'])) {
			$url .= $urlParts['path'];
		}
		if (!empty($urlParts['query'])) {
			$url .= '?' . $urlParts['query'];
		}
		return $url;

//        if (!empty($params)) {
//            if (is_string($url)) {
//                $url .= '?' . $params;
//            } elseif (is_array($url)) {
//                $url .= '?' . http_build_query($params);
//            }
//        }
//
//        return $url;
//        $cu        = self::protocol() . filter_input(INPUT_SERVER, 'HTTP_HOST');
//        if (filter_input(INPUT_SERVER, 'SERVER_PORT') != '80') {
//            $cu .= ':' . filter_input(INPUT_SERVER, 'SERVER_PORT');
//        }
//        if (is_null($action)) {
//            $cu .= filter_input(INPUT_SERVER, 'REQUEST_URI');
//            return $cu;
//        }
//
//        if (strpos($action, '://')) {
//            $ps = array(
//                'public' => 'LT_PUBLIC/',
//                'theme'  => 'LT_THEME/',
//                'data'   => 'LT_DATA/',
//                'cache'  => 'LT_CACHE/',
//            );
//            list($b, $n) = explode('://', $action);
//            if (!isset($ps[$b])) {
//                self::error('unsupported path protocol');
//            }
//            return self::config('WEB.BASE') . $ps[$b] . $n;
//        }
//
//        $n = $action;
//        if ($n == '.') {
//            $n = self::$_query['path'];
//        } elseif (is_null($n) || ($n === '')) {
//            $n = self::$_action;
//        } elseif ($n[0] == '/') {
//            $n = substr($n, 1);
//        } else {
//            $rP = '';
//            if (FALSE !== ($p  = strrpos(self::$_action, '/'))) {
//                $rP = substr(self::$_action, 0, $p) . '/';
//            }
//            $n = $rP . $n;
//        }
//        $u = $cu;
//        $u .= self::config('WEB.BASE');
//        if (self::config('MUI.ENABLED')) {
//            if (isset($opts['lang'])) {
//                $u .= $opts['lang'] == 'default' ? '/' : ($opts['lang'] . '/');
//            } else {
//                $u .= self::lang() . '/';
//            }
//        }
//        if (self::config('WEB.FIRENDLY_URL')) {
//            $u  .= $n;
//            $ct = '?';
//        } else {
//            $u  .= 'index.php?' . $action;
//            $ct = '&';
//        }
//
//        if (!empty($params)) {
//            if (is_array($params)) {
//                $u .= $ct . http_build_query($params);
//            } elseif (is_string($params)) {
//                $u .= $ct . $params;
//            }
//        }
////        $cu = ((strtolower(filter_input(INPUT_SERVER, 'HTTPS')) == 'on') ? 'https://' : 'http://') . filter_input(INPUT_SERVER, 'SERVER_NAME');
//
//        return $u;
	}

	public static function view($name, $data = array()) {
		$view = new View();
		$view->output($name, $data);
	}

	public static function file($path, $namespace = NULL) {
		
	}

	protected static function _onBeforeRoute() {
		
	}

	protected static function _onAfterRoute() {
		
	}

}
