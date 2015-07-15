<?php
/**
 * Helper class
 *
 * @author    Youstice
 * @copyright (c) 2015, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

class YousticeTools {

	public static function file_get_contents($url, $use_include_path = false, $stream_context = null)
	{
		if (in_array(ini_get('allow_url_fopen'), array('On', 'on', '1')) || !preg_match('/^https?:\/\//', $url)) {

			$response = @file_get_contents($url, $use_include_path, $stream_context);

			if (isset($http_response_header)) {
				$matches = array();
				preg_match('#HTTP/\d+\.\d+ (\d+)#', $http_response_header[0], $matches);
				$http_status_code = $matches[1];

				if ($http_status_code != 200 && $http_status_code != 201)
					throw new Exception('', $http_status_code);
			}
			
			return $response;
		}

		if (function_exists('curl_init'))
		{
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
			curl_setopt($curl, CURLOPT_TIMEOUT, 8);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

			if ($stream_context != null)
			{
				$opts = stream_context_get_options($stream_context);
				$headers = array();

				//add headers from stream context
				if (isset($opts['http']['header']))
				{
					$_headers = explode("\r\n", $opts['http']['header']);
					//remove last or empty
					$_headers = array_filter($_headers, 'strlen');

					array_merge($headers, $_headers);
				}

				//set POST fields
				if (isset($opts['http']['method']) && Tools::strtolower($opts['http']['method']) == 'post')
				{
					curl_setopt($curl, CURLOPT_POST, true);
					if (isset($opts['http']['content']))
					{
						$jsonData = $opts['http']['content'];
						curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonData);

						$headers[] = 'Content-Type: application/json';
						$headers[] = 'Content-Length: ' . Tools::strlen($jsonData);
					}
				}

				curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
			}
			$response = curl_exec($curl);
			$http_status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

			if ($http_status_code != 200 && $http_status_code != 201)
				throw new Exception('', $http_status_code);
			
			curl_close($curl);
			
			return $response;
		} else
			return false;
	}

}
