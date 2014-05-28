<?php
/**
 * Renders the "unrelated to orders" report button (web report / generic claim)
 *
 * @author    Youstice
 * @copyright (c) 2014, Youstice
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

namespace Youstice\Widgets;

use Youstice\Helpers\HelperFunctions;

class WebReportButton {

	protected $href;
	protected $translator;
	protected $report;

	public function __construct($href, $lang, \Youstice\Reports\WebReport $report) {
		$this->href = $href;
		$this->translator = new \Youstice\Translator($lang);
		$this->report = $report;
	}

	public function toString() {
		if (!$this->report->exists())
			return $this->renderUnreportedButton();

		if($this->report->getRemainingTime() == 0)
			return $this->renderReportedButton();
		else
			return $this->renderReportedButtonWithTimeString();
	}

	protected function renderReportedButton() {
		$status = $this->report->getStatus();
		$statusCssClass = "";
		if($status == "Problem reported")
			$statusCssClass = "yrsButton-problem-reported";

		$message = $this->translator->t($status);

		$output = '<a class="yrsButton ' . $statusCssClass . '" target="_blank" '
				. 'href="' . $this->href . '">'  . HelperFunctions::sh($message) . '</a>';

		return $output;
	}

	protected function renderReportedButtonWithTimeString() {

		$message = $this->translator->t($this->report->getStatus());
		$remainingTimeString = \Youstice\Helpers\HelperFunctions::remainingTimeToString($this->report->getRemainingTime(), $this->translator);

		$output = '<a class="yrsButton yrsButton-with-time" target="_blank" '
				. 'href="' . $this->href . '">'
				. '<span>' . HelperFunctions::sh($message) . '</span>'
				. '<span>' . HelperFunctions::sh($remainingTimeString) . '</span></a>';

		return $output;
	}

	protected function renderUnreportedButton() {

		$message = $this->translator->t('Report a problem unrelated to your orders');

		$output = '<a class="yrsButton" target="_blank" '
				. 'href="' . $this->href . '">' . HelperFunctions::sh($message) . '</a>';

		return $output;
	}
}
