<?php
/**
 * Handles remote API communication
 *
 * @author    Youstice
 * @copyright (c) 2014, Youstice
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

namespace Youstice;

/**
 * Handles remote API communication
 *
 */
class Remote extends Request {
	//deprecated
	protected $apiRemoteRedirectLink = "https://app.youstice.com/blox-odr13/generix/odr/sk/app2/_complaint_/token/";
	protected $apiUrl = "https://api.youstice.com/YApiServices/services/";
	protected $apiSandboxUrl = "https://api-sand.youstice.com/YApiServices/services/";
	protected $apiKey;
	protected $sandbox;
	protected $lang;
	protected $shopSells;
	protected $shopSoftwareType;

	public function __construct($apiKey, $sandbox, $lang, $shopSells, $shopSoftwareType) {
		$this->apiKey = $apiKey;
		$this->sandbox = $sandbox;
		$this->lang = $lang;
		$this->shopSells = $shopSells;
		$this->shopSoftwareType = $shopSoftwareType;
	}

	/**
	 *
	 * @return string
	 */
	public function getLogoWidgetData() {
		$this->get('Api/logo');
		$returnData = $this->responseToArray();

		$searchString = str_replace(array("[", "]"), "", $returnData['image']);

		$bytes = explode(",", $searchString);
		$image = "";
		foreach ($bytes as $byte) {
			$image .= chr((int)$byte);
		}

		$returnData['image'] = null;
		if(strlen($image))
			$returnData['image'] = "data:image/png;base64," . $image;

		return $returnData;
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

	public function createWebReport(array $data) {

		try {
			$this->post("Api/addTransactionShop", $data);
		}
		catch(Youstice\InvalidApiKeyException $e) {
			exit("Invalid API key");
		}

		$response = $this->responseToArray();

		return $response['redirect_link'];
	}

	public function createOrderReport(ShopOrder $order) {
		$data = $order->toArray();
		$now = new \DateTime();

		$requestData = array(
			'itemType'	=> $this->shopSells,
			'orderNumber'	=> $order->getCode(),
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

	public function createProductReport(ShopProduct $product) {
		$data = $product->toArray();

		$requestData = array(
			'itemType'	=> $this->shopSells,
			'orderNumber'	=> $product->getCode(),
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
