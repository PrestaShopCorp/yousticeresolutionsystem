<?php
/**
 * Main Youstice class.
 *
 * @author    Youstice
 * @copyright (c) 2014, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

namespace Youstice;

/**
 * Youstice main API class
 *
 * @author KBS Development
 */
class Api {

	//because updateData function is called every request, update only every 10 minutes
	protected $updateInterval = 600;
	//when setOftenUpdates was called, next 5 minutes updates occurs
	protected $oftenUpdateInterval = 300;
	// \Youstice\Translator
	protected $translator;
	// \Youstice\LocalInterface
	protected $local;
	//ISO 639-1 char code "en|sk|cz|es"
	protected $language;
	//string from youstice service
	protected $apiKey;
	// product|service
	protected $thisShopSells;
	//unique integer for user
	protected $userId;
	//true if testing, real claims are not creating
	protected $useSandbox;
	//string prestashop|magento|ownSoftware
	protected $shopSoftwareType;

	/**
	 *
	 * @param array $dbCredentials associative array for PDO connection with must fields: driver, host, name, user, pass
	 * @param string $language ISO 639-1 char code "en|sk|cz|es"
	 * @param string $apiKey string from youstice service
	 * @param string $thisShopSells "products|services"
	 * @param integer $userId unique integer for user
	 * @param boolean $useSandbox true if testing implementation
	 * @param string $shopSoftwareType prestashop|magento|ownSoftware
	 * @return \Youstice\Api
	 */
	public static function create(array $dbCredentials = array(), $language = 'sk', $apiKey = "", $thisShopSells = 'product', $userId = null, $useSandbox = false, $shopSoftwareType = "custom") {

		return new self($dbCredentials, $language, $apiKey, $thisShopSells, $userId, $useSandbox, $shopSoftwareType);
	}

	/**
	 *
	 * @param array $dbCredentials associative array for PDO connection with must fields: driver, host, name, user, pass
	 * @param string $language ISO 639-1 char code "en|sk|cz|es"
	 * @param string $apiKey string from youstice service
	 * @param string $thisShopSells "products|services"
	 * @param integer $userId unique integer for user
	 * @param boolean $useSandbox true if testing implementation
	 * @param string $shopSoftwareType prestashop|magento|ownSoftware
	 * @return \Youstice\Api
	 */
	public function __construct(array $dbCredentials = array(), $language = 'sk', $apiKey = "", $thisShopSells = 'product', $userId = null, $useSandbox = false, $shopSoftwareType = "custom") {

		$this->__registerAutoloader();
		if (!isset($_SESSION))
			@session_start();

		$this->setDbCredentials($dbCredentials);
		$this->setLanguage($language);
		$this->setUserId($userId);
		$this->setApiKey($apiKey, $useSandbox);
		$this->setThisShopSells($thisShopSells);
		$this->setShopSoftwareType($shopSoftwareType);

		return $this;
	}

	/**
	 * Start Youstice API
	 * @return \Youstice\Api
	 */
	public function run() {
		$this->checkShopSells();

		$this->remote = new Remote($this->apiKey, $this->useSandbox, $this->language, $this->shopSells, $this->shopSoftwareType);

		$this->updateData();

		return $this;
	}

	/**
	 * Helper function for autoloading classes (called in constructor)
	 */
	protected function __registerAutoloader() {
		spl_autoload_register(function ($className) {
			$className = str_replace('Youstice\\', '', $className);
			$classPath = str_replace('\\', DIRECTORY_SEPARATOR, $className);

			$path = __DIR__ . DIRECTORY_SEPARATOR . $classPath;

			if (is_readable($path . ".php")) {
				require $path . ".php";
			}
		}, true, true);  //prepend our autoloader
	}

	public function getShowButtonsWidgetHtml() {
		$reportsCount = count($this->local->getReportsByUser($this->userId));

		$widget = new Widgets\ShowButtonsWidget($this->language, $reportsCount > 0);

		return $widget->toString();
	}

	/**
	 * Returns html string of logo widget
	 * @param string $href url address where method setButtonsVisible must be called
	 * @return string html
	 */
	public function getLogoWidgetHtml() {
		if (!trim($this->apiKey)) {
			return "";
		}

		return $this->remote->getLogoWidgetData($this->local->getChangedReportStatusesCount());
	}

