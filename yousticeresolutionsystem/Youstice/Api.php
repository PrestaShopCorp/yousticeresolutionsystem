<?php
/**
 * Main Youstice class.
 *
 * @author    Youstice
 * @copyright (c) 2014, Youstice
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
	 * @var type YousticeTranslator
	 */
	protected $translator;

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
	 * product|service
	 * @var string 
	 */
	protected $shop_sells;

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
	 *
	 * @param array $db_credentials associative array for PDO connection with must fields: driver, host, name, user, pass
	 * @param string $language ISO 639-1 char code "en|sk|cz|es"
	 * @param string $api_key string from youstice service
	 * @param string $shop_sells "products|services"
	 * @param integer $user_id unique integer for user
	 * @param boolean $use_sandbox true if testing implementation
	 * @param string $shop_software_type prestashop|magento|ownSoftware
	 * @return \Youstice\Api
	 */
	public static function create(array $db_credentials = array(), $language = 'sk', $api_key = '', $shop_sells = 'product',
			$user_id = null, $use_sandbox = false, $shop_software_type = 'custom')
	{
		return new self($db_credentials, $language, $api_key, $shop_sells, $user_id, $use_sandbox, $shop_software_type);
	}

	/**
	 *
	 * @param array $db_credentials associative array for PDO connection with must fields: driver, host, name, user, pass
	 * @param string $language ISO 639-1 char code "en|sk|cz|es"
	 * @param string $api_key string from youstice service
	 * @param string $shop_sells "products|services"
	 * @param integer $user_id unique integer for user
	 * @param boolean $use_sandbox true if testing implementation
	 * @param string $shop_software_type prestashop|magento|ownSoftware
	 * @return \Youstice\Api
	 */
	public function __construct(array $db_credentials = array(), $language = 'sk', $api_key = '', $shop_sells = 'product',
			$user_id = null, $use_sandbox = false, $shop_software_type = 'custom')
	{
		$this->registerAutoloader();

		$this->setDbCredentials($db_credentials);
		$this->setLanguage($language);
		$this->setUserId($user_id);
		$this->setApiKey($api_key, $use_sandbox);
		$this->setThisShopSells($shop_sells);
		$this->setShopSoftwareType($shop_software_type);

		return $this;
	}

	/**
	 * Start Youstice API
	 * @return \Youstice\Api
	 */
	public function run()
	{
		$this->checkShopSells();

		$this->remote = new YousticeRemote($this->api_key, $this->use_sandbox, $this->language, $this->shop_sells, $this->shop_software_type);

		$this->updateData();

		return $this;
	}

	/**
	 * Helper function for autoloading classes (called in constructor)
	 */
	protected function registerAutoloader()
	{
		spl_autoload_register(function ($class_name) {
			$class_name = str_replace('Youstice', '', $class_name);
			//$class_path = str_replace('_', DIRECTORY_SEPARATOR, $class_name);
			$class_path = Tools::substr(preg_replace('/([A-Z])/', DIRECTORY_SEPARATOR.'\\1', $class_name), 1);

			$path = dirname(__FILE__).DIRECTORY_SEPARATOR.$class_path;

			if (is_readable($path.'.php'))
				require_once $path.'.php';
			else
			{
				$path = strrev(preg_replace('/\\'.DIRECTORY_SEPARATOR.'/', '', strrev($path), 1));

				if (is_readable($path.'.php'))
					require_once $path.'.php';
				else
				{
					$path = strrev(preg_replace('/\\'.DIRECTORY_SEPARATOR.'/', '', strrev($path), 1));

					if (is_readable($path.'.php'))
						require_once $path.'.php';
				}
			}
		}, true, true);  //prepend our autoloader
	}

	/**
	 * Renders form with fields email and orderNumber for reporting claims
	 * @return string html
	 */
	public function getReportClaimsFormHtml()
	{
		if (!trim($this->api_key))
			return "Invalid shop's api key";

		$widget = new YousticeWidgetsReportClaimsForm($this->language);

		return $widget->toString();
	}

	public function getShowButtonsWidgetHtml()
	{
		if (!trim($this->api_key))
			return '';

		$reports_count = count($this->local->getReportsByUser($this->user_id));

		$widget = new YousticeWidgetsShowButtons($this->language, $reports_count > 0);

		return $widget->toString();
	}

	/**
	 * Returns html string of logo widget
	 * @param string $claims_url url to report claims form
	 * @return string html
	 */
	public function getLogoWidgetHtml($claims_url = '')
	{
		if (!trim($this->api_key))
			return '';

		return $this->remote->getLogoWidgetData($this->local->getChangedReportStatusesCount(), $claims_url, $this->user_id !== null);
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

		$report = $this->local->getWebReport($this->user_id);

		//exists, just redirect
		if (!$report->canCreateNew())
		{
			$remote_link = $this->local->getCachedRemoteReportLink($report->getCode());

			if (Tools::strlen($remote_link))
				$href = $remote_link;
		}

		$web_button = new YousticeWidgetsWebReportButton($href, $this->language, $report);

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

		$report = $this->local->getProductReport($product_id, $order_id);

		//exists, just redirect
		if (!$report->canCreateNew())
		{
			$remote_link = $this->local->getCachedRemoteReportLink($report->getCode());

			if (Tools::strlen($remote_link))
				$href = $remote_link;
		}

		$product_button = new YousticeWidgetsProductReportButton($href, $this->language, $report);

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

		$report = $this->local->getOrderReport($order_id);

		//exists, just redirect
		if (!$report->canCreateNew())
		{
			$remote_link = $this->local->getCachedRemoteReportLink($report->getCode());

			if (Tools::strlen($remote_link))
				$href = $remote_link;
		}

		$order_button = new YousticeWidgetsOrderReportButton($href, $this->language, $report);

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

		$products = $order->getProducts();
		$product_codes = array();

		foreach ($products as $product)
			$product_codes[] = $product->getCode();

		$report = $this->local->getOrderReport($order->getId(), $product_codes);

		$order_button = new YousticeWidgetsOrderDetailButton($href, $this->language, $order, $report, $this);

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

		$products = $order->getProducts();
		$product_codes = array();

		foreach ($products as $product)
			$product_codes[] = $product->getCode();

		$report = $this->local->getOrderReport($order->getCode(), $product_codes);

		$order_detail = new YousticeWidgetsOrderDetail($this->language, $order, $report, $this);

		return $order_detail->toString();
	}

	/**
	 * Action when user viewed order history (for changing report statuses count)
	 * @return \Youstice\Api
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
	 * 
	 * @param string $string to translate
	 * @param array $variables
	 * @return string translated
	 */
	public function t($string, $variables = array())
	{
		return $this->translator->t($string, $variables);
	}

	/**
	 * Create necessary table
	 * @return boolean success
	 */
	public function install()
	{
		return $this->local->install();
	}

	/**
	 * Drop table
	 * @return boolean success
	 */
	public function uninstall()
	{
		return $this->local->uninstall();
	}

	public function setOftenUpdates()
	{
		$this->session->set('last_often_update', time());
	}

	/**
	 * Connect to remote and update local data
	 * @param boolean $force update also if data are acutal
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
	 * @return \Youstice\Api
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
	 * Set eshop language
	 * @param string ISO 639-1 char code "en|sk|cz|es"
	 * @return \Youstice\Api
	 * @throws InvalidArgumentException
	 */
	public function setLanguage($lang = null)
	{
		$lang = trim(Tools::strtolower($lang));

		if ($lang && YousticeHelpersLanguageCodes::check($lang))
		{
			$this->language = $lang;
			$this->translator = new YousticeTranslator($this->language);
		}
		else
			throw new InvalidArgumentException('Language code "'.$lang.'" is not allowed.');

		return $this;
	}

	/**
	 * Set API key
	 * @param string $api_key if true api is in playground mode, data are not real
	 * @return \Youstice\Api
	 */
	public function setApiKey($api_key, $use_sandbox = false)
	{
		if (!trim($api_key))
			return $this;

		$this->api_key = $api_key;

		$this->use_sandbox = ($use_sandbox == true ? true : false);

		return $this;
	}

	/**
	 * Set what type of goods is eshop selling
	 * @param string $shop_sells "product|service"
	 * @return \Youstice\Api
	 * @throws InvalidArgumentException
	 */
	public function setThisShopSells($shop_sells)
	{
		$this->shop_sells = Tools::strtolower($shop_sells);

		return $this;
	}

	/**
	 * Check if shopSells attribute is correct
	 * @throws InvalidArgumentException
	 */
	protected function checkShopSells()
	{
		$allowed_types = array('product', 'service');

		if (in_array(Tools::strtolower($this->shop_sells), $allowed_types))
			$this->shop_sells = Tools::strtolower($this->shop_sells);
		else
			throw new InvalidArgumentException('Shop selling "'.$this->shop_sells.'" is not allowed.');
	}

	/**
	 * Set on which software is eshop running
	 * @param string $shop_type "prestashop|magento|ownSoftware"
	 * @return \Youstice\Api
	 */
	public function setShopSoftwareType($shop_type)
	{
		if (Tools::strlen($shop_type))
			$this->shop_software_type = $shop_type;

		return $this;
	}

	/**
	 * Set user id, unique for eshop
	 * @param integer $user_id
	 * @return \Youstice\Api
	 */
	public function setUserId($user_id)
	{
		$this->user_id = $user_id;

		return $this;
	}

}

class YousticeInvalidApiKeyException extends Exception {

}

class YousticeFailedRemoteConnectionException extends Exception {

}
