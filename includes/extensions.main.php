<?php

	/**
	* Extensions manager which extends the framework capabilities with extra routines
	*
	* @package Scabbia
	* @subpackage Core
	*/
	class extensions {
		/**
		* @ignore
		*/
		public static $selected = array();
		/**
		* @ignore
		*/
		public static $loaded = array();

		/**
		* Loads the extensions module.
		*/
		public static function load() {
			foreach(config::get(config::MAIN, '/extensionList', array()) as $tExtension) {
				self::$selected[] = $tExtension;
			}
		}

		/**
		* Loads the selected extensions.
		*
		* @uses loadExtension()
		*/
		public static function loadExtensions() {
			foreach(self::$selected as &$tExtensionName) {
				self::loadExtension($tExtensionName);
			}
		}

		/**
		* Adds an extension.
		*
		* @param string $uExtensionName the extension
		*/
		public static function loadExtension($uExtensionName) {
			if(in_array($uExtensionName, self::$loaded)) {
				return true;
			}

			if(!class_exists($uExtensionName)) {
				throw new Exception('extension class not loaded - ' . $uExtensionName);
			}

			self::$loaded[] = $uExtensionName;
			$tClassInfo = call_user_func(array($uExtensionName, 'extension_info'));

			if(!COMPILED) {
				if(isset($tClassInfo['phpversion']) && !framework::phpVersion($tClassInfo['phpversion'])) {
					return false;
				}

				if(isset($tClassInfo['phpdepends'])) {
					foreach($tClassInfo['phpdepends'] as &$tExtension) {
						if(!extension_loaded($tExtension)) {
							throw new Exception('php extension is required - dependency: ' . $tExtension . ' for: ' . $uExtensionName);
						}
					}
				}

				if(isset($tClassInfo['fwversion']) && !framework::version($tClassInfo['fwversion'])) {
					return false;
				}

				if(isset($tClassInfo['fwdepends'])) {
					foreach($tClassInfo['fwdepends'] as &$tExtension) {
						// if(!self::add($tExtension)) {
						if(!in_array($tExtension, self::$loaded)) {
							throw new Exception('framework extension is required - dependency: ' . $tExtension . ' for: ' . $uExtensionName);
						}
					}
				}
			}

			if(method_exists($uExtensionName, 'extension_load')) {
				call_user_func(array($uExtensionName, 'extension_load'));
			}

			return true;
		}

		/**
		* Checks weather an extension is selected or not.
		*
		* @return bool selection status.
		*/
		public static function isSelected($uExtensionName) {
			return in_array($uExtensionName, self::$selected);
		}
	}

?>
