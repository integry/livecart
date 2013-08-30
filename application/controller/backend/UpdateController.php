<?php


/**
 * @package application/controller/backend
 * @author Integry Systems
 * @role update
 */
class UpdateController extends StoreManagementController
{
	public function indexAction()
	{
	// get the newest version
		$f = fsockopen('update.livecart.com', '80', $err);
		if ($err)
		{
			$this->set('err', true);
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
		$this->set('current', $current);
		$this->set('newest', $version);
		$this->set('needUpdate', version_compare($current, $version, '<'));
	}

	/**
	 *  Handles LiveCart update process
	 */
	public function updateAction()
	{
		$dir = $this->config->getPath('update') . '/' . $this->getCurrentVersion();
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

		$this->application->getConfigContainer()->clearCache();

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


		$this->set('progress', $progress);
		$this->set('errors', $errors);
	}

	public function testCopyAction()
	{
		$handler = new UpdateHelper($this->application);
		$tmpName = 'test-update-copy' . rand(1, 5000000);
		$tmp = $this->config->getPath('cache/') . $tmpName;
		file_put_contents($tmp, 'test');

		$res = $handler->copyFile($tmp, 'module/' . $tmpName);
		$expected = $this->config->getPath('module/') . $tmpName;

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

	}

	private function getCurrentVersion()
	{
		return trim(file_get_contents($this->config->getPath('.') . '/.version'));
	}
}

?>