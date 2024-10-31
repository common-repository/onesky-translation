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
require_once(ONESKY_PATH . '/include/table/posts.php');

class OneSky_Model_Post extends OneSky_Model_Abstract {

	public function __construct() {
		parent::__construct();
	}

	public function filter_by_language(&$query, $loading_locale, $site_default_locale, $filter, $orderby = null, $order = null, $post_type = 'both') {
		$param = array();
		if ($post_type != 'both') {
			$param['post_type'] = $post_type;
		}
		$post_table = new OneSky_Table_Posts();
		if ($loading_locale == $site_default_locale) {
			$post_ids = $post_table->get_post_ids_by_original_locale();
			if (!empty($post_ids)) {
				$param['post__not_in'] = $post_ids;
			}
			else {
				$param['post__not_in'] = array(-999);
			}
		}
		else {
			if ($filter == OneSky_Class_Abstract::FILTER_POST_TRANSLATION_ONLY) {
				$post_ids = $post_table->get_post_ids_by_translated_locale($loading_locale);
				if (empty($post_ids)) {
					$param['post__in'] = array(-999);
				}
				else {
					$param['post__in'] = $post_ids;
				}
			}
			else {

				$sql = '	SELECT post_id, original_post_id, locale
							FROM ' . $post_table->name;
				$translated_posts = $post_table->db->get_results($post_table->db->prepare($sql));

				$notIn = array();
				foreach ($translated_posts as $p) {
					if ($p->locale != $loading_locale) {
						$notIn[] = $p->post_id;
					}
					else {
						$notIn[] = $p->original_post_id;
					}
				}

				if (!empty($notIn)) {
					$param['post__not_in'] = $notIn;
				}
			}
		}
		if ($orderby !== null) {
			$param['orderby'] = $orderby;
		}
		if ($order !== null) {
			$param['order'] = $order;
		}
		if ($query) {
			foreach ($param as $idx=>$p) {
				$query->query_vars[$idx] = $p;
			}
		}
		else {
			$query = new WP_Query($param);
		}
		return $query;
	}

}

?>