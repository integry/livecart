<?php

abstract class ImageDriver
{
  	abstract function resize(ImageManipulator $image, $newPath, $newWidth, $newHeight);
}

?>