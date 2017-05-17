<?php
namespace Fuse;

class CMB2 {
	public static function load_field_files($include_path, $excludes=[]) {
		add_action('cmb2_init', function() use (&$include_path, &$excludes) {
			foreach (glob(trailingslashit($include_path).'*.php') as $file) {
				if (!in_array(basename($file), $excludes)) {
					include_once($file);
				}
			}
		});
	}

	public static function create($params, $fields=array()) {
		// add_action('cmb2_admin_init', function() use (&$params, &$fields) {
			// error_log('adding fields');

			$cmb = new_cmb2_box($params);

			foreach ($fields as $field) {
				$cmb->add_field($field);
			}
		// });
	}
}
