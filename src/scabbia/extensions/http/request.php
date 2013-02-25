<?php

	namespace Scabbia\Extensions\Http;

	use Scabbia\Extensions\Http\http;
	use Scabbia\Extensions\String\string;
	use Scabbia\config;

	/**
	 * Request Class
	 *
	 * @package Scabbia
	 * @subpackage LayerExtensions
	 */
	class request {
		/**
		 * @ignore
		 */
		public static $platform = null;
		/**
		 * @ignore
		 */
		public static $crawler = null;
		/**
		 * @ignore
		 */
		public static $crawlerType = null;
		/**
		 * @ignore
		 */
		public static $isAjax = false;
		/**
		 * @ignore
		 */
		public static $queryString;
		/**
		 * @ignore
		 */
		public static $remoteIp;
		/**
		 * @ignore
		 */
		public static $https;
		/**
		 * @ignore
		 */
		public static $host;
		/**
		 * @ignore
		 */
		public static $protocol;
		/**
		 * @ignore
		 */
		public static $method;
		/**
		 * @ignore
		 */
		public static $methodext;
		/**
		 * @ignore
		 */
		public static $isBrowser = false;
		/**
		 * @ignore
		 */
		public static $isRobot = false;
		/**
		 * @ignore
		 */
		public static $isMobile = false;
		/**
		 * @ignore
		 */
		public static $languages = array();
		/**
		 * @ignore
		 */
		public static $contentTypes = array();

		/**
		 * @ignore
		 */
		public static function extensionLoad() {
			// $remoteIp
			if(isset($_SERVER['HTTP_CLIENT_IP'])) {
				self::$remoteIp = $_SERVER['HTTP_CLIENT_IP'];
			}
			else if(!isset($_SERVER['REMOTE_ADDR']) && isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				self::$remoteIp = $_SERVER['HTTP_X_FORWARDED_FOR'];
			}
			else {
				self::$remoteIp = $_SERVER['REMOTE_ADDR'] = getenv('REMOTE_ADDR') or self::$remoteIp = '0.0.0.0';
			}

			// $https
			self::$https = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == '1' || strcasecmp($_SERVER['HTTPS'], 'on') == 0));

			// $protocol
			if(isset($_SERVER['SERVER_PROTOCOL']) && $_SERVER['SERVER_PROTOCOL'] == 'HTTP/1.0') {
				self::$protocol = 'HTTP/1.0';
			}
			else {
				self::$protocol = 'HTTP/1.1';
			}

			// $host
			if(!isset($_SERVER['HTTP_HOST']) || strlen($_SERVER['HTTP_HOST']) == 0) {
				self::$host = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : $_SERVER['SERVER_ADDR'];

				if(isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != '80') {
					self::$host .= $_SERVER['SERVER_PORT'];
				}
			}

			// $method, $methodext, $isAjax
			self::$method = strtolower($_SERVER['REQUEST_METHOD']);
			self::$methodext = self::$method;

			if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
				self::$isAjax = true;
				self::$methodext .= 'ajax';
			}

			// $userAgent
			if(config::get('/http/userAgents/autoCheck', '1') == '1') {
				self::checkUserAgent();
			}

			// self::$browser = get_browser(null, true);

			// $languages, $contentTypes
			self::$languages = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? http::parseHeaderString($_SERVER['HTTP_ACCEPT_LANGUAGE'], true) : array();
			self::$contentTypes = isset($_SERVER['HTTP_ACCEPT']) ? http::parseHeaderString($_SERVER['HTTP_ACCEPT'], true) : array();

			// $queryString
			self::$queryString = $_SERVER['QUERY_STRING'];
			foreach(config::get('/http/rewriteList', array()) as $tRewriteList) {
				if(http::rewrite(self::$queryString, $tRewriteList['match'], $tRewriteList['forward'], (isset($tRewriteList['limitMethods']) ? array_keys($tRewriteList['limitMethods']) : null))) {
					break;
				}
			}
		}

		/**
		 * @ignore
		 */
		public static function checkUserAgent() {
			foreach(config::get('/http/userAgents/platformList', array()) as $tPlatformList) {
				if(preg_match('/' . $tPlatformList['match'] . '/i', $_SERVER['HTTP_USER_AGENT'])) {
					self::$platform = $tPlatformList['name'];
					break;
				}
			}

			foreach(config::get('/http/userAgents/crawlerList', array()) as $tCrawlerList) {
				if(preg_match('/' . $tCrawlerList['match'] . '/i', $_SERVER['HTTP_USER_AGENT'])) {
					self::$crawler = $tCrawlerList['name'];
					self::$crawlerType = $tCrawlerList['type'];

					switch($tCrawlerList['type']) {
					case 'bot':
						self::$isRobot = true;
						break;
					case 'mobile':
						self::$isMobile = true;
						break;
					case 'browser':
					default:
						self::$isBrowser = true;
						break;
					}

					break;
				}
			}
		}

		/**
		 * @ignore
		 */
		public static function checkLanguage($uLanguage = null) {
			if(is_null($uLanguage)) {
				return self::$languages;
			}

			return in_array(strtolower($uLanguage), self::$languages);
		}

		/**
		 * @ignore
		 */
		public static function checkContentType($uContentType = null) {
			if(is_null($uContentType)) {
				return self::$contentTypes;
			}

			return in_array(strtolower($uContentType), self::$contentTypes);
		}

