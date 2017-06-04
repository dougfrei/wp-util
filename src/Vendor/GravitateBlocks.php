<?php
namespace WPUtil\Vendor;

class GravitateBlocks {
	/**
	 * Enforce background color choices
	 *
	 * @since 2017.01.20
	 * @author DF
	 */
	public static function enforce_background_colors($new_colors) {
		if (!is_array($new_colors)) {
			return;
		}
		add_filter('grav_block_background_colors', function($colors) use (&$new_colors) {
			return $new_colors;
		});
	}
}