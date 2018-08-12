<?php
namespace WPUtil;

abstract class Content
{
	public static function get_related_posts($object_id=0, $opts = [])
	{
		// No object id? Use the current post.
		if (!$object_id) {
			$object_id = the_post_ID();
		}

		// Return an empty array if there is no object id
		if (!$object_id) {
			return array();
		}

		$source_post = get_post($object_id);

		if (!isset($opts['post_type'])) $opts['post_type'] = $source_post->post_type;
		if (!isset($opts['num_posts'])) $opts['num_posts'] = 4;

		// default get_posts arguments
		$base_args = [
			'post_type' => $opts['post_type'],
			'post_status' => 'publish'
		];

		// get taxonomies used by post
		global $wpdb;

		$sql_get_used_taxonomies = $wpdb->prepare("SELECT {$wpdb->term_taxonomy}.term_id, {$wpdb->term_taxonomy}.taxonomy FROM {$wpdb->term_relationships} JOIN {$wpdb->term_taxonomy} ON {$wpdb->term_taxonomy}.term_taxonomy_id = {$wpdb->term_relationships}.term_taxonomy_id WHERE {$wpdb->term_relationships}.object_id = %d", $object_id);
		$results = $wpdb->get_results($sql_get_used_taxonomies);

		if (!$results) {
			// return the most recent posts if there are no taxonomies
			return get_posts(array_merge($base_args, [
				'posts_per_page' => $opts['num_posts']
			]));
		}

		// create 'tax_query' parameters
		$tax_queries = array_map(function($item) use (&$object_id) {
			$terms = get_the_terms($object_id, $item->taxonomy);
			$term_ids = $terms ? array_map(function($term) {
				return $term->term_id;
			}, $terms) : [];

			return array(
				'taxonomy' => $item->taxonomy,
				'field' => 'term_id',
				'terms' => $term_ids,
				'operator' => 'IN'
			);
		}, $results);

		$exclude_ids = [$object_id];
		$return_posts = [];

		// find posts matching all taxonomy terms
		$return_posts = get_posts(array_merge($base_args, [
			'posts_per_page' => $opts['num_posts'],
			'tax_query' => array_merge(['relation' => 'AND'], $tax_queries),
			'post__not_in' => $exclude_ids
		]));

		if (count($return_posts) >= $opts['num_posts']) {
			return $return_posts;
		}

		// find posts matching any taxonomy terms
		$add_exclude_ids = count($return_posts) ? array_map(function($item) { return $item->ID; }, $return_posts) : array();
		$exclude_ids = array_merge([$object_id], $add_exclude_ids);

		$match_some_posts = get_posts(array_merge($base_args, [
			'posts_per_page' => $opts['num_posts']-count($return_posts),
			'tax_query' => array_merge(['relation' => 'OR'], $tax_queries),
			'post__not_in' => $exclude_ids
		]));

		$return_posts = array_merge($return_posts, $match_some_posts);

		if (count($return_posts) >= $opts['num_posts']) {
			return $return_posts;
		}

		// fill out the remainder with the latest posts
		$add_exclude_ids = count($return_posts) ? array_map(function($item) { return $item->ID; }, $return_posts) : array();
		$exclude_ids = array_merge([$object_id], $add_exclude_ids);

		$most_recent_posts = get_posts(array_merge($base_args, [
			'posts_per_page' => $opts['num_posts']-count($return_posts),
			'post__not_in' => $exclude_ids
		]));

		$return_posts = array_merge($return_posts, $most_recent_posts);

		return $return_posts;
	}

	public static function get_excerpt($opts = [])
	{
		// options and variables
		$content = $opts['content'] ?? '';
		$full_content = $opts['full_content'] ?? false;
		$autop = $opts['wpautop'] ?? false;
		$strip_tags = $opts['strip_tags'] ?? true;
		$strip_shortcodes = $opts['strip_shortcodes'] ?? true;
		$prefer_excerpt = $opts['prefer_excerpt'] ?? false;
		$excerpt_length = $opts['length'] ?? apply_filters('excerpt_length', 55);
		$excerpt_more = $opts['more'] ?? apply_filters('excerpt_more', ' ' . '&hellip;');
		$filter_nbsp = $opts['filter_nbsp'] ?? true;
		$post_id = isset($opts['post_id']) ? (int)$opts['post_id'] : get_the_ID();
		$post = get_post($post_id);

		// use manually set content if it exists
		if ($content) {
			if (!$full_content) {
				$content = apply_filters('the_excerpt', $content);
				$content = wp_trim_words($content, $excerpt_length, $excerpt_more);
			}

			return $content;
		}

		// does the excerpt exist and is it preferred
		if ($prefer_excerpt && trim($post->post_excerpt)) {
			if ($autop) {
				return wpautop($post->post_excerpt);
			}

			return $post->post_excerpt;
		}

		$content = $post->post_content;

		// option to strip tags
		if ($strip_tags) {
			$content = strip_tags($content);
		}

		// option to strip shortcodes
		if ($strip_shortcodes) {
			$content = strip_shortcodes($content);
		}

		// escape html
		$content = str_replace([
			']]>',
			'\]\]\>'
		] , ']]&gt;', $content);

		// escape scripts
		$content = preg_replace('@<script[^>]*?>.*?</script>@si', '', $content);

		// handle as excerpt if not full content
		if (!$full_content) {
			$content = apply_filters('the_excerpt', $content);
			$content = wp_trim_words($content, $excerpt_length, $excerpt_more);
		}

		// option to wpautop
		if ($autop) {
			$content = wpautop($content);
		}

		if ($filter_nbsp) {
			$content = str_replace('&nbsp;', '', $content);
		}

		return trim($content);
	}

	public static function filter_p_tags_on_images()
	{
		$process_func = function($content) {
			$content = preg_replace('/<p>\s*(<a .*>)?\s*(<img .* \/>)\s*(<\/a>)?\s*<\/p>/iU', '\1\2\3', $content);
			
			return preg_replace('/<p>\s*(<iframe .*>*.<\/iframe>)\s*<\/p>/iU', '\1', $content);
		};

		add_filter('the_content', $process_func);
		add_filter('the_excerpt', $process_func);
		add_filter('acf/format_value/type=wysiwyg', $process_func);
	}
}
