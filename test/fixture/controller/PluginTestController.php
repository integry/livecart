<?php

ClassLoader::import('application.controller.BaseController');

class PluginTestController extends BaseController
{
	public function index()
	{
		return new ActionResponse('success', false);
	}
}

?>