<?php
/**
 * Helper for image manipulation. Output is in base64
 *
 * @author    Youstice
 * @copyright (c) 2015, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

class YousticeImage {
	
	protected $image;
	
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
	
	public function getBase64String() {
		if ($this->image)
			return base64_encode($this->image);
		
		return null;
	}
	
	public function getBase64StringWithDataURI() {
		if ($this->image)
			return 'data:image/jpg;base64,' . $this->getBase64String();
		
		return null;
	}

	public function loadFromPath($path)
	{
		if ($path == null || !trim($path) || !is_readable($path))
			return $this;

		$image_data = Tools::file_get_contents($path);

		if ($image_data === false)
			return $this;

		//correct image
		if (Tools::strlen($image_data) > 0)
		{
			$this->image = $this->resize($image_data, 300, 300, false);
		}

		return $this;
	}

	public function loadFromRawBytes($image = '')
	{
		if (Tools::strlen($image) > 0)
		{
			$this->image = $this->resize($image, 300, 300);
		}

		return $this;
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
		
		if ($width_original <= 300 && $height_original <= 300)
			return $image_data;

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
