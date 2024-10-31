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

class OneSky_Table_Orders extends OneSky_Table_Abstract {

	public $name;

	public function __construct() {
		parent::__construct();
		 $this->name = $this->_db_prefix . 'orders';
	}

	public function create_table() {
		$query = '	CREATE TABLE `' . $this->name . '` (
						`post_id` INT NOT NULL ,
						`locale` VARCHAR( 10 ) NOT NULL ,
						`status` INT NOT NULL ,
						`created_at` INT NOT NULL ,
						`updated_at` INT NOT NULL ,
						PRIMARY KEY ( `post_id`, `locale` )
					) ENGINE = MYISAM';
		return $this->db->query($this->db->prepare($query));
	}

	public function get_data_by_post_ids(array $ids) {
		$result = array();
		if (!empty($ids)) {
			foreach ($ids as &$id) {
				$id = (int)$id;
			}
			$id_string = implode(',', $ids);
			$query = '	SELECT *
						FROM ' . $this->name . '
						WHERE post_id IN (' . $id_string . ')';
			$result = $this->db->get_results($this->db->prepare($query));
		}
		return $result;
	}

	public function get_data_by_post_ids_locale(array $ids, $locale) {
		$result = array();
		if (!empty($ids)) {
			foreach ($ids as &$id) {
				$id = (int)$id;
			}
			$locale = preg_replace('/[^_a-zA-Z]/', '', $locale);
			$id_string = implode(',', $ids);
			$query = '	SELECT *
						FROM ' . $this->name . '
						WHERE post_id IN (' . $id_string . ')
						AND locale = \'' . $locale . '\'';
			$result = $this->db->get_results($this->db->prepare($query));
		}
		return $result;
	}

}

?>