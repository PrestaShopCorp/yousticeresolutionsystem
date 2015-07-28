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
	
	protected $mime_types = array(
        'png' => 'image/png',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'ico' => 'image/vnd.microsoft.icon',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml'
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

	public function setImagePath($image = '')
	{
		$this->data['image'] = $this->loadImage($image);

		return $this;
	}

	public function setImageRawBytes($image = '')
	{
		if (Tools::strlen($image) > 0)
		{
			$image_data = $this->resize($image, 300, 300);
			$this->data['image'] = base64_encode($image_data);
		}

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

	protected function loadImage($path)
	{
		if ($path == null || !trim($path) || !is_readable($path))
			return null;

		$image_data = Tools::file_get_contents($path);

		if ($image_data === false)
			return null;

		//correct image
		if (Tools::strlen($image_data) > 0)
		{
			$image_data = $this->resize($image_data, 300, 300, false);
			return base64_encode($image_data);
		}

		return null;
	}

	protected function resize($image_data, $width = 100, $height = 100, $stretch = false)
	{
		$file = tempnam(sys_get_temp_dir(), md5(time().'YRS'));

		if ($file === false)
			return null;

		$file_handle = fopen($file, 'w');
		fwrite($file_handle, $image_data);
		fclose($file_handle);
		
		list($width_original, $height_original, $file_type) = getimagesize($file);

		if (!$width_original || !$height_original)
			return null;

		switch (image_type_to_mime_type($file_type))
		{
			case 'image/bmp':
				$handle = imagecreatefromwbmp($file);
				break;
			case 'image/jpeg':
				$handle = imagecreatefromjpeg($file);
				break;
			case 'image/gif':
				$handle = imagecreatefromgif($file);
				break;
			case 'image/png':
				$handle = imagecreatefrompng($file);
				break;
			default:
				return null;
		}

		$offset_x = 0;
		$offset_y = 0;
		$dst_w = $width;
		$dst_h = $height;

		$bnd_x = $width / $width_original;
		$bnd_y = $height / $height_original;

		if ($stretch)
		{
			if ($bnd_x > $bnd_y)
			{
				$ratio = $height / $width;
				$temp = floor($height_original / $ratio);

				if ($temp > $width_original)
					$height_original -= ($temp - $width_original) * $ratio;
				else
					$width_original = $temp;
			}
			else
			{
				$ratio = $width / $height;
				$temp = floor($width_original / $ratio);
				if ($temp > $height_original)
					$width_original -= ($temp - $height_original) * $ratio;
				else
					$height_original = $temp;
			}
		}
		else
		{
			if ($bnd_x > $bnd_y)
			{
				# height reaches boundary first, modify width
				$offset_x = ($width - $width_original * $bnd_y) / 2;
				$dst_w = $width_original * $bnd_y;
			}
			else
			{
				# width reaches boundary first (or equal), modify height
				$offset_y = ($height - $height_original * $bnd_x) / 2;
				$dst_h = $height_original * $bnd_x;
			}
		}

		$preview = imagecreatetruecolor($width, $height);

		if (!$preview)
			return null;

		# draw white background -> opravene na transparent
		$c = imagecolorallocatealpha($preview, 255, 255, 255, 0);
		if ($c !== false)
		{
			imagefilledrectangle($preview, 0, 0, $width, $height, $c);
			imagecolortransparent($preview, $c);
			imagecolordeallocate($preview, $c);
		}

		if (!imagecopyresampled($preview, $handle, $offset_x, $offset_y, 0, 0, $dst_w, $dst_h, $width_original, $height_original))
			return null;

		unlink($file);
		imagedestroy($handle);

		ob_start();
		imagejpeg($preview);
		imagedestroy($preview);
		
		return ob_get_clean();
	}

}
