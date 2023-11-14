<?php
namespace WPUtil;

use WPUtil\Arrays;

abstract class Scripts
{
	static $scripts = [];
	static $footer_scripts = [];
	static $hooks_registered = false;
	static $has_run = false;
	static $footer_scripts_priority = 999;

	/**
	 * Set the priority of the 'wp_footer' action used internally.
	 *
	 * @param integer $priority
	 * @return void
	 */
	public static function set_footer_scripts_priority(int $priority)
	{
		static::$footer_scripts_priority = $priority;
	}

	/**
	 * Internal callback hook for the 'wp_enqueue_scripts' action.
	 * Do not call directly.
	 *
	 * @return void
	 */
	public static function _wp_enqueue_scripts()
	{
		static::$has_run = true;

		foreach (static::$scripts as $name => $params) {
			$url = Arrays::get_value_as_string($params, 'url');
			$deps = Arrays::get_value_as_array($params, 'deps');
			$version = isset($params['version']) ? $params['version'] : null;
			$in_footer = Arrays::get_value_as_bool($params, 'footer', false);
			$use_async = Arrays::get_value_as_bool($params, 'async', false);
			$use_defer = Arrays::get_value_as_bool($params, 'defer', false);
			$localize = Arrays::get_value_as_array($params, 'localize');
			$register_only = Arrays::get_value_as_bool($params, 'register_only', false);

			if (!$url) {
				continue;
			}

			$register_args = [];

			if ($in_footer) {
				$register_args['in_footer'] = $in_footer;
			}

			if ($use_async || $use_defer) {
				$strategy = '';

				if ($use_async && $use_defer) {
					trigger_error('Both the "defer" and "async" properties are enabled for script "' . $name . '". Only "defer" will be used.', E_USER_WARNING);
					$strategy = 'defer';
				} else if ($use_async) {
					$strategy = 'async';
				} else if ($use_defer) {
					$strategy = 'defer';
				}

				if ($strategy) {
					$register_args['strategy'] = $strategy;
				}
			}

			wp_register_script(
				$name,
				$url,
				$deps,
				is_null($version) ? null : strval($version),
				$register_args
			);

			if ($localize) {
				$localize_name = Arrays::get_value_as_string($localize, 'name');
				$localize_data = Arrays::get_value_as_array($localize, 'data', function () use ($localize, $name) {
					trigger_error('The "data" value of the "localize" parameter for script "' . $name . '" should be a key/value array', E_USER_WARNING);

					return isset($localize['data']) ? [ $localize['data'] ] : [];
				});

				if ($localize_name && $localize_data) {
					wp_localize_script(
						$name,
						$localize_name,
						$localize_data
					);
				}
			}

			if ($register_only) {
				continue;
			}

			wp_enqueue_script($name);
		}
	}

	/**
	 * Internal callback hook for the 'script_loader_tag' filter.
	 * Do not call directly.
	 *
	 * @return void
	 */
	public static function _script_loader_tag(string $tag, string $handle): string
	{
		$attrs = [];

		if (isset(static::$scripts[$handle]['type']) && is_string(static::$scripts[$handle]['type'])) {
			if (function_exists('current_theme_supports') && !current_theme_supports('html5', 'script')) {
				trigger_error("HTML5 support for scripts must be declared using \"add_theme_support('html5', ['script'])\" before a script 'type' can be specified.", E_USER_NOTICE);
			} else {
				$attrs[] = 'type="' . esc_attr(static::$scripts[$handle]['type']) . '"';
			}
		}

		if (isset(static::$scripts[$handle]['nomodule']) && static::$scripts[$handle]['nomodule']) {
			$attrs[] = 'nomodule';
		}

		if ($attrs) {
			$tag = str_replace(' src', ' ' . implode(' ', $attrs) . ' src', $tag);
		}

		return $tag;
	}

	/**
	 * Internal callback hook for the 'wp_footer' action.
	 * Do not call directly.
	 *
	 * @return void
	 */
	public static function _wp_footer()
	{
		foreach (static::$footer_scripts as $params) {
			$url = Arrays::get_value_as_string($params, 'url');
			$version = isset($params['version']) ? $params['version'] : null;
			$use_async = Arrays::get_value_as_bool($params, 'async', false);
			$use_defer = Arrays::get_value_as_bool($params, 'defer', false);
			$localize = Arrays::get_value_as_array($params, 'localize');

			if (!$url) {
				continue;
			}

			if ($localize) {
				$localize_name = Arrays::get_value_as_string($localize, 'name');
				$localize_data = Arrays::get_value_as_string($localize, 'data');

				if ($localize_name && $localize_data) {
					?>
					<script type="text/javascript">
					var <?php $localize_name; ?> = <?php echo json_encode($localize_data); ?>;
					</script>
					<?php
				}
			}

			if ($version) {
				$join_char = (stripos($url, '?') !== false) ? '&' : '?';
				$url .= $join_char . 'ver=' . strval($version);
			}

			$attrs_str = 'src=" ' . esc_url($url) . '"';

			$type = isset($params['type']) && is_string($params['type']) ? trim($params['type']) : '';

			if ($type) {
				$attrs_str = ' type="' . esc_attr($type) . '"';
			}

			if (isset($params['nomodule']) && $params['nomodule']) {
				$attrs_str .= ' nomodule';
			}

			if ($use_async && $use_defer) {
				trigger_error('Both the "defer" and "async" properties are enabled for footer script "' . $url . '". Only "defer" will be used.', E_USER_WARNING);
				$attrs_str .= ' defer';
			} else if ($use_async) {
				$attrs_str .= ' async';
			} else if ($use_defer) {
				$attrs_str .= ' defer';
			}

			echo "<script {$attrs_str}></script>\n";
		}
	}

