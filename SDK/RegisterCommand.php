<?php
/**
 * Class responsible for customer registration
 *
 * @author    Youstice
 * @copyright (c) 2015, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

class YousticeRegisterCommand {
	
	protected $api;
	protected $remote;
	
	public function __construct($api, $remote)
	{
		$this->api = $api;
		$this->remote = $remote;
	}
	
	/**
	 * 
	 * @param YousticeShopRegistration $registration
	 * @return apiKey or false
	 */
	public function execute(YousticeShopRegistration $registration) {
		
		$validation = $this->validate($registration);

		if ($validation !== true) {
			throw new YousticeShopRegistrationValidationException($validation);
		}
		
		return $this->remote->register($registration);
	}
	
	protected function validate(YousticeShopRegistration $registration) {
		
		if (Tools::strlen(trim($registration->getCompanyName())) == 0)
			return 'company_name_required';
		
		if (Tools::strlen(trim($registration->getFirstName())) == 0)
			return 'first_name_required';
		
		if (Tools::strlen(trim($registration->getLastName())) == 0)
			return 'last_name_required';
		
		if (!filter_var($registration->getEmail(), FILTER_VALIDATE_EMAIL))
			return 'email_invalid';
		
		if (!filter_var($registration->getShopUrl(), FILTER_VALIDATE_URL))
			return 'shop_url_invalid';
		
		if (Tools::strlen($registration->getPassword()) < 6)
			return 'password_less_than_6_characters';
		
		if ($registration->getPassword() !== $registration->getVerifyPasswordValue())
			return 'passwords_do_not_match';
		
		return true;
	}
}
