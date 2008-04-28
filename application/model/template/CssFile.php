<?php

class CssFile
{
	private $theme;

	private $code;

	public function __construct($theme = null)
	{
		$theme = basename($theme, '.css');

		$this->theme = $theme;

		$file = $this->getRealPath();
		if (file_exists($file))
		{
			$this->code = file_get_contents($file);
		}
	}

	public function getRealPath()
	{
		return ClassLoader::getRealPath('public.upload.css.') . $this->getFileName();
	}

	public function getFileName()
	{
		return $this->theme ? $this->theme . '.css' : 'common.css';
	}

	public function getTheme()
	{
		return $this->theme;
	}

	public function setCode($code)
	{
		$this->code = $code;
	}

	public function getCode()
	{
		return $this->code;
	}

	public function save()
	{
		$file = $this->getRealPath();
		if (!is_dir(dirname($file)))
		{
			mkdir(dirname($file), 0777);
		}

		if ($this->code)
		{
			return @file_put_contents($file, $this->code) !== false;
		}
		else
		{
			if (file_exists($file))
			{
				unlink($file);
			}

			return true;
		}
	}

	public function toArray()
	{
		return array(
					'id' => $this->getFileName(),
					'code' => $this->code,
					'title' => $this->theme
					);
	}
}

?>