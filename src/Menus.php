<?php
namespace Fuse;

class Menus {
	public static function get_menus() {
		$menus = array();

		foreach(get_terms('nav_menu') as $menu) {
			$menus[$menu->term_id] = $menu->name;
		}

		return $menus;
	}

	public static function get_locations() {
		return get_registered_nav_menus();
	}
}
