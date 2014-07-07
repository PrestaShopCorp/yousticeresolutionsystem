<?php
/**
 * Class handles local module translations
 *
 * @author    Youstice
 * @copyright (c) 2014, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

namespace Youstice;

class Translator {

	private $strings = array();

	public function __construct($lang = 'sk') {
		$file = __DIR__ . "/languageStrings/translations_local_{$lang}.php";
		if (file_exists($file)) {
			$this->strings = include $file;
		}
	}

	public function setLanguageStrings($strings) {
		$this->strings = $strings;
	}

	public function t($string) {
		$variables = func_get_args();
		array_shift($variables);
		if (array_key_exists($string, $this->strings)) {
			return vsprintf($this->strings[$string], $variables);
		}
		return vsprintf($string, $variables);
	}

}