	/**
	 * Internal callback hook for setting actions and filters.
	 * Do not call directly.
	 *
	 * @return void
	 */
	public static function register_hooks()
	{
		if (static::$hooks_registered) {
			return;
		}

		// register all the scripts
		add_action('wp_enqueue_scripts', [__CLASS__, '_wp_enqueue_scripts']);

		// add "type" and "nomodule" attributes if they are specified
		add_filter('script_loader_tag', [__CLASS__, '_script_loader_tag'], 10, 2);

		add_action('wp_footer', [__CLASS__, '_wp_footer'], static::$footer_scripts_priority);

		static::$hooks_registered = true;
	}

	/**
	 * Takes an array of script objects and updates various values within them
	 *
	 * @param array $scripts
	 * @return array
	 */
	protected static function process_scripts(array $scripts): array
	{
		$scripts_proc = [];

		foreach ($scripts as $name => $params) {
			if (!isset($params['url'])) {
				continue;
			}

			// calculate versions for scripts if they are local files
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

			// add a preload tag if a preload hook is specified
			if (isset($params['preload_hook']) && $params['preload_hook']) {
				$url = $params['url'];

				if (isset($params['version'])) {
					$url .= '?ver=' . $params['version'];
				}

				$crossorigin = isset($params['preload_crossorigin']) && is_string($params['preload_crossorigin']) ? $params['preload_crossorigin'] : '';

				add_action($params['preload_hook'], function() use ($url, $crossorigin) {
					$attrs = [
						'rel="preload"',
						'href="' . esc_url($url) . '"',
						'as="script"'
					];

					if ($crossorigin) {
						$attrs[] = 'crossorigin="' . esc_attr($crossorigin) . '"';
					}

					echo '<link ' . implode(' ', $attrs) . '>' . "\n";
				});
			}

			$scripts_proc[$name] = $params;
		}

		return $scripts_proc;
	}

	/**
	 * Enqueue an array of scripts
	 * Each item can have the following keys:
	 *     'version' (string) - version for the style enqueue
	 *     'url' (string) - URL of the CSS file to enqueue
	 *     'deps' (array) - List of style handles that this enqueue depends on
	 *     'footer' (bool) - Load the script during the 'wp_footer' function
	 *     'async' (bool) - Add the 'async' attribute to the script tag
	 *     'defer' (bool) - Add the 'defer' attribute to the script tag
	 *     'localize' (array) - An array with 'name' and 'data' keys used to localize
	 *         the script by printing inline JS after the script tag has been output.
	 *         The 'data' value will be encoded as JSON.
	 *     'preload_hook' (string) - An optional action hook name that will be used
	 *         to output a '<link rel="preload" href="..." as="script">' tag
	 *     'preload_crossorigin' (string) - Value of the 'crossorigin' attribute used by the preload tag
	 *     'register_only' (bool) - If true, the script will be registered but not enqueued
	 *     'type' (string) - Add the 'type' attribute to the script tag with the provided value
	 *     'nomodule' (bool) - Add the 'nomodule' attribute to the script tag
	 *
	 * @param array $scripts
	 * @return void
	 */
	public static function enqueue_scripts(array $scripts)
	{
		if (static::$has_run) {
			trigger_error('Scripts have already been enqueued and the additional call to '.__METHOD__.' will have no effect. Try '.__CLASS__.'::add_to_footer instead.', E_USER_NOTICE);
		}

		static::$scripts = array_merge(static::$scripts, static::process_scripts($scripts));
		static::register_hooks();
	}

	/**
	 * Output an array of scripts directly to the footer in the 'wp_footer' action.
	 * The 'wp_footer' action priority can be controlled with WPUtil\Scripts::set_footer_scripts_priority
	 *
	 * Each item can have the following keys:
	 *     'version' (string) - version for the style enqueue
	 *     'url' (string) - URL of the CSS file to enqueue
	 *     'async' (bool) - Add the 'async' attribute to the script tag
	 *     'defer' (bool) - Add the 'defer' attribute to the script tag
	 *     'localize' (array) - An array with 'name' and 'data' keys used to localize
	 *         the script by printing inline JS after the script tag has been output.
	 *         The 'data' value will be encoded as JSON.
	 *     'preload_hook' (string) - An optional action hook name that will be used
	 *         to output a '<link rel="preload" href="..." as="script">' tag
	 *     'preload_crossorigin' (string) - Value of the 'crossorigin' attribute used by the preload tag
	 *
	 * @param array $scripts
	 * @return void
	 */
	public static function add_to_footer(array $scripts)
	{
		static::$footer_scripts = array_merge(static::$footer_scripts, static::process_scripts($scripts));
		static::register_hooks();
	}
}
