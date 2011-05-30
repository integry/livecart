<?php

class UpdateHelper
{
	private $application;

	public function __construct(LiveCart $application)
	{
		$this->application = $application;
	}

	public function copyFile($source, $target)
	{
		$settings = $this->application->getConfig();
		switch ($settings->get('UPDATE_COPY_METHOD'))
		{
			case 'UPDATE_COPY':
				return @copy($source, ClassLoader::getRealPath('.') . $target);
			break;

			case 'UPDATE_FTP':
				if ($conn = $this->getFTPConnection())
				{
					ftp_put($conn, $target, $source, FTP_BINARY);
				}
				else
				{
					return false;
				}
			break;
		}
	}

	public function copyDirectory($source, $target)
	{
		$settings = $this->application->getConfig();
		switch ($settings->get('UPDATE_COPY_METHOD'))
		{
			case 'UPDATE_COPY':
				return $this->recurse_copy($source, $target);
			break;

			case 'UPDATE_FTP':
				if ($conn = $this->getFTPConnection())
				{
					return $this->ftp_uploaddirectory($conn, $source, $target);
				}
				else
				{
					return false;
				}
			break;
		}
	}

	public function deleteFile($file)
	{
		$settings = $this->application->getConfig();
		switch ($settings->get('UPDATE_COPY_METHOD'))
		{
			case 'UPDATE_COPY':
				return @unlink($file);
			break;

			case 'UPDATE_FTP':

			break;
		}

	}

	private function getFTPConnection()
	{
		$settings = $this->application->getConfig();

		$conn = ftp_connect($settings->get('UPDATE_FTP_SERVER'));

		if (!ftp_login($conn, $settings->get('UPDATE_FTP_USER'), $settings->get('UPDATE_FTP_PASSWORD')))
		{
			return false;
		}

		ftp_chdir($conn, $settings->get('UPDATE_FTP_DIRECTORY'));

		return $conn;
	}

	private function recurse_copy($src, $dst)
	{
		$failed = array();

		$dir = opendir($src);
		@mkdir($dst);
		while(false !== ( $file = readdir($dir)) )
		{
			if (( $file != '.' ) && ( $file != '..' ))
			{
				if (is_dir($src . '/' . $file))
				{
					$res = $this->recurse_copy($src . '/' . $file,$dst . '/' . $file);
					if ($res !== true)
					{
						return $res;
					}
				}
				else
				{
					copy($src . '/' . $file,$dst . '/' . $file);
				}
			}
		}

		closedir($dir);
	}

	private function ftp_uploaddirectory($conn_id, $local_dir, $remote_dir)
	{
		@ftp_mkdir($conn_id, $remote_dir);
		$handle = opendir($local_dir);
		while (($file = readdir($handle)) !== false)
		{
			if (($file != '.') && ($file != '..'))
			{
				if (is_dir($local_dir.$file))
				{
					$this->ftp_uploaddirectory($conn_id, $local_dir.$file.'/', $remote_dir.$file.'/');
				}
				else
				{
					$f[] = $file;
				}
			}
		}
		closedir($handle);

		if (count($f))
		{
			sort($f);
			@ftp_chdir($conn_id, $remote_dir);
			foreach ($f as $files)
			{
				$from = @fopen("$local_dir$files", 'r');
				@ftp_fput($conn_id, $files, $from, FTP_BINARY);
			}
		}
	}
}

?>