//		public static function is($uType) {
//			$tType = 'is' . ucfirst($uType);
//			return self::${$tType};
//		}
//
//		public static function __callStatic($uMethod, $uArgs) {
//			return self::${$uMethod};
//		}

		/**
		 * @ignore
		 */
		public static function get($uKey, $uDefault = null, $uFilter = null) {
			if(!array_key_exists($uKey, $_GET)) {
				return $uDefault;
			}

			if($uFilter === false) {
				return $_GET[$uKey];
			}

			if(!is_null($uFilter)) {
				$tArgs = array_slice(func_get_args(), 2);
				array_unshift($tArgs, $_GET[$uKey]);

				return call_user_func_array('string::filter', $tArgs);
			}

			return http::xss($_GET[$uKey]);
		}

		/**
		 * @ignore
		 */
		public static function post($uKey, $uDefault = null, $uFilter = null) {
			if(!array_key_exists($uKey, $_POST)) {
				return $uDefault;
			}

			if($uFilter === false) {
				return $_POST[$uKey];
			}

			if(!is_null($uFilter)) {
				$tArgs = array_slice(func_get_args(), 2);
				array_unshift($tArgs, $_POST[$uKey]);

				return call_user_func_array('string::filter', $tArgs);
			}

			return http::xss($_POST[$uKey]);
		}

		/**
		 * @ignore
		 */
		public static function cookie($uKey, $uDefault = null, $uFilter = null) {
			if(!array_key_exists($uKey, $_COOKIE)) {
				return $uDefault;
			}

			if($uFilter === false) {
				return $_COOKIE[$uKey];
			}

			if(!is_null($uFilter)) {
				$tArgs = array_slice(func_get_args(), 2);
				array_unshift($tArgs, $_COOKIE[$uKey]);

				return call_user_func_array('string::filter', $tArgs);
			}

			return http::xss($_COOKIE[$uKey]);
		}


		/**
		 * @ignore
		 */
		public static function getArray($uKeys, $uFilter = null) {
			$tValues = array();
			if(!is_null($uFilter)) {
				$tArgs = array_slice(func_get_args(), 2);
			}

			foreach($uKeys as $tKey) {
				if(!array_key_exists($tKey, $_GET)) {
					continue;
				}

				if($uFilter === false) {
					$tValues[$tKey] = $_GET[$tKey];
					continue;
				}

				if(isset($tArgs)) {
					$tNewArgs = $tArgs;
					array_unshift($tNewArgs, $_GET[$tKey]);

					$tValues[$tKey] = call_user_func_array('string::filter', $tNewArgs);
					continue;
				}

				$tValues[$tKey] = http::xss($_GET[$tKey]);
			}

			return $tValues;
		}

		/**
		 * @ignore
		 */
		public static function postArray($uKeys, $uFilter = null) {
			$tValues = array();
			if(!is_null($uFilter)) {
				$tArgs = array_slice(func_get_args(), 2);
			}

			foreach($uKeys as $tKey) {
				if(!array_key_exists($tKey, $_POST)) {
					continue;
				}

				if($uFilter === false) {
					$tValues[$tKey] = $_POST[$tKey];
					continue;
				}

				if(isset($tArgs)) {
					$tNewArgs = $tArgs;
					array_unshift($tNewArgs, $_POST[$tKey]);

					$tValues[$tKey] = call_user_func_array('string::filter', $tNewArgs);
					continue;
				}

				$tValues[$tKey] = http::xss($_POST[$tKey]);
			}

			return $tValues;
		}

		/**
		 * @ignore
		 */
		public static function cookieArray($uKeys, $uFilter = null) {
			$tValues = array();
			if(!is_null($uFilter)) {
				$tArgs = array_slice(func_get_args(), 2);
			}

			foreach($uKeys as $tKey) {
				if(!array_key_exists($tKey, $_COOKIE)) {
					continue;
				}

				if($uFilter === false) {
					$tValues[$tKey] = $_COOKIE[$tKey];
					continue;
				}

				if(isset($tArgs)) {
					$tNewArgs = $tArgs;
					array_unshift($tNewArgs, $_COOKIE[$tKey]);

					$tValues[$tKey] = call_user_func_array('string::filter', $tNewArgs);
					continue;
				}

				$tValues[$tKey] = http::xss($_COOKIE[$tKey]);
			}

			return $tValues;
		}
	}

?>