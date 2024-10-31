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

require_once(ONESKY_PATH . '/include/table/abstract.php');

class OneSky_Table_Posts extends OneSky_Table_Abstract {

	public $name;

	public function __construct() {
		parent::__construct();
		 $this->name = $this->_db_prefix . 'posts';
	}

	public function create_table() {
		$query = '	CREATE TABLE `' . $this->name . '` (
						`post_id` INT NOT NULL ,
						`original_post_id` INT NOT NULL ,
						`locale` VARCHAR( 10 ) NOT NULL ,
						`created_at` INT NOT NULL ,
						PRIMARY KEY ( `post_id` )
					) ENGINE = MYISAM';
		return $this->db->query($this->db->prepare($query));
	}

	public function get_post_ids_by_original_locale() {
		$sql = '	SELECT post_id
					FROM ' . $this->name;
		$post_ids = $this->db->get_col($this->db->prepare($sql));
		return $post_ids;
	}

	public function get_post_ids_by_translated_locale($loading_locale) {
		$sql = '	SELECT post_id
					FROM ' . $this->name . '
					WHERE locale = \'' . $this->db->escape($loading_locale) . '\'';
		$post_ids = $this->db->get_col($this->db->prepare($sql));
		return $post_ids;
	}

}

?>