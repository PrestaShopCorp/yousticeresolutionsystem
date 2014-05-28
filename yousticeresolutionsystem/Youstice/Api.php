<?php
/**
 * Main Youstice class.
 *
 * @author    Youstice
 * @copyright (c) 2014, Youstice
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

namespace Youstice;

/**
 * Youstice main API class
 *
 * @author KBS Development
 */
class Api {

	//because updateData function is called every request, update only every 1200 seconds
	protected $updateInterval = 1200;
	// \Youstice\Translator
	protected $translator;
	// \Youstice\Local
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
	 * @param boolean $use_sandbox true if testing implementation
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
		if(!isset($_SESSION))
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

	protected function __registerAutoloader() {
		spl_autoload_register(function ($className) {
			$className = str_replace('Youstice\\', '', $className);
			$classPath = str_replace('\\', DIRECTORY_SEPARATOR, $className);

			$path = __DIR__ . DIRECTORY_SEPARATOR . $classPath;

			if (is_readable($path . ".php")) {
				require $path . ".php";
			}
		}, true, true);		//prepend our autoloader
	}

	/**
	 * Returns html string
	 * @param string $href
	 * @return string html
	 */
	public function getLogoWidgetHtml($href = '#') {
		if ($this->userId == null || !trim($this->apiKey)) {
			return "<!-- Youstice logoWidget: user is not logged in -->";
		}

		try {
			$logoWidget = new Widgets\LogoWidget($href, $this->language, $_SESSION['YRS']['logoWidget'],
					$this->local->getChangedReportStatusesCount());
		}
		catch(\Exception $e) {
			return "";
		}

		return $logoWidget->toString();
	}

