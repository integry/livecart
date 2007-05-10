<?php

ClassLoader::import("library.*");
ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("framework.request.validator.Form");
ClassLoader::import("framework.request.validator.RequestValidator");
ClassLoader::import("application.model.tax.Tax");

/**
 *
 * @package application.controller.backend
 *
 */
class TaxController extends StoreManagementController
{
	/**
	 * List all system currencies
	 * @return ActionResponse
	 */
	public function index()
	{
		$response = new ActionResponse();
		
		$taxesForms = array();
		$taxes = array();
		foreach(Tax::getAllTaxes() as $tax) 
	    {
		    $taxes[] = $tax->toArray();
		    $taxesForms[] = $this->createTaxForm($tax);
		}
		
		$response->setValue('defaultLanguageCode', $this->store->getDefaultLanguageCode());
		$response->setValue('alternativeLanguagesCodes', $this->store->getLanguageSetArray(false, false));
		$response->setValue("taxesForms", $taxesForms);
		$response->setValue("taxes", $taxes);
		
		$newTax = Tax::getNewInstance('');
		$response->setValue("newTaxForm", $this->createTaxForm($newTax));
		$response->setValue("newTax", $newTax->toArray());
		
		return $response;
	}
	
	/**
	 * @return Form
	 */
	public function createTaxForm(Tax $tax)
	{
	    $form = new Form($this->createTaxFormValidator($tax));
	    
        $form->setData($tax->toArray());
	    
	    return $form;
	}
	
	/**
	 * @return RequestValidator
	 */
	public function createTaxFormValidator(Tax $tax)
	{
		$validator = new RequestValidator("taxForm_" . $tax->isExistingRecord() ? $tax->getID() : '', $this->request);
		$validator->addCheck("name", new IsNotEmptyCheck($this->translate("_error_the_name_should_not_be_empty")));
		
		return $validator;
	}

    public function save()
    {
        if($id = (int)$this->request->getValue('id'))
        {
            $tax = Tax::getInstanceByID($id);
        }
        else
        {
            $tax = Tax::getNewInstance($this->request->getValue('name'));
        }
        
        $validator = $this->createTaxFormValidator($tax);
        
        if($validator->isValid())
        {            
            $tax->isEnabled->set($this->request->getValue("isEnabled", 0));
            $tax->setValueArrayByLang(array('name'), $this->store->getDefaultLanguageCode(), $this->store->getLanguageArray(true, false), $this->request);      
		    
	        $tax->save();
	        
	        return new JSONResponse(array('status' => 'success', 'tax' => $tax->toArray()));
        }
        else
        {
            return new JSONResponse(array('status' => 'error', 'errors' => $validator->getErrorList()));
        }
    }
	
    public function edit()
    {
	    $tax = Tax::getInstanceByID((int)$this->request->getValue('id'), true);
		
	    $form = $this->createTaxForm($tax);
		$form->setData($tax->toArray());
		
		
		$response = new ActionResponse();
		$response->setValue('defaultLanguageCode', $this->store->getDefaultLanguageCode());
		$response->setValue('alternativeLanguagesCodes', $this->store->getLanguageSetArray(false, false));
		$response->setValue('tax', $tax->toArray());
	    $response->setValue('taxForm', $form);
	    
	    return $response;
    }
    
    public function delete()
    {
        $service = Tax::getInstanceByID((int)$this->request->getValue('id'));
        $service->delete();
        
        return new JSONResponse(array('status' => 'success'));
    }
}

?>