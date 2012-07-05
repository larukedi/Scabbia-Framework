<?php

if(extensions::isSelected('cache')) {
	/**
	* Cache Extension
	*
	* @package Scabbia
	* @subpackage Extensions
	*/
	class cache {
		public static $path;
		public static $defaultAge;
		public static $defaultEncryptKey;

		public static function extension_info() {
			return array(
				'name' => 'cache',
				'version' => '1.0.2',
				'phpversion' => '5.1.0',
				'phpdepends' => array(),
				'fwversion' => '1.0',
				'fwdepends' => array('io')
			);
		}

		public static function extension_load() {
			self::$path = framework::translatePath(config::get('/cache/@path', '{app}writable/cache'));
			self::$defaultAge = intval(config::get('/cache/@defaultAge', '120'));
			self::$defaultEncryptKey = config::get('/cache/@defaultEncryptKey', null);
		}

		public static function getPath($uFolder, $uHash, $uAge = 0) {
			// path
			$tPath = self::$path . '/' . $uFolder . '/';
			if(!is_dir($tPath)) {
				mkdir($tPath);
			}

			$tPath .= $uHash;

			// age
			if($uAge > 0) {
				$tAge = $uAge;
			}
			else {
				$tAge = self::$defaultAge;
			}

			// check
			if(
				framework::$development ||
				!file_exists($tPath) ||
				time() - filemtime($tPath) >= $tAge
			) {
				return array(false, $tPath);
			}

			return array(true, $tPath);
		}

		public static function get($uFolder, $uHash, $uAge) {
			// path
			$tPath = self::getPath($uFolder, $uHash, $uAge);
			
			// content
			return io::readSerialize($tPath[1], self::$defaultEncryptKey);
		}

		public static function set($uFolder, $uHash, $uObject) {
			// path
			$tPath = self::$path . '/' . $uFolder . '/';
			if(!is_dir($tPath)) {
				mkdir($tPath);
			}

			$tPath .= $uHash;

			// content
			io::writeSerialize($tPath, $uObject, self::$defaultEncryptKey);

			return $tPath;
		}

		public static function garbageCollect($uFolder, $uAge) {
			// path
			$tPath = self::$path . '/' . $uFolder;
			$tDirectory = new DirectoryIterator($tPath);
			
			// age
			if($uAge > 0) {
				$tAge = $uAge;
			}
			else {
				$tAge = self::$defaultAge;
			}

			clearstatcache();
			foreach($tDirectory as $tFile) {
				if(!$tFile->isFile()) {
					continue;
				}

				if(time() - $tFile->getMTime() < $tAge) {
					continue;
				}

				unlink($tFile->getPathname());
			}
		}
	}
}

?>