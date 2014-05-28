<?php
/**
 * Renders button to open order detail
 *
 * @author    Youstice
 * @copyright (c) 2014, Youstice
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

namespace Youstice\Widgets;

use Youstice\Helpers\HelperFunctions;

class OrderDetailButton {

	protected $api;
	protected $href;
	protected $productHref;
	protected $translator;
	protected $report;

	public function __construct($href, $lang, \Youstice\ShopOrder $order, \Youstice\Reports\OrderReport $report, $api) {
		$this->href = $href;
		$this->translator = new \Youstice\Translator($lang);
		$this->order = $order;
		$this->report = $report;
		$this->api = $api;
	}

	public function toString() {
		$reportedProductsCount = $this->report->getReportedProductsCount();

		//nothing reported
		if (!$this->report->exists())
			return $this->renderUnreportedButton();

		//exists report for order?
		if ($this->report->orderReportExists()) {
			if ($reportedProductsCount > 0)
				return $this->renderReportedButtonWithCount($reportedProductsCount+1);

			//only report is reported
			return $this->renderReportedButtonWithStatus($this->report->getStatus());
		}

		//only product/s reported
		if ($reportedProductsCount > 1)
			return $this->renderReportedButtonWithCount($reportedProductsCount);

		//only 1 product reported
		return $this->renderReportedButtonWithStatus($this->report->getFirstProductStatus());
	}

	protected function renderReportedButtonWithCount($count) {

		$message = $this->translator->t("%d ongoing cases", $count);

		$output = '<div class="orderDetailButtonWrap">'
					. '<a class="yrsButton yrsButton-order-detail" '
						. 'href="' . HelperFunctions::sh($this->href) .'">' . HelperFunctions::sh($message) . '</a>';

		$output .= '<a class="yrsButton yrsButton-plus" href="' . $this->href . '">+</a>';

		// POPUP
		$output .= '<div class="popup"><span>&nbsp;</span>';
		if($this->report->orderReportExists()) {
			$output .= $this->api->getOrderReportButtonHtml($this->order->getHref(), $this->report->getCode());
		}

		$orderProducts = $this->order->getProducts();
		$reportProducts = $this->report->getProducts();

		foreach($orderProducts as $op) {
			foreach($reportProducts as $rp) {
				$temp = explode("__", $rp['code']);
				$localProductCode = $temp[1];
				if($op->getId() != $localProductCode) {
					continue;
				}

				$output .= $this->api->getProductReportButtonHtml($op->getHref(), $op->getId(), $op->getOrderId());
			}
		}

		$output .= '</div></div>';

		return $output;
	}

	protected function renderReportedButton($status) {
		$statusCssClass = "yrsButton-" . \Youstice\Helpers\HelperFunctions::webalize($status);

		$message = $this->translator->t($status);

		$output = '<a class="yrsButton yrsOrderDetailButton ' . $statusCssClass . '" target="_blank" '
				. 'href="' . $this->href . '">'  . HelperFunctions::sh($message) . '</a>';

		return $output;
	}

	protected function renderReportedButtonWithStatus($status) {
		if($this->report->getRemainingTime() == 0)
			return $this->renderReportedButton($status);

		$message = $this->translator->t($status);
		$statusCssClass = "yrsButton-" . \Youstice\Helpers\HelperFunctions::webalize($this->report->getStatus());
		$remainingTimeString = \Youstice\Helpers\HelperFunctions::remainingTimeToString($this->report->getRemainingTime(), $this->translator);

		$output = '<a class="yrsButton yrsOrderDetailButton yrsButton-with-time ' . $statusCssClass . '" '
				. 'href="' . $this->href . '">'
				. '<span>' . HelperFunctions::sh($message) . '</span>'
				. '<span>' . HelperFunctions::sh($remainingTimeString) . '</span></a>';

		return $output;
	}

	protected function renderUnreportedButton() {

		$message = $this->translator->t('Report a problem');

		$output = '<a class="yrsButton yrsOrderDetailButton" '
				. 'href="' . $this->href . '">' . HelperFunctions::sh($message) . '</a>';

		return $output;
	}

}
