<?php
/**
 * Handles remote API communication
 *
 * @author    Youstice
 * @copyright (c) 2014, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

namespace Youstice;

/**
 * Handles remote API communication
 *
 */
class Remote extends Request {
	protected $apiUrl = "https://api.youstice.com/YApiServices/services/";
	protected $apiSandboxUrl = "https://api-sand.youstice.com/YApiServices/services/";
	protected $apiKey;
	protected $useSandbox;
	protected $lang;
	protected $shopSells;
	protected $shopSoftwareType;

	public function __construct($apiKey, $useSandbox, $lang, $shopSells, $shopSoftwareType) {
		$this->apiKey = $apiKey;
		$this->useSandbox = $useSandbox;
		$this->lang = $lang;
		$this->shopSells = $shopSells;
		$this->shopSoftwareType = $shopSoftwareType;
	}

	/**
	 *
	 * @return string html
	 */
	public function getLogoWidgetData($updatesCount) {
	    $this->setAdditionalParam('numberOfUpdates', $updatesCount);
	    $this->get('Api/logo');

	    $response = $this->responseToArray();
	    
	    return $response['html'];
	}

	public function getRemoteReportsData(array $localReportsData) {
		$send = array( "orders" => array() );

		foreach($localReportsData as $localReportdata) {
			$send['orders'][] = array("orderNumber" => $localReportdata['code']);
		}

		$this->post("Api/claims", $send);
		$response = $this->responseToArray();

		return $response['orders'];
	}

	public function createWebReport($orderNumber) {

		try {
			$this->post("Api/addTransactionShop", array('orderNumber' => $orderNumber));
		}
		catch(Youstice\InvalidApiKeyException $e) {
			exit("Invalid API key");
		}

		$response = $this->responseToArray();

		return $response['redirect_link'];
	}

	public function createOrderReport(ShopOrder $order, $code) {
		$data = $order->toArray();
		$now = new \DateTime();

		$requestData = array(
			'itemType'	=> $this->shopSells,
			'orderNumber'	=> $code,
			'itemDescription' => $data['description'],
			'itemName'			=> $data['name'],
			'itemCurrency'		=> $data['currency'],
			'itemPrice'			=> $data['price'],
			'itemCode'			=> $data['id'],
			'deliveryDate'		=> $data['deliveryDate'] ? $data['deliveryDate'] : $now->format(\DateTime::ISO8601),
			'orderDate'			=> $data['orderDate'] ? $data['orderDate'] : $now->format(\DateTime::ISO8601),
			'shopType'			=> $this->shopSoftwareType,
			'image'				=> $order->getImage(),
			'other'				=> $data['other'],
		);

		try {
			$this->post("Api/addTransaction", $requestData);
		}
		catch(Youstice\InvalidApiKeyException $e) {
			exit("Invalid API key");
		}

		$response = $this->responseToArray();

		return $response['redirect_link'];
	}

	public function createProductReport(ShopProduct $product, $code) {
		$data = $product->toArray();

		$requestData = array(
			'itemType'	=> $this->shopSells,
			'orderNumber'	=> $code,
			'itemDescription' => $data['description'],
			'itemName'			=> $data['name'],
			'itemCurrency'		=> $data['currency'],
			'itemPrice'			=> $data['price'],
			'itemCode'			=> $data['id'],
			'deliveryDate'		=> $data['deliveryDate'],
			'orderDate'			=> $data['orderDate'],
			'shopType'			=> $this->shopSoftwareType,
			'image'				=> $data['image'],
			'other'				=> $data['other'],
		);

		try {
			$this->post("Api/addTransaction", $requestData);
		}
		catch(Youstice\InvalidApiKeyException $e) {
			exit("Invalid API key");
		}

		$response = $this->responseToArray();

		return $response['redirect_link'];
	}

}
