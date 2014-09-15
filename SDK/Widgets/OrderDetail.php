<?php
/**
 * Renders order detail (usually in popup form)
 *
 * @author    Youstice
 * @copyright (c) 2014, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

class YousticeWidgetsOrderDetail {

	protected $api;
	protected $lang;
	protected $report;
	protected $order;

	public function __construct($lang, YousticeShopOrder $order, YousticeReportsOrderReport $report, $api)
	{
		$this->translator = new YousticeTranslator($lang);
		$this->order = $order;
		$this->report = $report;
		$this->api = $api;
	}

	public function toString()
	{
		$products = $this->order->getProducts();
		
		$smarty = Context::getContext()->smarty;
		$smarty->assign('orderName', $this->order->getName());
		$smarty->assign('orderButton', $this->api->getOrderReportButtonHtml($this->order->getHref(), $this->order->getCode()));
		$smarty->assign('productsMessage', 'Products in your order (%d)');
		$smarty->assign('productsMessageCount', count($products));
		$smarty->assign('products', $products);
		$smarty->assign('api', $this->api);
		
		return $smarty->fetch(YRS_TEMPLATE_PATH.'orderDetail.tpl');
	}

}
