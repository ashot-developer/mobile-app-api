/**************************************************************************************
 * @name:       svgavatars.admin.js
 * @version:    1.7
 * @URL:        https://svgavatars.com
 * @copyright:  (c) 2014-2021 DeeThemes (https://codecanyon.net/user/DeeThemes)
 * @licenses:   https://codecanyon.net/licenses/regular
 *              https://codecanyon.net/licenses/extended
***************************************************************************************/
window.jQuery(document).ready(function($) {

	"use strict";

	$(".svg-avatars-wrap #reset_to_default_options").on('change', function() {
		var that = $(this),
			c;
		if( that.is(':checked') ) {
			c = window.confirm(svgAvatars_confirms_in_settings.factory_defaults_msg);
			if (c === true) {
				that.attr('checked', 'checked');
			} else {
				that.removeAttr('checked');
			}
		}
	});

	$(".svg-avatars-wrap #delete_settings_on_uninstall").on('change', function() {
		var that = $(this),
			c;
		if( that.is(':checked') ) {
			c = window.confirm(svgAvatars_confirms_in_settings.delete_options_msg);
			if (c === true) {
				that.attr('checked', 'checked');
			} else {
				that.removeAttr('checked');
			}
		}
	});

});