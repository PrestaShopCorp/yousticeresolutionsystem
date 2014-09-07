<?php
/**
 * Renders button to report an order
 *
 * @author    Youstice
 * @copyright (c) 2014, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

class YousticeWidgetsOrderReportButton {

	protected $href;
	protected $translator;
	protected $report;

	public function __construct($href, $lang, YousticeReportsOrderReport $report)
	{
		$this->href = $href;
		$this->translator = new YousticeTranslator($lang);
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
		$smarty = getSmarty();
		$smarty->assign(array('href' => YousticeHelpersHelperFunctions::sh($this->href)));
		$smarty->assign(array('statusClass' => 'yrsButton-'.YousticeHelpersHelperFunctions::webalize($this->report->getStatus())));
		$smarty->assign(array('message' => $this->report->getStatus()));
		
		return $smarty->fetch(YRS_TEMPLATE_PATH.'reportedOrderButton.tpl');
	}

	protected function renderReportedButtonWithTimeString()
	{
		$status = $this->report->getStatus();

		$smarty = getSmarty();
		$smarty->assign(array('href' => YousticeHelpersHelperFunctions::sh($this->href)));
		$smarty->assign(array('statusClass' => 'yrsButton-'.YousticeHelpersHelperFunctions::webalize($this->report->getStatus())));
		$smarty->assign(array('message' => $status));
		$smarty->assign(array('remainingTime' => YousticeHelpersHelperFunctions::remainingTimeToString($this->report->getRemainingTime(), $this->translator)));
		
		return $smarty->fetch(YRS_TEMPLATE_PATH.'reportedOrderButtonWithStatus.tpl');
	}

	protected function renderUnreportedButton()
	{
		$smarty = getSmarty();
		$smarty->assign(array('href' => YousticeHelpersHelperFunctions::sh($this->href)));
		return $smarty->fetch(YRS_TEMPLATE_PATH.'unreportedOrderButton.tpl');
	}

}
