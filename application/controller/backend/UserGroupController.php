<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.controller.backend.*");
ClassLoader::import("application.model.user.*");
ClassLoader::import("framework.request.validator.Form");
ClassLoader::import("framework.request.validator.RequestValidator");

/**
 * @package application.controller.backend
 */
class UserGroupController extends StoreManagementController
{
    public function index()
    {
	    $group = UserGroup::getInstanceByID((int)$this->request->getValue('id'), true);
	    $form = $this->createUserForm($group);

		$response = new ActionResponse();
		$response->setValue('userGroup', $group->toArray());
	    $response->setValue('userGroupForm', $form);
	    
	    return $response;
    }
	
	/**
	 * @return Form
	 */
	public function createUserForm(UserGroup $group)
	{
	    $form = new Form($this->createUserFormValidator($group)); 
        $form->setData($group->toArray());
	    
	    return $form;
	}
	
	/**
	 * @return RequestValidator
	 */
	public function createUserFormValidator(UserGroup $group)
	{
		$validator = new RequestValidator("userGroupForm_" . $group->isExistingRecord() ? $group->getID() : '', $this->request);
		$validator->addCheck("name", new IsNotEmptyCheck($this->translate("_error_name_should_not_be_empty")));
		
		return $validator;
	}

    public function save()
    {
        $name = $this->request->getValue('name');
        $description = $this->request->getValue('description');
        
        if($id = (int)$this->request->getValue('id'))
        {
            $group = UserGroup::getInstanceByID($id);
        }
        else
        {
            $group = UserGroup::getNewInstance($name, $description);
        }
        
        $validator = $this->createUserFormValidator($group);
        if($validator->isValid())
        {            
            $group->name->set($name);
            $group->description->set($description);
            
	        $group->save();
	        
	        return new JSONResponse(array('status' => 'success', 'group' => $group->toArray()));
        }
        else
        {
            return new JSONResponse(array('status' => 'error', 'errors' => $validator->getErrorList()));
        }
    }

	/**
	 * Creates a new user group
	 *
	 * @return ActionRedirectResponse
	 */
	public function create()
	{
	    $userGroup = UserGroup::getNewInstance($this->translate('_new_user_group'));
	    $userGroup->save();
	    
		return new JSONResponse($userGroup->toArray());
	}
	public function remove()
	{
		$userGroup = UserGroup::getInstanceByID((int)$this->request->getValue("id"), true);
		$userGroupArray = $userGroup->toArray();
		$userGroup->delete();
		
		return new JSONResponse(array('status' => 'success', 'userGroup' => $userGroupArray));
	}
}
?>