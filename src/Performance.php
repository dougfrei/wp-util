<?php
namespace WPUtil;

class Performance {
	/**
	 * Remove emojicon support
	 *
	 * @since 2017.01.20
	 * @author DF
	 */
	public static function remove_emojicon_support() {
		add_action('init', function() {
			// all actions related to emojis
			remove_action('admin_print_styles', 'print_emoji_styles');
			remove_action('wp_head', 'print_emoji_detection_script', 7);
			remove_action('admin_print_scripts', 'print_emoji_detection_script');
			remove_action('wp_print_styles', 'print_emoji_styles');
			remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
			remove_filter('the_content_feed', 'wp_staticize_emoji');
			remove_filter('comment_text_rss', 'wp_staticize_emoji');

			// filter to remove TinyMCE emojis
			add_filter('tiny_mce_plugins', function($plugins) {
				if (is_array($plugins)) {
					return array_diff($plugins, array('wpemoji'));
				} else {
					return array();
				}
			});

			// disable emojicon svg url DNS prefetch
			add_filter('emoji_svg_url', '__return_false');
		});
	}

	/**
	 * Remove jquery-migrate script
	 *
	 * @since 2017.01.04
	 * @author DF
	 */
	public static function remove_jquery_migrate() {
		add_filter('wp_default_scripts', function(&$scripts) {
			if (!is_admin()) {
				$scripts->remove('jquery');
				$scripts->add('jquery', false, array( 'jquery-core' ), '1.10.2');
			}
		});
	}

	/**
	 * Move jQuery script include to the footer
	 *
	 * @since 2017.03.27
	 * @author DF
	 */
	public static function move_jquery_to_footer() {
		add_action('wp_enqueue_scripts', function() {
			if (is_admin()) {
		        return;
		    }

		    $wp_scripts = wp_scripts();

		    $wp_scripts->add_data('jquery', 'group', 1);
		    $wp_scripts->add_data('jquery-core', 'group', 1);
		    $wp_scripts->add_data('jquery-migrate', 'group', 1);
		}, 0);
	}
}
