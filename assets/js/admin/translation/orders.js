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

jQuery(document).ready(function($) {

	jQuery('.publish-translation').bind('click', function() {
		self = jQuery(this);
		
		if (self.hasClass('edit-translation')) {
			var post_id = self.parent().children('.translation-id').val();
			window.location='post.php?post=' + post_id + '&action=edit';
			return;
		}
		
		var item = jQuery(this).parent().children('.item-id').val();
		var data = {
			action		: 'publish',
			locale		: jQuery(this).parent().children('.locale').val(),
			item_id		: item,
			order_id	: jQuery(this).parent().children('.order-id').val()
		};


		jQuery.post(ajaxurl, data, function(response) {
			if (response != 'fail') {
				self.val('Edit this translation');
				self.removeClass('button-primary');
				self.addClass('button-secondary');
				self.removeClass('publish-translation');
				self.addClass('edit-translation');
				jQuery('#' + item).html('Posted');
				jQuery('#' + item).removeClass('new-post');
				jQuery('#' + item).addClass('posted');
				jQuery(self.parent().append('<input type="hidden" name="translation-id" class="translation-id" value="' + response + '"/>'));
			}
			else {
				alert('Original post was deleted. Cannot create post as translation');
			}
		});
	});
	
	jQuery('.edit-translation').bind('click', function() {
		var post_id = jQuery(this).parent().children('.translation-id').val();
		window.location='post.php?post=' + post_id + '&action=edit';
	});

});