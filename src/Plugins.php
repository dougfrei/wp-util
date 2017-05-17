<?php
namespace Fuse;

class Plugins {
	public static function force_activate_plugins($plugins=array()) {
		if (!$plugins) {
			return;
		}

		add_action(is_admin() ? 'admin_init' : 'wp', function() use (&$plugins) {
			if (!is_admin()) {
				include_once (ABSPATH.'wp-admin/includes/plugin.php');
			}

			foreach ($plugins as $plugin) {
				if (!is_plugin_active($plugin)) {
					activate_plugin($plugin);
				}
			}
		});
	}
}
