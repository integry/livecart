<?php

class CsvFile
{
	protected $path;

	protected $delimiter;

	protected $fileHandle;

	public function __construct($filePath, $delimiter)
	{
		$this->path = $filePath;
		$this->delimiter = $delimiter;
	}

	public function getRecord()
	{
		if (!$this->fileHandle)
		{
			$this->fileHandle = fopen($this->path, 'r');
		}

		return fgetcsv($this->fileHandle, 0, $this->delimiter);
	}

	public function close()
	{
		if ($this->fileHandle)
		{
			fclose($this->fileHandle);
		}
	}
}

?>