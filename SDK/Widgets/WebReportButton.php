<?php
/**
 * Renders the "unrelated to orders" report button (web report / generic claim)
 *
 * @author    Youstice
 * @copyright (c) 2015, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

class YousticeWidgetsWebReportButton {

	protected $href;
	protected $report;

	public function __construct($href, YousticeReportsWebReport $report)
	{
		$this->href = $href;
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
		
		$smarty = Context::getContext()->smarty;
		$smarty->assign('href', YousticeHelpersHelperFunctions::sh($this->href));
		$smarty->assign('statusClass', $status_css_class);
		$smarty->assign('message', $this->report->getStatus());
		
		return $smarty->fetch(YRS_TEMPLATE_PATH.'webButton/reportedButton.tpl');
	}

	protected function renderReportedButtonWithTimeString()
	{
		$status = $this->report->getStatus();

		$smarty = Context::getContext()->smarty;
		$smarty->assign('href', YousticeHelpersHelperFunctions::sh($this->href));
		$smarty->assign('statusClass', 'yrsButton-'.YousticeHelpersHelperFunctions::webalize($this->report->getStatus()));
		$smarty->assign('message', $status);
		$remainingTime = $this->report->getRemainingTime();
		$smarty->assign('remainingTimeDays', YousticeHelpersHelperFunctions::remainingTimeToDays($remainingTime));
		$smarty->assign('remainingTimeHours', YousticeHelpersHelperFunctions::remainingTimeToHours($remainingTime));
		
		return $smarty->fetch(YRS_TEMPLATE_PATH.'webButton/reportedButtonWithStatus.tpl');
	}

	protected function renderUnreportedButton()
	{
		$smarty = Context::getContext()->smarty;
		$smarty->assign('href', YousticeHelpersHelperFunctions::sh($this->href));
		return $smarty->fetch(YRS_TEMPLATE_PATH.'webButton/unreportedButton.tpl');
	}

}
