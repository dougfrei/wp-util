<?php
namespace WPUtil;

abstract class TinyMCE
{
	/**
	 * Add formatting options to the TinyMCE formats menu
	 *
	 * @param array $formats
	 * @param boolean $merge_formats
	 * @return void
	 */
	public static function add_formats(array $formats, bool $merge_formats = true): void
	{
		add_filter('tiny_mce_before_init', function ($init_array) use (&$formats, &$merge_formats) {
			/*
			* Each array child is a format with it's own settings
			* Notice that each array has title, block, classes, and wrapper arguments
			* Title is the label which will be visible in Formats menu
			* Block defines whether it is a span, div, selector, or inline style
			* Classes allows you to define CSS classes
			* Wrapper whether or not to add a new block-level element around any selected elements
			*/

			// Insert the array, JSON ENCODED, into 'style_formats'
			$init_array['style_formats'] = json_encode($formats);
			$init_array['style_formats_merge'] = $merge_formats;

			return $init_array;
		});
	}

	/**
	 * Set the TinyMCE editor options
	 *
	 * @param array $options
	 * @return void
	 */
	public static function set_options(array $options = []): void
	{
		add_filter('tiny_mce_before_init', function ($init_array) use (&$options) {
			return array_merge($init_array, $options);
		});
	}

	/**
	 * Remove toolbar items from the TinyMCE by their string IDs.
	 * Examples: "fontselect", "fontsizeselect", "forecolor"
	 *
	 * @param array<string> $toolbar_items
	 * @param integer $filter_priority
	 * @return void
	 */
	public static function remove_toolbar_items(array $toolbar_items, int $filter_priority = 999)
	{
		$tinyMCEremoveToolbarItems = fn (array $items, string $editor_id): array =>
			array_filter(
				$items,
				fn ($item) => !in_array($item, $toolbar_items)
			);

		add_filter('mce_buttons', $tinyMCEremoveToolbarItems, $filter_priority, 2);
		add_filter('mce_buttons_2', $tinyMCEremoveToolbarItems, $filter_priority, 2);
		add_filter('mce_buttons_3', $tinyMCEremoveToolbarItems, $filter_priority, 2);
		add_filter('mce_buttons_4', $tinyMCEremoveToolbarItems, $filter_priority, 2);
	}

	/**
	 * Add the specified stylesheet to the TinyMCE editor. This is a wrapper around
	 * add_editor_style to ensure it's called during the correct hook.
	 *
	 * @param string|array $stylesheet
	 * @return void
	 */
	public static function add_stylesheet($stylesheet)
	{
		add_action('admin_init', function () use (&$stylesheet) {
			add_editor_style($stylesheet);
		});
	}
}
