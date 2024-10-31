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
require_once(ONESKY_PATH . '/class/admin/config.php');
require_once(ONESKY_PATH . '/class/admin/setting.php');
require_once(ONESKY_PATH . '/class/admin/translation.php');
require_once(ONESKY_PATH . '/class/admin/post.php');
require_once(ONESKY_PATH . '/class/admin/menu.php');
require_once(ONESKY_PATH . '/include/table/posts.php');
require_once(ONESKY_PATH . '/include/table/orders.php');

class OneSky_Admin extends OneSky_Class_Abstract {

	public function __construct() {
		if (!current_user_can('install_plugins')) {
			return;
		}
		parent::__construct();

		add_action('admin_menu', array($this, 'menu'));
		add_action('admin_menu', array($this, 'config'));
		add_action('admin_menu', array($this, 'setting'));
		add_action('admin_menu', array($this, 'translation'));

		$post = new OneSky_Post();
		add_action('delete_post', array($post, 'delete'));

		// register ajax functions
		$translation = new OneSky_Translation();
		add_action('wp_ajax_quotation', array($translation, 'ajax_quote'));
		add_action('wp_ajax_publish', array($translation, 'ajax_publish'));

		$menu = new OneSky_Menu();
	}

	public function menu() {
		if (function_exists('add_menu_page')) {
			add_menu_page('OneSky Translator', 'OneSky', 'administrator', basename(__FILE__), NULL, '', 27 );
		}
	}

	public function config() {
		if (function_exists('add_submenu_page')) {
			$config = new OneSky_Config();
			add_submenu_page(basename(__FILE__), 'OneSky Translator', 'API Config', 'administrator', basename(__FILE__), array($config, 'update'));
		}
	}

	public function setting() {
		if (function_exists('add_submenu_page')) {
			$setting = new OneSky_Setting();
			add_submenu_page(basename(__FILE__), 'OneSky Translator', 'Settings for Visitors', 'administrator', 'onesky_setting', array($setting, 'update'));
		}
	}

	public function translation() {
		if (function_exists('add_submenu_page')) {
			$translation = new OneSky_Translation();
			add_submenu_page(basename(__FILE__), 'OneSky Translator', 'Translate', 'administrator', 'onesky_translate', array($translation, 'main'));
		}
	}

}


?>