	/**
	 * Returns html string of web report button
	 * @param string $href url address where web report is created
	 * @return string of html button
	 */
	public function getWebReportButtonHtml($href) {
		if (!trim($this->apiKey)) {
			return "";
		}

		$report = $this->local->getWebReport($this->userId);

		//exists, just redirect
		if (!$report->canCreateNew()) {
			$remoteLink = $this->local->getCachedRemoteReportLink($report->getCode());
			if (strlen($remoteLink)) {
				$href = $remoteLink;
			}
		}

		$webButton = new Widgets\WebReportButton($href, $this->language, $report);

		return $webButton->toString();
	}

	/**
	 * Returns html of product button
	 * @param string $href url address where product report is created
	 * @param integer $productId
	 * @param integer $orderId
	 * @return string of html button
	 */
	public function getProductReportButtonHtml($href, $productId, $orderId = null) {
		if (!trim($this->apiKey)) {
			return "";
		}

		$report = $this->local->getProductReport($productId, $orderId);

		//exists, just redirect
		if (!$report->canCreateNew()) {
			$remoteLink = $this->local->getCachedRemoteReportLink($report->getCode());
			if (strlen($remoteLink)) {
				$href = $remoteLink;
			}
		}

		$productButton = new Widgets\ProductReportButton($href, $this->language, $report);

		return $productButton->toString();
	}

	/**
	 * Returns html of button for simple order reporting
	 * @param string $href url address where order report is created
	 * @param inteter $orderId
	 * @return string of html button
	 */
	public function getOrderReportButtonHtml($href, $orderId) {
		if (!trim($this->apiKey)) {
			return "";
		}

		$report = $this->local->getOrderReport($orderId);

		//exists, just redirect
		if (!$report->canCreateNew()) {
			$remoteLink = $this->local->getCachedRemoteReportLink($report->getCode());
			if (strlen($remoteLink)) {
				$href = $remoteLink;
			}
		}

		$orderButton = new Widgets\OrderReportButton($href, $this->language, $report);

		return $orderButton->toString();
	}

	/**
	 * Returns button for opening popup
	 * @param string $href url address where showing order detail is mantained
	 * @param \Youstice\ShopOrder $order class with attached data
	 */
	public function getOrderDetailButtonHtml($href, ShopOrder $order) {
		if (!trim($this->apiKey)) {
			return "";
		}

		$products = $order->getProducts();
		$productCodes = array();

		foreach ($products as $product) {
			$productCodes[] = $product->getCode();
		}

		$report = $this->local->getOrderReport($order->getId(), $productCodes);

		$orderButton = new Widgets\OrderDetailButton($href, $this->language, $order, $report, $this);

		return $orderButton->toString();
	}

	/**
	 * Returns html string of popup
	 * @param \Youstice\ShopOrder $order class with attached data
	 */
	public function getOrderDetailHtml(ShopOrder $order) {
		if (!trim($this->apiKey)) {
			return "";
		}

		$products = $order->getProducts();
		$producCodes = array();

		foreach ($products as $product) {
			$producCodes[] = $product->getCode();
		}

		$report = $this->local->getOrderReport($order->getCode(), $producCodes);

		$orderDetail = new Widgets\OrderDetail($this->language, $order, $report, $this);

		return $orderDetail->toString();
	}

	/**
	 * Action when user viewed order history (for changing report statuses count)
	 * @return \Youstice\Api
	 */
	public function orderHistoryViewed() {
		$this->local->setChangedReportStatusesCount(0);

		return $this;
	}

	/**
	 * Creates report of web
	 * @return string where to redirect
	 */
	public function createWebReport() {
		$this->updateData(true);

		$localReport = $this->local->getWebReport($this->userId);

		if ($localReport->canCreateNew()) {
			return $this->_createWebReport($this->userId);
		} else {
			$remoteLink = $this->local->getCachedRemoteReportLink($localReport->getCode());

			if (strlen($remoteLink)) {
				return $remoteLink;
			} else {
				return $this->_createWebReport($this->userId);
			}
		}
	}

