<?php

namespace GoogleApi;


class Maps
{
	public static function getLocationData($address)
	{
		$data             = self::getLatLong($address);
		$data['timezone'] = self::getLocationTimezone($data['lat'], $data['lng'], date('U'));

		return $data;
	}


	public static function getLocationTimezone($lat, $lng, $timestamp)
	{
		$url = 'https://maps.googleapis.com/maps/api/timezone/json?timestamp='.$timestamp.'&sensor=false&location='.$lat.','.$lng;

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL,            $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER,         0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

		$result = curl_exec($ch);

		curl_close($ch);

		$result = json_decode($result, true);

		return $result['timeZoneId'];
	}



	public static function getLatLong($address)
	{
		$url = 'http://maps.googleapis.com/maps/api/geocode/json?sensor=false&address='.urlencode($address);

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL,            $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER,         0);

		$result = curl_exec($ch);

		curl_close($ch);

		$result = json_decode($result, true);
		$result = $result['results'][0];

		return array(
			'name'  => $result['formatted_address'],
			'lat'   => $result['geometry']['location']['lat'],
			'lng'   => $result['geometry']['location']['lng'],
		);
  }

}
