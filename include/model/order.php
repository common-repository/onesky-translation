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

require_once(ONESKY_PATH . '/include/model/abstract.php');
require_once(ONESKY_PATH . '/class/abstract.php');
require_once(ONESKY_PATH . '/include/table/orders.php');

class OneSky_Model_Order extends OneSky_Model_Abstract {

	const STATUS_TRANSLATE_ORDERED = 11;
	const STATUS_TRANSLATE_PUBLISHED = 51;

	public function __construct() {
		parent::__construct();
	}

}

?>