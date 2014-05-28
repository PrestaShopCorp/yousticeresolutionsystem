<?php
/**
 * Class represents one shop product
 *
 * @author    Youstice
 * @copyright (c) 2014, Youstice
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

namespace Youstice;

class ShopProduct extends ShopItem {

	public static function create($description = array(), $name = "", $currency = "EUR", $price = 0.0,
			$productId = null, $deliveryDate = null, $orderDate = null, $image = null, $otherInfo = "") {

		return new self($description, $name, $currency, $price, $productId, $deliveryDate, $orderDate, $image, $otherInfo);
	}

	public function __construct($description, $name = "", $currency = "EUR", $price = 0.0,
			$productId = null, $deliveryDate = null, $orderDate = null, $image = null, $otherInfo = "") {

		parent::__construct($description, $name, $currency, $price, $productId, $deliveryDate, $orderDate, $image, $otherInfo);
	}

	protected function parseOneArrayParameter($array) {
		return new self($array['description'], $array['name'], $array['currency'],
					$array['price'], $array['productId'], $array['deliveryDate'],
					$array['orderDate'], $array['image'], $array['otherInfo']);
	}

	public function setOrderId($id) {
		$this->data['orderId'] = $id;
	}

	public function getCode() {
		return $this->data['orderId']."__".$this->data['id'];
	}

}
