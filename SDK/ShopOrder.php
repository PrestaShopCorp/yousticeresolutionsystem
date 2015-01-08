<?php
/**
 * The shop order it self.
 *
 * @author    Youstice
 * @copyright (c) 2014, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

class YousticeShopOrder extends YousticeShopItem {
	
	const NOT_DELIVERED = 1;
	const DELIVERED = 2;
	const NOT_PAID = 1;
	const PAID = 2;

	public static function create($description = array(), $name = '', $currency = 'EUR', $price = 0.0, $product_id = null, $delivery_date = null, $order_date = null, $image = null, $other_info = '', $products = array())
	{
		return new self($description, $name, $currency, $price, $product_id, $delivery_date, $order_date, $image, $other_info, $products);
	}

	protected function parseOneArrayParameter($array)
	{
		return new self($array['description'], $array['name'], $array['currency'], $array['price'], $array['productId'], $array['deliveryDate'], $array['orderDate'], $array['image'], $array['otherInfo'], $array['products']);
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

	public function getOrderDetailHref()
	{
		return isset($this->data['orderDetailHref']) ? $this->data['orderDetailHref'] : '';
	}

	public function setOrderDetailHref($href = '')
	{
		$this->data['orderDetailHref'] = $href;

		return $this;
	}

	public function getImage()
	{
		if (trim($this->data['image']))
			return $this->data['image'];

		elseif (count($this->data['products']))
			return $this->data['products'][0]->getImage();
	}
	
	public function setDeliveryState($deliveryState) {
		if($deliveryState == self::DELIVERED || $deliveryState == self::NOT_DELIVERED)
			$this->data['deliveryState'] = $deliveryState;
		
		return $this;
	}
	
	public function getDeliveryState() {
		return isset($this->data['deliveryState']) ? $this->data['deliveryState'] : self::NOT_DELIVERED;
	}
	
	public function isDelivered() {
		return isset($this->data['deliveryState']) && $this->data['deliveryState'] == self::DELIVERED;
	}
	
	public function setPaymentState($paymentState) {
		if($paymentState == self::PAID || $paymentState == self::NOT_PAID)
			$this->data['paymentState'] = $paymentState;
		
		return $this;
	}
	
	public function getPaymentState() {
		return isset($this->data['paymentState']) ? $this->data['paymentState'] : self::NOT_PAID;
	}
	
	public function isPaid() {
		return isset($this->data['paymentState']) && $this->data['paymentState'] == self::PAID;
	}


}
