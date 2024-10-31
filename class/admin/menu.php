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

class OneSky_Menu extends OneSky_Class_Abstract {

	public function __construct() {
		global $pagenow;
		parent::__construct();

		if ($this->_api_key && $pagenow == 'nav-menus.php') {
			add_action('pre_get_posts', array($this, 'filter_by_original_language'));
		}
	}

	public function filter_by_original_language($query) {
		$post_model = new OneSky_Model_Post();
		$post_model->filter_by_language($query, $this->_site_default_locale, $this->_site_default_locale, self::FILTER_POST_TRANSLATION_ONLY, null, null, 'page');
		return;
	}

}