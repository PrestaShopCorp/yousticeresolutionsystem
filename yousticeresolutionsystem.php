<?php
/**
 * Youstice Resolution Module
 *
 * @author    Youstice
 * @copyright (c) 2014, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

if (!defined('_PS_VERSION_'))
	exit;

class YousticeResolutionSystem extends Module
{
	private $y_api;

	public function __construct()
	{
		$this->name                   = 'yousticeresolutionsystem';
		$this->tab                    = 'advertising_marketing';
		$this->version                = '1.5.2';
		$this->author                 = 'Youstice';
		$this->need_instance          = 0;
		$this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.6');
		$this->dependencies			  = array();

		parent::__construct();

		$this->displayName		= $this->l('Youstice');
		$description = 'Increase customer satisfaction and become a trusted retailer. Negotiate and resolve customer complaints just in a few clicks';
		$this->description		= $this->l($description);
		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

		require_once('SDK/Api.php');
		$db = array(
			'driver' => 'mysql',
			'host' => _DB_SERVER_,
			'user' => _DB_USER_,
			'pass' => _DB_PASSWD_,
			'name' => _DB_NAME_,
			'prefix' => _DB_PREFIX_
		);

		$this->y_api = YousticeApi::create();

		$this->y_api->setDbCredentials($db);
		$this->y_api->setLanguage($this->context->language->iso_code);
		$this->y_api->setShopSoftwareType('prestashop');
		$this->y_api->setThisShopSells(Configuration::get('YRS_ITEM_TYPE'));
		$this->y_api->setApiKey(Configuration::get('YRS_API_KEY'), Configuration::get('YRS_SANDBOX'));
		$this->y_api->setSession(new YousticeProvidersSessionPrestashopProvider());

	}

	public function hookDisplayHeader()
	{
		//if ajax call
		if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && Tools::strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
			return;

		$this->context->controller->addCSS($this->_path.'public/css/youstice.css', 'all');
		$this->context->controller->addCSS($this->_path.'public/css/youstice_prestashop.css', 'all');
		$this->context->controller->addJS($this->_path.'public/js/yrs_order_history.js');

		//add fancybox (to 1.5 only)
		if (version_compare(_PS_VERSION_, '1.6.0') <= 0)
		{
			$this->context->controller->addCSS($this->_path.'public/css/jquery.fancybox.css', 'all');
			$this->context->controller->addJS($this->_path.'public/js/fancybox/jquery.fancybox.pack.js');
		}

		if (Tools::getValue('section') == 'getReportClaimsPage')
			return $this->addReportClaimsPageMetaTags();
	}

	public function hookDisplayFooter()
	{
		//if ajax call
		if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && Tools::strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
			return;

		return '<a class="yousticeShowLogoWidget">'.$this->l('Youstice - show logo').'</a>';
	}

	protected function addReportClaimsPageMetaTags()
	{
		$title_string = $this->context->shop->name.' - Youstice - '.$this->l('File a complaint');
		$html = '<meta property="og:title" content="'.$title_string.'" />';
		$html .= '<meta property="og:type" content="website" />';
		$html .= '<meta property="og:url" content="'.$this->getReportClaimsPageLink().'" />';
		$html .= '<meta property="og:image" content="'._PS_BASE_URL_.$this->_path.'logo.png" />';

		$description_text = $this->l('In case you want to complain about a product or service, please follow this link.');
		$html .= '<meta property="og:description" content="'.$description_text.'" />';
		return $html;
	}

	public function hookActionOrderDetail()
	{
		echo '<script type="text/javascript" src="'.$this->_path.'public/js/yrs_order_detail.js"></script>';
	}

	public function getContent()
	{
		$output = null;

		if (Tools::isSubmit('save'.$this->name))
		{
			$yrs_apikey = (string)Tools::getValue('api_key');
			if (!$yrs_apikey	|| empty($yrs_apikey) || !Validate::isGenericName($yrs_apikey))
				$output .= $this->displayError( $this->l('Invalid API KEY') );
			else
			{
				Configuration::updateValue('YRS_API_KEY', $yrs_apikey);
				$output .= $this->displayConfirmation($this->l('Settings were saved successfully.'));
			}

			$yrs_sandbox = (string)Tools::getValue('use_sandbox');
			if (!in_array($yrs_sandbox, array(0,1)))
				$output .= $this->displayError( $this->l('Invalid Configuration value') );
			else
				Configuration::updateValue('YRS_SANDBOX', $yrs_sandbox);

			$this->y_api->setApiKey($yrs_apikey, $yrs_sandbox);

			$this->y_api->install();
		}

		$smarty = Context::getContext()->smarty;

		$smarty->assign('saveHref', AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
				'&token='.Tools::getAdminTokenLite('AdminModules'));
		$smarty->assign('api_key', Configuration::get('YRS_API_KEY'));
		$smarty->assign('use_sandbox', Configuration::get('YRS_SANDBOX'));
		$smarty->assign('reportClaimsPageLink', $this->getReportClaimsPageLink());
		$smarty->assign('cssFile', _PS_BASE_URL_.$this->_path.'public/css/admin.css');

		$output .= $smarty->fetch(_PS_MODULE_DIR_.$this->name.'/views/templates/admin/main.tpl');

		return $output;
	}

	protected function getReportClaimsPageLink()
	{
		$base = Tools::getShopDomainSsl(true).__PS_BASE_URI__;
		return $base.'index.php?fc=module&module=yousticeresolutionsystem&controller=yrs&action=getReportClaimsPage';
	}

	public function install()
	{
		if (Shop::isFeatureActive())
			Shop::setContext(Shop::CONTEXT_ALL);

		$db = array(
			'driver' => 'mysql',
			'host' => _DB_SERVER_,
			'user' => _DB_USER_,
			'pass' => _DB_PASSWD_,
			'name' => _DB_NAME_,
			'prefix' => _DB_PREFIX_
		);

		$y_api = YousticeApi::create();
		$y_api->setDbCredentials($db);
		$y_api->setLanguage($this->context->language->iso_code);
		$y_api->setShopSoftwareType('prestashop');
		$y_api->setThisShopSells('product');
		$y_api->setApiKey(Configuration::get('YRS_API_KEY'), Configuration::get('YRS_SANDBOX'));

		$this->y_api->install();

		return parent::install() &&
			$this->registerHook('header') &&
			$this->registerHook('footer') &&
			$this->registerHook('orderDetail') &&
			Configuration::updateValue('YRS_ITEM_TYPE', 'product') &&
			Configuration::updateValue('YRS_API_KEY', '');
	}

	public function uninstall()
	{
		$this->y_api->uninstall();

		if (!parent::uninstall() ||
				!Configuration::deleteByName('YRS_ITEM_TYPE') ||
				!Configuration::deleteByName('YRS_API_KEY'))
			return false;

		return true;
	}

}
