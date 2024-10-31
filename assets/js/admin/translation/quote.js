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

	jQuery('#loading-locale').bind('change', function() {
		window.location='admin.php?page=onesky_translate&loading-locale=' + jQuery(this).val();
	});
	
	jQuery('.post-checkbox').bind('change', function() {
		quote();
	});
	
	jQuery('#target-language').bind('change', function() {
		quote();
	});
	
	function quote() {
		var post_arr = new Array();
		jQuery('.post-checkbox').each(function() {
			if (this.checked) {
				post_arr.push(jQuery(this).val());
			}
		});

		var locale_arr = jQuery('#target-language').val();
		if (post_arr.length > 0 && locale_arr != null && locale_arr.length > 0) {
			var data = {
				action		: 'quotation',
				posts		: post_arr,
				to_locales	: locale_arr,
				from_locale	: jQuery('#loading-locale').val()
			};
	
			jQuery('#quotation-box').html('<p>Quoting... Please wait.</p>');
			jQuery.post(ajaxurl, data, function(response) {
				jQuery('#quotation-box').html(response);
			});
		}
		else {
			jQuery('#quotation-box').html('<p>Please select posts and langauges.</p>');
		}
	}
	
	jQuery('.chzn-select').chosen();
	
});
