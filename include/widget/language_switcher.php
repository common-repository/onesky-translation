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

class OneSky_Widget_Language_Switcher extends WP_Widget {

	const WIDGET_ID = 'onesky_widget_language_switcher';
	const WIDGET_NAME = 'OneSky Language Switcher';

	public function __construct() {
		parent::__construct(
	 		self::WIDGET_ID, // Base ID
			self::WIDGET_NAME, // Name
			array(
				'description'	=> __( 'Visitor can switch language by this widget', 'text_domain' ),
			)
		);
		$this->_autoloader();
	}

	private function _autoloader() {
		if (!get_option('widget_' . self::WIDGET_ID)) {
			$sidebar_options = get_option('sidebars_widgets');
			$add_to_sidebar = 'sidebar-1';
			if (!isset($sidebar_options[$add_to_sidebar])) {
				$sidebar_options[$add_to_sidebar] = array('_multiwidget' => 1);
			}

			//better handle duplicate widget here

			$widget = get_option('widget_' . self::WIDGET_ID);
			if (!is_array($widget)) {
				$widget = array();
			}
			$count = count($widget) + 1;

			array_unshift($sidebar_options[$add_to_sidebar], self::WIDGET_ID . '-' . $count);
			$widget[$count] = array(
				'title'		=> 'SELECAT LANGUAGE',
			);
			update_option('sidebars_widgets', $sidebar_options);
			update_option('widget_' . self::WIDGET_ID, $widget);
		}
	}

	public function widget($args, $instance) {
		extract($args);
		$title = apply_filters( 'widget_title', $instance['title'] );
		$display_locales = get_option('onesky_display_locales');
		$locale = '';
		if (isset($_GET['locale'])) {
			$locale = $_GET['locale'];
		}
		else if (isset($_COOKIE['onesky_locale'])) {
			$locale = $_COOKIE['onesky_locale'];
		}

		echo $before_widget;
		echo $before_title . $title . $after_title;
		?>
		<select id="language-switcher">
		<?php foreach ($display_locales as $l):?>
			<option value="<?php echo $l['locale'];?>" <?php echo $l['locale'] == $locale ? 'selected=selected' : '';?>><?php echo $l['name']['local'];?></option>
		<?php endforeach;?>
		</select>
		<script>
			var switcher = document.getElementById('language-switcher');
			switcher.onchange = function() {
				window.location = '?&locale=' + this.value;
			};
		</script>
		<?php
		echo $after_widget;
	}

	public function update($new_instance, $old_instance) {
		$instance = array();
		$instance['title'] = strip_tags($new_instance['title']);

		return $instance;
	}

	public function form($instance) {
		$defaults = array(
			'title'	=> 'Select Language',
		);
		$instance = wp_parse_args((array)$instance, $defaults);
		$title = apply_filters( 'widget_title', $instance['title'] );
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<?php
	}

}

?>