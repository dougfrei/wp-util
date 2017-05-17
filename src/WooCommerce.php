<?php
namespace Fuse;

class WooCommerce {
	/**
	 * Add theme support for WooCommerce
	 *
	 * @since 2017.01.11
	 * @author DF
	 */
	public static function add_theme_support() {
		add_action('after_setup_theme', function() {
			add_theme_support('woocommerce');
		});
	}

	/**
	 * Modify the default WooCommerce loop output wrapper
	 *
	 * @param  string $openHTML  the open wrapper HTML
	 * @param  string $closeHTML the close wrapper HTML
	 *
	 * @since 2017.01.11
	 * @author DF
	 */
	public static function modify_woocommerce_wrapper($openHTML, $closeHTML) {
		remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
		remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);

		add_action('woocommerce_before_main_content', function() use (&$openHTML) {
			echo $openHTML;
		}, 10);

		add_action('woocommerce_after_main_content', function() use (&$closeHTML) {
			echo $closeHTML;
		}, 10);
	}

	/**
	 * Add custom product variation fields
	 *
	 * @param array $fields array of fields to add
	 *
	 * @since 2017.02.17
	 * @author DF
	 */
	public static function add_product_variation_fields($fields=array()) {

		foreach ($fields as &$field) {
			if (!is_array($field)) {
				throw new Exception(__CLASS__.'::'.__METHOD__.' - fields must be arrays');
			}

			// FIXME: don't check for labels on hidden input types
			if (!isset($field['id']) || !isset($field['label']) || !isset($field['type'])) {
				throw new Exception(__CLASS__.'::'.__METHOD__.' - fields must contain "id", "label", and "type" values');
			}

			if (substr($field['id'], 0, 1) != '_') {
				$field['id'] = '_'.$field['id'];
			}
		}

		// add the custom variation attributes to the admin UI
		add_action('woocommerce_product_after_variable_attributes', function($loop, $variation_data, $variation) use (&$fields) {

			foreach ($fields as $field) {
				$base_values = array(
					'id' => $field['id'].'['.$variation->ID.']',
					'label' => $field['label'],
					'desc_tip' => isset($field['description']) ? true : false,
					'description' => isset($field['description']) ? $field['description'] : '',
					'value' => get_post_meta($variation->ID, $field['id'], true)
				);

				switch ($field['type']) {
					case 'textarea':
						woocommerce_wp_textarea_input(array_merge($base_values, array(
							'placeholder' => isset($field['placeholder']) ? $field['placeholder'] : '',
						)));
						break;

					case 'select':
						woocommerce_wp_select(array_merge($base_values, array(
							'options' => isset($field['options']) ? $field['options'] : '',
						)));
						break;

					case 'checkbox':
						woocommerce_wp_checkbox($base_values);
						break;

					case 'hidden':
						unset($base_values['label']);
						unset($base_values['desc_tip']);
						unset($base_values['description']);

						woocommerce_wp_hidden_input($base_values);
						break;

					case 'text':
					default:
						woocommerce_wp_text_input(array_merge($base_values, array(
							'placeholder' => isset($field['placeholder']) ? $field['placeholder'] : '',
						)));
						break;
				}
			}

		}, 10, 3);

		// save the custom variation attributes when the product is saved/updated
		add_action('woocommerce_save_product_variation', function($post_id) use (&$fields) {

			foreach ($fields as $field) {
				switch ($field['type']) {
					case 'checkbox':
						$data = isset($_POST[$field['id']][ $post_id ]) ? 'yes' : 'no';
						update_post_meta($post_id, $field['id'], $data);

						break;

					default:
						$data = $_POST[$field['id']][$post_id];
						if (!empty($data)) {
							update_post_meta($post_id, $field['id'], esc_attr($data));
						}
						break;
				}
			}

		}, 10, 2);

	}
}
