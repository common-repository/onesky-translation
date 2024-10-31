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
require_once(ONESKY_PATH . '/include/table/orders.php');
require_once(ONESKY_PATH . '/include/table/posts.php');
require_once(ONESKY_PATH . '/include/model/order.php');
require_once(ONESKY_PATH . '/include/model/post.php');

class OneSky_Post extends OneSky_Class_Abstract {

	public function __construct() {
		parent::__construct();

		if ($this->_api_key && preg_match('/\/wp-admin\/edit.php/', $_SERVER['PHP_SELF'])) {
			add_action('restrict_manage_posts', array($this, 'select_language'));
			add_action('pre_get_posts', array($this, 'filter_by_language'));
			add_action('request', array($this, 'request_post'));
			add_filter('manage_posts_columns', array($this, 'translate_column'));
			add_action('manage_posts_custom_column',  array($this, 'translate_column_content'));
			add_filter('manage_pages_columns', array($this, 'translate_column'));
			add_action('manage_pages_custom_column',  array($this, 'translate_column_content'));
		}

		if ($this->_api_key && preg_match('/\/wp-admin\/post.php/', $_SERVER['PHP_SELF']) && isset($_GET['action']) && $_GET['action'] == 'edit') {
			add_action('add_meta_boxes', array($this, 'edit_other_translation_box'));
		}
	}

	public function translate_column($columns) {
		$columns['translate'] = 'Translate';
		return $columns;
	}

	function translate_column_content($name) {
	    global $post, $post_type;
	    if ($name == 'translate') {
	    	echo '<a href="admin.php?page=onesky_translate&post=' . $post->ID . '&loading-locale=' . $this->_admin_loading_locale . '&type=' . $post_type . '">Translate it!</a>';
	    }
	}

	public function edit_other_translation_box() {
		global $post;
		add_meta_box(
			'onesky_edit_other_translations',
			esc_html__('Edit other translations', 'example'),
			array($this, 'edit_other_translation'),
			$post->post_type,
			'side',
			'high'
		);
	}

	public function edit_other_translation() {
		$view = new OneSky_View();

		$api = new OneSky_Api($this->_api_key, $this->_api_secret, $this->_platform_id);

		$tmp = $api->get_all_locales();
		$languages = array();
		foreach ($tmp['locales'] as $t) {
			$languages[$t['locale']] = $t;
		}

		$post_table = new OneSky_Table_Posts();
		$current_post_id = $post_table->db->escape($_GET['post']);

		$sql = '	SELECT *
					FROM ' . $post_table->name . '
					WHERE original_post_id = ' . $current_post_id . '
					OR post_id = ' . $current_post_id;
		$onesky_posts = $post_table->db->get_results($post_table->db->prepare($sql));

		$sql = '	SELECT *
					FROM ' . $post_table->name . '
					WHERE original_post_id = (
						SELECT original_post_id
						FROM ' . $post_table->name . '
						WHERE post_id = ' . $current_post_id . '
					) t1';
		$onesky_posts = array_merge($onesky_posts, $post_table->db->get_results($post_table->db->prepare($sql)));

		$edit_links = array();
		foreach ($onesky_posts as $p) {
			if ($p->post_id != $current_post_id) {
				$post = get_post($p->post_id);
				$locale = $p->locale;
				$post_id = $p->post_id;
			}
			else {
				$post = get_post($p->original_post_id);
				$locale = $this->_site_default_locale;
				$post_id = $p->original_post_id;
			}
			$edit_links[$post->post_title] = array(
				'locale'	=> $languages[$locale],
				'link'		=> get_edit_post_link($post_id),
			);
		}

		$params = array();
		$params['edit_links'] = $edit_links;
		echo $view->render('admin/post/edit-other-translation-box', $params);
	}

	public function request_post($request) {
		if ($this->is_get() && isset($_GET['loading-locale'])) {
			$this->_set_admin_loading_locale(preg_replace('/[^_a-zA-Z]/', '', $_GET['loading-locale']));
		}
		return $request;
	}

	public function select_language() {
		$view = new OneSky_View();
		$api = new OneSky_Api($this->_api_key, $this->_api_secret, $this->_platform_id);

		$tmp = $api->get_locales();
		$languages = array();
		foreach ($tmp['locales'] as $t) {
			$languages[$t['locale']] = $t;
		}

		$params = array();
		$params['languages'] = $languages;
		$params['loading_locale'] = $this->_admin_loading_locale;
		echo $view->render('admin/post/filter-by-language', $params);
	}

	public function filter_by_language($query) {
		global $post_type;
		$post_model = new OneSky_Model_Post();
		$post_model->filter_by_language($query, $this->_admin_loading_locale, $this->_site_default_locale, self::FILTER_POST_TRANSLATION_ONLY, null, null, $post_type);
		return;
	}

	public function add_translation($original_post, $translated_title, $translated_content, $locale) {
		$args = array(
			'post_author'		=> $original_post->post_author,
			'post_date'			=> $original_post->post_date,
			'post_date_gmt'		=> $original_post->post_date_gmt,
			'post_content'		=> $translated_content,
			'post_title'		=> $translated_title,
			//'post_category'		=> $original_post->post_category,
			'post_excerpt'		=> $original_post->post_excerpt,
			'post_status'		=> $original_post->post_status, // publish or draft?
			'comment_status'	=> $original_post->comment_status,
			'ping_status'		=> $original_post->ping_status,
			'post_password'		=> $original_post->post_password,
			'post_parent'		=> $original_post->post_parent,
			'menu_order'		=> $original_post->menu_order,
			'post_type'			=> $original_post->post_type,
		);
		$post_id = wp_insert_post($args);

		if (!$post_id) {
			throw new Exception('Cannot add translated post');
		}

		$post_table = new OneSky_Table_Posts();
		$data = array(
			'post_id'			=> $post_id,
			'original_post_id'	=> $original_post->ID,
			'locale'			=> $locale,
			'created_at'		=> time(),
		);
		$post_table->insert($data);
		return $post_id;
	}

	public function delete($id) {
		$post_table = new OneSky_Table_Posts();
		$row = $post_table->db->get_row($post_table->db->prepare('SELECT * FROM ' . $post_table->name . ' WHERE post_id = ' . (int)$id));
		$data = array(
			'post_id = ' . (int)$id,
		);
		$post_table->delete($data);
		$order_table = new OneSky_Table_Orders();
		$data = array(
			'post_id = ' . (int)$id,
		);
		$order_table->delete($data);
		$sql = '	UPDATE ' . $order_table->name . '
					SET status = ' . (int)OneSky_Model_Order::STATUS_TRANSLATE_ORDERED . '
					WHERE post_id = ' . (int)$row->original_post_id . '
					AND locale = \'' . $row->locale . '\'';
		$order_table->db->query($order_table->db->prepare($sql));
		return;
	}

	private function _set_admin_loading_locale($locale) {
		update_option(self::ADMIN_LOADING_LOCALE, $locale);
		$this->_admin_loading_locale = $locale;
		return $locale;
	}

}