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

require_once(ONESKY_PATH . '/include/utility.php');

class OneSky_Api {

	private $app_key;
	private $secret;
	private $platform_id;
	private $api_version = 2;

	public function __construct($api_key = null, $secret = null, $platfrom_id = null) {
		$this->app_key			= $api_key;
		$this->secret			= $secret;
		$this->platform_id		= $platfrom_id;
	}

	private function _auth() {
		if (!$this->app_key) {
			OneSky_Utility::error('Empty OneSky API Key, please go to <a href="admin.php?page=onesky_admin.php">Config Page</a>');
		}
		if (!$this->secret) {
			OneSky_Utility::error('Empty OneSky API Secret, please go to <a href="admin.php?page=onesky_admin.php">Config Page</a>');
		}
		if (!$this->platform_id) {
			OneSky_Utility::error('Empty OneSky platform ID, please go to <a href="admin.php?page=onesky_admin.php">Config Page</a>');
		}
		return true;
	}

	public function sign_up($email, $locale) {
		$result = $this->call_api('system/create-wordpress-admin', array(
			'email'		=> $email,
			'locale'	=> $locale,
		));

        if (isset($result['error'])) {
            OneSky_Utility::error($result['error']);
        }

		return $result['config'];
	}

	public function get_url($uri) {
		$this->_auth();
		$result = $this->call_api('system/get-url', array(
			'uri'	=> $uri,
		));
		return $result['link'];
	}

	public function get_item($itemId, $locale) {
		$this->_auth();
		$result = $this->call_api('item', array(
			'item-id'	=> $itemId,
			'locale'	=> $locale,
		));
		return $result;
	}

	public function get_items($order_id, $locale, $start_from, $number) {
		$this->_auth();
		$result = $this->call_api('items', array(
			'order-id'		=> $order_id,
			'locale'		=> $locale,
			'start-from'	=> $start_from,
			'number'		=> $number,
		));
		return $result;
	}

	public function put_item($itemId, $title, $content) {
		$this->_auth();
		$result = $this->call_api('items/put', array(
			'input'	=> array(
				$itemId	=> array(
					'title'		=> $title,
					'content'	=> $content,
				),
			),
		));
		return $result;
	}

	public function platform_details($platform_id) {
		$this->_auth();
		$result = $this->call_api('platform/details', array(
			'platform-id'	=> (int)$platform_id,
		));
		return $result['platform'];
	}

	/*
	 * return a list of languages that are usable
	 */
	public function get_locales() {
		$this->_auth();
		$result = $this->call_api('platform/locales');
		return $result;
	}

	public function get_all_locales() {
		$result = $this->call_api('locales');
		return $result;
	}

	public function translation_quote($input, $fromLocale, $toLocales) {
		$this->_auth();
		$result = $this->call_api('items/quote', array(
			'input'			=> json_encode($input),
			'from-locale'	=> $fromLocale,
			'to-locales'	=> $this->_handle_to_locales($toLocales),
		));
		return $result;
	}

	public function translation_order($input, $fromLocale, $toLocales) {
		$this->_auth();
		$result = $this->call_api('items/order', array(
			'input'			=> json_encode($input),
			'from-locale'	=> $fromLocale,
			'to-locales'	=> $this->_handle_to_locales($toLocales),
		));
		return $result;
	}

	private function _handle_to_locales($toLocales) {
		if (!is_array($toLocales)) {
			$toLocales = array($toLocales);
		}
		$toLocales = implode(',', $toLocales);
		return $toLocales;
	}

	public function get_orders($start_from, $number) {
		$result = $this->call_api('orders', array(
			'start-from'	=> $start_from,
			'number'		=> $number,
			'order'			=> 'DESC',
		));
		return $result;
	}

	// calling onesky API
	private function &call_api($api, $params = array(), $method = 'post')
	{
		$query_strings = array();
		$ts = time();
		$params['api-key'] = $this->app_key;
		$params['timestamp'] = $ts;
		$params['dev-hash'] = md5($ts . $this->secret);
		$params['platform-id'] = $this->platform_id;
		$params['sdk-type'] = 'php';
		$params['locale-type'] = 'general';

		foreach($params as $name => $value)
		{
			$query_strings[$name] = $value;
		}

		$url_to_be_called = 'https://api.oneskyapp.com/'.$this->api_version.'/' . $api;

		$result = '';

		try{
			$result = $this->send_request($url_to_be_called, $query_strings);
			$decoded_result = json_decode($result, true);
		}catch(Exception $e)
		{
			OneSky_Utility::error('Error occurs when calling api: ' . $e->getMessage());
		}

		if(isset($decoded_result['response']) && $decoded_result['response'] == 'error')
		{
			if (preg_match('/Platform id does not exist.*/', $decoded_result['error'])) {
				OneSky_Utility::error($decoded_result['error'] . ', please go to <a href="admin.php?page=onesky_admin.php">Config Page</a>');
			}
			else if (preg_match('/Invalid application key/', $decoded_result['error'])) {
				OneSky_Utility::error('Invalid API Key, please go to <a href="admin.php?page=onesky_admin.php">Config Page</a>');
			}
			else if (preg_match('/Invalid developer hash or timestamp/', $decoded_result['error'])) {
				OneSky_Utility::error('Invalid API Secret, please go to <a href="admin.php?page=onesky_admin.php">Config Page</a>');
			}
			else if (preg_match('/Insufficient credit/', $decoded_result['error'])) {
				$url = $this->get_url('account/credit');
				OneSky_Utility::error('Insufficient credit, please click <a href="' . $url . '" target="_blank">here</a> to top up your balance');
			}
			else {
				OneSky_Utility::error($decoded_result['error']);
			}
		}
		return $decoded_result;
	}

	private function send_request($url, $body = array()) {
		$request = new WP_Http;
		$result = $request->request($url, array(
			'method'	=> 'POST',
			'body'		=> $body,
			'timeout'	=> 60,
			'sslverify'	=> false,
		));

		if ($result['response']['code'] != 200) {
			OneSky_Utility::error('Failed to connect OneSky API');
		}

		return $result['body'];
	}

}

?>
