<?php
namespace Fuse;

class SVG {
	public static function get_clean_svg($filename, $opts=array()) {
		$filename = get_template_directory().$filename.'.svg';

		if (!file_exists($filename)) {
			if (isset($opts['debug']) && $opts['debug']) {
				error_log('failed to open: '.$filename);
			}

			return;
		}

		$content = preg_replace(array(
			'/(<\?xml\ .*\?>)/i', // remove XML tag - causes issues with W3C validation
			'/(<!--.*-->)/i', // remove comments
			'/(\<title>[^\<]+\<\/title\>)/', // remove 'title' tag
			'/(\<desc>[^\<]+\<\/desc\>)/', // remove 'desc' tag
			'/\s\s+/', // remove 2+ sequential spaces
			'/(\n|\r)/' // remove line breaks
		), '', file_get_contents($filename));

		// remove 'id' attributes
		if (isset($opts['remove_ids']) && $opts['remove_ids']) {
			$content = preg_replace('/(id=\"[^\"]+\")/', '', $content);
		}

		if (isset($opts['debug']) && $opts['debug']) {
			error_log($filename);
			error_log($content);
		}

		return $content;
	}
}
