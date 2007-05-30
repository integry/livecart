<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.controller.backend.*");
ClassLoader::import("application.model.order.*");
ClassLoader::import("application.model.currency");
ClassLoader::import("framework.request.validator.Form");
ClassLoader::import("framework.request.validator.RequestValidator");

/**
 * @package application.controller.backend
 * @role order
 */
class CustomerOrderController extends StoreManagementController
{
	/**
	 * Action shows filters and datagrid.
	 * @return ActionResponse
	 */
	public function index()
	{
		$orderGroups = array(
		    array('ID' => 1, 'name' => $this->translate('_all_orders'), 'rootID' => 0),
		        array('ID' => 2, 'name' => $this->translate('_current_orders'), 'rootID' => 1),
		            array('ID' => 3, 'name' => $this->translate('_new_orders'), 'rootID' => 2),
		            array('ID' => 4, 'name' => $this->translate('_backordered_orders'), 'rootID' => 2),
		            array('ID' => 5, 'name' => $this->translate('_awaiting_shipment_orders'), 'rootID' => 2),
		        array('ID' => 6, 'name' => $this->translate('_shipped_orders'), 'rootID' => 1),
		        array('ID' => 7, 'name' => $this->translate('_returned_orders'), 'rootID' => 1),
		    array('ID' => 8, 'name' => $this->translate('_shopping_carts'), 'rootID' => 0),
		);
		
		$response = new ActionResponse();
		$response->setValue('orderGroups', $orderGroups);
		return $response;
	    
	}
	
	public function orders()
	{        
		$availableColumns = $this->getAvailableColumns();
		$displayedColumns = $this->getDisplayedColumns();
		
		// sort available columns by display state (displayed columns first)
		$displayedAvailable = array_intersect_key($availableColumns, $displayedColumns);
		$notDisplayedAvailable = array_diff_key($availableColumns, $displayedColumns);		
		$availableColumns = array_merge($displayedAvailable, $notDisplayedAvailable);
			
		$response = new ActionResponse();
        $response->setValue("massForm", $this->getMassForm());
        $response->setValue("massForm", $this->getMassForm());
        $response->setValue("orderGroupID", $this->request->getValue('id'));
        $response->setValue("displayedColumns", $displayedColumns);
        $response->setValue("availableColumns", $availableColumns);
		$response->setValue("offset", $this->request->getValue('offset'));
		$response->setValue("totalCount", '0');	
		return $response;
	}
	
	
	/**
	 * @role mass
	 */
    public function processMass()
    {        
		$filter = new ARSelectFilter();
		
		$filters = (array)json_decode($this->request->getValue('filters'));
		$this->request->setValue('filters', $filters);
		
        $grid = new ActiveGrid($this->request, $filter, 'User');
        $filter->setLimit(0);
        					
		$users = ActiveRecordModel::getRecordSet('User', $filter, User::LOAD_REFERENCES);
		
        $act = $this->request->getValue('act');
		$field = array_pop(explode('_', $act, 2));           

        foreach ($users as $user)
		{
            if (substr($act, 0, 7) == 'enable_')
            {
                $user->setFieldValue($field, 1);    
            }        
            else if (substr($act, 0, 8) == 'disable_')
            {
                $user->setFieldValue($field, 0);                 
            } 
            else if ('delete' == $act)
            {
				$user->delete();
			}         
            
			$user->save();
        }		
		
		return new JSONResponse($this->request->getValue('act'));	
    } 
	
	
    public function edit()
    {
	    $group = UserGroup::getInstanceByID((int)$this->request->getValue('id'), true);
	    $form = $this->createUserGroupForm($group);

		$response = new ActionResponse();
		$response->setValue('userGroup', $group->toArray());
	    $response->setValue('userGroupForm', $form);
	    
	    return $response;
    }
    
	public function changeColumns()
	{		
		$columns = array_keys($this->request->getValue('col', array()));
		$this->setSessionData('columns', $columns);
		return new ActionRedirectResponse('backend.userGroup', 'users', array('id' => $this->request->getValue('group')));
	}

