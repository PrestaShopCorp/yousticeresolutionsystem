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
class YousticeRemote extends YousticeRequest
{

    protected $api_key;
    protected $use_sandbox;
    protected $lang;
    protected $shop_software_type;
    protected $shop_software_version;
    protected $plugin_software_version;

    public function __construct($api_key, $use_sandbox, $lang, $shop_software_type, 
            $shop_software_version = '', $plugin_software_version = '')
    {
        $this->api_key = $api_key;
        $this->use_sandbox = $use_sandbox;
        $this->lang = $lang;
        $this->shop_software_type = $shop_software_type;
        $this->shop_software_version = $shop_software_version;
        $this->plugin_software_version = $plugin_software_version;
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

        $url = $this->generateUrl('services/Api/logo');

        $this->get($url);

        $response = $this->responseToArray();

        return $response['html'];
    }

    public function getRemoteReportsData(array $local_reports_data)
    {
        $send = array('orders' => array());

        foreach ($local_reports_data as $local_report_data) {
            $send['orders'][] = array('orderNumber' => $local_report_data['code']);
        }

        $url = $this->generateUrl('services/Api/claims');

        $this->post($url, $send);
        $response = $this->responseToArray();

        return $response['orders'];
    }

    public function createWebReport($order_number)
    {
        $url = $this->generateUrl('services/Api/addTransactionShop');

        $this->post($url, array('orderNumber' => $order_number));

        $response = $this->responseToArray();

        return $response['redirect_link'];
    }

    public function createOrderReport(YousticeShopOrder $order, $code)
    {
        $data = $order->toArray();
        $now = new Datetime();

        $request_data = array(
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

        $url = $this->generateUrl('services/Api/addTransaction');

        $this->post($url, $request_data);

        $response = $this->responseToArray();

        return $response['redirect_link'];
    }

    public function createProductReport(YousticeShopProduct $product, $code)
    {
        $data = $product->toArray();
        $now = new Datetime();

        $request_data = array(
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

        $url = $this->generateUrl('services/Api/addTransaction');

        $this->post($url, $request_data);

        $response = $this->responseToArray();

        return $response['redirect_link'];
    }

    public function checkApiKey()
    {
        $request_data = array(
            'platform' => $this->shop_software_type,
            'version' => $this->shop_software_version
        );

        $url = $this->generateUrl('services/Api/auth');

        try {
            $this->post($url, $request_data);
        } catch (YousticeInvalidApiKeyException $e) {
            return false;
        }

        $response = $this->responseToArray();

        return $response && $response['result'] == 'true';
    }

    public function register(YousticeShopRegistration $registration)
    {
        $referenceString = uniqid();

        $request_data = array(
            'orgName' => $registration->getCompanyName(),
            'orgUrl' => $registration->getShopUrl(),
            'orgLogo' => $registration->getShopLogo(),
            'admin' => array(
                'email' => $registration->getEmail(),
                'firstName' => $registration->getFirstName(),
                'lastName' => $registration->getLastName(),
                'password' => $registration->getPassword(),
                'primaryLang' => $registration->getAdminLang()
            ),
            'country' => $registration->getDefaultCountry(),
            'subscription' => 'SMALL', //todo change for production
            'acceptsTC' => true,
            'acceptsPP' => true,
            'couponCode' => null, //TODO change for production
            'shops' => array(
                array(
                    'ref' => $referenceString,
                    'name' => $registration->getShopName(),
                    'url' => $registration->getShopUrl(),
                    'sector' => 'RETAIL',
                    'currency' => $registration->getShopCurrency(),
                    'primaryLang' => $registration->getShopLang(),
                    'logo' => $registration->getShopLogo(),
                    'integrationInfo' => array(
                        'platform' => $this->shop_software_type,
                        'version' => $this->shop_software_version,
                        'pluginVersion' => $this->plugin_software_version
                    )
                )
            )
        );

        //TODO change for production
        //$url = $this->api_production_url . 'organizations/retailers/registration';
        $url = 'https://api-qa.youstice.com/api/organizations/retailers/registration?test=true';

        try {
            $this->post($url, $request_data);
        } catch (YousticeFailedRemoteConnectionException $e) {
            //shop was already registered
            if ($e->getCode() == 409) {
                throw new YousticeShopRegistrationShopAlreadyRegistered();
            }

            throw $e;
        }

        $response = $this->responseToArray();

        $shop_data = $response['shops'][0];

        if ($shop_data['ref'] != $referenceString) {
            throw new YousticeFailedRemoteConnectionException('Post Request failed: ' . $url, -1);
        }

        return $shop_data['apiKey'];
    }
}
