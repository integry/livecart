<?php
/**
 * CSV file read wrapper
 *
 * @package application.controller.backend
 * @author Integry Systems
 *
 */
class CsvFile implements Iterator
{
	protected $path;

	protected $delimiter;

	protected $fileHandle;

	protected $iteratorKey = 0;

	protected $iteratorValue;

	public function __construct($filePath, $delimiter)
	{
		$this->path = $filePath;

		if (!$delimiter)
		{
			$delimiter = "\t";
		}

		$this->delimiter = $delimiter;
	}

	public function getRecord()
	{
		do
		{
			$record = fgetcsv($this->getFileHandle(), 0, $this->delimiter);
		}
		while (((count($record) == 0) || ((count($record) == 1) && empty($record[0]))) && !feof($this->getFileHandle()));

		return $record;
	}

	public function getRecordCount()
	{
		$f = fopen($this->path, 'r');
		$count = 0;
		while (!feof($f))
		{
			$s = trim(fgets($f));
			if (!empty($s))
			{
				$count++;
			}
		}

		return $count;
	}

	public function close()
	{
		if ($this->fileHandle)
		{
			fclose($this->fileHandle);
		}
	}

	public function rewind()
	{
		rewind($this->getFileHandle());
		$this->iteratorKey = 0;
		$this->iteratorValue = $this->getRecord();
	}

	public function valid()
	{
		return !feof($this->getFileHandle());
	}

	public function next()
	{
		$this->iteratorKey++;
		$this->iteratorValue = $this->getRecord();
	}

	public function key()
	{
		return $this->iteratorKey;
	}

	public function current()
	{
		return $this->iteratorValue;
	}

	private function getFileHandle()
	{
		if (!$this->fileHandle)
		{
			$this->fileHandle = fopen($this->path, 'r');
		}

		return $this->fileHandle;
	}
}

?>