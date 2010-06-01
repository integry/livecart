<?php

ClassLoader::import('application.model.datasync.api.ApiAuthorization');

class ApiAuthKey extends ApiAuthorization
{
	public function isValid()
	{
		$params = $this->params['key'];
		$serverKey = $this->application->getConfig()->get('API_SECRET_KEY_SERVER');
		$expectedHash = md5($params['client'] . '|' . $serverKey);
//var_dump($expectedHash);
		return $expectedHash == strtolower($params['hash']);
	}
}

?>
