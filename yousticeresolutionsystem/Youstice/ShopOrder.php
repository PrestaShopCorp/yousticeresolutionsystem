<?php
/**
 * The shop order it self.
 *
 * @author    Youstice
 * @copyright (c) 2014, Youstice
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

namespace Youstice;

class ShopOrder extends ShopItem {

	public static function create($description = array(), $name = "", $currency = "EUR", $price = 0.0,
			$productId = null, $deliveryDate = null, $orderDate = null, $image = null,
			$otherInfo = "", $products = array()) {

		return new self($description, $name, $currency, $price, $productId, $deliveryDate, $orderDate, $image, $otherInfo, $products);
	}

	public function __construct($description, $name = "", $currency = "EUR", $price = 0.0,
			$productId = null, $deliveryDate = null, $orderDate = null, $image = null,
			$otherInfo = "", $products = array()) {

		parent::__construct($description, $name, $currency, $price, $productId, $deliveryDate, $orderDate, $image, $otherInfo, $products);
	}

	protected function parseOneArrayParameter($array) {
		return new self($array['description'], $array['name'], $array['currency'],
					$array['price'], $array['productId'], $array['deliveryDate'],
					$array['orderDate'], $array['image'], $array['otherInfo'], $array['products']);
	}

	public function addProduct(ShopProduct $product) {
		$this->data['products'][] = $product;
	}

	public function getCode() {
		return $this->data['id'];
	}

	public function getImage() {
		if( trim($this->data['image']) )
			return $this->data['image'];

		elseif( count($this->data['products']) )
			return $this->data['products'][0]->getImage();
	}
}
