<?php
/**
 * Class handles local module translations
 *
 * @author    Youstice
 * @copyright (c) 2015, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

class YousticeTranslator {

	private $strings = array();

	public function __construct($lang = 'en')
	{
		$file = dirname(__FILE__)."/languageStrings/{$lang}.php";

		if (file_exists($file))
			$this->strings = include $file;
	}

	public function setLanguageStrings($strings)
	{
		$this->strings = $strings;
	}

	public function t($string)
	{
		$variables = func_get_args();
		array_shift($variables);

		if (array_key_exists($string, $this->strings))
			return vsprintf($this->strings[$string], $variables);

		return vsprintf($string, $variables);
	}
    
    public function getAdminTemplateLinks($lang = 'en')
    {        
        switch ($lang) {
            case 'de':
                return array (
                    'watch_video_link' => '//www.youtube.com/watch?v=I4dgL2e0xp8',
                    'pricing_link' => '//www.youstice.com/de/pricing',
                    'terms_link' => '//www.youstice.com/de/terms-of-use',
                    'privacy_policy_link' => '//www.youstice.com/de/datenschutzrichtlinien',
                );
                
            case 'es':
                return array (
                    'watch_video_link' => '//www.youtube.com/watch?v=IQMdq2ELA-Y',
                    'pricing_link' => '//www.youstice.com/es/pricing',
                    'terms_link' => '//www.youstice.com/es/terms-of-use',
                    'privacy_policy_link' => '//www.youstice.com/es/politica-de-privacid',
                );
                
            case 'ru':
                return array (
                    'watch_video_link' => '//www.youtube.com/watch?v=QfNrPR6zWfs',
                    'pricing_link' => '//www.youstice.com/ru/pricing',
                    'terms_link' => '//www.youstice.com/ru/terms-of-use',
                    'privacy_policy_link' => '//www.youstice.com/ru/privacy-policy',
                );
                
            case 'sk':
                return array (
                    'watch_video_link' => '//www.youtube.com/watch?v=efbCjCbwc3E',
                    'pricing_link' => '//www.youstice.com/sk/cennik',
                    'terms_link' => '//www.youstice.com/sk/podmienky-pouzivania-youstice',
                    'privacy_policy_link' => '//www.youstice.com/sk/ochrana-osobnych-udajov',
                );
                
            case 'cs':
                return array (
                    'watch_video_link' => '//www.youtube.com/watch?v=efbCjCbwc3E',
                    'pricing_link' => '//www.youstice.com/cz/ceny',
                    'terms_link' => '//www.youstice.com/cz/podminky-uzivani-youstice',
                    'privacy_policy_link' => '//www.youstice.com/cz/youstice-zasady-ochrany-osobnich-udaju',
                );
                
            case 'pt':
                return array (
                    'watch_video_link' => '//www.youtube.com/watch?v=bdAd-3MT9c4',
                    'pricing_link' => '//www.youstice.com/pt/precos',
                    'terms_link' => '//www.youstice.com/pt/politica-de-privacidade',
                    'privacy_policy_link' => '//www.youstice.com/pt/termos-de-uso',
                );
                
            case 'fr':
                return array (
                    'watch_video_link' => '//www.youtube.com/watch?v=TACuCmbqqDA',
                    'pricing_link' => '//www.youstice.com/fr/pricing',
                    'terms_link' => '//www.youstice.com/fr/entreprises-conditions-d-utilisation',
                    'privacy_policy_link' => '//www.youstice.com/fr/politique-de-confidentialite',
                );
        }
        
        // English & other languages
        return array (
            'watch_video_link' => '//www.youtube.com/watch?v=QfNrPR6zWfs',
            'pricing_link' => '//www.youstice.com/en/pricing',
            'terms_link' => '//www.youstice.com/en/terms-of-use',
            'privacy_policy_link' => '//www.youstice.com/en/privacy-policy',
        );
    }
}
