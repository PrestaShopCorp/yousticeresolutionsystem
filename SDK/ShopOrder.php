<?php
/**
 * The shop order it self.
 *
 * @author    Youstice
 * @copyright (c) 2014, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

class YousticeShopOrder extends YousticeShopItem {

	public static function create($description = array(), $name = '', $currency = 'EUR', $price = 0.0, $product_id = null,
			$delivery_date = null, $order_date = null, $image = null, $other_info = '', $products = array())
	{
		return new self($description, $name, $currency, $price, $product_id, $delivery_date, $order_date,
				$image, $other_info, $products);
	}

	protected function parseOneArrayParameter($array)
	{
		return new self($array['description'], $array['name'], $array['currency'], $array['price'], $array['productId'],
				$array['deliveryDate'], $array['orderDate'], $array['image'], $array['otherInfo'], $array['products']);
	}

	/**
	 * Add product related to this order
	 * @param YousticeShopProduct $product of order
	 */
	public function addProduct(YousticeShopProduct $product)
	{
		$this->data['products'][] = $product;
	}

	public function getCode()
	{
		return $this->data['id'];
	}

	public function getImage()
	{
		if (trim($this->data['image']))
			return $this->data['image'];

		elseif (count($this->data['products']))
			return $this->data['products'][0]->getImage();
	}

}
