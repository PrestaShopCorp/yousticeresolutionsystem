<?php
/**
 * Container for registration data
 *
 * @author    Youstice
 * @copyright (c) 2015, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

class YousticeShopRegistration {

	public $company_name;
	public $email;
	public $password;
	public $first_name;
	public $last_name;
	public $verify_password_value;
	public $shop_url;
	public $company_is_in_france;
	public $is_french_form;
	public $admin_lang;
	public $default_country;
	public $shop_name;
	public $shop_currency;
	public $shop_lang;
	public $shop_logo;
    
    public function getSubscription() {
        return $this->getIsFrenchForm() && $this->getCompanyIsInFrance() 
                ? 'Règlement des Litiges en Ligne (la période d\'essai)'
                : 'Free trial';
    }
    
    public function getCouponCode() {
        return $this->getIsFrenchForm() && $this->getCompanyIsInFrance() 
                ? 'reglementdeslitiges'
                : null;
    }
	
	public function getCompanyName()
	{
		return $this->company_name;
	}

	public function getEmail()
	{
		return $this->email;
	}

	public function getPassword()
	{
		return $this->password;
	}

	public function getFirstName()
	{
		return $this->first_name;
	}

	public function getLastName()
	{
		return $this->last_name;
	}

	public function getVerifyPasswordValue()
	{
		return $this->verify_password_value;
	}

	public function getShopUrl()
	{
		return $this->shop_url;
	}

	public function getCompanyIsInFrance()
	{
		return $this->company_is_in_france;
	}

	public function getIsFrenchForm()
	{
		return $this->is_french_form;
	}

	public function getAdminLang()
	{
		return $this->admin_lang;
	}

	public function getDefaultCountry()
	{
		return $this->default_country;
	}

	public function getShopName()
	{
		return $this->shop_name;
	}

	public function getShopCurrency()
	{
		return $this->shop_currency;
	}

	public function getShopLang()
	{
		return $this->shop_lang;
	}

	public function getShopLogo()
	{
		return $this->shop_logo;
	}

	public function setCompanyName($company_name)
	{
		$this->company_name = $company_name;
		
		return $this;
	}

	public function setEmail($email)
	{
		$this->email = $email;
		
		return $this;
	}

	public function setPassword($password)
	{
		$this->password = $password;
		
		return $this;
	}

	public function setFirstName($first_name)
	{
		$this->first_name = $first_name;
		
		return $this;
	}

	public function setLastName($last_name)
	{
		$this->last_name = $last_name;
		
		return $this;
	}

	public function setVerifyPasswordValue($verify_password_value)
	{
		$this->verify_password_value = $verify_password_value;
		
		return $this;
	}

	public function setShopUrl($shop_url)
	{
		$this->shop_url = $shop_url;
		
		return $this;
	}

	public function setCompanyIsInFrance($company_is_in_france)
	{
		$this->company_is_in_france = $company_is_in_france;
		
		return $this;
	}

	public function setIsFrenchForm($is_french_form)
	{
		$this->is_french_form = $is_french_form;
		
		return $this;
	}

	public function setAdminLang($admin_lang)
	{
		$this->admin_lang = $admin_lang;
		
		return $this;
	}

	public function setDefaultCountry($default_country)
	{
		$this->default_country = $default_country;
		
		return $this;
	}

	public function setShopName($shop_name)
	{
		$this->shop_name = $shop_name;
		
		return $this;
	}

	public function setShopCurrency($shop_currency)
	{
		$this->shop_currency = $shop_currency;
		
		return $this;
	}

	public function setShopLang($shop_lang)
	{
		$this->shop_lang = $shop_lang;
		
		return $this;
	}

	public function setShopLogo($shop_logo)
	{
		$this->shop_logo = $shop_logo;
		
		return $this;
	}
	
	public function setShopLogoPath($image_path = '')
	{
		$image = new YousticeImage();
		$image->loadFromPath($image_path);
		
		$this->shop_logo = $image->getBase64StringWithDataURI();

		return $this;
	}

	public function setShopLogoRawBytes($image_data = '')
	{
		$image = new YousticeImage();
		$image->loadFromRawBytes($image_data);
		
		$this->shop_logo = $image->getBase64StringWithDataURI();

		return $this;
	}
}
