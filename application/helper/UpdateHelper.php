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
					return @ftp_put($conn, $target, $source, FTP_BINARY);
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
					$root = ClassLoader::getRealPath('.');
					$ftpRoot = '/' . $settings->get('UPDATE_FTP_DIRECTORY') . '/' . substr($target, strlen($root) - 1);

					return $this->ftp_uploaddirectory($conn, $source, $ftpRoot);
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

		if (!function_exists('ftp_connect') ||
			!($conn = @ftp_connect($settings->get('UPDATE_FTP_SERVER'))) ||
			!ftp_login($conn, $settings->get('UPDATE_FTP_USER'), $settings->get('UPDATE_FTP_PASSWORD')))
		{
			return false;
		}

		return $conn;
	}

	private function recurse_copy( $source, $target )
	{
		if ( is_dir( $source ) )
		{
			@mkdir( $target );

			$d = dir( $source );

			while ( FALSE !== ( $entry = $d->read() ) )
			{
				if ( $entry == '.' || $entry == '..' )
				{
					continue;
				}

				$Entry = $source . '/' . $entry;
				if ( is_dir( $Entry ) )
				{
					$res = $this->recurse_copy( $Entry, $target . '/' . $entry );
					if ($res !== true)
					{
						return $res;
					}
					continue;
				}

				if (!@copy( $Entry, $target . '/' . $entry ))
				{
					return $Entry;
				}
			}

			$d->close();
		}
		else
		{
			if (!@copy( $source, $target ))
			{
				return $source;
			}
		}

		return true;
	}

	private function ftp_uploaddirectory($conn_id, $local_dir, $remote_dir)
	{
		@ftp_mkdir($conn_id, $remote_dir);

		$handle = opendir($local_dir);
		$local_dir .= '/';
		$remote_dir .= '/';

		$f = array();
		while (($file = readdir($handle)) !== false)
		{
			if (($file != '.') && ($file != '..'))
			{
				if (is_dir($local_dir.$file))
				{
					$res = $this->ftp_uploaddirectory($conn_id, $local_dir.$file.'/', $remote_dir.$file.'/');
					if ($res !== true)
					{
						return $res;
					}
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
				$from = @fopen("$local_dir/$files", 'r');
				if (!@ftp_fput($conn_id, $files, $from, FTP_BINARY))
				{
					return $files;
				}
			}
		}

		return true;
	}
}

?>