<?php

ClassLoader::import('application.model.datasync.api.ApiAuthorization');

class ApiAuthTest extends ApiAuthorization
{
	public function isValid()
	{
		return true;
	}
}

?>
