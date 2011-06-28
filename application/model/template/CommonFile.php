<?php

/**
 * CommonTemplate
 * @author Integry Systems
 */
 
abstract class CommonFile
{

	protected $version;

	const BACKUP_FILE_COUNT = 10;

	protected abstract function getBackupPath();

	protected abstract function getFileName();

	protected abstract function getCode();

	protected function setVersion($version)
	{
		$this->version = is_numeric($version) && $version > 0 ? (int)$version : null;
	}

	protected function getBackups($prettyBackupNames=true)
	{
		$application = ActiveRecordModel::getApplication();
		$locale = $application->getLocale();
		$result = array();
		$path = $this->getBackupPath().$this->getFileName().DIRECTORY_SEPARATOR;
		foreach(glob($path.'*') as $file)
		{
			if(preg_match('/(\d{4}\-\d{2}\-\d{2}\-\d{2}\-\d{2}\-\d{2})$/', $file, $z)) // is this a backup file?
			{
				if ($prettyBackupNames)
				{
					list($y, $m, $d, $h, $min, $s) = explode('-',$z[1]);
					$ts = strtotime($y.'-'.$m.'-'.$d.' '.$h.':'.$min.':'.$s);
					$formatted = $locale->getFormattedTime($ts);
					$result[$ts] = $formatted['date_medium'].' '.$formatted['time_short'];
				}
				else
				{
					$result[$z[1]] = $file;
				}
			}
		}
		ksort($result);
		if ($prettyBackupNames)
		{
			$result[-1] = $application->translate('_previous_file_versions');
		}
		return array_reverse($result, true);
	}

	protected function readBackup()
	{
		$path = $this->getBackupPath().$this->getFileName().DIRECTORY_SEPARATOR.date('Y-m-d-H-i-s', $this->version);
		return file_exists($path) ? file_get_contents($path) : false;
	}

	protected function backup()
	{
		$path = $this->getBackupPath().$this->getFileName().DIRECTORY_SEPARATOR.date('Y-m-d-H-i-s');
		$dir = dirname($path);
		if(is_dir($dir) == false)
		{
			mkdir($dir, 0777, true);
			chmod($dir, 0777);
		}
		$res = file_put_contents($path, $this->getCode() );
		$backups = $this->getBackups(false);
		$c = count($backups);
		if ($c > self::BACKUP_FILE_COUNT)
		{
			$backupBase = $this->getBackupPath().$this->getFileName().DIRECTORY_SEPARATOR;
			$keys = array_keys($backups);
			for ($i= self::BACKUP_FILE_COUNT; $i<$c; $i++)
			{
				if (strpos($backups[$keys[$i]], $backupBase) === 0 && is_readable($backups[$keys[$i]]))
				{
					unlink($backups[$keys[$i]]);
				}
			}
		}
	}

}

?>