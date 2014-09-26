<?php
/**
 * Renders button to report a product
 *
 * @author    Youstice
 * @copyright (c) 2014, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

class YousticeWidgetsProductReportButton {

	protected $href;
	protected $report;

	public function __construct($href, YousticeReportsProductReport $report)
	{
		$this->href = $href;
		$this->report = $report;
	}

	public function toString()
	{
		if ($this->report->exists())
		{
			if ($this->report->getRemainingTime() == 0)
				return $this->renderReportedButton();

			return $this->renderReportedButtonWithTimeString();
		}

		return $this->renderUnreportedButton();
	}

	protected function renderReportedButton()
	{
		$smarty = Context::getContext()->smarty;
		$smarty->assign('href', YousticeHelpersHelperFunctions::sh($this->href));
		$smarty->assign('statusClass', 'yrsButton-'.YousticeHelpersHelperFunctions::webalize($this->report->getStatus()));
		$smarty->assign('message', $this->report->getStatus());
		
		return $smarty->fetch(YRS_TEMPLATE_PATH.'productButton/reportedButton.tpl');
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
		
		return $smarty->fetch(YRS_TEMPLATE_PATH.'productButton/reportedButtonWithStatus.tpl');
	}

	protected function renderUnreportedButton()
	{
		$smarty = Context::getContext()->smarty;
		$smarty->assign('href', YousticeHelpersHelperFunctions::sh($this->href));
		return $smarty->fetch(YRS_TEMPLATE_PATH.'productButton/unreportedButton.tpl');
	}

}
