<?php
/**
 * Base communication interface
 *
 * @author    Youstice
 * @copyright (c) 2014, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

namespace Youstice;

class Request {

	private $authLogin = 'adminapi';
	private $authPassw = 'AdminApi';
	protected $response = null;
	private $additionalParams = array();

	public function returnResponse() {
		return $this->response;
	}

	public function responseToArray() {
		$data = json_decode($this->response, true);

		return $data;
	}
	
	public function setAdditionalParam($key, $val) {
	    $this->additionalParams[$key] = $val;
	}

	protected function generateUrl($url) {
	    $apiUrl = $this->useSandbox ? $this->apiSandboxUrl : $this->apiUrl;
	    
	    $returnUrl = $apiUrl . $url . '/' . $this->apiKey . "?"
			    . "version=1&channel=" . $this->shopSoftwareType;
	    
	    if(count($this->additionalParams)) {
		foreach($this->additionalParams as $key => $val) {
		    $returnUrl .= "&$key=$val";
		}
		
		$this->additionalParams = array();
	    }
	    
	    return $returnUrl;
	}

	public function post($url, $data = array()) {
		$url = $this->generateUrl($url);

		if (function_exists('curl_version'))
			$this->postCurl($url, $data);
		else
			$this->postStream($url, $data);

		if ($this->response === false || $this->response === null)
			throw new \Exception('Post Request failed: ' . $url);

		if(strpos($this->response, "Invalid api key") !== false || strpos($this->response, "Invalid api key") !== false)
			throw new InvalidApiKeyException;

		return $this->response;
	}

	public function get($url) {
		$url = $this->generateUrl($url);

		if (function_exists('curl_version'))
			$this->getCurl($url);
		else
			$this->getStream($url);

		if ($this->response === false || $this->response === null)
			throw new \Exception('get Request failed: ' . $url);

		if(strpos($this->response, "Invalid api key") !== false || strpos($this->response, "Invalid api key") !== false)
			throw new InvalidApiKeyException;

		return $this->response;
	}

	protected function getCurl($url) {
		$header = array();
		$header[] = "Accept-Language: " . $this->lang;
		$header[] = "Content-Type: application/json";
		$ch = curl_init($url);
		curl_setopt_array($ch, array(
			CURLOPT_USERPWD => $this->authLogin . ":" . $this->authPassw,
			CURLOPT_HTTPAUTH, CURLAUTH_ANY,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => $header,
		));

		$this->response = curl_exec($ch);
		curl_close($ch);
	}

	protected function postCurl($url, $data) {
		$header = array();
		$header[] = "Accept-Language: " . $this->lang;
		$header[] = "Content-Type: application/json";
		$ch = curl_init($url);
		curl_setopt_array($ch, array(
			CURLOPT_USERPWD => $this->authLogin . ":" . $this->authPassw,
			CURLOPT_POST => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => $header,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_POSTFIELDS => json_encode($data),
		));

		$this->response = curl_exec($ch);
		curl_close($ch);
	}

	protected function getStream($url) {
		$request = stream_context_create(array(
			'http' => array(
				'method' => 'GET',
				'header' => "Content-Type: application/json\r\n" .
				"Accept-Language: " . $this->lang . "\r\n",
			)
		));

		$url = str_replace("://", "://" . $this->authLogin . ":" . $this->authPassw . "@", $url);
		$this->response = @file_get_contents($url, false, $request);
	}

	protected function postStream($url, $data) {
		$request = stream_context_create(array(
			'http' => array(
				'method' => 'POST',
				'content' => json_encode($data),
				'header' => "Content-Type: application/json\r\n" .
				"Accept-Language: " . $this->lang . "\r\n",
			)
		));

		$this->response = @file_get_contents(str_replace("://", "://" . $this->authLogin . ":" . $this->authPassw . "@", $url), false, $request);
	}

}
