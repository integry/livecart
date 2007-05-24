<?php

ClassLoader::import("application.controller.backend.abstract.BackendController");

/**
 * Main backend controller which stands as an entry point to administration functionality
 *
 * @package application.controller.backend
 * @author Saulius Rupainis <saulius@remo.lt>
 */
class IndexController extends BackendController
{
	public function index()
	{
		//echo "<pre>"; print_r($this->user); echo "</pre>";
		echo $this->user->email->get();
		//return new ActionResponse();
	}
}

?>
