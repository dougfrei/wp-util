<?php
namespace Fuse;

class Util {
	public static function strip_specific_tags($str, $tags) {
		foreach ($tags as $tag) {
			$str = preg_replace('/<'.$tag.'[^>]*>/i', '', $str);
		    $str = preg_replace('/<\/'.$tag.'>/i', '', $str);
		}

		return trim($str);
	}
}
