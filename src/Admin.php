<?php
namespace Fuse;

class Admin {
	/**
	 * Change the label of an admin menu item and the post type labels associated with it
	 *
	 * @param  string $post_type       The post type (ex: 'post', 'page', 'custom_type', etc...)
	 * @param  string $slug            The URL slug in the admin to target (ex: 'post.php')
	 * @param  string $label           The new label for the item
	 * @param  array  $sub_menu_labels (optional) Array of submenu items in 'slug.php' => 'new label' format
	 * @param  array  $post_labels     (optional) Array of post type label overrides - key is the label field,
	 *                                 value is the new label (ex: 'new_item' => 'New Type Item',
	 *                                 'not_found_in_trash' => 'Type not found in trash')
	 *
	 * @author DF
	 */
	public static function change_menu_label($post_type, $slug, $label, $sub_menu_labels=array(), $post_labels=array()) {
		$label_singular = is_array($label) ? $label[0] : $label;
		$label_plural = is_array($label) ? $label[1] : $label;

		add_action('admin_menu', function() use ($slug, $label_singular, $label_plural, $sub_menu_labels) {
			global $menu;
			global $submenu;

			$menu_index = false;

			foreach ($menu as $menu_key => $menu_values) {
				if ($menu_values[2] == $slug) {
					$menu_index = $menu_key;
					break;
				}
			}

			if ($menu_index === false) {
				return;
			}

			$menu[$menu_index][0] = $label_plural;

			if (isset($submenu[$slug])) {
				foreach ($submenu[$slug] as &$sub_menu_values) {
					if (isset($sub_menu_labels[$sub_menu_values[2]])) {
						$sub_menu_values[0] = $sub_menu_labels[$sub_menu_values[2]];
					}
				}
			}
		});

		add_action('init', function() use ($post_type, $label_singular, $label_plural, $post_labels) {
			global $wp_post_types;

			if (!isset($post_labels)) {
				$post_labels = array();
			}

			$labels = &$wp_post_types[$post_type]->labels;
		    $labels->name = isset($post_labels['name']) ? $post_labels['name'] : $label_plural;
		    $labels->singular_name = isset($post_labels['singular_name']) ? $post_labels['singular_name'] : $label_singular;
		    $labels->add_new = isset($post_labels['add_new']) ? $post_labels['add_new'] : "Add {$label_singular}";
		    $labels->add_new_item = isset($post_labels['add_new_item']) ? $post_labels['add_new_item'] : "Add {$label_singular} Item";
		    $labels->edit_item = isset($post_labels['edit_item']) ? $post_labels['edit_item'] : "Edit {$label_singular}";
		    $labels->new_item = isset($post_labels['new_item']) ? $post_labels['new_item'] : $label_singular;
		    $labels->view_item = isset($post_labels['view_item']) ? $post_labels['view_item'] : "View {$label_singular}";
		    $labels->search_items = isset($post_labels['search_items']) ? $post_labels['search_items'] : "Search {$label_plural}";
		    $labels->not_found = isset($post_labels['not_found']) ? $post_labels['not_found'] : "No {$label_plural} found";
		    $labels->not_found_in_trash = isset($post_labels['not_found_in_trash']) ? $post_labels['not_found_in_trash'] : "No {$label_plural} found in Trash";
		    $labels->all_items = isset($post_labels['all_items']) ? $post_labels['all_items'] : "All {$label_plural}";
		    $labels->menu_name = isset($post_labels['menu_name']) ? $post_labels['menu_name'] : $label_plural;
		    $labels->name_admin_bar = isset($post_labels['menu_admin_bar']) ? $post_labels['menu_admin_bar'] : $label_plural;
		});
	}
}
