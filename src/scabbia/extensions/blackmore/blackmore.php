<?php

	namespace Scabbia\Extensions\Blackmore;

	use Scabbia\Extensions\Auth\auth;
	use Scabbia\Extensions\Http\request;
	use Scabbia\Extensions\Mvc\controller;
	use Scabbia\Extensions\Validation\validation;
	use Scabbia\events;

	/**
	 * Blackmore Extension
	 *
	 * @package Scabbia
	 * @subpackage blackmore
	 * @version 1.1.0
	 *
	 * @scabbia-fwversion 1.1
	 * @scabbia-fwdepends string, resources, validation, http, auth, zmodels
	 * @scabbia-phpversion 5.3.0
	 * @scabbia-phpdepends
	 */
	class blackmore extends controller {
		/**
		 * @ignore
		 */
		public static $menuItems = array();
		/**
		 * @ignore
		 */
		public static $modules = array();
		/**
		 * @ignore
		 */
		public static $module;

		/**
		 * @ignore
		 */
		public function render($uAction, $uParams, $uInput) {
			self::$modules['index'] = array(
				'title' => 'Dashboard',
				'callback' => array(&$this, 'index')
			);

			$tParms = array(
				'modules' => &self::$modules
			);
			events::invoke('blackmoreRegisterModules', $tParms);

			self::$modules['login'] = array(
				'title' => 'Logout',
				'callback' => array(&$this, 'login')
			);

			if(!isset(self::$modules[$uAction])) {
				return false;
			}

			self::$module = $uAction;

			if(count($uParams) > 0) {
				foreach(self::$modules[$uAction]['actions'] as $tAction) {
					if($uParams[0] != $tAction['action']) {
						continue;
					}

					return call_user_func_array($tAction['callback'], $uParams);
				}
			}

			return call_user_func_array(self::$modules[$uAction]['callback'], $uParams);
		}

		/**
		 * @ignore
		 */
		public function login() {
			if(request::$method != 'post') {
				auth::clear();

				$this->viewFile('{vendor}views/blackmore/login.php');

				return;
			}

			// validations
			validation::addRule('username')->isRequired()->errorMessage('Username shouldn\'t be blank.');
			// validation::addRule('username')->isEmail()->errorMessage('Please consider your e-mail address once again.');
			validation::addRule('password')->isRequired()->errorMessage('Password shouldn\'t be blank.');
			validation::addRule('password')->lengthMinimum(4)->errorMessage('Password should be longer than 4 characters at least.');

			if(!validation::validate($_POST)) {
				$this->set('error', implode('<br />', validation::getErrorMessages(true)));
				$this->viewFile('{vendor}views/blackmore/login.php');

				return;
			}

			$username = request::post('username');
			$password = request::post('password');

			// user not found
			if(!auth::login($username, $password)) {
				$this->set('error', 'User not found');
				$this->viewFile('{vendor}views/blackmore/login.php');

				return;
			}

			$this->redirect('blackmore/index');
		}

		/**
		 * @ignore
		 */
		public function index() {
			auth::checkRedirect('user');

			$this->viewFile('{vendor}views/blackmore/index.php');
		}
	}

	?>