<?php

ClassLoader::import('application.controller.backend.abstract.StoreManagementController');
ClassLoader::import('application.helper.UpdateHelper');

/**
 * @package application.controller.backend
 * @author Integry Systems
 * @role update
 */
class UpdateController extends StoreManagementController
{
	public function index()
	{
	// get the newest version
		$f = fsockopen('update.livecart.com', '80', $err);
		if ($err)
		{
			return new ActionResponse('err', true);
		}

		$out = "GET /version HTTP/1.1\r\n";
		$out .= "Host: update.livecart.com\r\n";
		$out .= "Connection: Close\r\n\r\n";

		fwrite($f, $out);
		$res = '';
		while (!feof($f))
		{
			$res .= fgets($f, 128);
		}

		$res = str_replace("\r", '', $res);
		list($headers, $version) = explode("\n\n", $res);
		$current = $this->getCurrentVersion();

		// get current version
		$response = new ActionResponse('current', $current);
		$response->set('newest', $version);
		$response->set('needUpdate', version_compare($current, $version, '<'));
		return $response;
	}

	/**
	 *  Handles LiveCart update process
	 */
	public function update()
	{
		$dir = ClassLoader::getRealPath('update') . '/' . $this->getCurrentVersion();
		if (!is_dir($dir))
		{
			return new RawResponse('Update directory not found');
		}

		$progress = array();
		$errors = array();

		// load SQL dump file
		$sql = $dir . '/update.sql';
		if (file_exists($sql))
		{
			try
			{
				Installer::loadDatabaseDump(file_get_contents($sql), true, $this->request->get('force'));
				$progress['sql'] = true;
			}
			catch (Exception $e)
			{
				$errors['sql'] = $e->getMessage();
			}
		}

		// clear cache
		$this->delTree(ClassLoader::getRealPath('cache'));
		$this->delTree(ClassLoader::getRealPath('public.cache'));
		$this->delTree(ClassLoader::getRealPath('public.upload.css.patched'));

		foreach (array('cache', 'storage') as $secured)
		{
			$dir = ClassLoader::getRealPath($secured);
			file_put_contents($dir . '/.htaccess', 'Deny from all');
		}

		// execute custom update code
		$code = $dir . '/custom.php';
		if (file_exists($code))
		{
			ob_start();
			if (!include $code)
			{
				$errors['code'] = ob_get_contents();
			}
			else
			{
				$progress['code'] = true;
			}

			ob_end_clean();
		}

		$response = new ActionResponse();
		$response->set('progress', $progress);
		$response->set('errors', $errors);
		return $response;
	}

	public function testCopy()
	{
		$handler = new UpdateHelper($this->application);
		$tmpName = 'test-update-copy' . rand(1, 5000000);
		$tmp = ClassLoader::getRealPath('cache.') . $tmpName;
		file_put_contents($tmp, 'test');

		$res = $handler->copyFile($tmp, 'module/' . $tmpName);
		$expected = ClassLoader::getRealPath('module.') . $tmpName;

		if (file_exists($expected))
		{
			$response = new JSONResponse(array(), 'success', $this->translate('_test_copy_success'));
		}
		else
		{
			$response = new JSONResponse(array(), 'failure', $this->translate('_test_copy_failure'));
		}

		$handler->deleteFile('module/' . $tmp);
		unlink($tmp);

		return $response;
	}

	private function delTree($path)
	{
		if (is_dir($path))
		{
			$entries = scandir($path);
			foreach ($entries as $entry)
			{
				if ($entry != '.' && $entry != '..')
				{
					$this->delTree($path . DIRECTORY_SEPARATOR . $entry);
				}
			}

			if (substr($path, -6) != '/cache')
			{
				rmdir($path);
			}
		}
		else if (file_exists($path))
		{
			unlink($path);
		}
	}

	private function getCurrentVersion()
	{
		return trim(file_get_contents(ClassLoader::getRealPath('.') . '/.version'));
	}
}

?>