<?php
namespace Fuse;

class Geo {
	/**
	 * Calculates the distance between two set of coordinate points.
	 * See http://stackoverflow.com/questions/10053358/measuring-the-distance-between-two-coordinates-in-php for more information.
	 *
	 * @param  float  $fromLat           source latitude value
	 * @param  float  $fromLng           source longitude valude
	 * @param  float  $toLat             destination latitude value
	 * @param  float  $toLng             destination longitude value
	 * @param  string  $format           return format - can be 'mi' (default) for miles or 'm' for meters
	 * @param  boolean $vincenty_formula optionally use the vincenty formula for calculation (default is the haversine formula)
	 * @return float                     distance between the two points in the specified format
	 */
	public static function distance_between_points($fromLat, $fromLng, $toLat, $toLng, $format='mi', $vincenty_formula=false) {
		// convert from degrees to radians
		$fromLat = deg2rad($fromLat);
		$fromLng = deg2rad($fromLng);
		$toLat = deg2rad($toLat);
		$toLng = deg2rad($toLng);

		$angle = 0;
		$earthRadius = ($format == 'mi') ? 3959 : 6371000;

		if ($vincenty_formula) {

			$lngDelta = $toLng - $fromLng;
			$a = pow(cos($toLat) * sin($lngDelta), 2) + pow(cos($fromLat) * sin($toLat) - sin($fromLat) * cos($toLat) * cos($lngDelta), 2);
			$b = sin($fromLat) * sin($toLat) + cos($fromLat) * cos($toLat) * cos($lngDelta);

			$angle = atan2(sqrt($a), $b);

		} else {

			$latDelta = $toLat - $fromLat;
			$lngDelta = $toLng - $fromLng;

			$angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($fromLat) * cos($toLat) * pow(sin($lngDelta / 2), 2)));

		}

		return $angle * $earthRadius;
	}
}
