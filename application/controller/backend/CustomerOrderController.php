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
        $grid = new ActiveGrid($this->request, $filter, 'CustomerOrder');
        $filter->setLimit(0);
        					
		$orders = CustomerOrder::getRecordSet($filter);
		
        $act = $this->request->getValue('act');
		$field = array_pop(explode('_', $act, 2));           

        foreach ($orders as $order)
		{
		    switch($act)
		    {
		        case 'setNew':
		            $order->status->set(CustomerOrder::STATUS_NEW);
		            break;
		        case 'setBackordered':
		            $order->status->set(CustomerOrder::STATUS_BACKORDERED);
		            break;
		        case 'setAwaitingShipment':
		            $order->status->set(CustomerOrder::STATUS_AWAITING_SHIPMENT);
		            break;
		        case 'setShipped':
		            $order->status->set(CustomerOrder::STATUS_SHIPPED);
		            break;
		        case 'setReturned':
		            $order->status->set(CustomerOrder::STATUS_RETURNED);
		            break;
		        case 'delete':
		            $order->delete();
		            break;
		    }

			$order->save();
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
		return new ActionRedirectResponse('backend.customerOrder', 'orders', array('id' => $this->request->getValue('group')));
	}

	public function lists()
	{
	    $filter = new ARSelectFilter();
	    switch($id = $this->request->getValue('id'))
	    {
	        case 'orders_1': 
	            $cond = new EqualsCond(new ARFieldHandle('CustomerOrder', "isFinalized"), 1);
	            break;
	        case 'orders_2': 
	            $cond = new EqualsCond(new ARFieldHandle('CustomerOrder', "isFinalized"), 1);
	            $cond2 = new NotEqualsCond(new ARFieldHandle('CustomerOrder', "status"), CustomerOrder::STATUS_SHIPPED);
	            $cond2->addOR(new NotEqualsCond(new ARFieldHandle('CustomerOrder', "status"), CustomerOrder::STATUS_RETURNED));
	            $cond2->addOR(new IsNullCond(new ARFieldHandle('CustomerOrder', "status")));
	            $cond2->addAND($cond);
	            break;
	        case 'orders_3':
	            $cond = new IsNullCond(new ARFieldHandle('CustomerOrder', "status")); 
	            $cond->addAND(new EqualsCond(new ARFieldHandle('CustomerOrder', "isFinalized"), 1));
	            break;
	        case 'orders_4': 
	            $cond = new EqualsCond(new ARFieldHandle('CustomerOrder', "status"), CustomerOrder::STATUS_BACKORDERED);
	            $cond->addAND(new EqualsCond(new ARFieldHandle('CustomerOrder', "isFinalized"), 1));
	            break;
	        case 'orders_5': 
	            $cond = new EqualsCond(new ARFieldHandle('CustomerOrder', "status"), CustomerOrder::STATUS_AWAITING_SHIPMENT);
	            $cond->addAND(new EqualsCond(new ARFieldHandle('CustomerOrder', "isFinalized"), 1));
	            break;
	        case 'orders_6': 
	            $cond = new EqualsCond(new ARFieldHandle('CustomerOrder', "status"), CustomerOrder::STATUS_SHIPPED);
	            $cond->addAND(new EqualsCond(new ARFieldHandle('CustomerOrder', "isFinalized"), 1));
	            break;
	        case 'orders_7': 
	            $cond = new EqualsCond(new ARFieldHandle('CustomerOrder', "status"), CustomerOrder::STATUS_RETURNED);
	            $cond->addAND(new EqualsCond(new ARFieldHandle('CustomerOrder', "isFinalized"), 1));
	            break;
	        case 'orders_8': 
	            $cond = new EqualsCond(new ARFieldHandle('CustomerOrder', "isFinalized"), 0);
	            break;
	        default: 
	            return;
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
				
                if ('ShippingAddress' == $class)
                {
					$value = isset($order['ShippingAddress'][$field]) ? $order['ShippingAddress'][$field] : '';
                }
                
				if ('bool' == $type)
				{
					$value = $value ? $this->translate('_yes') : $this->translate('_no');
				}
				
				if('status' == $field)
				{
				    switch($order[$field])
				    {
				        case 1: 
				            $value = $this->translate('_status_backordered');
				            break;
				        case 2: 
				            $value = $this->translate('_status_awaiting_shipment');
				            break;
				        case 3: 
				            $value = $this->translate('_status_shipped');
				            break;
				        case 4:  
				            $value = $this->translate('_status_canceled');
				            break;
				        default: 
				            $value = $this->translate('_status_new');   
				            break;
				    }
				}
				
				if('totalAmount' == $field || 'capturedAmount' == $field)
				{
				    if(empty($value))
				    {
				        $value = '0';
				    }
				    
				    if(isset($order['Currency']))
				    {
				        $value .= ' ' . $order['Currency']["ID"];
				    }
				}
				
				if('dateCompleted' == $field && !$value)
				{
				    $value = '-';
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
				'CustomerOrder.dateCompleted',
				'CustomerOrder.totalAmount',
				'CustomerOrder.status', 
			);				
		}
		
		$availableColumns = $this->getAvailableColumns();
		$displayedColumns = array_intersect_key(array_flip($displayedColumns), $availableColumns);	

		
		// User ID is always passed as the first column
		$displayedColumns = array_merge(array('User.email' => 'text'), $displayedColumns);
		$displayedColumns = array_merge(array('User.ID' => 'number'), $displayedColumns); // user id must go after user email here
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
		$availableColumns['User.email'] = 'text'; 
		$availableColumns['User.ID'] = 'text'; 
		$availableColumns['CustomerOrder.status'] = 'text'; 
		$availableColumns['CustomerOrder.totalAmount'] = 'text';
		$availableColumns['CustomerOrder.dateCreated'] = 'text';
		$availableColumns['CustomerOrder.dateCompleted'] = 'text'; 
		$availableColumns['CustomerOrder.isFinalized'] = 'bool'; 
		$availableColumns['CustomerOrder.isPaid'] = 'bool'; 
		$availableColumns['CustomerOrder.isCanceled'] = 'bool'; 
			
        // Order
		$availableColumns['CustomerOrder.status'] = 'text'; 
		$availableColumns['CustomerOrder.totalAmount'] = 'text';
		$availableColumns['CustomerOrder.capturedAmount'] = 'text';
		$availableColumns['CustomerOrder.dateCreated'] = 'text';
		$availableColumns['CustomerOrder.dateCompleted'] = 'text'; 
		$availableColumns['CustomerOrder.isFinalized'] = 'bool'; 
		$availableColumns['CustomerOrder.isPaid'] = 'bool'; 
		$availableColumns['CustomerOrder.isCanceled'] = 'bool'; 
		
		// Address
		$availableColumns['ShippingAddress.countryID'] = 'text';  
		$availableColumns['ShippingAddress.city'] = 'text';  
		$availableColumns['ShippingAddress.address1'] = 'text'; 
		$availableColumns['ShippingAddress.postalCode'] = 'numeric'; 
	 	
		// User
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
		$validator = new RequestValidator("OrdersMassFormValidator", $this->request);		
		
        return new Form($validator);                
    }
}
?>