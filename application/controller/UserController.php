<?php

ClassLoader::import('application.model.order.CustomerOrder');
ClassLoader::import('application.model.Currency');

/**
 *  Handles user account related logic
 */
class UserController extends FrontendController
{
    public function register()
    {
        $states = $this->locale->info()->getAllCountries();
        asort($states);        
        
        $response = new ActionResponse();   
        $response->setValue('form', $this->buildForm());
        $response->setValue('countries', $states);
        return $response;
    }        
    
    /**
     *  Return a list of states for the selected country
     *  @return JSONResponse
     */
    public function states()
    {
        $f = new ARSelectFilter();
        $f->setCondition(new EqualsCond(new ARFieldHandle('State', 'countryID'), $this->request->getValue('country')));
        $f->setOrder(new ARFieldHandle('State', 'name'));        
        $stateArray = ActiveRecordModel::getRecordSetArray('State', $f);

        $states = array();
        foreach ($stateArray as $state)
        {
            $states[$state['ID']] = $state['name'];
        }        
        
        return new JSONResponse($states);  
    }
    
    private function buildForm()
    {
		ClassLoader::import("framework.request.validator.Form");
		return new Form($this->buildValidator());        
    }
    
    private function buildValidator()
    {    
		ClassLoader::import("framework.request.validator.RequestValidator");
    	$validator = new RequestValidator("registrationValidator", $this->request);
    
        return $validator;    
    }
    
}

    
?>