<?php

	namespace Scabbia\Extensions\Oauth;

	use Scabbia\Extensions\Controllers\controller;

	/**
	 * Docs Extension
	 *
	 * @package Scabbia
	 * @subpackage oauth
	 * @version 1.0.5
	 *
	 * @scabbia-fwversion 1.0
	 * @scabbia-fwdepends
	 * @scabbia-phpversion 5.3.0
	 * @scabbia-phpdepends
	 */
	class oauth extends controller {
		/**
		 * @ignore
		 */
		public function index() {
			$this->view('{core}views/oauth/index.php');
		}
	}

	?>