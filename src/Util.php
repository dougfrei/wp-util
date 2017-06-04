<?php
namespace WPUtil;

class Util {
	public static function register_autoloader($path='') {
        spl_autoload_register(function($class) use (&$path) {
            $file = trailingslashit($path) . str_replace('\\', '/', $class) . '.php';

            if (file_exists($file)) {
                require $file;
            }
        });
    }

	public static function strip_specific_tags($str, $tags) {
		foreach ($tags as $tag) {
			$str = preg_replace('/<'.$tag.'[^>]*>/i', '', $str);
		    $str = preg_replace('/<\/'.$tag.'>/i', '', $str);
		}

		return trim($str);
	}
}
