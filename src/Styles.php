<?php
namespace WPUtil;

class Styles {
	public static function enqueue_styles($styles) {
		add_action('wp_enqueue_scripts', function() use (&$styles) {
			foreach ($styles as $name => $params) {
				if (!isset($params['url'])) continue;
				if (!isset($params['deps'])) $params['deps'] = array();

				if (!isset($params['version'])) {
					if (strpos($params['url'], get_template_directory_uri()) !== false) {
						// If Local file then get the time of when it was modified
			        	$file_path = str_replace(get_template_directory_uri(), get_template_directory(), $params['url']);

			            if (file_exists($file_path)) {
			                $params['version'] = filemtime($file_path);
			            }
			        } else {
						// If the value is not set to null WordPress will use it's version number as the script version
						$params['version'] = null;
					}
				}

				wp_enqueue_style($name, $params['url'], $params['deps'], $params['version']);
			}
		});
	}
}
