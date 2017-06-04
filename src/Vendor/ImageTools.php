<?php
namespace WPUtil\Vendor;

class ImageTools {
	public static function get_image_choices($image, $options=array()) {
		if (!isset($options['sizes'])) $options['sizes'] = array('medium', 'medium_large', 'large', 'xlarge', 'xxlarge', 'full-width');
		// if (!isset($options['use_dpr'])) $options['use_dpr'] = false;
		
        // check if image is an id -- treat as post id if it's not an attachment id
		if (is_numeric($image) && get_post_type($image) !== 'attachment') {
			$image = get_post_thumbnail_id($image);
		}
		
        // if image is set as 'featured' use the current post featured image id
		if ($image === 'featured') {
			$image = get_post_thumbnail_id();
		}
		
        $image_choices = array();
		$used_urls = array();
		
        // if the image is an id or an ACF image array with a 'sizes' key
		if (is_numeric($image) || !empty($image['sizes'])) {
		
        	foreach ($options['sizes'] as $size) {
				$url = '';
				$width = 0;
				$height = 0;
				
                if (is_numeric($image)) {
					// get image info from attachment source if it's an attachment id
					$image_info = wp_get_attachment_image_src($image, $size);
				
                	if ($image_info === false) {
						continue;
					}
				
                	$url = $image_info[0];
					$width = $image_info[1];
					$height = $image_info[2];
					$indeterminate = $image_info[3];
				} else {
					// get image info from the 'sizes' array if it's an ACF object
					if (isset($image['sizes'][$size])) {
						$url = $image['sizes'][$size];
						$width = $image['sizes'][$size.'-width'];
						$height = $image['sizes'][$size.'-height'];
					}
				}
				
                // All fields valid? URL already used?
				if (!$url || !$width || !$height || in_array($url, $used_urls)) {
					continue;
				}
				
                // Add the image info to the choices array
				$image_choices[] = array(
					'url' => $url,
					'width' => $width,
					'height' => $height
				);

				// Add the URL to the used array to prevent duplicates
				$used_urls[] = $url;
			}
		}

		// ensure master image is added if this is an ACF object
		if (is_array($image) && !empty($image['sizes'])) {
			if (!in_array($image['url'], $used_urls)) {
				$image_choices[] = array(
					'url' => $image['url'],
					'width' => $image['width'],
					'height' => $image['height']
				);
			}
		}

		$image_choices_attr = implode(';', array_map(function($item) {
			return implode(',', array($item['url'], $item['width'], $item['height']));
		}, $image_choices));
		
        $html_attributes = array(
			// 'style="background-image: url(data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==)"',
			'data-it-sources="'.esc_attr($image_choices_attr).'"'
		);
		
        // if ($options['use_dpr']) {
		// 	$html_attributes[] = 'data-image-choices-dpr="true"';
		// }
		
        return implode(' ', $html_attributes);
	}
}