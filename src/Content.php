<?php
namespace Fuse;

class Content {
	/**
	 * Advanced version of `get_template_part` that allows for variables to be passed
	 * to the template being called
	 *
	 * @param  string $slug   Template part slug -- same as `get_template_part` first argument
	 * @param  array  $params Array of arguments to pass. Each key becomes and argument availble
	 *                        to the template. For example, `'my_var' => $my_current_var` would
	 *                        make `$my_var` available in the template with the value of
	 *                        `$my_current_var` initially assigned to it
	 * @param  boolean $output Set this to false to return the template output as a variable
	 * @return string         The template output if `$output` is set to false
	 *
	 * @author DF
	 */
	public static function get_template_part($slug, $params=array(), $output=true) {
		if (!$output) {
			ob_start();
		}

	    if (!$template_file = locate_template("{$slug}.php", false, false)) {
	    	trigger_error("Error locating '{$slug}' for inclusion", E_USER_ERROR);
	    }

	    extract($params, EXTR_SKIP);
	    require($template_file);

		if (!$output) {
			return ob_get_clean();
		}
	}
}
