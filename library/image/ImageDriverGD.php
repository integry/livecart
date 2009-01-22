<?php

require_once('ImageDriver.php');
class ImageDriverGD extends ImageDriver
{
  	public function resize(ImageManipulator $image, $newPath, $newWidth, $newHeight)
  	{
		$path = $image->getImagePath();
		$height = $image->getHeight();
		$width = $image->getWidth();
		$quality = $image->getQuality();
		$type = $image->getType();

		$this->setMemoryForImage($path);
		switch($type)
		{
			case IMAGETYPE_GIF:   $newimg = imagecreatefromgif($path); break;
			case IMAGETYPE_JPEG:  $newimg = imagecreatefromjpeg($path); break;
			case IMAGETYPE_PNG:   $newimg = imagecreatefrompng($path); break;
			default: throw new ApplicationException('Invalid image type: ' . $type);
		}

		if($newimg)
		{
			// resize large images in two steps - first resample, then resize
			// http://lt.php.net/manual/en/function.imagecopyresampled.php
			if ($width > 1500 || $height > 1200)
			{
				list($width, $height) = $this->resample($newimg, $image, $width, $height, 1024, 768, 0);
			}

			$this->resample($newimg, $image, $width, $height, $newWidth, $newHeight);

			if(!is_dir(dirname($newPath)))
			{
				mkdir(dirname($newPath), 0777, true);
			}

			$pathInfo = pathinfo($newPath);
			$ext = strtolower($pathInfo['extension']);
			if ($ext == 'jpg')
			{
			  	$ext = 'jpeg';
			}

			switch($type)
			{
				case IMAGETYPE_GIF: imagegif($newimg, $newPath); break;
				case IMAGETYPE_PNG: imagepng($newimg, $newPath);  break;
				case IMAGETYPE_JPEG:
				default:
					imagejpeg($newimg, $newPath, $quality);
				break;
			}

	 		imagedestroy($newimg);
			return true;
		}
		else
		{
		  	return false;
		}
	}

	public function getValidTypes()
	{
	  	return array(1, /* GIF */
	  				 2, /* JPEG */
	  				 3  /* PNG */
		  			 );
	}

	private function setMemoryForImage($filename)
	{
		$imageInfo = getimagesize($filename);
		$MB = 1048576;
		$K64 = 65536;
		$TWEAKFACTOR = 1.8;
		$memoryLimitMB = 32;
		if (!isset($imageInfo['channels']))
		{
			$imageInfo['channels'] = 4;
		}
		$memoryNeeded = round( ( $imageInfo[0] * $imageInfo[1]
											   * $imageInfo['bits']
											   * $imageInfo['channels'] / 8
								 + $K64
							   ) * $TWEAKFACTOR
							 );

		//ini_get('memory_limit') only works if compiled with "--enable-memory-limit" also
		//Default memory limit is 8MB so well stick with that.
		//To find out what yours is, view your php.ini file.
		$memoryLimit = $memoryLimitMB * $MB;
		if (function_exists('memory_get_usage') && (memory_get_usage() + $memoryNeeded > $memoryLimit))
		{
			$newLimit = $memoryLimitMB + ceil( ( memory_get_usage()
												+ $memoryNeeded
												- $memoryLimit
												) / $MB
											);
			ini_set('memory_limit', $newLimit . 'M');
			return true;
		}
		else
		{
			return false;
		}
	}

	private function resample(&$img, ImageManipulator $source, $owdt, $ohgt, $maxwdt, $maxhgt, $quality = 1)
	{
		// make sure the image doesn't get enlarged
		$maxwdt = min($maxwdt, $owdt);
		$maxhgt = min($maxhgt, $ohgt);

		if(!$maxwdt)
		{
			$divwdt = 1;
		}
		else
		{
			$divwdt = max(1, $owdt/$maxwdt);
		}

		if(!$maxhgt)
		{
			$divhgt = 1;
		}
		else
		{
			$divhgt = max(1, $ohgt/$maxhgt);
		}

		if($divwdt >= $divhgt)
		{
			$newwdt = round($owdt/$divwdt);
			$newhgt = round($ohgt/$divwdt);
		}
		else
		{
			$newhgt = round($ohgt/$divhgt);
			$newwdt = round($owdt/$divhgt);
		}

		$tn = imagecreatetruecolor($newwdt, $newhgt);

		if (in_array($source->getType(), array(IMAGETYPE_GIF, IMAGETYPE_PNG)))
		{
			$trnprt_indx = imagecolortransparent($img);

			// If we have a specific transparent color
			if ($trnprt_indx >= 0)
			{
				// Get the original image's transparent color's RGB values
				$trnprt_color = imagecolorsforindex($img, $trnprt_indx);

				// Allocate the same color in the new image resource
				$trnprt_indx = imagecolorallocate($tn, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);

				// Completely fill the background of the new image with allocated color.
				imagefill($tn, 0, 0, $trnprt_indx);

				// Set the background color for new image to transparent
				imagecolortransparent($tn, $trnprt_indx);
			}

			// Always make a transparent background color for PNGs that don't have one allocated already
			elseif ($source->getType() == IMAGETYPE_PNG)
			{
				// Turn off transparency blending (temporarily)
				imagealphablending($tn, false);

				// Create a new transparent color for image
				$color = imagecolorallocatealpha($tn, 0, 0, 0, 127);

				// Completely fill the background of the new image with allocated color.
				imagefill($tn, 0, 0, $color);

				// Restore transparency blending
				imagesavealpha($tn, true);
			}
		}

		if ($quality)
		{
			imagecopyresampled($tn,$img,0,0,0,0,$newwdt,$newhgt,$owdt,$ohgt);
		}
		else
		{
		   imagecopyresized($tn,$img,0,0,0,0,$newwdt,$newhgt,$owdt,$ohgt);
		}

		imagedestroy($img);

		$img = $tn;

		return array($newwdt, $newhgt);
	}

}

?>