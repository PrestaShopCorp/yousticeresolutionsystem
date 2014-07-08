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
		$this->version                = '1.2.7';
		$this->author                 = 'Youstice';
		$this->need_instance          = 0;
		$this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.6');
		$this->dependencies			  = array();

		parent::__construct();

		$this->displayName		= $this->l('Youstice Resolution Module');
		$this->description		= $this->l('Your online justice.');
		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

		if (!Configuration::get('YRS_API_KEY'))
			$this->warning = $this->l('No API KEY');

		require_once('Youstice/Api.php');
		$db = array(
			'driver' => 'mysql',
			'host' => _DB_SERVER_,
			'user' => _DB_USER_,
			'pass' => _DB_PASSWD_,
			'name' => _DB_NAME_,
			'prefix' => _DB_PREFIX_
		);

		$this->y_api = \Youstice\Api::create();

		$this->y_api->setDbCredentials($db);
		$this->y_api->setLanguage($this->context->language->iso_code);
		$this->y_api->setShopSoftwareType('prestashop');
		$this->y_api->setThisShopSells(Configuration::get('YRS_ITEM_TYPE'));
		$this->y_api->setApiKey(Configuration::get('YRS_API_KEY'), Configuration::get('YRS_SANDBOX'));

	}

	public function hookDisplayHeader()
	{
		//if ajax call
		if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
		    return;
		}
		
		$this->context->controller->addCSS($this->_path.'public/css/youstice.css', 'all');
		$this->context->controller->addCSS($this->_path.'public/css/jquery.fancybox.css', 'all');
		$this->context->controller->addJS($this->_path.'public/js/yrs_order_history.js');
		$this->context->controller->addJS($this->_path.'public/js/fancybox/jquery.fancybox.pack.js');
	}

	public function hookActionOrderDetail()
	{
		echo '<script type="text/javascript" src="'.$this->_path.'public/js/yrs_order_detail.js"></script>';
	}

	public function getContent()
	{
		$output = null;

		if (Tools::isSubmit('submit'.$this->name))
		{
			$yrs_apikey = (string)Tools::getValue('YRS_API_KEY');
			if (!$yrs_apikey	|| empty($yrs_apikey) || !Validate::isGenericName($yrs_apikey))
				$output .= $this->displayError( $this->l('Invalid API KEY') );
			else
			{
				Configuration::updateValue('YRS_API_KEY', $yrs_apikey);
				$output .= $this->displayConfirmation($this->l('Settings updated'));
			}

			$yrs_sandbox = (string)Tools::getValue('YRS_SANDBOX');
			if (!in_array($yrs_sandbox, array(0,1)))
				$output .= $this->displayError( $this->l('Invalid Configuration value') );
			else
				Configuration::updateValue('YRS_SANDBOX', $yrs_sandbox);

			$yrs_item_type = (string)Tools::getValue('YRS_ITEM_TYPE');
			if (!$yrs_item_type	|| empty($yrs_item_type) || !Validate::isGenericName($yrs_item_type))
				$output .= $this->displayError( $this->l('Invalid Configuration value') );
			else
				Configuration::updateValue('YRS_ITEM_TYPE', $yrs_item_type);

			$this->y_api->setThisShopSells($yrs_item_type);
			$this->y_api->setApiKey($yrs_apikey, $yrs_sandbox);

			$this->y_api->install();

		}
		
		$output .= '<h2>'.$this->displayName.'</h2>'
			. '<img src="../modules/yousticeresolutionsystem/youstice.png" style="float:left; margin-right:15px;"><b>'
			.$this->l('We help customers and retailers resolve shopping issues quickly and effectively.').'</b><br /><br />'
			.$this->l('Youstice is a global online application for customers and retailers').'<br />'
			.$this->l('It allows quick and efficient communication between shops and customers').'<br />'
			.$this->l('Complaints are resolved in just a few clicks.').'<br /><br /><br />';
		
		$output .= $this->displayForm();

                $footerFile = _PS_ROOT_DIR_ . _THEME_DIR_ . 'footer.tpl';
                $footerContent = file_get_contents($footerFile);
                $anchorFound = false;

                if($footerContent !== false && strpos($footerContent, 'yousticeShowLogoWidget') !== false) {
                    $anchorFound = true;
                }

                if(!$anchorFound) {
                    $output .= $this->displayError(
                            'No logo widget anchor found! '
                            . 'Please add following code to file <b>'. $footerFile . '</b><br><br>'
                            . '<b>' . htmlspecialchars('<a class="yousticeShowLogoWidget">Youstice - show logo</a>') . '</b>');
                }

                return $output;
	}

	public function displayForm()
	{
		// Get default Language
		$default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

		$options_sandbox = array(
			array(
				'id_option' => '0',
				'name' => $this->y_api->t('No')
			),
			array(
				'id_option' => '1',
				'name' => $this->y_api->t('Yes')
			),
		);

		$options_item_types = array(
			array(
				'id_option' => 'product',
				'name' => $this->y_api->t('Products')
			),
			array(
				'id_option' => 'service',
				'name' => $this->y_api->t('Services')
			)
		);

		// Init Fields form array
		$fields_form = array();
		$fields_form[0]['form'] = array(
			'legend' => array(
				'title' => $this->y_api->t('Settings'),
			),
			'input' => array(
				array(
					'type'  => 'text',
					'label' => $this->y_api->t('API Key'),
					'name'  => 'YRS_API_KEY',
					'size'  => 40,
					'required' => true
				),
				array(
					'type' => 'select',
					'label' => $this->y_api->t('Use sandbox environment'),
					'name' => 'YRS_SANDBOX',
					'required' => true,
					'options' => array(
					'query' => $options_sandbox,
					'id'	=> 'id_option',
					'name'	=> 'name'
					)
				),
				array(
					'type' => 'select',
					'label' => $this->y_api->t('This e-shop sells'),
					'name' => 'YRS_ITEM_TYPE',
					'required' => true,
					'options' => array(
					'query' => $options_item_types,
					'id'	=> 'id_option',
					'name'	=> 'name'
					)
				),
			),
			'submit' => array(
				'title' => $this->y_api->t('Save'),
				'class' => 'button'
			)
		);

		$helper = new HelperForm();

		// Module, token and currentIndex
		$helper->module          = $this;
		$helper->name_controller = $this->name;
		$helper->token           = Tools::getAdminTokenLite('AdminModules');
		$helper->currentIndex    = AdminController::$currentIndex.'&configure='.$this->name;

		$helper->default_form_language    = $default_lang;
		$helper->allow_employee_form_lang = $default_lang;

		// Title and toolbar
		$helper->show_toolbar   = false;
		$helper->toolbar_scroll = true;
		$helper->submit_action  = 'submit'.$this->name;
		$helper->toolbar_btn = array(
			'save' => array(
				'desc' => $this->l('Save'),
				'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
				'&token='.Tools::getAdminTokenLite('AdminModules'),
			),
			'back' => array(
				'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
				'desc' => $this->l('Back to list')
			)
		);

		// current values
		$helper->fields_value['YRS_API_KEY']		 = Configuration::get('YRS_API_KEY');
		$helper->fields_value['YRS_SANDBOX']		 = Configuration::get('YRS_SANDBOX');
		$helper->fields_value['YRS_ITEM_TYPE']		 = Configuration::get('YRS_ITEM_TYPE');

		return $helper->generateForm($fields_form);
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

		$y_api = \Youstice\Api::create();
		$y_api->setDbCredentials($db);
		$y_api->setLanguage($this->context->language->iso_code);
		$y_api->setShopSoftwareType('prestashop');
		$y_api->setThisShopSells('product');
		$y_api->setApiKey(Configuration::get('YRS_API_KEY'), Configuration::get('YRS_SANDBOX'));

		$this->y_api->install();

		return parent::install() &&
			$this->registerHook('header') &&
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
