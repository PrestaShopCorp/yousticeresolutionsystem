<?php
/**
 * Handles remote API communication
 *
 * @author    Youstice
 * @copyright (c) 2015, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

/**
 * Handles remote API communication
 *
 */
class YousticeRemote extends YousticeRequest {

	protected $api_url = 'https://api.youstice.com/YApiServices/services/';
	protected $api_sandbox_url = 'https://api-sand.youstice.com/YApiServices/services/';
	protected $api_key;
	protected $use_sandbox;
	protected $lang;
	protected $shop_sells;
	protected $shop_software_type;
	protected $shop_software_version;

	public function __construct($api_key, $use_sandbox, $lang, $shop_sells, $shop_software_type, $shop_software_version = '')
	{
		$this->api_key = $api_key;
		$this->use_sandbox = $use_sandbox;
		$this->lang = $lang;
		$this->shop_sells = $shop_sells;
		$this->shop_software_type = $shop_software_type;
		$this->shop_software_version = $shop_software_version;
	}

	/**
	 *
	 * @return string html
	 */
	public function getLogoWidgetData($updates_count, $claim_url = '', $is_logged_in = false)
	{
		$this->setAdditionalParam('numberOfUpdates', $updates_count);
		$this->setAdditionalParam('claimUrl', $claim_url);
		$this->setAdditionalParam('isLoggedIn', $is_logged_in);

		$this->get('Api/logo');

		$response = $this->responseToArray();

		return $response['html'];
	}

	public function getRemoteReportsData(array $local_reports_data)
	{
		$send = array('orders' => array());

		foreach ($local_reports_data as $local_report_data)
			$send['orders'][] = array('orderNumber' => $local_report_data['code']);

		$this->post('Api/claims', $send);
		$response = $this->responseToArray();

		return $response['orders'];
	}

	public function createWebReport($order_number)
	{
		$this->post('Api/addTransactionShop', array('orderNumber' => $order_number));

		$response = $this->responseToArray();

		return $response['redirect_link'];
	}

	public function createOrderReport(YousticeShopOrder $order, $code)
	{
		$data = $order->toArray();
		$now = new Datetime();

		$request_data = array(
			'itemType' => $this->shop_sells,
			'orderNumber' => $code,
			'itemDescription' => $data['description'],
			'itemName' => $data['name'],
			'itemCurrency' => $data['currency'],
			'itemPrice' => $data['price'],
			'itemCode' => $data['id'],
			'deliveryDate' => $data['deliveryDate'],
			'orderDate' => $data['orderDate'] ? $data['orderDate'] : $now->format(Datetime::ISO8601),
			'shopType' => $this->shop_software_type,
			'image' => $order->getImage(),
			'other' => $data['other'],
		);

		$this->post('Api/addTransaction', $request_data);

		$response = $this->responseToArray();

		return $response['redirect_link'];
	}

	public function createProductReport(YousticeShopProduct $product, $code)
	{
		$data = $product->toArray();
		$now = new Datetime();

		$request_data = array(
			'itemType' => $this->shop_sells,
			'orderNumber' => $code,
			'itemDescription' => $data['description'],
			'itemName' => $data['name'],
			'itemCurrency' => $data['currency'],
			'itemPrice' => $data['price'],
			'itemCode' => $data['id'],
			'deliveryDate' => $data['deliveryDate'],
			'orderDate' => $data['orderDate'] ? $data['orderDate'] : $now->format(Datetime::ISO8601),
			'shopType' => $this->shop_software_type,
			'image' => $data['image'],
			'other' => $data['other'],
		);

		$this->post('Api/addTransaction', $request_data);

		$response = $this->responseToArray();

		return $response['redirect_link'];
	}
	
	public function checkApiKey()
	{
		$request_data = array(
			'platform' => $this->shop_software_type,
			'version' => $this->shop_software_version
		);
		
		$this->post('Api/auth', $request_data);

		$response = $this->responseToArray();

		return $response && $response['result'] == 'true';
	}

}