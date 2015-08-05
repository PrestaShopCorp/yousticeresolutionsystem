<?php
/**
 * Main Youstice class.
 *
 * @author    Youstice
 * @copyright (c) 2015, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

/**
 * Youstice main API class
 *
 * @author KBS Development
 */
class YousticeApi {

	/**
	 * Because updateData function is called every request, update only every 10 minutes
	 * @var int 
	 */
	protected $update_interval = 600;

	/**
	 * When setOftenUpdates was called, next 5 minutes updates occurs
	 * @var int 
	 */
	protected $often_update_interval = 300;

	/**
	 *
	 * @var SessionProviderInterface 
	 */
	protected $session;

	/**
	 *
	 * @var type YousticeLocalInterface
	 */
	protected $local;

	/**
	 * ISO 639-1 char code "en|sk|cz|es"
	 * @var string 
	 */
	protected $language;

	/**
	 * string from youstice service
	 * @var string 
	 */
	protected $api_key;

	/**
	 * unique integer identifier
	 * @var type 
	 */
	protected $user_id;

	/**
	 * true for testing environment
	 * @var boolean 
	 */
	protected $use_sandbox;

	/**
	 * prestashop|magento|ownSoftware
	 * @var string 
	 */
	protected $shop_software_type;

	/**
	 * e.g. 1.9.4.2
	 * @var string 
	 */
	protected $shop_software_version;
	
	/*
	 * Is true when curl, PDO and fileinfo are available
	 */
	protected $is_properly_installed = false;
	
	const CURL_NOT_INSTALLED = 1;
	const PDO_NOT_INSTALLED = 2;
	const FINFO_NOT_INSTALLED = 3;
	
	protected $infinario;

	/**
	 *
	 * @param array $db_credentials associative array for PDO connection with must fields: driver, host, name, user, pass
	 * @param string $language ISO 639-1 char code "en|sk|cz|es"
	 * @param string $api_key string from youstice service
	 * @param string $shop_sells "product|service"
	 * @param integer $user_id unique integer for user
	 * @param boolean $use_sandbox true if testing implementation
	 * @param string $shop_software_type prestashop|magento|ownSoftware
	 * @return YousticeApi
	 */
	public static function create(array $db_credentials = array(), $language = 'sk', $api_key = '', $shop_sells = 'product',
			$user_id = null, $use_sandbox = false, $shop_software_type = 'custom', $shop_software_version = '')
	{
		return new self($db_credentials, $language, $api_key, $shop_sells, $user_id, $use_sandbox, $shop_software_type, $shop_software_version);
	}

