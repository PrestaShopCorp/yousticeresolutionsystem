<?php
/**
 * Various helpers for the Youstice API
 *
 * @author    Youstice
 * @copyright (c) 2014, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

class YousticeHelpersHelperFunctions {

	public static function webalize($string)
	{
		$string = preg_replace('~[^\\pL0-9_]+~u', '-', $string);
		$string = trim($string, '-');
		$string = iconv('utf-8', 'us-ascii//TRANSLIT', $string);
		$string = Tools::strtolower($string);
		$string = preg_replace('~[^-a-z0-9_]+~', '', $string);

		return $string;
	}

	public static function sh($string)
	{
		return htmlspecialchars($string, ENT_QUOTES);
	}

	public static function remainingTimeToDays($time = 0)
	{
		return floor($time / (60 * 60 * 24));
	}

	public static function remainingTimeToHours($time = 0)
	{
		$days = floor($time / (60 * 60 * 24));

		return floor(($time - ($days * 60 * 60 * 24)) / (60 * 60));
	}

	public static function isSessionStarted()
	{
		if (php_sapi_name() !== 'cli')
		{
			if (version_compare(phpversion(), '5.4.0', '>='))
				return session_status() === PHP_SESSION_ACTIVE;
			else
				return session_id() === '' ? false : true;
		}

		return false;
	}

}
