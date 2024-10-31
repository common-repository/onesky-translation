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

require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
require_once(ONESKY_PATH . '/include/model/post.php');
require_once(ONESKY_PATH . '/include/model/order.php');
require_once(ONESKY_PATH . '/include/api.php');
require_once(ONESKY_PATH . '/class/abstract.php');

class Translate_List_Table extends WP_List_Table {

	protected $_loading_locale;
	protected $_site_default_locale;
	protected $_post_type;

	function __construct($loading_locale, $site_default_locale, $post_type) {
		parent::__construct( array(
			'singular'	=> 'post',
			'plural'	=> 'posts',
			'ajax'		=> false,
        ));
		$this->_loading_locale = $loading_locale;
		$this->_site_default_locale = $site_default_locale;
		$this->_post_type = $post_type;
	}

	function column_default($item, $column_name) {
		switch ($column_name) {
			case 'date':
			case 'status':
			case 'languages':
				return $item[$column_name];
			default:
				return print_r($item,true); //Show the whole array for troubleshooting purposes
		}
	}

    function column_title($item) {
        //Return the title contents
        return sprintf('%1$s', $item['title']);
    }

    function column_cb($item) {
    	$checked = '';
		if (isset($_GET['post']) && $_GET['post'] == $item['ID']) {
			$checked = 'checked=checked';
		}
        return sprintf('<input type="checkbox" class="post-checkbox" name="post[]" value="%1$s" %2$s/>', $item['ID'], $checked);
    }

    function get_columns() {
        $columns = array(
        	'cb'		=> '<input type="checkbox" />',
            'title'		=> 'Title',
            'date'		=> 'Date',
            'status'	=> 'Status',
        	'languages'	=> 'Ordered Languages',
        );
        return $columns;
    }

	function extra_tablenav($which) {
		if ($which == 'top') {
			//global $post_type_object, $cat;
			do_action('restrict_manage_posts');

			echo '<div class="alignleft actions">';

			/*
			$dropdown_options = array(
				'show_option_all' => __( 'View all categories' ),
				'hide_empty' => 0,
				'hierarchical' => 1,
				'show_count' => 0,
				'orderby' => 'name',
				'selected' => $cat
			);
			wp_dropdown_categories($dropdown_options);
			*/
			//submit_button(__('Filter'), 'secondary', false, false, array('id' => 'post-query-submit'));

			echo '</div>';
		}
	}

    function get_sortable_columns() {
        $sortable_columns = array(
            'title'		=> array('title', false),     //true means its already sorted
            'date'		=> array('date', true),
            'status'	=> array('status', false)
        );
        return $sortable_columns;
    }

    function prepare_items() {
		$api = new OneSky_Api(get_option(OneSky_Class_Abstract::API_KEY), get_option(OneSky_Class_Abstract::API_SECRET), get_option(OneSky_Class_Abstract::PLATFORM_ID));
		$tmp = $api->get_locales();
		$languages = array();
		foreach ($tmp['locales'] as $t) {
			$languages[$t['locale']] = $t;
		}

        $per_page = 8;

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

		global $post;

		$ids = array();
		$data = array();
        $query = $this->_query();
		while ($query->have_posts()) {
			$query->the_post();
			$data[$post->ID] = array(
				'ID'		=> $post->ID,
				'title'		=> $post->post_title,
				'date'		=> $post->post_date,
				'status'	=> $post->post_status,
				'languages'	=> 'None',
			);
			$ids[] = $post->ID;
		}
		if (!empty($ids)) {
			$order_table = new OneSky_Table_Orders();
			$result = $order_table->get_data_by_post_ids($ids);
			$languages_by_post_id = array();
			foreach ($result as $r) {
				if (!isset($languages_by_post_id[$r->post_id])) {
					$languages_by_post_id[$r->post_id] = array();
				}
				$languages_by_post_id[$r->post_id][$r->locale] = $languages[$r->locale]['name']['eng'];
			}
			foreach ($data as $post_id => $d) {
				if (!empty($languages_by_post_id[$post_id])) {
					$data[$post_id]['languages'] = implode(', ', $languages_by_post_id[$post_id]);
				}
			}
			unset($d);
		}

        $current_page = $this->get_pagenum();
        $total_items = $query->found_posts;
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        $this->items = $data;

        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items/$per_page),
        ) );
    }

    private function _query() {
		if (!isset($_GET['post'])) {
			$post_model = new OneSky_Model_Post();
			$query = false;
			$orderby = null;
			$order = null;
			if (isset($_GET['orderby'])) {
				$orderby = preg_replace('/[^_0-9a-zA-Z]/', '', $_GET['orderby']);
			}
			if (isset($_GET['order'])) {
				$order = preg_replace('/[^_0-9a-zA-Z]/', '', $_GET['order']);
			}
			$query = $post_model->filter_by_language($query, $this->_loading_locale, $this->_site_default_locale, OneSky_Class_Abstract::FILTER_POST_TRANSLATION_ONLY, $orderby, $order, $this->_post_type);
		}
		else {
			$q = 'p';
			if ($this->_post_type == 'page') {
				$q = 'page_id';
			}
			$query = new WP_Query($q . '=' . (int)$_GET['post']);
		}
		return $query;
    }

}