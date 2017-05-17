<?php
namespace Fuse;

class Scripts {
	static $scripts = array();
	static $hook_registered = false;

	public static function register_hook() {
		if (self::$hook_registered) {
			return;
		}

		$scripts = &self::$scripts;

		add_action('wp_enqueue_scripts', function() use (&$scripts) {
			foreach ($scripts as $name => $params) {
				if (!isset($params['url'])) continue;
				if (!isset($params['deps'])) $params['deps'] = array();
				if (!isset($params['footer'])) $params['footer'] = true;

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

				wp_register_script($name, $params['url'], $params['deps'], $params['version'], $params['footer']);

				if (isset($params['localize'])) {
					wp_localize_script($name, $params['localize']['name'], $params['localize']['data']);
				}

				wp_enqueue_script($name);
			}
		});
		
		self::$hook_registered = true;
	}

	public static function enqueue_scripts($scripts) {
		self::$scripts = array_merge(self::$scripts, $scripts);
		self::register_hook();
	}
}
