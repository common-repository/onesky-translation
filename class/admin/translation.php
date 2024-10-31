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
require_once(ONESKY_PATH . '/include/translate_list_table.php');
require_once(ONESKY_PATH . '/include/table/posts.php');
require_once(ONESKY_PATH . '/include/table/orders.php');
require_once(ONESKY_PATH . '/include/model/post.php');
require_once(ONESKY_PATH . '/include/model/order.php');
require_once(ONESKY_PATH . '/include/pagination.php');

class OneSky_Translation extends OneSky_Class_Abstract {

	public function __construct() {
		parent::__construct();
	}

	const TYPE_POST_ID = 'post';
	const TYPE_PAGE_ID = 'page';
	const TYPE_POST = 'Post';
	const TYPE_PAGE = 'Page';

	// select translation or order tab
	public function main() {
		if (isset($_GET['section']) && $_GET['section'] == 'orders') {
			$this->orders();
		}
		else if (isset($_GET['section']) && $_GET['section'] == 'place_order') {
			$this->place_order();
		}
		else {
			$this->quote();
		}
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
		echo $view->render('admin/translation/quote/from-language', $params);
	}

	public function filter_by_language($query) {
		$post_model = new OneSky_Model_Post();
		$post_model->filter_by_language($query, $this->_admin_loading_locale, $this->_site_default_locale, self::FILTER_POST_TRANSLATION_ONLY);
		return;
	}

	public function quote() {
		if ($this->is_get() && isset($_GET['loading-locale']) && $this->_admin_loading_locale != $_GET['loading-locale']) {
			update_option(self::ADMIN_LOADING_LOCALE, preg_replace('/[^_a-zA-Z]/', '', $_GET['loading-locale']));
			$this->_admin_loading_locale = preg_replace('/[^_a-zA-Z]/', '', $_GET['loading-locale']);
		}

		add_action('restrict_manage_posts', array($this, 'select_language'));

		$post_type = 'post';
		if (isset($_REQUEST['type']) && strtolower($_REQUEST['type']) == 'page') {
			$post_type = 'page';
		}

		$post_list_table = new Translate_List_Table($this->_admin_loading_locale, $this->_site_default_locale, $post_type);
		$post_list_table->prepare_items();

		$api = new OneSky_Api($this->_api_key, $this->_api_secret, $this->_platform_id);

		$tmp = $api->get_all_locales();
		$languages = array();
		foreach ($tmp['locales'] as $t) {
			if ($t['locale'] != $this->_admin_loading_locale) {
				$languages[$t['locale']] = $t;
			}
		}

		$view = new OneSky_View();

		$params = array();
		$params['page'] = $_REQUEST['page'];
		$params['post_list_table'] = $post_list_table;
		$params['languages'] = $languages;
		$params['post_type'] = $post_type;

		// jquery chosen
		wp_deregister_script('chosen_jquery');
		wp_register_script('chosen_jquery', plugins_url() . '/onesky-translation/assets/js/library/chosen/chosen.jquery.min.js');
		wp_enqueue_script('chosen_jquery');

		wp_deregister_style('chosen_jquery');
		wp_register_style('chosen_jquery', plugins_url() . '/onesky-translation/assets/js/library/chosen/chosen.css');
		wp_enqueue_style('chosen_jquery');

		// self resources
		wp_deregister_script('quote_onesky');
		wp_register_script('quote_onesky', plugins_url() . '/onesky-translation/assets/js/admin/translation/quote.js');
		wp_enqueue_script('quote_onesky');

		wp_deregister_style('quote_onesky');
		wp_register_style('quote_onesky', plugins_url() . '/onesky-translation/assets/css/admin/translation/quote.css');
		wp_enqueue_style('quote_onesky');

		echo $view->render('admin/translation/quote', $params);
	}

