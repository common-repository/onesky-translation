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

require_once(ONESKY_PATH . '/include/view.php');
require_once(ONESKY_PATH . '/include/api.php');
require_once(ONESKY_PATH . '/include/utility.php');
require_once(ONESKY_PATH . '/include/widget/language_switcher.php');

abstract class OneSky_Class_Abstract {

	protected $_api_key;
	protected $_api_secret;
	protected $_platform_id;
	protected $_site_default_locale;
	protected $_admin_loading_locale;
	protected $_language_switcher_position;
	protected $_filter_post_by_translation_existence;
	protected $_auto_detect_locale;
	protected $_selected_translate_posts;
	protected $_translate_from_locale;
	protected $_translate_to_locales;

	const API_KEY = 'onesky_api_key';
	const API_SECRET = 'onesky_api_secret';
	const PLATFORM_ID = 'onesky_platform_id';
	const DISPLAY_LOCALES = 'onesky_display_locales';
	const SITE_DEFAULT_LOCALE = 'onesky_site_default_locale';
	const ADMIN_LOADING_LOCALE = 'onesky_admin_loading_locale';
	const LANGUAGE_SWITCHER_POSITION = 'onesky_language_switcher_position';
	const FILTER_POST_BY_TRANSLTION_EXISTENCE = 'onesky_filter_post_by_translation_existence';
	const AUTO_DETECT_LOCALE = 'onesky_auto_detect_locale';

	const FILTER_POST_TRANSLATION_ONLY = 'translation_only';
	const FILTER_POST_ORIGINAL_POST = 'show_original';

	const LANGUAGE_SWITCHER_POSITION_MAIN_SIDEBAR = 'main_sidebar';
	const LANGUAGE_SWITCHER_POSITION_NONE = 'none';

	public function __construct() {
		$this->_api_key = get_option(self::API_KEY);
		$this->_api_secret = get_option(self::API_SECRET);
		$this->_platform_id = get_option(self::PLATFORM_ID);
		$this->_display_locales = get_option(self::DISPLAY_LOCALES);
		$this->_site_default_locale = get_option(self::SITE_DEFAULT_LOCALE);
		$this->_admin_loading_locale = get_option(self::ADMIN_LOADING_LOCALE);
		if ($this->_admin_loading_locale === false) {
			$this->_admin_loading_locale = $this->_site_default_locale;
		}
		$this->_language_switcher_position = get_option(self::LANGUAGE_SWITCHER_POSITION);
		$this->_filter_post_by_translation_existence = get_option(self::FILTER_POST_BY_TRANSLTION_EXISTENCE);

		if ($this->_language_switcher_position != self::LANGUAGE_SWITCHER_POSITION_NONE) {
			add_action( 'widgets_init', array($this, 'language_switcher_init'));
		}
		$this->_auto_detect_locale = get_option(self::AUTO_DETECT_LOCALE);
	}

	function language_switcher_init() {
		register_widget('OneSky_Widget_Language_Switcher');
		register_sidebars();
	}

	public function is_post() {
		return !empty($_POST);
	}

	public function is_get() {
		return !empty($_GET);
	}

}

?>