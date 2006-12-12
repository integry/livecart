<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");

/**
 * Handles dynamic interface customizations
 *
 * @package application.controller.backend
 * @author Rinalds Uzkalns <rinalds@integry.net>
 * @role admin.site.language
 */
class CustomizeController extends StoreManagementController
{
	public function index()
	{
		$response = new ActionResponse();		
		$response->setValue('isTranslationModeEnabled', isset($_SESSION['translationMode']));
		return $response;
	}	
	
	public function translationMode()
	{
	  	if (isset($_SESSION['translationMode']))
	  	{
			unset($_SESSION['translationMode']);
		}
		else
		{
			$_SESSION['translationMode'] = true;  
		}
		
		return new ActionRedirectResponse('backend.customize', 'index');
	}
}

?>