	public function lists()
	{
	    $filter = new ARSelectFilter();
	    switch($id = $this->request->getValue('id'))
	    {
	        case 'orders_1': 
	            $cond = new MoreThanCond(new ARFieldHandle('CustomerOrder', "dateCompleted"), 0);
	            break;
	        case 'orders_2': 
	            $cond = new EqualsCond(new ARFieldHandle('CustomerOrder', "status"), CustomerOrder::STATUS_AWAITING_SHIPMENT);
	            $cond->addOR(new EqualsCond(new ARFieldHandle('CustomerOrder', "status"), CustomerOrder::STATUS_BACKORDERED));
	            $cond->addOR(new MoreThanCond(new ARFieldHandle('CustomerOrder', "dateCompleted"), CustomerOrder::STATUS_BACKORDERED));
	            break;
	        case 'orders_3':
	            $cond = new MoreThanCond(new ARFieldHandle('CustomerOrder', "dateCompleted"), 0);	            
	            $cond2 = new EqualsCond(new ARFieldHandle('CustomerOrder', "status"), CustomerOrder::STATUS_AWAITING_SHIPMENT);
	            $cond2->addOR(new EqualsCond(new ARFieldHandle('CustomerOrder', "status"), CustomerOrder::STATUS_BACKORDERED));
	            $cond2->addOR(new MoreThanCond(new ARFieldHandle('CustomerOrder', "dateCompleted"), CustomerOrder::STATUS_BACKORDERED));
	            $cond->addAND($cond2);
	            break;
	        case 'orders_4': 
	            $cond = new EqualsCond(new ARFieldHandle('CustomerOrder', "status"), CustomerOrder::STATUS_BACKORDERED);
	            break;
	        case 'orders_5': 
	            $cond = new EqualsCond(new ARFieldHandle('CustomerOrder', "status"), CustomerOrder::STATUS_AWAITING_SHIPMENT);
	            break;
	        case 'orders_6': 
	            $cond = new EqualsCond(new ARFieldHandle('CustomerOrder', "status"), CustomerOrder::STATUS_SHIPPED);
	            break;
	        case 'orders_7': 
	            $cond = new EqualsCond(new ARFieldHandle('CustomerOrder', "status"), CustomerOrder::STATUS_RETURNED);
	            break;
	        case 'orders_8': 
	            $cond = new EqualsCond(new ARFieldHandle('CustomerOrder', "dateCompleted"), 0);
	            break;
	        default: return;
	    }
	   
	    $filter->setCondition($cond);
	    new ActiveGrid($this->request, $filter);
	    $orders = CustomerOrder::getRecordSet($filter, true)->toArray();
		$displayedColumns = $this->getDisplayedColumns();

    	$data = array();
		foreach ($orders as $order)
    	{
            $record = array();
            foreach ($displayedColumns as $column => $type)
            {
                list($class, $field) = explode('.', $column, 2);
                if ('CustomerOrder' == $class)
                {
					$value = isset($order[$field]) ? $order[$field] : '';
                }
				
                if ('User' == $class)
                {
					$value = isset($order['User'][$field]) ? $order['User'][$field] : '';
                }
                
				if ('bool' == $type)
				{
					$value = $value ? $this->translate('_yes') : $this->translate('_no');
				}
				
				$record[] = $value;
            }
            
            $data[] = $record;
        }
    	
    	return new JSONResponse(array(
	    	'columns' => array_keys($displayedColumns),
	    	'totalCount' => count($orders),
	    	'data' => $data
    	));	  	  	
	}

	/**
	 * @role update
	 */
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
        
        $validator = $this->createUserGroupFormValidator($group);
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
	 * @role create
	 */
	public function create()
	{
	    $userGroup = UserGroup::getNewInstance($this->translate('_new_user_group'));
	    $userGroup->save();
	    
		return new JSONResponse($userGroup->toArray());
	}
	
