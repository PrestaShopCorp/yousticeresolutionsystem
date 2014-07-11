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
		$this->postStream($url, $data);

		if ($this->response === false || $this->response === null)
			throw new \Exception('Post Request failed: ' . $url);

		if(strpos($this->response, "Invalid api key") !== false || strpos($this->response, "Invalid api key") !== false)
			throw new InvalidApiKeyException;

		return $this->response;
	}

	public function get($url) {
		$url = $this->generateUrl($url);
		$this->getStream($url);

		if ($this->response === false || $this->response === null)
			throw new \Exception('get Request failed: ' . $url);

		if(strpos($this->response, "Invalid api key") !== false || strpos($this->response, "Invalid api key") !== false)
			throw new InvalidApiKeyException;

		return $this->response;
	}

	protected function getStream($url) {
		$request = stream_context_create(array(
			'http' => array(
				'method'        => 'GET',
				'ignore_errors' => true,
				'timeout'       => 30.0,
				'header'        => "Content-Type: application/json\r\n" .
				"Accept-Language: " . $this->lang . "\r\n",
			)
		));

		$url = str_replace("://", "://" . $this->authLogin . ":" . $this->authPassw . "@", $url);
		$this->response = @file_get_contents($url, false, $request);
	}

	protected function postStream($url, $data) {
		$request = stream_context_create(array(
			'http' => array(
				'method'        => 'POST',
				'ignore_errors' => true,
				'timeout'       => 30.0,
				'content'       => json_encode($data),
				'header'        => "Content-Type: application/json\r\n" .
				"Accept-Language: " . $this->lang . "\r\n",
			)
		));

		$this->response = @file_get_contents(str_replace("://", "://" . $this->authLogin . ":" . $this->authPassw . "@", $url), false, $request);
	}

}
