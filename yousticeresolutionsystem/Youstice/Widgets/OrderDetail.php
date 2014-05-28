<?php
/**
 * Renders order detail (usually in popup form)
 *
 * @author    Youstice
 * @copyright (c) 2014, Youstice
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

namespace Youstice\Widgets;

use Youstice\Helpers\HelperFunctions;

class OrderDetail {

	protected $api;
	protected $href;
	protected $lang;
	protected $productHref;
	protected $report;
	protected $order;

	public function __construct($href, $lang, \Youstice\ShopOrder $order, \Youstice\Reports\OrderReport $report, $api) {
		$this->href = $href;
		$this->translator = new \Youstice\Translator($lang);
		$this->order = $order;
		$this->report = $report;
		$this->api = $api;
	}

	public function toString() {
		$products = $this->order->getProducts();
		$output =
				'<div class="orderDetailWrap">'
				. '<h1>' . HelperFunctions::sh($this->order->getName()) . '</h1>';
		$output .= '<div class="topRightWrap">';
			$output .= $this->api->getOrderReportButtonHtml($this->href, $this->order->getCode());
		$output .=		'<span class="space"></span>'
				.		'<a class="yrsButton yrsButton-close">x</a>'
				.	'</div>'
				.	'<h2>' . $this->translator->t('Products in your order (%d)', count($products)) . '</h2>';



		if(count($products)) {
			$output .=
					'<table class="orderDetail">';

			$products = $this->order->getProducts();

			foreach($products as $product) {
				$output .= '<tr><td>' . HelperFunctions::sh($product->getName()) . '</td>'
						. '<td>' . $this->api->getProductReportButtonHtml($product->getHref(), $product->getId(), $product->getOrderId()) . '</td></tr>';
			}

			$output .= '</table></div>';
		}

		return $output;
	}

}
