<?php
/*
Plugin Name: OneSky Translation
Plugin URI: http://www.oneskyapp.com
Description: Build multilingual blog easily with human translation. 1-click to translate. 1-click to publish. Just that simple.
Author: OneSky
Author URI: http://www.oneskyapp.com
Version: 1.0.0
*/

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


define('ONESKY_VERSION', '1.0.0');
define('ONESKY_PATH', dirname(__FILE__));

require_once(ONESKY_PATH . '/class/onesky_admin.php');
require_once(ONESKY_PATH . '/class/onesky_visitor.php');
include_once(ABSPATH . WPINC . '/class-http.php');

add_action('init', 'onesky_init');

if (is_admin()) {
	require_once(ONESKY_PATH . '/class/admin/init.php');
	$init = new OneSky_Init();
	register_activation_hook(__FILE__, array($init, 'activate'));
	register_deactivation_hook(__FILE__, array($init, 'deactivate'));
}
else {
	$onesky = new OneSky_Visitor();
}

function onesky_init() {
	if (is_admin()) {
		// Must wait for init
		$onesky = new OneSky_Admin();
	}
}


?>