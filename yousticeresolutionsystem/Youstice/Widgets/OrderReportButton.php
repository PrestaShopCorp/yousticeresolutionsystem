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
		$status = $this->report->getStatus();
		$status_css_class = 'yrsButton-'.YousticeHelpersHelperFunctions::webalize($status);

		$message = $this->translator->t($status);

		$output = '<a class="yrsButton yrsOrderButton '.$status_css_class.'" target="_blank" 
					href="'.YousticeHelpersHelperFunctions::sh($this->href).'">'.YousticeHelpersHelperFunctions::sh($message).'</a>';

		return $output;
	}

	protected function renderReportedButtonWithTimeString()
	{
		$status = $this->report->getStatus();

		$message = $this->translator->t($status);
		$status_css_class = 'yrsButton-'.YousticeHelpersHelperFunctions::webalize($status);
		$remaining_time_string = YousticeHelpersHelperFunctions::remainingTimeToString($this->report->getRemainingTime(), $this->translator);

		$output = '<a class="yrsButton yrsOrderButton yrsButton-with-time '.$status_css_class.'" target="_blank" 
					href="'.YousticeHelpersHelperFunctions::sh($this->href).'">
					<span>'.YousticeHelpersHelperFunctions::sh($message).'</span>
					<span>'.YousticeHelpersHelperFunctions::sh($remaining_time_string).'</span></a>';

		return $output;
	}

	protected function renderUnreportedButton()
	{
		$message = $this->translator->t('Report a problem');

		$output = '<a class="yrsButton yrsOrderButton" target="_blank" 
					href="'.YousticeHelpersHelperFunctions::sh($this->href).'">'.YousticeHelpersHelperFunctions::sh($message).'</a>';

		return $output;
	}

}
