<?php

ClassLoader::import('application.model.datasync.api.ApiAuthorization');

/**
 * Dummy authorization method (for testing purposes, does not require any authentication credentials)
 *
 * @package application.model.datasync.api.auth
 * @author Integry Systems <http://integry.com>
 */
class ApiAuthTest extends ApiAuthorization
{
	public function isValid()
	{
		return true;
	}
}

?>
