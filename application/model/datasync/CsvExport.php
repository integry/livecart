<?php

ClassLoader::import('application.model.datasync.DataExport');

/**
 * Handle data export in CSV format
 *
 * @package application.model
 * @author Integry Systems <http://integry.com>
 */
class CsvExport extends DataExport
{
	public function __construct(ExportProfile $profile, $file, $append = false)
	{
		$this->profile = $profile;

		if (is_resource($file))
		{
			$this->file = $file;
		}
		else
		{
			$this->file = fopen($file, $append ? 'a' : 'w');
		}
	}

	protected function writeData($data)
	{
		fputcsv($this->file, $data);
	}

	public function close()
	{
		fclose($this->file);
	}
}

?>