<?php
/**
 * Class representing one shop item (order or product)
 *
 * @author    Youstice
 * @copyright (c) 2014, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

namespace Youstice;

abstract class ShopItem {
	protected $data = array(
		"description" => "",
		"name" => "",
		"currency" => "",
		"price" => 0.0,
		"id" => -1,
		"deliveryDate" => "",
		"orderDate" => "",
		"image" => "",
		"other" => "",
		"products" => array(),
		"href"	=> ""
	);

	public function __construct($description, $name = "", $currency = "EUR", $price = 0.0, $id = null, $deliveryDate = null, $orderDate = null, $image = null, $otherInfo = "", $products = array()) {

		//one array parameter
		if (is_array($description) && count($description)) {
			$this->setDescription($description['description']);
			$this->setName($description['name']);
			$this->setCurrency($description['currency']);
			$this->setPrice($description['price']);
			$this->setId($description['id']);
			$this->setDeliveryDate($description['deliveryDate']);
			$this->setOrderDate($description['orderDate']);
			if(isset($description['image']) && is_readable($description['image']))
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
		$this->setDeliveryDate($deliveryDate);
		$this->setOrderDate($orderDate);
		if(isset($image) && is_readable($image))
			$this->setImagePath($image);
		else
			$this->setImageRawBytes($image);
		$this->setOtherInfo($otherInfo);
		$this->setProducts($products);


		return $this;
	}

	public function getDescription() {
		return $this->data['description'];
	}

	public function getName() {
		return $this->data['name'];
	}

	public function getCurrency() {
		return $this->data['currency'];
	}

	public function getPrice() {
		return $this->data['price'];
	}

	public function getId() {
		return $this->data['id'];
	}

	public function getDeliveryDate() {
		return $this->data['deliveryDate'];
	}

	public function getOrderDate() {
		return $this->data['orderDate'];
	}

	public function getImage() {
		return $this->data['image'];
	}

	public function getOtherInfo() {
		return $this->data['other'];
	}

	public function getProducts() {
		return $this->data['products'];
	}

	public function getOrderId() {
		return $this->data['orderId'];
	}

	public function getHref() {
		return $this->data['href'];
	}

	public function setDescription($description = "") {
		$this->data['description'] = $description;

		return $this;
	}

	public function setName($name = "") {
		$this->data['name'] = $name;

		return $this;
	}

	public function setCurrency($currency = "") {
		$this->data['currency'] = $currency;

		return $this;
	}

	public function setPrice($price = 0.0) {
		if($price < 0)
			throw new \InvalidArgumentException("Price cannot be negative number.");

		$this->data['price'] = $price;

		return $this;
	}

	public function setId($id = null) {

		$this->data['id'] = $id;

		return $this;
	}

	public function setDeliveryDate($deliveryDate) {
		if(strlen($deliveryDate > 1))
			$this->data['deliveryDate'] = $deliveryDate;

		$zeroDate = new \DateTime();
		$zeroDate->setTimestamp(0);

		$this->data['deliveryDate'] = $zeroDate->format(\DateTime::ISO8601);

		return $this;
	}

	public function setOrderDate($orderDate) {
		$this->data['orderDate'] = $orderDate;

		return $this;
	}

	public function setImage($image = "") {
		if(is_readable($image))
			$this->setImagePath($image);
		else
			$this->setImageRawBytes($image);

		return $this;
	}

	public function setImagePath($image = "") {
		$this->data['image'] = $this->loadImage($image);

		return $this;
	}

	public function setImageRawBytes($image = "") {
		if(strlen($image) > 0) {
			$imageData = $this->resize($image, 300, 300);
			$this->data['image'] = base64_encode($imageData);
		}

		return $this;
	}

	public function setOtherInfo($otherInfo = "") {
		$this->data['other'] = $otherInfo;

		return $this;
	}

	public function setProducts($products = array()) {
		$this->data['products'] = $products;

		return $this;
	}

	public function setHref($href = "") {
		$this->data['href'] = $href;

		return $this;
	}

	public function toArray() {
		return $this->data;
	}

	protected function loadImage($path) {
		if ($path == null || !trim($path))
			return;

		if (is_readable($path)) {
			$imageData = file_get_contents($path);
			if ($imageData === false) {
				throw new \Exception("Image does not exists");
			}
			//correct image
			if(strlen($imageData) > 0) {
				$imageData = $this->resize($imageData, 300, 300);
				return base64_encode($imageData);
			}

			return null;
		} else {
			throw new \Exception("Image path is not readable");
		}
	}

	protected function resize($imageData, $width = 100, $height = 100, $stretch = false) {
		$file = tempnam(sys_get_temp_dir(), md5(time().'YRS'));
		if($file === false) {
			throw new \Exception("Creating temporary file failed. Temporary Directory: " . sys_get_temp_dir());
		}

		$fileHandle = fopen($file, 'w');
		fwrite($fileHandle, $imageData);
		fclose($fileHandle);


		$mime = $this->getMimeType($file);

		switch ($mime) {
			case "image/bmp":
				$handle = @imagecreatefromwbmp($file);
			break;
			case "image/jpeg":
				$handle = @imagecreatefromjpeg($file);
			break;
			case "image/gif":
				$handle = @imagecreatefromgif($file);
			break;
			case "image/png":
				$handle = @imagecreatefrompng($file);
			break;
			default:
				throw new \Exception("Unsupported image type " . $mime);
		}

		$dimensions = @getimagesize($file);
		if (!$dimensions) {
			throw new \Exception("Reading of temporary file failed");
		}


		$offset_x = 0;
		$offset_y = 0;
		$dst_w = $width;
		$dst_h = $height;

		$bnd_x = $width / $dimensions[0];
		$bnd_y = $height / $dimensions[1];

		if ($stretch) {
			if ($bnd_x > $bnd_y) {
				$ratio = $height / $width;
				$temp = floor($dimensions[1] / $ratio);
				if ($temp > $dimensions[0])
					$dimensions[1] -= ($temp - $dimensions[0]) * $ratio;
				else
					$dimensions[0] = $temp;
			} else {
				$ratio = $width / $height;
				$temp = floor($dimensions[0] / $ratio);
				if ($temp > $dimensions[1])
					$dimensions[0] -= ($temp - $dimensions[1]) * $ratio;
				else
					$dimensions[1] = $temp;
			}
		} else {
			if ($bnd_x > $bnd_y) {
				# height reaches boundary first, modify width
				$offset_x = ($width - $dimensions[0] * $bnd_y) / 2;
				$dst_w = $dimensions[0] * $bnd_y;
			} else {
				# width reaches boundary first (or equal), modify height
				$offset_y=  ($height - $dimensions[1] * $bnd_x) / 2;
				$dst_h = $dimensions[1] * $bnd_x;
			}
		}

		$preview = imagecreatetruecolor($width, $height);
		if (!$preview) {
			throw new \Exception("Creating thumbnail failed");
		}

		# draw white background -> opravene na transparent
		$c = imagecolorallocatealpha($preview, 255, 255, 255, 0);
		if ($c !== false) {
			imagefilledrectangle($preview, 0, 0, $width, $height, $c);
			imagecolortransparent($preview, $c);
			imagecolordeallocate($preview, $c);
		}

		if (!imagecopyresampled($preview, $handle, $offset_x, $offset_y, 0, 0, $dst_w, $dst_h, $dimensions[0], $dimensions[1])) {
			throw new \Exception("Creating thumbnail failed");
		}

		unlink($file);
		imagedestroy($handle);

		ob_start();
		imagejpeg($preview);
		imagedestroy($preview);
		return ob_get_clean();
	}

	protected function getMimeType($filename) {
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime = finfo_file($finfo, $filename);
		finfo_close($finfo);
		return $mime;
	}

}
