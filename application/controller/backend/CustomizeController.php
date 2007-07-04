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
		$response->setValue('isCustomizationModeEnabled', $this->store->isCustomizationMode());
		$response->setValue('isTranslationModeEnabled', $this->store->isTranslationMode());
		return $response;
	}	
	
	public function translationMode()
	{
	  	if ($this->store->isTranslationMode())
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
	  	if ($this->store->isCustomizationMode())
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