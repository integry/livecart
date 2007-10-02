<?php

ClassLoader::import("library.*");
ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("framework.request.validator.Form");
ClassLoader::import("framework.request.validator.RequestValidator");
ClassLoader::import("application.model.tax.Tax");

/**
 *
 * @package application.controller.backend
 * @author	Integry Systems
 * @role taxes
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
		
		$response->set("taxesForms", $taxesForms);
		$response->set("taxes", $taxes);
		
		$newTax = Tax::getNewInstance('');
		$response->set("newTaxForm", $this->createTaxForm($newTax));
		$response->set("newTax", $newTax->toArray());
		
		return $response;
	}

    public function edit()
    {
	    $tax = Tax::getInstanceByID((int)$this->request->get('id'), true);
		
	    $form = $this->createTaxForm($tax);
		$form->setData($tax->toArray());
		
		
		$response = new ActionResponse();
		$response->set('tax', $tax->toArray());
	    $response->set('taxForm', $form);
	    
	    return $response;
    }
    
	/**
	 * @role remove
	 */
    public function delete()
    {
        $service = Tax::getInstanceByID((int)$this->request->get('id'));
        $service->delete();
        
        return new JSONResponse(false, 'success');
    }

	/**
	 * @role update
	 */
    public function update()
    {
        $tax = Tax::getInstanceByID((int)$this->request->get('id'));
        
        return $this->saveTax($tax);
    }

	/**
	 * @role create
	 */
    public function create()
    {
        $tax = Tax::getNewInstance($this->request->get('name'));
        $tax->position->set(1000);
        
        return $this->saveTax($tax);
    }
    
    private function saveTax(Tax $tax)
    {
        $validator = $this->createTaxFormValidator($tax);
        
        if($validator->isValid())
        {            
            $tax->setValueArrayByLang(array('name'), $this->application->getDefaultLanguageCode(), $this->application->getLanguageArray(true, false), $this->request);      
		    
	        $tax->save();
	        
	        return new JSONResponse(array('tax' => $tax->toArray()), 'success');
        }
        else
        {
	        
	        return new JSONResponse(array('errors' => $validator->getErrorList()), 'failure', $this->translate('_could_not_save_tax_entry'));
        }
    }
    
	/**
	 * @return Form
	 */
	private function createTaxForm(Tax $tax)
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

    /**
     * @role update
     */
    public function sort()
    {
        foreach($this->request->get($this->request->get('target'), array()) as $position => $key)
        {
           $tax = Tax::getInstanceByID((int)$key);
           $tax->position->set((int)$position);
           $tax->save();
        }

        return new JSONResponse(false, 'success');
    }
}

?>