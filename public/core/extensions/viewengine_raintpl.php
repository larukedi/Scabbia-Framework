<?php

if(extensions::isSelected('viewengine_raintpl')) {
	/**
	* ViewEngine: RainTpl Extension
	*
	* @package Scabbia
	* @subpackage Extensions
	*/
	class viewengine_raintpl {
		/**
		* @ignore
		*/
		public static $engine = null;

		/**
		* @ignore
		*/
		public static function extension_info() {
			return array(
				'name' => 'viewengine: raintpl',
				'version' => '1.0.2',
				'phpversion' => '5.1.0',
				'phpdepends' => array(),
				'fwversion' => '1.0',
				'fwdepends' => array('mvc')
			);
		}

		/**
		* @ignore
		*/
		public static function extension_load() {
			mvc::registerViewEngine('rain', 'viewengine_raintpl');
		}

		/**
		* @ignore
		*/
		public static function renderview($uObject) {
			if(is_null(self::$engine)) {
				$tPath = framework::translatePath(config::get('/raintpl/installation/@path', '{core}include/3rdparty/raintpl/inc'));
				require($tPath . '/rain.tpl.class.php');

				raintpl::configure('base_url', null);
				raintpl::configure('tpl_dir', $uObject['templatePath'] . '/');
				raintpl::configure('tpl_ext', '.rain');
				raintpl::configure('cache_dir', $uObject['compiledPath'] . '/');

				if(framework::$development >= 1) {
					raintpl::configure('check_template_update', true);
				}

				self::$engine = new RainTPL();
			}
			else {
				self::$engine = new RainTPL();
			}

			self::$engine->assign('model', $uObject['model']);
			if(is_array($uObject['model'])) {
				foreach($uObject['model'] as $tKey => &$tValue) {
					self::$engine->assign($tKey, $tValue);
				}
			}

			if(isset($uObject['extra'])) {
				foreach($uObject['extra'] as $tKey => &$tValue) {
					self::$engine->assign($tKey, $tValue);
				}
			}

			self::$engine->draw($uObject['viewFile']);
		}
	}
}

?>