	/**
	 * Returns html string of web report button
	 * @param string $href
	 * @return string of html button
	 */
	public function getWebReportButtonHtml($href) {
		if (!$this->getButtonsVisible() || !trim($this->apiKey)) {
			return "";
		}

		$report = $this->local->getWebReport($this->userId);

		//exists, just redirect
		if($report->exists() && trim($report->getStatus())) {
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
	 * @param string $href
	 * @param integer $orderId
	 * @param integer $productId
	 * @return string of html button
	 */
	public function getProductReportButtonHtml($href, $productId, $orderId = null) {
		if (!$this->getButtonsVisible() || !trim($this->apiKey)) {
			return "";
		}

		$report = $this->local->getProductReport($productId, $orderId);

		//exists, just redirect
		if($report->exists() && trim($report->getStatus())) {
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
	 * @param string $href
	 * @param inteter $orderId
	 * @return string of html button
	 */
	public function getOrderReportButtonHtml($href, $orderId) {
		if (!$this->getButtonsVisible() || !trim($this->apiKey)) {
			return "";
		}

		$report = $this->local->getOrderReport($orderId);

		//exists, just redirect
		if($report->exists() && trim($report->getStatus())) {
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
	 * @param string $href
	 * @param \Youstice\ShopOrder $order class with attached data
	 */
	public function getOrderDetailButtonHtml($href, ShopOrder $order) {
		if (!$this->getButtonsVisible() || !trim($this->apiKey)) {
			return "";
		}

		$products = $order->getProducts();
		$productIds = array();

		foreach($products as $product) {
			$productIds[] = $product->getId();
		}

		$report = $this->local->getOrderReport($order->getId(), $productIds);

		$orderButton = new Widgets\OrderDetailButton($href, $this->language, $order, $report, $this);

		return $orderButton->toString();
	}

	/**
	 * Returns html string of popup
	 * @param string $href
	 * @param \Youstice\ShopOrder $order class with attached data
	 */
	public function getOrderDetailHtml($href, ShopOrder $order) {
		if (!$this->getButtonsVisible() || !trim($this->apiKey)) {
			return "";
		}

		$products = $order->getProducts();
		$productIds = array();

		foreach($products as $product) {
			$productIds[] = $product->getCode();
		}

		$report = $this->local->getOrderReport($order->getCode(), $productIds);

		$orderDetail = new Widgets\OrderDetail($href, $this->language, $order, $report, $this);

		return $orderDetail->toString();
	}


	/**
	 * Start showing buttons
	 */
	public function setButtonsVisible() {
		if($this->userId)
			$_SESSION['YRS']['visibleButtons'] = true;

		return $this;
	}

	protected function getButtonsVisible() {
		return isset($_SESSION['YRS']['visibleButtons'])
				&& $_SESSION['YRS']['visibleButtons'];
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
	 * Creates web report
	 * @return string where to redirect
	 */
	public function createWebReport() {
		$report = $this->local->getWebReport($this->userId);

		//exists, just redirect
		if($report->exists() && trim($report->getStatus())) {
			$remoteLink = $this->local->getCachedRemoteReportLink($report->getCode());
			if (strlen($remoteLink)) {
				return $remoteLink;
			}
		}
		//create report
		$report->setCode($this->userId);

		$redirectLink = $this->remote->createWebReport(array(
			"orderNumber" => $report->getCode(),
		));

		if($redirectLink == null) {
			exit("Remote server connection failed");
		}

		if(!$report->exists()) {
			$this->local->createReport($report->getCode(), $this->userId);
		}

		$this->updateData(true);

		return $redirectLink;
	}

	/**
	 * Creates order report
	 * @param \Youstice\ShopOrder $order class with attached data
	 * @return string where to redirect
	 */
	public function createOrderReport(ShopOrder $order) {
		$report = new Reports\OrderReport($order->toArray());
		$localReport = $this->local->getOrderReport($report->getCode());

		//exists, just redirect
		if($localReport->exists() && trim($localReport->getStatus())) {
			$remoteLink = $this->local->getCachedRemoteReportLink($report->getCode());
			if (strlen($remoteLink)) {
				return $remoteLink;
			}

		}
		//create report
		$redirectLink = $this->remote->createOrderReport($order);

		if($redirectLink == null) {
			exit("Remote server connection failed");
		}

		if(!$localReport->exists()) {
			$this->local->createReport($report->getCode(), $this->userId);
		}

		$this->updateData(true);

		return $redirectLink;
	}

	/**
	 * Creates product report
	 * @param \Youstice\ShopProduct $product class with attached data
	 * @return string where redirect
	 */
	public function createProductReport(ShopProduct $product) {
		$report = new Reports\ProductReport($product->toArray());
		$localReport = $this->local->getOrderReport($report->getCode());

		//exists, just redirect
		if($localReport->exists() && trim($localReport->getStatus())) {
			$remoteLink = $this->local->getCachedRemoteReportLink($report->getCode());
			if (strlen($remoteLink)) {
				return $remoteLink;
			}

		}
		//create report
		$redirectLink = $this->remote->createProductReport($product);

		if($redirectLink == null) {
			exit("Remote server connection failed");
		}

		if(!$localReport->exists()) {
			$this->local->createReport($report->getCode(), $this->userId);
		}

		$this->updateData(true);

		return $redirectLink;
	}

	public function t($string, $variables = array()) {
		return $this->translator->t($string, $variables);
	}

	/**
	 * Create necessary tables
	 * @return boolean success
	 */
	public function install() {
		return $this->local->install();
	}

	/**
	 * @return boolean success
	 */
	public function uninstall() {
		return $this->local->uninstall();
	}

	protected function updateData($force = false) {
		if ($force || $this->canUpdate()) {
			if($this->__updateData()) {
				$_SESSION['YRS']['last_update'] = time();
			}
		}
	}

	/**
	 * If api key is set and last update was below now-updateInterval
	 * @return boolean if can update
	 */
	protected function canUpdate() {
		if(strlen($this->apiKey) == 0)
			return false;

		$lastUpdate = 0;
		if(isset($_SESSION['YRS']['last_update'])) {
			$lastUpdate = $_SESSION['YRS']['last_update'];
		}

		return $lastUpdate + $this->updateInterval < time();
	}

	/**
	 * Get data for logoWidget, update report statuses and time
	 * @return boolean success
	 */
	protected function __updateData() {
		try {
			$logoWidgetData = $this->remote->getLogoWidgetData();
		}
		catch(\Exception $e) {
			return false;
		}

		$_SESSION['YRS']['logoWidget'] = $logoWidgetData;
		//getting by user id
		if($this->userId) {
			$localReportsData = $this->local->getReportsByUser($this->userId);
			try {
				$remoteReportsData = $this->remote->getRemoteReportsData($localReportsData);
			}
			catch(\Exception $e) {
				return false;
			}

			//no new updates
			if(count($remoteReportsData) === 0)
				return true;

			$changedReportStatusesCount = $this->local->getChangedReportStatusesCount();

			foreach($localReportsData as $local) {
				foreach($remoteReportsData as $remote) {
					if (!isset($remote['id']) || $local['code'] !== $remote['id']) {
						continue;
					}

					$this->local->setCachedRemoteReportLink($local['code'], $remote['redirect_link']);
					//status changed?
					if($local['status'] !== $remote['status']) {
						$changedReportStatusesCount++;
						$this->local->updateReportStatus($remote['id'], $remote['status']);
					}

					$this->local->updateReportRemainingTime($remote['id'], $remote['remaining_time']);
				}
			}

			$this->local->setChangedReportStatusesCount($changedReportStatusesCount);
		}

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
	 * Set eshop language
	 * @param string ISO 639-1 char code "en|sk|cz|es"
	 * @return \Yosutice\Api
	 * @throws \InvalidArgumentException
	 */
	public function setLanguage($lang = null) {
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
	 * @param string $apiKey
	 * @return \Yosutice\Api
	 */
	public function setApiKey($apiKey, $useSandbox = false) {
		if(!trim($apiKey))
			return $this;

		$this->apiKey = $apiKey;

		$this->useSandbox = ($useSandbox == true ? true : false);

		return $this;
	}

	/**
	 * Set what type of goods eshop is selling
	 * @param string $shopSells "products|services"
	 * @return \Yosutice\Api
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
	 * Set on which software eshop run
	 * @param string $shopSoftwareType "prestashop|magento|ownSoftware"
	 * @return \Yosutice\Api
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
