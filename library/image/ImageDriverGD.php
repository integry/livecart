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
	    
		switch($type) 
		{
			case 1:  $newimg = imagecreatefromgif($path); break;
			case 2:  $newimg = imagecreatefromjpeg($path); break;
			case 3:  $newimg = imagecreatefrompng($path); break;
			default: throw new ApplicationException('Invalid image type: ' . $type);
		}
		
		if($newimg) 
		{
			// resize large images in two steps - first resample, then resize
			// http://lt.php.net/manual/en/function.imagecopyresampled.php
			if($width > 1500 || $height > 1200)
			{
			    list($width, $height) = $this->resample($newimg, $width, $height, 1024, 768, 0);
			}
			      
			$this->resample($newimg, $width, $height, $newWidth, $newHeight);
			  
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
			
			switch($ext) 
			{
				case 'gif': imagegif($newimg, $newPath); break;   
				case 'png': imagepng($newimg, $newPath);  break;
				case 'jpeg': 
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
	
	private function resample(&$img, $owdt, $ohgt, $maxwdt, $maxhgt, $quality = 1) 
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
					
		$tn = imagecreatetruecolor($newwdt,$newhgt);
		
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