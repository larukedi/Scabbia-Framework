<?php

if(extensions::isSelected('access')) {
	/**
	* Access Extension
	*
	* @package Scabbia
	* @subpackage Extensions
	*/
	class access {
		/**
		* @ignore
		*/
		public static $maintenance = 0;
		/**
		* @ignore
		*/
		public static $maintenanceExcludeIps = array();
		/**
		* @ignore
		*/
		public static $ipFilters = array();

		public static function extension_info() {
			return array(
				'name' => 'access',
				'version' => '1.0.2',
				'phpversion' => '5.1.0',
				'phpdepends' => array(),
				'fwversion' => '1.0',
				'fwdepends' => array('http')
			);
		}
		
		public static function extension_load() {
			events::register('run', events::Callback('access::run'));

			self::$maintenance = intval(config::get('/access/maintenance/@mode', '0')) > 0;
			foreach(config::get('/access/maintenanceExcludeList', array()) as $tMaintenanceExcludeIp) {
				self::$maintenanceExcludeIps[] = $tMaintenanceExcludeIp['@ip'];
			}

			foreach(config::get('/access/ipFilterList', array()) as $tIpFilterList) {
				if(preg_match('/^' . str_replace(array('.', '*', '?'), array('\\.', '[0-9]{1,3}', '[0-9]{1}'), $tIpFilterList['@pattern']) . '$/i', $_SERVER['REMOTE_ADDR'])) {
					if($tIpFilterList['@type'] == 'allow') {
						self::$ipFilters = array();
						continue;
					}

					self::$ipFilters[] = $tIpFilterList['@pattern'];
				}
			}
		}

		public static function run() {
			if(self::$maintenance && !in_array($_SERVER['REMOTE_ADDR'], self::$maintenanceExcludeIps)) {
				$tFile = framework::translatePath(config::get('/access/maintenance/@page'));
				include($tFile);

				// to interrupt event-chain execution
				return false;
			}

			if(count(self::$ipFilters) > 0) {
				$tFile = framework::translatePath(config::get('/access/ipFilter/@page'));
				include($tFile);

				// to interrupt event-chain execution
				return false;
			}
		}
	}
}

?>
