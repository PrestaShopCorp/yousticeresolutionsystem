<?php
/**
 * Renders order detail (usually in popup form)
 *
 * @author    Youstice
 * @copyright (c) 2015, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

class YousticeWidgetsOrdersPage {

	protected $api;
	protected $webReportHref;
	protected $shopName;
	protected $orders;

	public function __construct($webReportHref, $shopName, array $orders, $api)
	{
		$this->webReportHref = $webReportHref;
		$this->shopName = $shopName;
		$this->orders = $orders;
		$this->api = $api;
	}

	public function toString()
	{
		$smarty = Context::getContext()->smarty;
		$smarty->assign('shopName', $this->shopName);
		$smarty->assign('webReportButton', $this->api->getWebReportButtonHtml($this->webReportHref));
		$smarty->assign('ordersCount', count($this->orders));
		$smarty->assign('orders', $this->orders);
		$smarty->assign('orderDateFormat', Context::getContext()->language->date_format_full);
		$smarty->assign('api', $this->api);
		
		
		return $smarty->fetch(YRS_TEMPLATE_PATH.'ordersPage.tpl');
	}

}
