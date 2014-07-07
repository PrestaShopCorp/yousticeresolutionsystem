<?php
/**
 * Various helpers for the Youstice API
 *
 * @author    Youstice
 * @copyright (c) 2014, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

namespace Youstice\Helpers;

class HelperFunctions {

	public static function webalize($string) {

		$string = preg_replace('~[^\\pL0-9_]+~u', '-', $string);
		$string = trim($string, "-");
		$string = iconv("utf-8", "us-ascii//TRANSLIT", $string);
		$string = strtolower($string);
		$string = preg_replace('~[^-a-z0-9_]+~', '', $string);

		return $string;
	}

	public static function sh($string) {
		return htmlspecialchars($string, ENT_QUOTES);
	}

	public static function remainingTimeToString($time = 0, \Youstice\Translator $translator) {
		$days = floor($time / (60*60*24));

		$hours = floor(($time - ($days*60*60*24)) / (60*60));

		return $translator->t("%d days %d hours", $days, $hours);

	}

}
