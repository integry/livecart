<?php

class UploadController extends ControllerBase
{
	public function indexAction()
	{
		$files = $_FILES['uploadedFile'];
		$res = array();
		foreach ($files['name'] as $index => $name)
		{
			$tmp_name = $files['tmp_name'][$index];
			
			if (!getimagesize($tmp_name))
			{
				echo(json_encode(array('error' => 'This is not a valid image')));
				return;
			}
			
			include_once __ROOT__ . '/library/image/ImageManipulator.php';
			$tmp = 'upload/tmp/' . md5($tmp_name . time()) . '.' . pathinfo($name, PATHINFO_EXTENSION);
			$man = new ImageManipulator($tmp_name);
			
			$size = $this->request->get('size');
			if (!$size || !strpos($size, 'x'))
			{
				$size = '316x195';
			}
			
			$sizes = explode('x', $size);
			
			$r = $man->resize(array_shift($sizes), array_shift($sizes), $tmp);
			if ($r)
			{
				$res[] = array('image' => $tmp);
			}
		}
		
		if (!$res)
		{
			echo(json_encode(array('error' => 'This is not a valid image')));
		}
		else
		{
			echo(json_encode($res));
		}
	}
}
