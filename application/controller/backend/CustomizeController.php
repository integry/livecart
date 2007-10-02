<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");

/**
 * Handles dynamic interface customizations
 *
 * @package application.controller.backend
 * @author Integry Systems
 * 
 * @role customize
 */
class CustomizeController extends StoreManagementController
{
	public function index()
	{
		$response = new ActionResponse();		
		$response->set('isCustomizationModeEnabled', $this->application->isCustomizationMode());
		$response->set('isTranslationModeEnabled', $this->application->isTranslationMode());
		return $response;
	}	
	
	public function translationMode()
	{
	  	if ($this->application->isTranslationMode())
	  	{
			$this->session->unsetValue('translationMode');
		}
		else
		{
			$this->session->set('translationMode', true);
		}
		
		return new ActionRedirectResponse('backend.customize', 'index');
	}

	public function customizationMode()
	{
	  	if ($this->application->isCustomizationMode())
	  	{
			$this->session->unsetValue('customizationMode');
		}
		else
		{
			$this->session->set('customizationMode', true);
		}
		
		return new ActionRedirectResponse('backend.customize', 'index');
	}
}

?>