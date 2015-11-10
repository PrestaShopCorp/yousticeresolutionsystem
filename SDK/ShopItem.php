<?php
/**
 * Class representing one shop item (order or product)
 *
 * @author    Youstice
 * @copyright (c) 2015, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

abstract class YousticeShopItem {

	protected $data = array(
		'description' => '',
		'name' => '',
		'currency' => '',
		'price' => 0.0,
		'id' => -1,
		'deliveryDate' => '',
		'orderDate' => '',
		'image' => '',
		'other' => '',
		'products' => array(),
		'href' => ''
	);

	public function __construct($description, $name = '', $currency = 'EUR', $price = 0.0, $id = null, $delivery_date = null,
			$order_date = null, $image = null, $other_info = '', $products = array())
	{
		//one array parameter
		if (is_array($description) && count($description))
		{
			$this->setDescription($description['description']);
			$this->setName($description['name']);
			$this->setCurrency($description['currency']);
			$this->setPrice($description['price']);
			$this->setId($description['id']);
			$this->setDeliveryDate($description['deliveryDate']);
			$this->setOrderDate($description['orderDate']);
			if (isset($description['image']) && is_readable($description['image']))
				$this->setImagePath($description['image']);
			else
				$this->setImageRawBytes($description['image']);
			$this->setOtherInfo($description['otherInfo']);
			$this->setProducts($description['products']);
		}

		$this->setDescription($description);
		$this->setName($name);
		$this->setCurrency($currency);
		$this->setPrice($price);
		$this->setId($id);
		$this->setDeliveryDate($delivery_date);
		$this->setOrderDate($order_date);
		if (isset($image) && is_readable($image))
			$this->setImagePath($image);
		else
			$this->setImageRawBytes($image);
		$this->setOtherInfo($other_info);
		$this->setProducts($products);

		return $this;
	}

	public function getDescription()
	{
		return $this->data['description'];
	}

	public function getName()
	{
		return $this->data['name'];
	}

	public function getCurrency()
	{
		return $this->data['currency'];
	}

	public function getPrice()
	{
		return $this->data['price'];
	}

	public function getId()
	{
		return $this->data['id'];
	}

	public function getDeliveryDate()
	{
		return $this->data['deliveryDate'];
	}

	public function getOrderDate()
	{
		return $this->data['orderDate'];
	}

	public function getImage()
	{
		return $this->data['image'];
	}

	public function getOtherInfo()
	{
		return $this->data['other'];
	}

	public function getProducts()
	{
		return $this->data['products'];
	}

	public function getOrderId()
	{
		return $this->data['orderId'];
	}

	public function getHref()
	{
		return $this->data['href'];
	}

	public function setDescription($description = '')
	{
		$this->data['description'] = $description;

		return $this;
	}

	public function setName($name = '')
	{
		$this->data['name'] = $name;

		return $this;
	}

	public function setCurrency($currency = '')
	{
		$this->data['currency'] = $currency;

		return $this;
	}

	public function setPrice($price = 0.0)
	{
		if ($price < 0)
			throw new InvalidArgumentException('Price cannot be negative number.');

		$this->data['price'] = $price;

		return $this;
	}

	public function setId($id = null)
	{
		$this->data['id'] = $id;

		return $this;
	}

	public function setDeliveryDate($delivery_date)
	{
		if (Tools::strlen($delivery_date > 1)) {
			$timestamp = strtotime($delivery_date);
			$this->data['deliveryDate'] = date('c', $timestamp);
			return $this;
		}

		return $this;
	}

	public function setOrderDate($order_date)
	{
		if (Tools::strlen($order_date > 1)) {
			$timestamp = strtotime($order_date);
			$this->data['orderDate'] = date('c', $timestamp);
			return $this;
		}

		return $this;
	}

	public function setImage($image = '')
	{
		if (is_readable($image))
			$this->setImagePath($image);
		else
			$this->setImageRawBytes($image);

		return $this;
	}

	public function setImagePath($image_path = '')
	{
		$image = new YousticeImage();
		$image->loadFromPath($image_path);
		$this->data['image'] = $image->getBase64String();

		return $this;
	}

	public function setImageRawBytes($image_data = '')
	{
		$image = new YousticeImage();
		$image->loadFromRawBytes($image_data);
		$this->data['image'] = $image->getBase64String();

		return $this;
	}

	public function setOtherInfo($other_info = '')
	{
		$this->data['other'] = $other_info;

		return $this;
	}

	public function setProducts($products = array())
	{
		$this->data['products'] = $products;

		return $this;
	}

	public function setHref($href = '')
	{
		$this->data['href'] = $href;

		return $this;
	}

	public function toArray()
	{
		return $this->data;
	}

}
