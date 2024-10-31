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
require_once(ONESKY_PATH . '/class/admin/post.php');
require_once(ONESKY_PATH . '/include/table/posts.php');

class OneSky_Visitor extends OneSky_Class_Abstract {

	protected $_loading_locale = null;

	public function __construct() {
		parent::__construct();
		if ($this->is_get() && isset($_GET['locale'])) {
			$this->_loading_locale = preg_replace('/[^_a-zA-Z]/', '', htmlspecialchars_decode($_GET['locale']));
			setcookie('onesky_locale', $this->_loading_locale);
		}
		else if (isset($_COOKIE['onesky_locale'])) {
			$this->_loading_locale = preg_replace('/[^_a-zA-Z]/', '', $_COOKIE['onesky_locale']);
		}
		else if ($this->_auto_detect_locale) {
			$this->_loading_locale = $this->_detect_locale();
		}
		else {
			$this->_loading_locale = $this->_site_default_locale;
		}
		add_action('pre_get_posts', array($this, 'filter_by_language'));

		add_filter('wp_list_pages', array($this, 'list_pages_filter'));
	}

	public function list_pages_filter($output) {
		preg_match_all('/<li\s*class="page_item [a-zA-Z\-_]*([0-9]*)"[^>]*>\s*<a[^>]*>[^<]*<\/a>\s*<\/li>/', $output, $matches);
		if (isset($matches[1])) {
			$page_list = array();
			foreach ($matches[0] as $idx => $html) {
				$page_list[$matches[1][$idx]] = $html;
			}
			$post_table = new OneSky_Table_Posts();
			$post_data = $post_table->db->get_results($post_table->db->prepare('SELECT * FROM ' . $post_table->name));
			$pids = array();
			foreach ($post_data as $d) {
				$pids[$d->post_id] = true;
				if (isset($page_list[$d->post_id])) {
					$output = str_replace($page_list[$d->post_id], '', $output);
					if ($d->locale == $this->_loading_locale && isset($page_list[$d->original_post_id])) {
						$output = str_replace($page_list[$d->original_post_id], $page_list[$d->post_id], $output);
					}
				}
			}
		}
		if ($this->_site_default_locale != $this->_loading_locale && $this->_filter_post_by_translation_existence == self::FILTER_POST_TRANSLATION_ONLY) {
			$output = $this->_replace_link_no_fallback($output, $page_list, $pids);
		}
		return $output;
	}

	private function _replace_link_no_fallback($output, $page_list, $pids) {
		foreach ($page_list as $pid => $l) {
			if (!isset($pids[$pid])) {
				$output = str_replace($page_list[$pid], '', $output);
			}
		}
		unset($l);
		return $output;
	}

	public function filter_by_language($query) {
		$post_model = new OneSky_Model_Post();
		$post_model->filter_by_language($query, $this->_loading_locale, $this->_site_default_locale, $this->_filter_post_by_translation_existence, null, null, 'both');
		return;
	}

	private function _detect_locale() {
		$browser_locale = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 5);
		$browser_locale = preg_replace('/[^_a-z]/', '', strtolower(str_replace('-', '_', $browser_locale)));
		$browser_locale_short = substr($browser_locale, 0, 2);
		$candidate = null;
		$result = null;
		foreach ($this->_display_locales as $locale => $language) {
			unset($language);
			if (strtolower($locale) == $browser_locale) {
				$result = $locale;
				break;
			}
			else if ($candidate === null && strtolower($locale) == $browser_locale_short) {
				$candidate = $locale;
			}
		}
		if ($result === null) {
			if ($candidate !== null) {
				$result = $candidate;
			}
			else {
				$result = $this->_site_default_locale;
			}
		}
		return $result;
	}

}

?>