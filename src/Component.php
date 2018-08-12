<?php
namespace WPUtil;

class Component {
	public static function render($filename, $params = [])
	{
		if (!$template_file = locate_template("{$filename}.php", false, false)) {
	    	trigger_error("Error locating '{$filename}' for inclusion", E_USER_ERROR);
	    }

	    extract($params, EXTR_SKIP);
	    include($template_file);
	}

	public static function render_to_string($filename, $params = [])
	{
		ob_start();

		self::render($params);

		return ob_get_clean();
	}
}
