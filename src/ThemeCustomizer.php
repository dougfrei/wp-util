<?php
namespace WPUtil;

abstract class ThemeCustomizer
{
	/**
	 * Remove sections of the theme customizer by section ID. Example: "custom_css"
	 *
	 * @param array<string> $sections
	 * @return void
	 */
	public static function remove_sections(array $sections): void
	{
		add_action('customize_register', function ($wp_customize) use ($sections) {
			foreach ($sections as $section) {
				$wp_customize->remove_section($section);
			}
		});
	}
}
