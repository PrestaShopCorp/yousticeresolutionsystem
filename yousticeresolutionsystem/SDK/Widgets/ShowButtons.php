<?php
/**
 * Youstice show buttons widget.
 *
 * @author    Youstice
 * @copyright (c) 2014, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

class YousticeWidgetsShowButtons {

	protected $href;
	protected $has_reports;
	protected $translator;

	public function __construct($lang, $has_reports)
	{
		$this->has_reports = $has_reports;
		$this->translator = new YousticeTranslator($lang);
	}

	public function toString()
	{
		$smarty = getSmarty();
		$smarty->assign(array('hasReports' => $this->has_reports));
		return $smarty->fetch(YRS_TEMPLATE_PATH.'showButtons.tpl');
	}

}
