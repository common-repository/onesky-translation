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
require_once(ONESKY_PATH . '/include/table/posts.php');
require_once(ONESKY_PATH . '/include/table/orders.php');

class OneSky_Init extends OneSky_Class_Abstract {

	public function __construct() {
		parent::__construct();
	}

	public function activate() {
		$post_table = new OneSky_Table_Posts();
		$post_table->create_table();
		$order_table = new OneSky_Table_Orders();
		$order_table->create_table();
	}

	public function deactivate() {
		$post_table = new OneSky_Table_Posts();
		$post_table->drop_table();
		$order_table = new OneSky_Table_Orders();
		$order_table->drop_table();

		delete_option(self::API_KEY);
		delete_option(self::API_SECRET);
		delete_option(self::PLATFORM_ID);
		delete_option(self::DISPLAY_LOCALES);
		delete_option(self::SITE_DEFAULT_LOCALE);
		delete_option(self::ADMIN_LOADING_LOCALE);
		delete_option(self::LANGUAGE_SWITCHER_POSITION);
		delete_option(self::FILTER_POST_BY_TRANSLTION_EXISTENCE);
		delete_option(self::AUTO_DETECT_LOCALE);

		delete_option('widget_onesky_widget_language_switcher');
	}

}