	public function ajax_quote() {
		$api = new OneSky_Api($this->_api_key, $this->_api_secret, $this->_platform_id);

		$tmp = $api->get_all_locales();
		$languages = array();
		foreach ($tmp['locales'] as $t) {
			$languages[$t['locale']] = $t;
		}

		$input = array();
		$args = array(
			'include'	=> implode(',', $_POST['posts']),
		);
		$posts = get_posts($args);
		$posts = array_merge($posts, get_pages($args));
		foreach ($posts as $p) {
			$input['post-' . $p->ID] = array(
				'title'		=> (string)$p->post_title,
				'content'	=> (string)$p->post_content,
			);
		}
		$from_locale = preg_replace('/[^_a-zA-Z]/', '', $_POST['from_locale']);
		$to_locales = implode(',', $_POST['to_locales']);
		$quotation = $api->translation_quote($input, $from_locale, $to_locales);

		$view = new OneSky_View();
		$params = array();
		$params['quotation'] = $quotation;
		$params['languages'] = $languages;
		$params['from_locale'] = $_POST['from_locale'];
		$params['to_locales'] = $_POST['to_locales'];
		$params['posts'] = $_POST['posts'];
		$params['insufficient'] = $quotation['total_amount'] > $quotation['credits'];
		if ($quotation['total_amount'] > $quotation['credits']) {
			$params['top_up_link'] = $api->get_url('account/credit');
		}
		echo $view->render('admin/translation/quote/ajax-quotation', $params);
		die();
	}

	public function place_order() {
		if (isset($_POST['place-order']) && $_POST['place-order'] == 'true') {
			$input = array();
			$args = array(
				'include'	=> implode(',', $_POST['posts']),
			);
			$posts = get_posts($args);
			$posts = array_merge($posts, get_pages($args));
			foreach ($posts as $p) {
				$input['post-' . $p->ID] = array(
					'title'		=> $p->post_title,
					'content'	=> $p->post_content,
				);
			}
			$from_locale = $_POST['from-locale'];
			$to_locales = implode(',', $_POST['to-locales']);
			$api = new OneSky_Api($this->_api_key, $this->_api_secret, $this->_platform_id);
			$order = $api->translation_order($input, $from_locale, $to_locales, '');
			if ($order['response'] == 'ok') {
				$order_table = new OneSky_Table_Orders();
				foreach ($_POST['to-locales'] as $l) {
					foreach ($posts as $p) {
						$order_table->insert(array(
							'post_id'		=> (int)$p->ID,
							'locale'		=> preg_replace('/[^_a-zA-Z]/', '', $l),
							'status'		=> OneSky_Model_Order::STATUS_TRANSLATE_ORDERED,
							'created_at'	=> time(),
							'updated_at'	=> time(),
						));
					}
				}
			}
		}

		wp_deregister_script('place_order_onesky');
		wp_register_script('place_order_onesky', plugins_url() . '/onesky-translation/assets/js/admin/translation/place_order.js');
		wp_enqueue_script('place_order_onesky');

		$view = new OneSky_View();
		echo $view->render('admin/translation/place_order', array());
	}

