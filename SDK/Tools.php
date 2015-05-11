<?php
/**
 * Helper class
 *
 * @author    Youstice
 * @copyright (c) 2015, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

class YousticeTools {

	public static function file_get_contents($url, $use_include_path = false, $stream_context = null, $curl_timeout = 5)
	{
		if ($stream_context == null && preg_match('/^https?:\/\//', $url))
			$stream_context = @stream_context_create(array('http' => array('timeout' => $curl_timeout)));
		if (in_array(ini_get('allow_url_fopen'), array('On', 'on', '1')) || !preg_match('/^https?:\/\//', $url))
			return Tools::file_get_contents($url, $use_include_path, $stream_context);
		elseif (function_exists('curl_init'))
		{
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
			curl_setopt($curl, CURLOPT_TIMEOUT, $curl_timeout);
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
			$content = curl_exec($curl);
			curl_close($curl);
			return $content;
		} else
			return false;
	}

}
