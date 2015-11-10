<?php
/**
 * Base communication interface
 *
 * @author    Youstice
 * @copyright (c) 2015, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

class YousticeRequest {

	protected $api_production_url = 'https://api.youstice.com/api/';
	//protected $api_production_url = 'https://api-qa.youstice.com/';
	protected $api_sandbox_url = 'https://api-sand.youstice.com/api/';
	protected $response = null;
	protected $additional_params = array();
	private $last_url;

	public function returnResponse()
	{
		return $this->response;
	}

	public function responseToArray()
	{
		$data = Tools::jsonDecode($this->response, true);

		return $data;
	}
	
	protected function isResponseInvalid()
	{
		$responseArray = $this->responseToArray();

		return $this->response === false 
				|| $this->response === null 
				|| (isset($responseArray['error']) && $responseArray['error'] == false);
	}
	
	protected function checkForInvalidApiKeyInResponse() {
		if (strpos($this->response, 'Invalid api key') !== false || strpos($this->response, 'apiKey is invalid') !== false)
			throw new YousticeInvalidApiKeyException;
		
		if (strpos($this->last_url, 'Api/auth/') !== false && $this->response === null)
			throw new YousticeInvalidApiKeyException;
	}

	public function setAdditionalParam($key, $val)
	{
		$this->additional_params[$key] = $val;
	}

	protected function generateUrl($url)
	{
		$api_url = $this->use_sandbox ? $this->api_sandbox_url : $this->api_production_url;

		$return_url = $api_url . $url . '/' . $this->api_key . '?version=1&channel=' . $this->shop_software_type;

		if (count($this->additional_params))
		{
			foreach ($this->additional_params as $key => $val) {
				$return_url .= '&' . urlencode($key) . '=' . urlencode($val);
			}

			//reset params for next calls
			$this->additional_params = array();
		}

		return $return_url;
	}

	public function get($url)
	{
		try {
			$this->getStream($url);
		}
		catch (Exception $e) {
			$this->checkForInvalidApiKeyInResponse();
			
			$this->logError($url, "GET", array(), $this->response);

			throw new YousticeFailedRemoteConnectionException('get Request failed: ' . $url, $e->getCode());
		}

		$this->checkForInvalidApiKeyInResponse();

		return $this->response;
	}

	public function post($url, $data = array())
	{
		try {
			$this->postStream($url, $data);
		}
		catch (Exception $e) {
			$this->checkForInvalidApiKeyInResponse();
			
			$this->logError($url, "POST", $data, $this->response);

			throw new YousticeFailedRemoteConnectionException('Post Request failed: ' . $url, $e->getCode());
		}

		$this->checkForInvalidApiKeyInResponse();

		return $this->response;
	}

	protected function getStream($url)
	{
		$request = stream_context_create(array(
			'http' => array(
				'method' => 'GET',
				'ignore_errors' => true,
				'timeout' => 8,
				'header' => "Content-Type: application/json\r\n" . 'Accept-Language: ' . $this->lang . "\r\n",
			)
		));
		
		$this->executeCall($url, $request);
	}

	protected function postStream($url, $data)
	{
		$request = stream_context_create(array(
			'http' => array(
				'method' => 'POST',
				'ignore_errors' => true,
				'timeout' => 8,
				'content' => Tools::jsonEncode($data),
				'header' => "Content-Type: application/json\r\n" . 'Accept-Language: ' . $this->lang . "\r\n",
			)
		));

		$this->executeCall($url, $request);
	}
	
	protected function executeCall($url, $request) {
		
		$this->last_url = $url;
		
		//empty response before new call
		$this->response = null;

		$this->response = YousticeTools::file_get_contents($url, false, $request);

		if ($this->isResponseInvalid())
		{
			throw new Exception();
		}
	}

	protected function logError($url, $type, $data, $response)
	{
		error_log("Youstice - remote request failed [url]: " . $type . " " . $url . " [request]: " . Tools::jsonEncode($data) . " [response]: " . $response);
	}

}
