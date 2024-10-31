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

class OneSky_Setting extends OneSky_Class_Abstract {

	public function __construct() {
		parent::__construct();
	}

	public function init() {
		$api = new OneSky_Api($this->_api_key, $this->_api_secret, $this->_platform_id);

		$tmp = $api->get_locales();
		$languages = array();
		foreach ($tmp['locales'] as $t) {
			$languages[$t['locale']] = $t;
		}

		$platform_details = $api->platform_details($this->_platform_id);
		$this->_set_site_default_locale($platform_details['base_locale']);
		$this->_set_language_switcher_position(self::LANGUAGE_SWITCHER_POSITION_MAIN_SIDEBAR);
		$this->_set_filter_post_by_translation_existence(self::FILTER_POST_ORIGINAL_POST);
		$locales = array(
			$platform_details['base_locale']	=> $languages[$platform_details['base_locale']],
		);
		$this->_set_display_locales($locales);
		$this->_set_auto_detect_locale(true);
	}

	public function update() {
		$view = new OneSky_View();
		$api = new OneSky_Api($this->_api_key, $this->_api_secret, $this->_platform_id);

		$platform_details = $api->platform_details($this->_platform_id);

		$tmp = $api->get_locales();
		$languages = array();
		foreach ($tmp['locales'] as $t) {
			$languages[$t['locale']] = $t;
		}

		$language_switcher_position_options = array(
			self::LANGUAGE_SWITCHER_POSITION_MAIN_SIDEBAR	=> 'Main Sidebar',
			self::LANGUAGE_SWITCHER_POSITION_NONE			=> '-- None --',
		);

		$filter_post_by_translation_existence_options = array(
			self::FILTER_POST_ORIGINAL_POST		=> 'Show Posts in Default Language',
			self::FILTER_POST_TRANSLATION_ONLY	=> 'Only Show Posts with Translation',
		);

		$saved = false;
		if (!$this->_display_locales || empty($this->_display_locales)) {
			$this->_set_display_locales(array(
				$platform_details['base_locale']	=> $languages[$platform_details['base_locale']],
			));
		}

		if ($this->is_post()) {
			$this->_set_site_default_locale($platform_details['base_locale']);
			$this->_set_language_switcher_position(preg_replace('/[^\-_\s0-9a-zA-Z]/', '', $_POST['language-switcher-position']));
			$this->_set_filter_post_by_translation_existence(preg_replace('/[^\-_\s0-9a-zA-Z]/', '', $_POST['filter-post-by-translation-existence']));
			$locales = array();
			if (isset($_POST['display-locale'])) {
				foreach ($_POST['display-locale'] as $locale => $dump) {
					$locales[$locale] = $languages[$locale];
				}
				unset($dump);
			}
			$this->_set_display_locales($locales);
			$this->_set_auto_detect_locale((bool)$_POST['auto-detect-locale']);
			$saved = true;
		}

		$params = array();

		$params['saved'] = $saved;
		$params['site_default_locale'] = $platform_details['base_locale'];
		$params['language_switcher_position'] = $this->_language_switcher_position;
		$params['filter_post_by_translation_existence'] = $this->_filter_post_by_translation_existence;
		$params['display_locales'] = $this->_display_locales;
		$params['auto_detect_locale'] = $this->_auto_detect_locale;

		$params['languages'] = $languages;
		$params['language_switcher_position_options'] = $language_switcher_position_options;
		$params['filter_post_by_translation_existence_options'] = $filter_post_by_translation_existence_options;
		$params['signup'] = isset($_REQUEST['signup']);

		echo $view->render('admin/setting/update', $params);
	}

	private function _set_site_default_locale($locale) {
		update_option(self::SITE_DEFAULT_LOCALE, $locale);
		$this->_site_default_locale = $locale;
		return $locale;
	}

	private function _set_language_switcher_position($position) {
		update_option(self::LANGUAGE_SWITCHER_POSITION, $position);
		$this->_language_switcher_position = $position;
		return $position;
	}

	private function _set_filter_post_by_translation_existence($filter) {
		update_option(self::FILTER_POST_BY_TRANSLTION_EXISTENCE, $filter);
		$this->_filter_post_by_translation_existence = $filter;
		return $filter;
	}

	private function _set_display_locales(array $locales) {
		update_option(self::DISPLAY_LOCALES, $locales);
		$this->_display_locales = $locales;
		return $locales;
	}

	private function _set_auto_detect_locale($locale) {
		update_option(self::AUTO_DETECT_LOCALE, $locale);
		$this->_auto_detect_locale = $locale;
		return $locale;
	}

}