	private function _createWebReport($userId) {
		$newCode = $this->local->createWebReport($userId, $userId);

		$redirectLink = $this->remote->createWebReport($newCode);

		if ($redirectLink == null) {
			throw new FailedRemoteConnectionException;
		}

		$this->setOftenUpdates();

		return $redirectLink;
	}

	/**
	 * Creates order report
	 * @param \Youstice\ShopOrder $order class with attached data
	 * @return string where to redirect
	 */
	public function createOrderReport(ShopOrder $order) {
		$this->updateData(true);

		$report = new Reports\OrderReport($order->toArray());
		$localReport = $this->local->getOrderReport($report->getCode());

		if ($localReport->canCreateNew()) {
			return $this->_createOrderReport($order);
		} else {
			$remoteLink = $this->local->getCachedRemoteReportLink($localReport->getCode());

			if (strlen($remoteLink)) {
				return $remoteLink;
			} else {
				return $this->_createOrderReport($order);
			}
		}
	}

	private function _createOrderReport(ShopOrder $order) {
		$report = new Reports\OrderReport($order->toArray());
		$newCode = $this->local->createReport($report->getCode(), $this->userId);

		$redirectLink = $this->remote->createOrderReport($order, $newCode);

		if ($redirectLink == null) {
			throw new FailedRemoteConnectionException;
		}

		$this->setOftenUpdates();

		return $redirectLink;
	}

	/**
	 * Creates product report
	 * @param \Youstice\ShopProduct $product class with attached data
	 * @return string where redirect
	 */
	public function createProductReport(ShopProduct $product) {
		$this->updateData(true);

		$report = new Reports\ProductReport($product->toArray());
		$localReport = $this->local->getProductReport($report->getCode());

		if ($localReport->canCreateNew()) {
			return $this->_createProductReport($product);
		} else {
			$remoteLink = $this->local->getCachedRemoteReportLink($localReport->getCode());

			if (strlen($remoteLink)) {
				return $remoteLink;
			} else {
				return $this->_createProductReport($product);
			}
		}
	}

	private function _createProductReport(ShopProduct $product) {
		$report = new Reports\ProductReport($product->toArray());
		$newCode = $this->local->createReport($report->getCode(), $this->userId);

		$redirectLink = $this->remote->createProductReport($product, $newCode);

		if ($redirectLink == null) {
			throw new FailedRemoteConnectionException;
		}

		$this->setOftenUpdates();

		return $redirectLink;
	}

	/**
	 * 
	 * @param string $string to translate
	 * @param array $variables
	 * @return string translated
	 */
	public function t($string, $variables = array()) {
		return $this->translator->t($string, $variables);
	}

	/**
	 * Create necessary table
	 * @return boolean success
	 */
	public function install() {
		return $this->local->install();
	}

	/**
	 * Drop table
	 * @return boolean success
	 */
	public function uninstall() {
		return $this->local->uninstall();
	}

	public function setOftenUpdates() {
		$_SESSION['YRS']['last_often_update'] = time();
	}

	/**
	 * Connect to remote and update local data
	 * @param boolean $force update also if data are acutal
	 */
	protected function updateData($force = false) {
		if ($force || $this->canUpdate()) {
			if ($this->__updateData()) {
				$_SESSION['YRS']['last_update'] = time();
			}
		}
	}

	/**
	 * If api key is set and time upate intervals are in range
	 * @return boolean if can update
	 */
	protected function canUpdate() {
		if (strlen($this->apiKey) == 0)
			return false;

		$lastOftenUpdate = 0;
		if (isset($_SESSION['YRS']['last_often_update'])) {
			$lastOftenUpdate = $_SESSION['YRS']['last_often_update'];
		}

		//setOftenUpdates() was called 5 minutes before or earlier
		if ($lastOftenUpdate + $this->oftenUpdateInterval > time()) {
			return true;
		}

		$lastUpdate = 0;
		if (isset($_SESSION['YRS']['last_update'])) {
			$lastUpdate = $_SESSION['YRS']['last_update'];
		}

		return $lastUpdate + $this->updateInterval < time();
	}

