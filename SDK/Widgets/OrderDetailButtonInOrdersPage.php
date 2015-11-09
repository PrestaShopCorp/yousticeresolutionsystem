<?php
/**
 * Renders button to open order detail
 *
 * @author    Youstice
 * @copyright (c) 2015, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

class YousticeWidgetsOrderDetailButtonInOrdersPage {

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
		$smarty = Context::getContext()->smarty;
		$smarty->assign('href', YousticeHelpersHelperFunctions::sh($this->href));
		return $smarty->fetch(YRS_TEMPLATE_PATH.'orderDetailButton/unreportedButton.tpl');
	}

}