	/**
	 *
	 * @param array $db_credentials associative array for PDO connection with must fields: driver, host, name, user, pass
	 * @param string $language ISO 639-1 char code "en|sk|cz|es"
	 * @param string $api_key string from youstice service
	 * @param string $shop_sells "product|service"
	 * @param integer $user_id unique integer for user
	 * @param boolean $use_sandbox true if testing implementation
	 * @param string $shop_software_type prestashop|magento|ownSoftware
	 * @return YousticeApi
	 */
	public function __construct(array $db_credentials = array(), $language = 'sk', $api_key = '', $shop_sells = 'product',
			$user_id = null, $use_sandbox = false, $shop_software_type = 'custom', $shop_software_version = '')
	{
		$this->registerAutoloader();

		$this->setDbCredentials($db_credentials);
		$this->setLanguage($language);
		$this->setUserId($user_id);
		$this->setApiKey($api_key, $use_sandbox);
		$this->setShopSoftwareType($shop_software_type, $shop_software_version);

		return $this;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function getVersionName() {
		return '238';
	}

	/**
	 * Start Youstice API
	 * @return YousticeApi
	 */
	public function run()
	{
		$this->runWithoutUpdates();

		$this->updateData();

		return $this;
	}

	/**
	 * Start Youstice API and do not run updates
	 * @return YousticeApi
	 */
	public function runWithoutUpdates()
	{
		$this->checkShopSells();
		
		$this->is_properly_installed = $this->checkIsProperlyInstalled();

		$this->remote = new YousticeRemote($this->api_key, $this->use_sandbox, $this->language, $this->shop_software_type, $this->shop_software_version);

		return $this;
	}

	/**
	 * Helper function for autoloading classes (called in constructor)
	 */
	protected function registerAutoloader()
	{
		spl_autoload_register(function ($class_name) {
			if (strpos($class_name, 'Youstice') === false)
				return;
			
			$class_name = str_replace('Youstice', '', $class_name);
			
			if ($class_name === 'WidgetsOrderDetailButtonInOrdersPage') {
				require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Widgets' 
						. DIRECTORY_SEPARATOR . 'OrderDetailButtonInOrdersPage.php';
				return;
			}

			//prepend uppercase letter with directory separator 
			$class_path = preg_replace('/([A-Z])/', '/\\1', $class_name);
			$path = dirname(__FILE__).str_replace('/', DIRECTORY_SEPARATOR, $class_path);

			if (is_readable($path.'.php'))
				require_once $path.'.php';
			else
			{
				for ($i = 0; $i < 2; $i++) {
					//Providers/Session/PrestashopProvider -> Providers/SessionPrestashopProvider
					$path = strrev(preg_replace('/\\'.DIRECTORY_SEPARATOR.'/', '', strrev($path), 1));

					if (is_readable($path.'.php')) {
						require_once $path.'.php';
						break;
					}
				}
			}
		}, true, true);  //prepend our autoloader
	}

	public function getShowButtonsWidgetHtml()
	{
		if (!trim($this->api_key))
			return '';

		if (!$this->is_properly_installed)
			return '';

		$reports_count = count($this->local->getReportsByUser($this->user_id));

		$widget = new YousticeWidgetsShowButtons($reports_count > 0);

		return $widget->toString();
	}
	
	public function getOrdersPageWidgetHtml($webReportHref, $shopName, array $shopOrders)
	{
		if (!trim($this->api_key))
			return '';

		if (!$this->is_properly_installed)
			return '';
		
		if(empty($shopOrders))
			return '';

		$widget = new YousticeWidgetsOrdersPage($webReportHref, $shopName, $shopOrders, $this);

		return $widget->toString();
	}

	/**
	 * Returns html string of logo widget
	 * @param string $claims_url url to report claims form
	 * @return string html
	 */
	public function getLogoWidgetHtml($claim_url = '', $is_on_order_history_page = false)
	{
		if (!trim($this->api_key))
			return '';
		
		if (!$this->is_properly_installed)
			return '';
		
		if($is_on_order_history_page)
			$claim_url .= (parse_url($claim_url, PHP_URL_QUERY) ? '&' : '?') . 'ordersPage';

		try {
			$html = $this->remote->getLogoWidgetData($this->local->getChangedReportStatusesCount(), $claim_url, $this->user_id !== null);
		}
		catch (Exception $e) {
			return '';
		}

		return $html;
	}

	/**
	 * Returns html string of web report button
	 * @param string $href url address where web report is created
	 * @return string of html button
	 */
	public function getWebReportButtonHtml($href)
	{
		if (!trim($this->api_key))
			return '';

		if (!$this->is_properly_installed)
			return '';

		$report = $this->local->getWebReport($this->user_id);

		//exists, just redirect
		if (!$report->canCreateNew())
		{
			$remote_link = $this->local->getCachedRemoteReportLink($report->getCode());

			if (Tools::strlen($remote_link))
				$href = $remote_link;
		}

		$web_button = new YousticeWidgetsWebReportButton($href, $report);

		return $web_button->toString();
	}

	/**
	 * Returns html of product button
	 * @param string $href url address where product report is created
	 * @param integer $product_id
	 * @param integer $order_id
	 * @return string of html button
	 */
	public function getProductReportButtonHtml($href, $product_id, $order_id = null)
	{
		if (!trim($this->api_key))
			return '';

		if (!$this->is_properly_installed)
			return '';

		$report = $this->local->getProductReport($product_id, $order_id);

		//exists, just redirect
		if (!$report->canCreateNew())
		{
			$remote_link = $this->local->getCachedRemoteReportLink($report->getCode());

			if (Tools::strlen($remote_link))
				$href = $remote_link;
		}

		$product_button = new YousticeWidgetsProductReportButton($href, $report);

		return $product_button->toString();
	}

	/**
	 * Returns html of button for simple order reporting
	 * @param string $href url address where order report is created
	 * @param inteter $order_id
	 * @return string of html button
	 */
	public function getOrderReportButtonHtml($href, $order_id)
	{
		if (!trim($this->api_key))
			return '';

		if (!$this->is_properly_installed)
			return '';

		$report = $this->local->getOrderReport($order_id);

		//exists, just redirect
		if (!$report->canCreateNew())
		{
			$remote_link = $this->local->getCachedRemoteReportLink($report->getCode());

			if (Tools::strlen($remote_link))
				$href = $remote_link;
		}

		$order_button = new YousticeWidgetsOrderReportButton($href, $report);

		return $order_button->toString();
	}

	/**
	 * Returns button for opening popup
	 * @param string $href url address where showing order detail is mantained
	 * @param YousticeShopOrder $order class with attached data
	 */
	public function getOrderDetailButtonHtml($href, YousticeShopOrder $order)
	{
		if (!trim($this->api_key))
			return '';

		if (!$this->is_properly_installed)
			return '';

		$products = $order->getProducts();
		$product_codes = array();

		foreach ($products as $product)
			$product_codes[] = $product->getCode();

		$report = $this->local->getOrderReport($order->getId(), $product_codes);

		$order_button = new YousticeWidgetsOrderDetailButton($href, $order, $report, $this);

		return $order_button->toString();
	}

	/**
	 * Returns button for opening order detail popup
	 * @param string $href url address where showing order detail is mantained
	 * @param YousticeShopOrder $order class with attached data
	 */
	public function getOrderDetailButtonInOrdersPageHtml($href, YousticeShopOrder $order)
	{
		if (!trim($this->api_key))
			return '';

		if (!$this->is_properly_installed)
			return '';

		$products = $order->getProducts();
		$product_codes = array();

		foreach ($products as $product)
			$product_codes[] = $product->getCode();

		$report = $this->local->getOrderReport($order->getId(), $product_codes);

		$order_button = new YousticeWidgetsOrderDetailButtonInOrdersPage($href, $this->language, $order, $report, $this);

		return $order_button->toString();
	}

	/**
	 * Returns html string of popup
	 * @param YousticeShopOrder $order class with attached data
	 */
	public function getOrderDetailHtml(YousticeShopOrder $order)
	{
		if (!trim($this->api_key))
			return '';

		if (!$this->is_properly_installed)
			return '';

		$products = $order->getProducts();
		$product_codes = array();

		foreach ($products as $product)
			$product_codes[] = $product->getCode();

		$report = $this->local->getOrderReport($order->getCode(), $product_codes);

		$order_detail = new YousticeWidgetsOrderDetail($order, $report, $this);

		return $order_detail->toString();
	}

	/**
	 * Action when user viewed order history (for changing report statuses count)
	 * @return YousticeApi
	 */
	public function orderHistoryViewed()
	{
		$this->local->setChangedReportStatusesCount(0);

		return $this;
	}

	/**
	 * Creates report of web
	 * @return string where to redirect
	 */
	public function createWebReport()
	{
		$this->updateData(true);

		$local_report = $this->local->getWebReport($this->user_id);

		if ($local_report->canCreateNew())
			return $this->createWebReportExecute($this->user_id);
		else
		{
			$remote_link = $this->local->getCachedRemoteReportLink($local_report->getCode());

			if (Tools::strlen($remote_link))
				return $remote_link;
			else
				return $this->createWebReportExecute($this->user_id);
		}
	}

	private function createWebReportExecute($user_id)
	{
		$new_code = $this->local->createWebReport($user_id, $user_id);

		$redirect_link = $this->remote->createWebReport($new_code);

		if ($redirect_link == null)
			throw new YousticeFailedRemoteConnectionException;

		$this->setOftenUpdates();

		return $redirect_link;
	}

	/**
	 * Creates order report
	 * @param YousticeShopOrder $order class with attached data
	 * @return string where to redirect
	 */
	public function createOrderReport(YousticeShopOrder $order)
	{
		$this->updateData(true);

		$report = new YousticeReportsOrderReport($order->toArray());
		$local_report = $this->local->getOrderReport($report->getCode());

		if ($local_report->canCreateNew())
			return $this->createOrderReportExecute($order);
		else
		{
			$remote_link = $this->local->getCachedRemoteReportLink($local_report->getCode());

			if (Tools::strlen($remote_link))
				return $remote_link;
			else
				return $this->createOrderReportExecute($order);
		}
	}

	private function createOrderReportExecute(YousticeShopOrder $order)
	{
		$report = new YousticeReportsOrderReport($order->toArray());
		$new_code = $this->local->createReport($report->getCode(), $this->user_id);

		$redirect_link = $this->remote->createOrderReport($order, $new_code);

		if ($redirect_link == null)
			throw new YousticeFailedRemoteConnectionException;

		$this->setOftenUpdates();

		return $redirect_link;
	}

	/**
	 * Creates product report
	 * @param YousticeShopProduct $product class with attached data
	 * @return string where redirect
	 */
	public function createProductReport(YousticeShopProduct $product)
	{
		$this->updateData(true);

		$report = new YousticeReportsProductReport($product->toArray());
		$local_report = $this->local->getProductReport($report->getCode());

		if ($local_report->canCreateNew())
			return $this->createProductReportExecute($product);
		else
		{
			$remote_link = $this->local->getCachedRemoteReportLink($local_report->getCode());

			if (Tools::strlen($remote_link))
				return $remote_link;
			else
				return $this->createProductReportExecute($product);
		}
	}

	private function createProductReportExecute(YousticeShopProduct $product)
	{
		$report = new YousticeReportsProductReport($product->toArray());
		$new_code = $this->local->createReport($report->getCode(), $this->user_id);

		$redirect_link = $this->remote->createProductReport($product, $new_code);

		if ($redirect_link == null)
			throw new YousticeFailedRemoteConnectionException;

		$this->setOftenUpdates();

		return $redirect_link;
	}

	/**
	 * Create necessary table
	 * @return boolean success
	 */
	public function install()
	{
		//raise exceptions
		$this->checkIsProperlyInstalledWithExceptions();
		
		$result = $this->local->install();
		
		$this->infinarioInstalledEvent();
		
		return $result;
	}

	/**
	 * Drop table
	 * @return boolean success
	 */
	public function uninstall()
	{
		$result = $this->local->uninstall();
		
		$this->infinarioUninstalledEvent();
		
		return $result;
	}

	public function setOftenUpdates()
	{
		$this->session->set('last_often_update', time());
	}

	/**
	 * Connect to remote and update local data
	 * @param boolean $force_update update also if data are acutal
	 */
	protected function updateData($force_update = false)
	{
		if ($force_update || $this->canUpdate())
		{
			if ($this->updateDataExecute())
				$this->session->set('last_update', time());
		}
	}

	/**
	 * If api key is set and time upate intervals are in range
	 * @return boolean if can update
	 */
	protected function canUpdate()
	{
		if (Tools::strlen($this->api_key) == 0)
			return false;

		if (!$this->is_properly_installed)
			return false;

		$last_often_update = 0;
		if ($this->session->get('last_often_update'))
			$last_often_update = $this->session->get('last_often_update');

		//setOftenUpdates() was called 5 minutes before or earlier
		if ($last_often_update + $this->often_update_interval > time())
			return true;

		$last_update = 0;
		if ($this->session->get('last_update'))
			$last_update = $this->session->get('last_update');

		return $last_update + $this->update_interval < time();
	}

	/**
	 * Get data for logoWidget, update report statuses and time
	 * @return boolean success
	 */
	protected function updateDataExecute()
	{
		if (!$this->user_id)
			return false;

		$local_reports_data = $this->local->getReportsByUser($this->user_id);

		//try to get remote reports
		try {
			$remote_reports_data = $this->remote->getRemoteReportsData($local_reports_data);
		} catch (Exception $e) {
			return false;
		}

		//no new updates
		if (count($remote_reports_data) === 0)
			return true;

		$changed_report_statuses_count = $this->local->getChangedReportStatusesCount();

		foreach ($local_reports_data as $local)
		{
			foreach ($remote_reports_data as $remote)
			{
				if (!isset($remote['orderNumber']) || $local['code'] !== $remote['orderNumber'])
					continue;

				$this->local->setCachedRemoteReportLink($local['code'], $remote['redirect_link']);
				//status changed?
				if ($local['status'] !== $remote['status'])
				{
					$changed_report_statuses_count++;
					$this->local->updateReportStatus($remote['orderNumber'], $remote['status']);
				}

				$this->local->updateReportRemainingTime($remote['orderNumber'], $remote['remaining_time']);
			}
		}

		$this->local->setChangedReportStatusesCount($changed_report_statuses_count);

		return true;
	}

	/**
	 * Set database params in associative array for PDO
	 * @param array $db_credentials associative array for PDO connection with must fields: driver, host, name, user, pass
	 * @return YousticeApi
	 */
	public function setDbCredentials(array $db_credentials)
	{
		if (count($db_credentials))
			$this->setLocal(new YousticeLocal($db_credentials));

		return $this;
	}

	/**
	 * 
	 * @param YousticeProvidersSessionProviderInterface $session
	 * @return YousticeApi
	 */
	public function setSession(YousticeProvidersSessionProviderInterface $session)
	{
		$this->session = $session;
		$this->session->start();

		if ($this->local !== null)
			$this->local->setSession($this->session);

		return $this;
	}

	/**
	 * 
	 * @param YousticeLocalInterface $local
	 * @return YousticeApi
	 */
	public function setLocal(YousticeLocalInterface $local)
	{
		$this->local = $local;

		if ($this->session !== null)
			$this->local->setSession($this->session);

		return $this;
	}
	
	/**
	 *
	 * @return Youstice_LocalInterface $local
	 */
	public function getLocal()
	{
		return $this->local;
	}


	/**
	 * Set eshop language
	 * @param string
	 * @return YousticeApi
	 * @throws InvalidArgumentException
	 */
	public function setLanguage($lang = null)
	{		
		$this->language = $lang;
		return $this;
	}

	/**
	 * Set API key
	 * @param string $api_key if true api is in playground mode, data are not real
	 * @return YousticeApi
	 */
	public function setApiKey($api_key, $use_sandbox = false)
	{
		if (!trim($api_key))
			return $this;

		$this->api_key = $api_key;

		$this->use_sandbox = ($use_sandbox == true ? true : false);

		return $this;
	}
	
	public function checkApiKey()
	{
		$result = $this->remote->checkApiKey();

		if ($result) {
			$this->infinarioValidApiKeySetEvent();
		}

		return $result;
	}

	/**
	 * Set what type of goods is eshop selling
	 * @param string $shop_sells "product|service"
	 * @return YousticeApi
	 * @throws InvalidArgumentException
	 * @deprecated
	 */
	public function setThisShopSells($shop_sells)
	{
		return $this;
	}

	/**
	 * Check if shopSells attribute is correct
	 * @throws InvalidArgumentException
	 * @deprecated
	 */
	protected function checkShopSells()
	{

	}

	/**
	 * Check if curl, PDO and fileinfo are available
	 * @return boolean
	 */
	public function checkIsProperlyInstalled()
	{
		if ((!in_array(ini_get('allow_url_fopen'), array('On', 'on', '1')) && !function_exists('curl_exec')) || !extension_loaded('PDO') || !function_exists('finfo_open') || !$this->local)
			return false;

		return true;
	}

	/**
	 * Check if curl, PDO and fileinfo are available
	 * @throws Youstice_ApiException
	 */
	public function checkIsProperlyInstalledWithExceptions()
	{
		if (!in_array(ini_get('allow_url_fopen'), array('On', 'on', '1')) && !function_exists('curl_exec'))
			throw new YousticeApiException('Youstice: cURL is not installed, please install it.', self::CURL_NOT_INSTALLED);

		if (!extension_loaded('PDO'))
			throw new YousticeApiException('Youstice: PDO is not installed, please install it.', self::PDO_NOT_INSTALLED);
	}

	/**
	 * Set on which software is eshop running
	 * @param string $shop_type "prestashop|magento|ownSoftware"
	 * @param string $shop_version full version string
	 * @return YousticeApi
	 */
	public function setShopSoftwareType($shop_type, $shop_version = '')
	{
		if (Tools::strlen($shop_type))
			$this->shop_software_type = $shop_type;

		if (Tools::strlen($shop_version))
			$this->shop_software_version = $shop_version;

		return $this;
	}

	/**
	 * Set user id, unique for eshop
	 * @param integer $user_id
	 * @return YousticeApi
	 */
	public function setUserId($user_id)
	{
		$this->user_id = $user_id;

		return $this;
	}
	
	public function initInfinarioWithPlayerData($player_email, array $player_data) {
		$this->infinario = new YousticeInfinarioFacade('3d95aefc-df52-11e4-8a48-b083fedeed2e', false);
		$this->infinario->setPlayerId($player_email);		
		$this->infinario->setPlayerData($player_data);
		
		return $this;
	}
	
	public function setInfinarioEventData(array $event_data) {		
		$basic_event_data = array (
			'language' => $this->language,
			'shop_software_type' => $this->shop_software_type,
			'shop_software_version' => $this->shop_software_version,
			'sdk_version' => $this->getVersionName(),
			'server_data' => array (
				'php_version' => phpversion(),
				'os'	=> php_uname()
			)
		);
		
		$this->infinario->setEventData(array_merge($basic_event_data, $event_data));
		
		return $this;
	}
	
	public function infinarioInstalledEvent() {
		try {			
			//register player's data only on install
			$this->infinario->registerPlayerData();
			$this->infinario->installedEvent();
		}
		catch(Exception $e) {
			//ignore
		}
		
		return $this;
	}
	
	public function infinarioUninstalledEvent() {
		try {			
			$this->infinario->uninstalledEvent();
		}
		catch(Exception $e) {
			//ignore
		}
		
		return $this;
	}
	
	public function infinarioValidApiKeySetEvent() {
		try {			
			$this->infinario->validApiKeySetEvent();
		}
		catch(Exception $e) {
			//ignore
		}
		
		return $this;		
	}
	
	public function infinarioRegisterMeClickedEvent() {
		try {			
			$this->infinario->registerMeClickedEvent();
		}
		catch(Exception $e) {
			//ignore
		}
		
		return $this;		
	}

}

class YousticeApiException extends Exception {

}

class YousticeInvalidApiKeyException extends Exception {

}

class YousticeFailedRemoteConnectionException extends Exception {
	/**
	 * Contains http status code from response if provided
	 * @var int
	 */
	protected $code;

}