	/**
	 * Get data for logoWidget, update report statuses and time
	 * @return boolean success
	 */
	protected function __updateData() {
		if (!$this->userId)
			return false;

		$localReportsData = $this->local->getReportsByUser($this->userId);

		//try to get remote reports
		try {
			$remoteReportsData = $this->remote->getRemoteReportsData($localReportsData);
		} catch (\Exception $e) {
			return false;
		}

		//no new updates
		if (count($remoteReportsData) === 0)
			return true;

		$changedReportStatusesCount = $this->local->getChangedReportStatusesCount();

		foreach ($localReportsData as $local) {
			foreach ($remoteReportsData as $remote) {
				if (!isset($remote['orderNumber']) || $local['code'] !== $remote['orderNumber']) {
					continue;
				}

				$this->local->setCachedRemoteReportLink($local['code'], $remote['redirect_link']);
				//status changed?
				if ($local['status'] !== $remote['status']) {
					$changedReportStatusesCount++;
					$this->local->updateReportStatus($remote['orderNumber'], $remote['status']);
				}

				$this->local->updateReportRemainingTime($remote['orderNumber'], $remote['remaining_time']);
			}
		}

		$this->local->setChangedReportStatusesCount($changedReportStatusesCount);

		return true;
	}

	/**
	 * Set database params in associative array for PDO
	 * @param array $dbCredentials associative array for PDO connection with must fields: driver, host, name, user, pass
	 * @return \Youstice\Api
	 */
	public function setDbCredentials(array $dbCredentials) {
		if (count($dbCredentials))
			$this->local = new Local($dbCredentials);

		return $this;
	}

	/**
	 * 
	 * @param Youstice\LocalInterface $local
	 */
	public function setLocal(LocalInterface $local) {
		$this->local = $local;
	}

	/**
	 * Set eshop language
	 * @param string ISO 639-1 char code "en|sk|cz|es"
	 * @return \Youstice\Api
	 * @throws \InvalidArgumentException
	 */
	public function setLanguage($lang = null) {
		$lang = trim(strtolower($lang));

		if ($lang && \Youstice\Helpers\LanguageCodes::check($lang)) {
			$this->language = $lang;
			$this->translator = new Translator($this->language);
		} else {
			throw new \InvalidArgumentException('Language code "' . $lang . '" is not allowed.');
		}

		return $this;
	}

	/**
	 * Set API key
	 * @param string $apiKey if true api is in playground mode, data are not real
	 * @return \Youstice\Api
	 */
	public function setApiKey($apiKey, $useSandbox = false) {
		if (!trim($apiKey))
			return $this;

		$this->apiKey = $apiKey;

		$this->useSandbox = ($useSandbox == true ? true : false);

		return $this;
	}

	/**
	 * Set what type of goods is eshop selling
	 * @param string $shopSells "products|services"
	 * @return \Youstice\Api
	 * @throws \InvalidArgumentException
	 */
	public function setThisShopSells($shopSells) {
		$this->shopSells = strtolower($shopSells);

		return $this;
	}

	/**
	 * Check if shopSells attribute is correct
	 * @throws \InvalidArgumentException
	 */
	protected function checkShopSells() {
		$allowedTypes = array("product", "service");

		if (in_array(strtolower($this->shopSells), $allowedTypes)) {
			$this->shopSells = strtolower($this->shopSells);
		} else {
			throw new \InvalidArgumentException('Shop selling "' . $this->shopSells . '" is not allowed.');
		}
	}

	/**
	 * Set on which software is eshop running
	 * @param string $shopType "prestashop|magento|ownSoftware"
	 * @return \Youstice\Api
	 */
	public function setShopSoftwareType($shopType) {
		if (strlen($shopType))
			$this->shopSoftwareType = $shopType;

		return $this;
	}

	/**
	 * Set user id, unique for eshop
	 * @param integer $userId
	 * @return \Youstice\Api
	 */
	public function setUserId($userId) {
		$this->userId = $userId;

		return $this;
	}

}

class InvalidApiKeyException extends \Exception {
	
}

class FailedRemoteConnectionException extends \Exception {
	
}