<?php
/**
 * Prestashop specific session provider.
 *
 * @author    Youstice
 * @copyright (c) 2014, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

/**
 * Prestashop specific session provider.
 *
 * @author KBS Development
 */
class YousticeProvidersSessionPrestashopProvider implements YousticeProvidersSessionProviderInterface {

	private $cookie;

	public function start()
	{
		$this->cookie = new Cookie('YRS');
	}

	public function get($var)
	{
		return $this->cookie->{$var};
	}

	public function set($var, $value)
	{
		$this->cookie->{$var} = $value;
	}

	public function destroy()
	{
		$this->cookie->setExpire(1);
	}

}
