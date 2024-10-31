<?php
/*  Copyright 2012  OneSky  (email : support@oneskyapp.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

require_once(ONESKY_PATH . '/class/abstract.php');

class OneSky_Config extends OneSky_Class_Abstract {

	public function __construct() {
		parent::__construct();
	}

	public function update() {
		if (!$this->_api_key && !isset($_REQUEST['input_auth'])) {
			return $this->signup();
		}

		$view = new OneSky_View();

		$saved = false;

		$is_new_user = !$this->_api_key;
		if ($this->is_post()) {
			$this->_set_api_key(preg_replace('/[^0-9a-zA-Z]/', '', $_POST['api-key']));
			$this->_set_api_secret(preg_replace('/[^0-9a-zA-Z]/', '', $_POST['api-secret']));
			$this->_set_platform_id(preg_replace('/[^0-9a-zA-Z]/', '', $_POST['platform-id']));
			$saved = true;

			if ($is_new_user) {
				$setting = new OneSky_Setting();
				$setting->init();
			}
		}

		$params = array();

		$params['saved'] = $saved;
		$params['api_key'] = $this->_api_key;
		$params['api_secret'] = $this->_api_secret;
		$params['platform_id'] = $this->_platform_id;

		echo $view->render('admin/config/update', $params);
	}

	public function signup() {
		$view = new OneSky_View();

		$api = new OneSky_Api();

		$tmp = $api->get_all_locales();
		$languages = array();
		foreach ($tmp['locales'] as $t) {
			$languages[$t['locale']] = $t;
		}

		if ($this->is_post()) {
			$result = $api->sign_up($_POST['email'], $_POST['locale']);
			$this->_set_api_key(preg_replace('/[^0-9a-zA-Z]/', '', $result['api_key']));
			$this->_set_api_secret(preg_replace('/[^0-9a-zA-Z]/', '', $result['api_secret']));
			$this->_set_platform_id((int)$result['platform_id']);

			$setting = new OneSky_Setting();
			$setting->init();

			wp_deregister_script('signup_redirect_onesky');
			wp_register_script('signup_redirect_onesky', plugins_url() . '/onesky-translation/assets/js/admin/config/signup_redirect.js');
			wp_enqueue_script('signup_redirect_onesky');

			return $view->render('admin/config/signup-redirect');
		}

		$params = array();

		$params['languages'] = $languages;

		echo $view->render('admin/config/signup', $params);
	}

	private function _set_api_key($key) {
		update_option(self::API_KEY, $key);
		$this->_api_key = $key;
		return $key;
	}

	private function _set_api_secret($secret) {
		update_option(self::API_SECRET, $secret);
		$this->_api_secret = $secret;
		return $secret;
	}

	private function _set_platform_id($id) {
		update_option(self::PLATFORM_ID, $id);
		$this->_platform_id = $id;
		return $id;
	}

}
