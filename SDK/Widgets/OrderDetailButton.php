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
	protected $report;

	public function __construct($href, YousticeShopOrder $order, YousticeReportsOrderReport $report, $api)
	{
		$this->href = $href;
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
		$popup = '';

		if ($this->report->orderReportExists())
			$popup .= $this->api->getOrderReportButtonHtml($this->order->getHref(), $this->order->getId());

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

				$popup .= $this->api->getProductReportButtonHtml($op->getHref(), $op->getId(), $op->getOrderId());
			}
		}

		$smarty = Context::getContext()->smarty;

		$smarty->assign('href', YousticeHelpersHelperFunctions::sh($this->href));
		$smarty->assign('statusClass', 'yrsButton-'.YousticeHelpersHelperFunctions::webalize($this->report->getStatus()));
		$smarty->assign('messageCount', $count);
		$smarty->assign('popup', $popup);

		return $smarty->fetch(YRS_TEMPLATE_PATH.'orderDetailButton/reportedButtonWithCount.tpl');
	}

	protected function renderReportedButton($status)
	{
		$smarty = Context::getContext()->smarty;
		$smarty->assign('href', YousticeHelpersHelperFunctions::sh($this->href));
		$smarty->assign('statusClass', 'yrsButton-'.YousticeHelpersHelperFunctions::webalize($this->report->getStatus()));
		$smarty->assign('message', $status);
		
		return $smarty->fetch(YRS_TEMPLATE_PATH.'orderDetailButton/reportedButton.tpl');
	}

	protected function renderReportedButtonWithStatus($status)
	{
		if ($this->report->getRemainingTime() == 0)
			return $this->renderReportedButton($status);
		
		$smarty = Context::getContext()->smarty;
		$smarty->assign('href', YousticeHelpersHelperFunctions::sh($this->href));
		$smarty->assign('statusClass', 'yrsButton-'.YousticeHelpersHelperFunctions::webalize($this->report->getStatus()));
		$smarty->assign('message', $status);
		$remainingTime = $this->report->getRemainingTime();
		$smarty->assign('remainingTimeDays', YousticeHelpersHelperFunctions::remainingTimeToDays($remainingTime));
		$smarty->assign('remainingTimeHours', YousticeHelpersHelperFunctions::remainingTimeToHours($remainingTime));
		
		return $smarty->fetch(YRS_TEMPLATE_PATH.'orderDetailButton/reportedButtonWithStatus.tpl');
	}

	protected function renderUnreportedButton()
	{
		$smarty = Context::getContext()->smarty;
		$smarty->assign('href', YousticeHelpersHelperFunctions::sh($this->href));
		return $smarty->fetch(YRS_TEMPLATE_PATH.'orderDetailButton/unreportedButton.tpl');
	}

}
