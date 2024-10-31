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

abstract class OneSky_Table_Abstract {

	protected $_db_prefix;
	public $db;
	public $name;

	public function __construct() {
		global $wpdb;
		$this->db = $wpdb;
		$this->_db_prefix = $this->db->prefix . 'onesky_';
	}

	public function drop_table() {
		$query = 'DROP TABLE `' . $this->name . '`';
		return $this->db->query($this->db->prepare($query));
	}

	public function insert($data) {
		$data = $this->_escape($data);
		return $this->db->insert($this->name, $data);
	}

	public function delete(array $data) {
		$query = '	DELETE FROM `' . $this->name . '`';
		$data = $this->_escape($data);
		if (!empty($data)) {
			$query .= ' WHERE ' . implode(' AND ', $data);
		}
		return $this->db->query($this->db->prepare($query));
	}

	protected function _escape($data) {
		if (is_array($data)) {
			foreach ($data as $idx => $d) {
				unset($data[$idx]);
				$data[$this->db->escape($idx)] = $this->db->escape($d);
			}
		}
		else {
			$data = $this->db->escape($data);
		}
		return $data;
	}

}

?>