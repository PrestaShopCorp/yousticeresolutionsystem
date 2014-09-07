<?php
/**
 * Class represents one shop product
 *
 * @author    Youstice
 * @copyright (c) 2014, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

class YousticeShopProduct extends YousticeShopItem {

	public static function create($description = array(), $name = '', $currency = 'EUR', $price = 0.0, $product_id = null,
			$delivery_date = null, $order_date = null, $image = null, $other_info = '')
	{
		return new self($description, $name, $currency, $price, $product_id, $delivery_date, $order_date, $image, $other_info);
	}

	protected function parseOneArrayParameter($array)
	{
		return new self($array['description'], $array['name'], $array['currency'], $array['price'], $array['productId'],
				$array['deliveryDate'], $array['orderDate'], $array['image'], $array['otherInfo']);
	}

	/**
	 * Set related order id to this product
	 * @param string $id
	 */
	public function setOrderId($id)
	{
		$this->data['orderId'] = $id;
	}

	public function getCode()
	{
		return $this->data['orderId'].'__'.$this->data['id'];
	}

}
