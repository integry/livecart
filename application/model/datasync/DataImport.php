<?php

abstract class DataImport
{
	protected $application;
	protected $fields;
	private $lastImportName;
	private $flushMessage;

	public function __construct(LiveCart $application)
	{
		$this->application = $application;
	}

	abstract public function getFields();
	abstract public function importInstance($record, CsvImportProfile $profile);

	public function isRootCategory()
	{
		return false;
	}

	public function getLastImportedRecordName()
	{
		return $this->lastImportName;
	}

	public function setLastImportedRecordName($name)
	{
		$this->lastImportName = $name;
	}

	public function beforeImport()
	{

	}

	public function afterImport()
	{

	}

	public function afterSave(ActiveRecordModel $instance, $record)
	{

	}

	public function importFile(CsvFile $file, CsvImportProfile $profile)
	{
		$total = 0;

		do
		{
			$cnt = $this->importFileChunk($file, $profile, 5);
			$total += $cnt;
		}
		while (!is_null($cnt));

		return $total;
	}

	public function importFileChunk(CsvFile $file, CsvImportProfile $profile, $count)
	{
		$processed = null;

		do
		{
			$file->next();
			$record = $file->current();

			if (!is_array($record))
			{
				continue;
			}

			foreach ($record as &$cell)
			{
				$cell = trim($cell);
				if (!$this->isValidUTF8($cell) && function_exists('utf8_encode'))
				{
					$cell = utf8_encode($cell);
				}
			}

			$status = $this->importInstance($record, $profile);

			if ($this->flushMessage)
			{
				echo $this->flushMessage;
				flush();
			}

			if (++$processed >= $count)
			{
				break;
			}
		}
		while ($file->valid());

		return $processed;
	}

	public function setImportPosition(CsvFile $file, $position)
	{
		$processed = 0;

		foreach ($csv as $record)
		{
			if (!is_array($record))
			{
				continue;
			}

			if (++$processed < $position)
			{
				continue;
			}
		}
	}

	public function skipHeader(CsvFile $file)
	{
		$this->setImportPosition($file, 1);
	}

	public function isCompleted(CsvFile $file)
	{
		return $file->valid();
	}

	public function getClassName()
	{
		preg_match('/(.*)Import/', get_class($this), $match);
		return array_pop($match);
	}

	public function setFlushMessage($msg)
	{
		$this->flushMessage = $msg;
	}

	protected function translate($key)
	{
		return $this->application->translate($key);
	}

	protected function makeText($key, $params)
	{
		return $this->application->makeText($key, $params);
	}

	protected function loadLanguageFile($file)
	{
		$this->application->loadLanguageFile($file);
	}

	protected function isValidUTF8($str)
	{
		// values of -1 represent disalloweded values for the first bytes in current UTF-8
		static $trailing_bytes = array (
			0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0, 0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,
			0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0, 0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,
			0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0, 0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,
			0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0, 0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,
			-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1, -1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,
			-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1, -1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,
			-1,-1,1,1,1,1,1,1,1,1,1,1,1,1,1,1, 1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,
			2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2, 3,3,3,3,3,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1
		);

		$ups = unpack('C*', $str);
		if (!($aCnt = count($ups))) return true; // Empty string *is* valid UTF-8
		for ($i = 1; $i <= $aCnt;)
		{
			if (!($tbytes = $trailing_bytes[($b1 = $ups[$i++])])) continue;
			if ($tbytes == -1) return false;

			$first = true;
			while ($tbytes > 0 && $i <= $aCnt)
			{
				$cbyte = $ups[$i++];
				if (($cbyte & 0xC0) != 0x80) return false;

				if ($first)
				{
					switch ($b1)
					{
						case 0xE0:
							if ($cbyte < 0xA0) return false;
							break;
						case 0xED:
							if ($cbyte > 0x9F) return false;
							break;
						case 0xF0:
							if ($cbyte < 0x90) return false;
							break;
						case 0xF4:
							if ($cbyte > 0x8F) return false;
							break;
						default:
							break;
					}
					$first = false;
				}
				$tbytes--;
			}
			if ($tbytes) return false; // incomplete sequence at EOS
		}
		return true;
	}
}

?>