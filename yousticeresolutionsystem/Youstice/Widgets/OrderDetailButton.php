<?php
/**
 * Renders button to open order detail
 *
 * @author    Youstice
 * @copyright (c) 2014, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

class YousticeWidgetsOrderDetailButton {

	protected $api;
	protected $href;
	protected $translator;
	protected $report;

	public function __construct($href, $lang, YousticeShopOrder $order, YousticeReportsOrderReport $report, $api)
	{
		$this->href = $href;
		$this->translator = new YousticeTranslator($lang);
		$this->order = $order;
		$this->report = $report;
		$this->api = $api;
	}

	public function toString()
	{
		$reported_products_count = $this->report->getReportedProductsCount();

		//nothing reported
		if (!$this->report->exists())
			return $this->renderUnreportedButton();

		//exists report for order?
		if ($this->report->orderReportExists())
		{
			if ($reported_products_count > 0)
				return $this->renderReportedButtonWithCount($reported_products_count + 1);

			//only report is reported
			return $this->renderReportedButtonWithStatus($this->report->getStatus());
		}

		//only product/s reported
		if ($reported_products_count > 1)
			return $this->renderReportedButtonWithCount($reported_products_count);

		//only 1 product reported
		return $this->renderReportedButtonWithStatus($this->report->getFirstProductStatus());
	}

	protected function renderReportedButtonWithCount($count)
	{
		$message = $this->translator->t('%d ongoing cases', $count);

		$output = '<div class="orderDetailButtonWrap">
					<a class="yrsButton yrsButton-order-detail" 
						href="'.YousticeHelpersHelperFunctions::sh($this->href).'">'.YousticeHelpersHelperFunctions::sh($message).'</a>';

		$output .= '<a class="yrsButton yrsButton-plus" href="'.YousticeHelpersHelperFunctions::sh($this->href).'">+</a>';

		// POPUP
		$output .= '<div class="popup"><span>&nbsp;</span>';

		if ($this->report->orderReportExists())
			$output .= $this->api->getOrderReportButtonHtml($this->order->getHref(), $this->order->getId());

		$order_products = $this->order->getProducts();
		$report_products = $this->report->getProducts();

		foreach ($order_products as $op)
		{
			foreach ($report_products as $rp)
			{
				$temp = explode('__', $rp['code']);
				$local_product_code = $temp[1];

				if ($op->getId() != $local_product_code)
					continue;

				$output .= $this->api->getProductReportButtonHtml($op->getHref(), $op->getId(), $op->getOrderId());
			}
		}

		$output .= '</div></div>';

		return $output;
	}

	protected function renderReportedButton($status)
	{
		$status_css_class = 'yrsButton-'.YousticeHelpersHelperFunctions::webalize($status);

		$message = $this->translator->t($status);

		$output = '<a class="yrsButton yrsOrderDetailButton '.$status_css_class.'" target="_blank" 
					href="'.YousticeHelpersHelperFunctions::sh($this->href).'">'.YousticeHelpersHelperFunctions::sh($message).'</a>';

		return $output;
	}

	protected function renderReportedButtonWithStatus($status)
	{
		if ($this->report->getRemainingTime() == 0)
			return $this->renderReportedButton($status);

		$message = $this->translator->t($status);
		$status_css_class = 'yrsButton-'.YousticeHelpersHelperFunctions::webalize($this->report->getStatus());
		$remaining_time_string = YousticeHelpersHelperFunctions::remainingTimeToString($this->report->getRemainingTime(), $this->translator);

		$output = '<a class="yrsButton yrsOrderDetailButton yrsButton-with-time '.$status_css_class.'" 
					href="'.YousticeHelpersHelperFunctions::sh($this->href).'">
					<span>'.YousticeHelpersHelperFunctions::sh($message).'</span>
					<span>'.YousticeHelpersHelperFunctions::sh($remaining_time_string).'</span></a>';

		return $output;
	}

	protected function renderUnreportedButton()
	{
		$message = $this->translator->t('Report a problem');

		$output = '<a class="yrsButton yrsOrderDetailButton" 
					href="'.YousticeHelpersHelperFunctions::sh($this->href).'">'.YousticeHelpersHelperFunctions::sh($message).'</a>';

		return $output;
	}

}
