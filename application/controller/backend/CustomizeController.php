<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");

/**
 * Handles dynamic interface customizations
 *
 * @package application.controller.backend
 * @author Rinalds Uzkalns <rinalds@integry.net>
 * 
 * @role customize
 */
class CustomizeController extends StoreManagementController
{
	public function index()
	{
		$response = new ActionResponse();		
		$response->setValue('isCustomizationModeEnabled', Store::isCustomizationMode());
		$response->setValue('isTranslationModeEnabled', Store::isTranslationMode());
		return $response;
	}	
	
	public function translationMode()
	{
	  	if (Store::isTranslationMode())
	  	{
			unset($_SESSION['translationMode']);
		}
		else
		{
			$_SESSION['translationMode'] = true;  
		}
		
		return new ActionRedirectResponse('backend.customize', 'index');
	}

	public function customizationMode()
	{
	  	if (Store::isCustomizationMode())
	  	{
			unset($_SESSION['customizationMode']);
		}
		else
		{
			$_SESSION['customizationMode'] = true;  
		}
		
		return new ActionRedirectResponse('backend.customize', 'index');
	}
}

?>