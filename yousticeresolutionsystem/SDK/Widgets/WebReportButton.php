<?php
/**
 * Renders the "unrelated to orders" report button (web report / generic claim)
 *
 * @author    Youstice
 * @copyright (c) 2014, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

class YousticeWidgetsWebReportButton {

	protected $href;
	protected $translator;
	protected $report;

	public function __construct($href, $lang, YousticeReportsWebReport $report)
	{
		$this->href = $href;
		$this->translator = new YousticeTranslator($lang);
		$this->report = $report;
	}

	public function toString()
	{
		if (!$this->report->exists())
			return $this->renderUnreportedButton();

		if ($this->report->getRemainingTime() == 0)
			return $this->renderReportedButton();
		else
			return $this->renderReportedButtonWithTimeString();
	}

	protected function renderReportedButton()
	{
		$status = $this->report->getStatus();
		$status_css_class = '';
		if ($status == 'Problem reported')
			$status_css_class = 'yrsButton-problem-reported';
		
		$smarty = getSmarty();
		$smarty->assign(array('href' => YousticeHelpersHelperFunctions::sh($this->href)));
		$smarty->assign(array('statusClass' => $status_css_class));
		$smarty->assign(array('message' => $this->report->getStatus()));
		
		return $smarty->fetch(YRS_TEMPLATE_PATH.'reportedWebButton.tpl');
	}

	protected function renderReportedButtonWithTimeString()
	{
		$status = $this->report->getStatus();

		$smarty = getSmarty();
		$smarty->assign(array('href' => YousticeHelpersHelperFunctions::sh($this->href)));
		$smarty->assign(array('statusClass' => 'yrsButton-'.YousticeHelpersHelperFunctions::webalize($this->report->getStatus())));
		$smarty->assign(array('message' => $status));
		$smarty->assign(array('remainingTime' => YousticeHelpersHelperFunctions::remainingTimeToString($this->report->getRemainingTime(), $this->translator)));
		
		return $smarty->fetch(YRS_TEMPLATE_PATH.'reportedWebButtonWithStatus.tpl');
	}

	protected function renderUnreportedButton()
	{
		$smarty = getSmarty();
		$smarty->assign(array('href' => YousticeHelpersHelperFunctions::sh($this->href)));
		return $smarty->fetch(YRS_TEMPLATE_PATH.'unreportedWebButton.tpl');
	}

}
