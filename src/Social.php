<?php
namespace WPUtil;

class Social {
	static public function share_link($site, $twitter_screen_name='', $post_id=0)
	{
		if (!$post_id) {
			$post_id = get_the_ID();
		}

		$share_link = '';

		switch ($site) {
			case 'facebook':
				$share_link = "window.open('https://www.facebook.com/sharer/sharer.php?u='+encodeURIComponent(location.href),'facebookShare','width=626,height=436');return false;";
				break;

			case 'twitter':
				$share_link = "window.open('https://twitter.com/share?text=".get_the_title($post_id)."&url='+encodeURIComponent(location.href)+'&via=".$twitter_screen_name."','twitterShare','width=626,height=436');return false;";
				break;

			case 'googleplus':
				$share_link = "window.open('https://plus.google.com/share?url='+encodeURIComponent(location.href),'googlePlusShare','width=626,height=436');return false;";
				break;

			case 'pinterest':
				$share_link = "window.open('https://www.pinterest.com/pin/create/button/?url='+encodeURIComponent(location.href)+'&media=&description=".get_the_title($post_id)."','pinterestShare','width=626,height=436');return false;";
				break;

			case 'linkedin':
				$share_link = "window.open('https://www.linkedin.com/cws/share?url='+encodeURIComponent(location.href),'linkedinShare','width=626,height=436');return false;";
				break;
		}

		return $share_link;
	}
}
