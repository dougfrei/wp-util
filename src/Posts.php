<?php
namespace WPUtil;

/**
 * Posts Class containing all Methods related to Posts
 */
abstract class Posts
{
    /**
	 * Check all Post Types and Taxonomies for changes.  If so then flush rewrite rules.
	 **/
	public static function permalinks_update()
	{
		$cache = implode('', get_post_types()).implode('', get_taxonomies());

	    if (get_option('grav_registered_permalinks_cache') != $cache) {
	        flush_rewrite_rules();
	        update_option('grav_registered_permalinks_cache', $cache);
	    }
	}
}
