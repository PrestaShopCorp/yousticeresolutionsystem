<?php
/**
 * Youstice show buttons widget.
 *
 * @author    Youstice
 * @copyright (c) 2015, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

class YousticeWidgetsShowButtons {

	protected $href;
	protected $has_reports;
	protected $translator;

	public function __construct($has_reports)
	{
		$this->has_reports = $has_reports;
	}

	public function toString()
	{
		$smarty = Context::getContext()->smarty;
		$smarty->assign('hasReports', $this->has_reports);
		return $smarty->fetch(YRS_TEMPLATE_PATH.'showButtons.tpl');
	}

}