	// list out orders
	public function orders() {
		$per_page = 5;

		$view = new OneSky_View();
		$api = new OneSky_Api($this->_api_key, $this->_api_secret, $this->_platform_id);

		$page = $this->_get_page();

		$order_result = $api->get_orders($per_page * ($page - 1), $per_page);
		$count = $order_result['total_order'];
		$orders = $order_result['orders'];

		$p = $this->_pagination($count, $per_page, $page);

		//may move to ajax
		$translations = array();
		$is_translated = array();
		$order_table = new OneSky_Table_Orders();
		$post_table = new OneSky_Table_Posts();
		$post_data = $post_table->db->get_results($post_table->db->prepare('SELECT * FROM ' . $post_table->name));
		$published_translation = $this->_published_translation($post_data);
		$all_post_ids = array();


		foreach ($orders as $o) {
			if ($o['status'] == 'Completed') {
				$translations[$o['id']] = $api->get_items($o['id'], $o['to_locale'], 0, 100);
				$post_ids = array();
				foreach ($translations[$o['id']]['items'] as $item_id => $item) {
					$post_ids[] = substr($item_id, 5, 10);
				}
				unset($item);
				$all_post_ids = array_merge($all_post_ids, $post_ids);
				$result = $order_table->get_data_by_post_ids_locale($post_ids, $o['to_locale']);
				foreach ($result as $r) {
					if (!isset($is_translated[$r->post_id])) {
						$is_translated[$r->post_id] = array();
					}
					$is_translated[$r->post_id][$o['to_locale']] = $published_translation[$r->post_id][$o['to_locale']];
				}
			}
		}

		$args = array(
			'include'	=> implode(',', $all_post_ids),
		);
		$tmp = get_posts($args);
		$tmp = array_merge($tmp, get_pages($args));
		$original_posts = array();
		foreach ($tmp as $t) {
			$original_posts[$t->ID] = $t;
		}

		$ordered = false;
		if (isset($_GET['ordered']) && $_GET['ordered'] == 'true') {
			$ordered = true;
		}

		$params = array();
		$params['orders'] = $orders;
		$params['translations'] = $translations;
		$params['is_translated'] = $is_translated;
		$params['original_posts'] = $original_posts;
		$params['ordered'] = $ordered;
		$params['pagination'] = $p;

		wp_deregister_script('orders_onesky');
		wp_register_script('orders_onesky', plugins_url() . '/onesky-translation/assets/js/admin/translation/orders.js');
		wp_enqueue_script('orders_onesky');

		wp_deregister_style('orders_onesky');
		wp_register_style('orders_onesky', plugins_url() . '/onesky-translation/assets/css/admin/translation/orders.css');
		wp_enqueue_style('orders_onesky');

		echo $view->render('admin/translation/orders', $params);
	}

	private function _pagination($count, $per_page, $page) {
		if (!$count) {
			OneSky_Utility::error('Order not found. Please order a translation.');
		}
		$p = new pagination;
        $p->items($count);
        $p->limit($per_page);
        $p->target("admin.php?page=onesky_translate&section=orders");
        $p->currentPage($_GET[$p->paging]); // Gets and validates the current page
        $p->calculate();
        $p->parameterName('p');
        $p->adjacents(1);
        $p->page = $page;
		return $p;
	}

	private function _get_page() {
        if (!isset($_GET['p'])) {
            $page = 1;
        }
        else {
            $page = (int)$_GET['p'];
        }
        return $page;
	}

	private function _published_translation($post_data) {
		$published_translation = array();
		if (is_array($post_data)) {
			foreach ($post_data as $d) {
				if (!isset($published_translation[$d->original_post_id])) {
					$published_translation[$d->original_post_id] = array();
				}
				$published_translation[$d->original_post_id][$d->locale] = $d->post_id;
			}
		}
		return $published_translation;
	}

	// post the translation
	public function ajax_publish() {
		$order_locale = str_replace(array('\'', '"', '\\') , '', $_POST['locale']);
		$item_id = $_POST['item_id'];
		$api = new OneSky_Api($this->_api_key, $this->_api_secret, $this->_platform_id);
		$item = $api->get_item($item_id, $order_locale);
		if (is_array($item) && isset($item[$item_id])) {
			$id = substr($item_id, 5, 10);

			// get the very original post here if the post is translation from an intermediate language
			$post_table = new OneSky_Table_Posts();
			$oid = $post_table->db->get_var($post_table->db->prepare('SELECT original_post_id FROM ' . $post_table->name . ' WHERE post_id = ' . (int)$id));
			if ($oid) {
				$id = $oid;
			}

			$original_post = get_post($id);
			if ($original_post) {
				$post = new OneSky_Post();
				$post_id = $post->add_translation($original_post, $item[$item_id]['title'], $item[$item_id]['content'], $order_locale);

				$order_table = new OneSky_Table_Orders();
				$query = '	UPDATE ' . $order_table->name . '
							SET status = ' . (int)OneSky_Model_Order::STATUS_TRANSLATE_PUBLISHED . ', updated_at = ' . time() . '
							WHERE post_id = ' . (int)$post_id . '
							AND locale = \'' . $order_locale . '\'';
				$order_table->db->query($order_table->db->prepare($query));
				$return = $post_id;
			}
			else {
				$return = 'fail';
			}
		}
		echo $return;
		die();
	}

}
