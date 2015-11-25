<?php
/**
 * Youstice Resolution Module
 *
 * @author    Youstice
 * @copyright (c) 2015, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class YousticeResolutionSystem extends Module
{
    private $y_api;

    public function __construct()
    {
        $this->name = 'yousticeresolutionsystem';
        $this->tab = 'advertising_marketing';
        $this->version = '1.11.3';
        $this->author = 'Youstice';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.6');
        $this->dependencies = array();

        parent::__construct();

        $this->displayName = $this->l('Youstice');
        $this->preloadStringTranslations();
        $description = 'Increase customer satisfaction and become a trusted retailer. Negotiate and resolve customer complaints just in a few clicks';
        $this->description = $this->l($description);
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        $this->createApi();
    }

    protected function createApi()
    {
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
        $this->y_api->setShopSoftwareType('prestashop', _PS_VERSION_);
        $this->y_api->setApiKey(Configuration::get('YRS_API_KEY'), Configuration::get('YRS_SANDBOX'));
        $this->y_api->setSession(new YousticeProvidersSessionPrestashopProvider());
    }

    public function hookDisplayHeader()
    {
        //if ajax call
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && Tools::strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            return;
        }

        $this->context->controller->addCSS($this->_path . 'views/css/youstice.css', 'all');
        $this->context->controller->addCSS($this->_path . 'views/css/youstice_prestashop.css', 'all');
        $this->context->controller->addJS($this->_path . 'views/js/yrs_order_history.js');
        
        $logo_widget_left_offset = (int) Configuration::get('YRS_LOGO_WIDGET_LEFT_OFFSET');
        
        $html = '<style>#yousticeFloatingWidget{left:'.$logo_widget_left_offset.'%}</style>';

        if (Tools::getValue('section') == 'getReportClaimsPage') {
            $html .= $this->addReportClaimsPageMetaTags();
        }
        
        return $html;
    }

    protected function addReportClaimsPageMetaTags()
    {
        $title_string = $this->context->shop->name . ' - Youstice - ' . $this->l('File a complaint');
        $html = '<meta property="og:title" content="' . $title_string . '" />';
        $html .= '<meta property="og:type" content="website" />';
        $html .= '<meta property="og:url" content="' . $this->getReportClaimsPageLink() . '" />';
        $html .= '<meta property="og:image" content="' . _PS_BASE_URL_ . $this->_path . 'logo.png" />';

        $description_text = $this->l('In case you want to complain about a product or service, please follow this link.');
        $html .= '<meta property="og:description" content="' . $description_text . '" />';
        return $html;
    }

    public function hookActionOrderDetail()
    {
        echo '<script type="text/javascript" src="' . $this->_path . 'views/js/yrs_order_detail.js"></script>';
    }

    public function getContent()
    {
        if (Tools::isSubmit('registerMe') || Tools::isSubmit('registerMeSandbox')) {
            $lang = $this->context->language->iso_code;

            if (Tools::isSubmit('registerMeSandbox')) {
                $url = 'https://app-sand.youstice.com/blox-odr13/generix/odr/' . $lang . '/app2/_shopConfiguration_';
            } else {
                $url = 'https://app.youstice.com/blox-odr/generix/odr/' . $lang . '/app2/_subscription_';
            }

            $url .= '?utm_source=eshop&utm_medium=cpc&utm_content=presta_signup&utm_campaign=plugins';

            Tools::redirect($url);
        }

        $output = '';

        $properly_installed = $this->checkIfProperlyInstalled();

        //ajax
        $this->checkForSentApiKey();

        //ajax
        $this->checkForSentRegistration();

        $output .= $this->displayErrorMessage();
        $output .= $this->displayConfirmMessage();

        //disable form
        if (!$properly_installed) {
            return $output;
        }

        $smarty = Context::getContext()->smarty;

        $language_code = $this->context->language->iso_code;
        $translator = new YousticeTranslator();
        $base_url = AdminController::$currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules');

        $smarty->assign('saveHref', $base_url . '&save' . $this->name);
        $smarty->assign('checkApiKeyUrl', $base_url . '&checkForApiKey' . $this->name);
        $smarty->assign('registerMeUrl', $base_url . '&registerMe');
        $smarty->assign('registerMeSandboxUrl', $base_url . '&registerMeSandbox');
        $smarty->assign('api_key', Configuration::get('YRS_API_KEY'));
        $smarty->assign('show_logo_widget', Configuration::get('YRS_SHOW_LOGO_WIDGET'));
        $smarty->assign('logo_widget_left_offset', Configuration::get('YRS_LOGO_WIDGET_LEFT_OFFSET'));
        $smarty->assign('use_sandbox', Configuration::get('YRS_SANDBOX'));
        $smarty->assign('reportClaimsPageLink', $this->getReportClaimsPageLink());
        $smarty->assign('modulePath', $this->_path);
        $smarty->assign('is1_5Version', version_compare(_PS_VERSION_, '1.6.0') <= 0);

        $smarty->assign('registrationUrl', $base_url . '&registration' . $this->name);
        $smarty->assign('shopName', Configuration::get('PS_SHOP_NAME'));
        $smarty->assign('adminFirstName', $this->context->employee->firstname);
        $smarty->assign('adminLastName', $this->context->employee->lastname);
        $smarty->assign('shopURL', _PS_BASE_URL_);
        $smarty->assign('shopMail', Configuration::get('PS_SHOP_EMAIL'));
        $smarty->assign('languageCode', $language_code);
        $smarty->assign('templateLinks', $translator->getAdminTemplateLinks($language_code));

        $output .= $smarty->fetch(_PS_MODULE_DIR_ . $this->name . '/views/templates/admin/main.tpl');

        return $output;
    }

    protected function checkIfProperlyInstalled()
    {
        try {
            $this->y_api->checkIsProperlyInstalledWithExceptions();
        } catch (YousticeApiException $e) {
            $this->saveErrorMessage($this->l($e->getMessage()));
            return false;
        }

        if (Configuration::get('YRS_DB_INSTALLED') == '0') {
            $this->y_api->install();
        }

        return true;
    }

    protected function checkForSentApiKey()
    {
        if (Tools::isSubmit('checkForApiKey' . $this->name)) {
            $result = $this->checkForApiKey(Tools::getValue('api_key'), Tools::getValue('use_sandbox'));

            if (is_array($result)) {
                $this->saveApiKey($result);
            }

            Configuration::updateValue('YRS_SHOW_LOGO_WIDGET', Tools::getValue('show_logo_widget'));
            Configuration::updateValue('YRS_LOGO_WIDGET_LEFT_OFFSET', Tools::getValue('logo_widget_left_offset'));

            $response = Tools::jsonEncode(array('result' => $result));
            exit($response);
        }
    }

    protected function checkForSentRegistration()
    {
        if (Tools::isSubmit('registration' . $this->name)) {
            $terms_and_conditions = Tools::getIsset('terms_and_conditions');

            if (!$terms_and_conditions) {
                exit(Tools::jsonEncode(array('result' => 'terms_not_accepted')));
            }

            $registration = new YousticeShopRegistration();

            $shop_currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
            $shop_lang = new Language(Configuration::get('PS_LANG_DEFAULT'));

            $registration
                    ->setCompanyName(Tools::getValue('company_name', ''))
                    ->setEmail(Tools::getValue('email', ''))
                    ->setPassword(Tools::getValue('password', ''))
                    ->setFirstName(Tools::getValue('first_name', ''))
                    ->setLastName(Tools::getValue('last_name', ''))
                    ->setVerifyPasswordValue(Tools::getValue('verify_password', ''))
                    ->setShopUrl(Tools::getValue('shop_url', ''))
                    ->setCompanyIsInFrance(Tools::getIsset('company_is_in_france'))
                    ->setIsFrenchForm(Tools::getIsset('is_french_form'))
                    ->setAdminLang($this->context->language->iso_code)
                    ->setDefaultCountry($this->context->country->iso_code)
                    ->setShopName(Configuration::get('PS_SHOP_NAME'))
                    ->setShopCurrency($shop_currency->iso_code)
                    ->setShopLang($shop_lang->iso_code)
                    ->setShopLogoPath(_PS_IMG_DIR_ . Configuration::get('PS_LOGO'));

            try {
                $api_key = $this->y_api->runWithoutUpdates()->register($registration);
            } catch (YousticeShopRegistrationValidationException $e) {
                $response = Tools::jsonEncode(array('result' => $e->getMessage()));
                exit($response);
            } catch (YousticeShopRegistrationShopAlreadyRegistered $e) {
                $this->saveErrorMessage($this->l('Registration failed: Email is already in use.'));
                $response = Tools::jsonEncode(array('result' => true));
                exit($response);
            }
            catch (YousticeFailedRemoteConnectionException $e) {
                $response = Tools::jsonEncode(array('result' => 'request_failed'));
                exit($response);
            }

            Configuration::updateValue('YRS_API_KEY', $api_key);
            Configuration::updateValue('YRS_SANDBOX', false);
            $this->saveConfirmMessage($this->l('Settings saved. Integration with Youstice successfully configured.'));
            $response = Tools::jsonEncode(array('result' => true));
            exit($response);
        }
    }

    protected function saveApiKey(array $data)
    {
        Configuration::updateValue('YRS_API_KEY', $data['api_key']);
        Configuration::updateValue('YRS_SANDBOX', $data['use_sandbox']);
        
        $this->saveConfirmMessage($this->l('Settings were saved successfully.'));
    }

    protected function saveErrorMessage($string)
    {
        $this->context->cookie->yAdminError = $string;
    }

    protected function saveConfirmMessage($string)
    {
        $this->context->cookie->yAdminConfirm = $string;
    }

    public function displayErrorMessage()
    {
        $message = $this->context->cookie->yAdminError;

        if ($message) {
            $this->context->cookie->yAdminError = null;
            return parent::displayError($message);
        }
    }

    public function displayConfirmMessage()
    {
        $message = $this->context->cookie->yAdminConfirm;

        if ($message) {
            $this->context->cookie->yAdminConfirm = null;
            return parent::displayConfirmation($message);
        }
    }

    protected function getReportClaimsPageLink()
    {
        $base = Tools::getShopDomainSsl(true) . __PS_BASE_URI__;
        return $base . 'index.php?fc=module&module=yousticeresolutionsystem&controller=yrs&action=getReportClaimsPage';
    }

    protected function checkForApiKey($api_key, $use_sandbox)
    {
        if (!trim($api_key)) {
            return false;
        }
        
        $result = $this->_checkForApiKey($api_key, $use_sandbox);

        if ($result === -1) {
            return 'request_failed';
        } elseif ($result == true) {
            return array (
                'api_key' => $api_key,
                'use_sandbox' => $use_sandbox
            );
        }

        //try opposite user_sandbox value
        $result = $this->_checkForApiKey($api_key, !$use_sandbox);

        if ($result === -1) {
            return 'request_failed';
        } elseif ($result == true) {
            return array (
                'api_key' => $api_key,
                'use_sandbox' => !$use_sandbox
            );
        }

        return false;
    }
    
    protected function _checkForApiKey($api_key, $use_sandbox)
    {
        $this->y_api->setApiKey($api_key, $use_sandbox);
        $this->y_api->runWithoutUpdates();

        try {
            $result = $this->y_api->checkApiKey();
        } catch (Exception $e) {
            return -1;
        }

        return $result;
    }

    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        Configuration::updateValue('YRS_DB_INSTALLED', 1);
        Configuration::updateValue('YRS_SHOW_LOGO_WIDGET', 1);
        Configuration::updateValue('YRS_LOGO_WIDGET_LEFT_OFFSET', 75);

        try {
            $this->y_api->install();
        } catch (YousticeApiException $e) {
            //be silent here, instead show error message at admin page
            Configuration::updateValue('YRS_DB_INSTALLED', '0');
        }

        return parent::install() &&
                $this->registerHook('header') &&
                $this->registerHook('orderDetail') &&
                Configuration::updateValue('YRS_SANDBOX', '0') &&
                Configuration::updateValue('YRS_API_KEY', '');
    }

    public function uninstall()
    {
        $this->y_api->uninstall();

        Configuration::deleteByName('YRS_ITEM_TYPE');
        Configuration::deleteByName('YRS_SHOW_LOGO_WIDGET');
        Configuration::deleteByName('YRS_LOGO_WIDGET_LEFT_OFFSET');

        if (!parent::uninstall() ||
                !Configuration::deleteByName('YRS_SANDBOX') ||
                !Configuration::deleteByName('YRS_API_KEY') ||
                !Configuration::deleteByName('YRS_DB_INSTALLED')) {
            return false;
        }

        return true;
    }

    protected function preloadStringTranslations()
    {
        $this->l('Increase customer satisfaction and become a trusted retailer. Negotiate and resolve customer complaints just in a few clicks');
        $this->l('Youstice: cURL is not installed, please install it.');
        $this->l('Youstice: PDO is not installed, please install it.');
        $this->l('Youstice: PECL finfo is not installed, please install it.');
    }
}