	/**
	 * @role remove
	 */
	public function remove()
	{
		$userGroup = UserGroup::getInstanceByID((int)$this->request->getValue("id"), true);
		$userGroupArray = $userGroup->toArray();
		$userGroup->delete();
		
		return new JSONResponse(array('status' => 'success', 'userGroup' => $userGroupArray));
	}

	/**
	 * @return Form
	 */
	private function createUserGroupForm(UserGroup $group)
	{
	    $form = new Form($this->createUserGroupFormValidator($group)); 
        $form->setData($group->toArray());
	    
	    return $form;
	}
	
	/**
	 * @return RequestValidator
	 */
	private function createUserGroupFormValidator(UserGroup $group)
	{
		$validator = new RequestValidator("userGroupForm_" . $group->isExistingRecord() ? $group->getID() : '', $this->request);
		$validator->addCheck("name", new IsNotEmptyCheck($this->translate("_error_name_should_not_be_empty")));
		
		return $validator;
	}
		
	protected function getDisplayedColumns()
	{	
		// get displayed columns
		$displayedColumns = $this->getSessionData('columns');		

		if (!$displayedColumns)
		{
			$displayedColumns = array(
				'CustomerOrder.companyName',
				'CustomerOrder.dateCompleted', 
				'CustomerOrder.status', 
				'CustomerOrder.dateCreated', 
				'CustomerOrder.dateCompleted', 
				'ShippingAddress.countryID', 
				'ShippingAddress.city', 
				'ShippingAddress.address1', 
				'ShippingAddress.postalCode', 
			 	'User.email',
				'User.firstName', 
				'User.lastName', 
				'User.companyName', 
			);				
		}
		
		$availableColumns = $this->getAvailableColumns();
		$displayedColumns = array_intersect_key(array_flip($displayedColumns), $availableColumns);	

		
		// User ID is always passed as the first column
		$displayedColumns = array_merge(array('CustomerOrder.ID' => 'numeric'), $displayedColumns);
				
		// set field type as value
		foreach ($displayedColumns as $column => $foo)
		{
			if (is_numeric($displayedColumns[$column]))
			{
				$displayedColumns[$column] = $availableColumns[$column]['type'];					
			}
		}

		return $displayedColumns;		
	}
	
	protected function getAvailableColumns()
	{
		// get available columns
		$availableColumns = array();
		foreach (ActiveRecordModel::getSchemaInstance('CustomerOrder')->getFieldList() as $field)
		{
			$fieldType = $field->getDataType();
			
			if($field->getName() == 'password')
			{
			    continue;
			}
			if ($field instanceof ARForeignKeyField)
			{
			  	continue;
			}		            
			if ($field instanceof ARPrimaryKeyField)
			{
			  	continue;
			}		            
			elseif ($fieldType instanceof ARBool)
			{
			  	$type = 'bool';
			}	  
			elseif ($fieldType instanceof ARNumeric)
			{
				$type = 'numeric';	  	
			}			
			else
			{
			  	$type = 'text';
			}
			
			$availableColumns['User.' . $field->getName()] = $type;
		}		
		
		$availableColumns['UserGroup.name'] = 'text';
		$availableColumns['ShippingAddress.city'] = 'text';
		$availableColumns['ShippingAddress.address1'] = 'text';
		$availableColumns['ShippingAddress.postalCode'] = 'text';
		$availableColumns['ShippingAddress.countryID'] = 'number';
		$availableColumns['User.firstName'] = 'text';
		$availableColumns['User.lastName'] = 'text';
		$availableColumns['User.companyName'] = 'text';

		foreach ($availableColumns as $column => $type)
		{
			$availableColumns[$column] = array(
				'name' => $this->translate($column), 
				'type' => $type
			);	
		}

		return $availableColumns;
	}
	
    protected function getMassForm()
    {
		$validator = new RequestValidator("UsersFilterFormValidator", $this->request);		
		
        return new Form($validator);                
    }
}
?>