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
		
		$smarty = getSmarty();
		$smarty->assign(array('orderName' => $this->order->getName()));
		$smarty->assign(array('orderButton' =>  $this->api->getOrderReportButtonHtml($this->order->getHref(), $this->order->getCode())));
		$smarty->assign(array('productsMessage' =>  'Products in your order (%d)'));
		$smarty->assign(array('productsMessageCount' =>  count($products)));
		$smarty->assign(array('products' =>  $products));
		$smarty->assign(array('api' =>  $this->api));
		
		return $smarty->fetch(YRS_TEMPLATE_PATH.'orderDetail.tpl');
	}

}
