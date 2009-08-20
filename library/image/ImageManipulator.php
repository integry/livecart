<?php

include_once(dirname(__file__) . '/ImageException.php');

class ImageManipulator
{
	private $imagePath;

	private $driverInstance;

	private $quality = 90;

	private $width;

	private $height;

	private $type;

	private $validTypes = array();

	public function __construct($imagePath = false)
	{
	  	include_once(dirname(__file__) . '/ImageDriverGD.php');
		$this->driverInstance = new ImageDriverGD();
	  	$this->validTypes = $this->driverInstance->getValidTypes();

	  	if ($imagePath)
	  	{
			$this->setImage($imagePath);
		}
	}

	public function setImage($imagePath)
	{
		$imageInfo = getimagesize($imagePath);

		if ($imageInfo)
		{
			$this->width = $imageInfo[0];
			$this->height = $imageInfo[1];
			$this->type = $imageInfo[2];
			$this->imagePath = $imagePath;
			return true;
		}
		else
		{
		  	return false;
		}
	}

	public function setQuality($quality)
	{
		$this->quality = $quality;
	}

	public function resize($width, $height, $newPath)
	{
		if (!$this->imagePath)
		{
		  	throw new ApplicationException('No image path set');
		}

		$res = $this->driverInstance->resize($this, $newPath, $width, $height);
		if (!$res)
		{
		  	return false;
		}
		else
		{
		  	return new ImageManipulator($newPath);
		}
	}

	public function isValidImage()
	{
		if (!$this->type)
		{
		  	return false;
		}

		if (count($this->validTypes) == 0)
		{
			return true;
		}
		else
		{
		  	return in_array($this->type, $this->validTypes);
		}
	}

	public function getImagePath()
	{
	  	return $this->imagePath;
	}

	public function getHeight()
	{
	  	return $this->height;
	}

	public function getWidth()
	{
	  	return $this->width;
	}

	public function getQuality()
	{
	  	return $this->quality;
	}

	public function getType()
	{
	  	return $this->type;
	}

	public function getValidTypes()
	{
	  	return $this->validTypes;
	}

}

?>