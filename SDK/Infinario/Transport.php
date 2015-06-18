<?php
/**
 * Transport class for infinario requests - not depended on cURL libary
 *
 * @author    Youstice
 * @copyright (c) 2015, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

class YousticeInfinarioTransport implements Transport {
	
	public function check(Environment $environment)
	{
		//ignore
	}

	public function post(Environment $environment, $url, $payload)
	{
		return $this->post($environment, $url, $payload);
	}

	public function postAndForget(Environment $environment, $url, $payload)
	{
		$request = stream_context_create(array(
			'http' => array(
				'method' => 'POST',
				'ignore_errors' => false,
				'timeout' => 10.0,
				'content' => Tools::jsonEncode($payload),
				'header' => "Content-Type: application/json\r\n",
			)
		));

		return YousticeTools::file_get_contents($url, false, $request);
	}

}
