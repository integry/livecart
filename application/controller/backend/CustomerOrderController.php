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
	
	public function info()
	{
	    $order = CustomerOrder::getInstanceById((int)$this->request->getValue('id'), true, true);
	    
	    $response = new ActionResponse();
	    $response->setValue('statuses', array(
	                                    CustomerOrder::STATUS_BACKORDERED  => $this->translate('_status_backordered'),
	                                    CustomerOrder::STATUS_AWAITING_SHIPMENT  => $this->translate('_status_awaiting_shipment'),
	                                    CustomerOrder::STATUS_SHIPPED  => $this->translate('_status_shipped'),
	                                    CustomerOrder::STATUS_RETURNED  => $this->translate('_status_returned'),
	                                    CustomerOrder::STATUS_NEW => $this->translate('_status_new'),
				            ));
				            
        $response->setValue('countries', $this->store->getEnabledCountries());
        
        $orderArray = $order->toArray();
        if($order->isFinalized->get())
        {
	        $response->setValue('shippingStates',  State::getStatesByCountry($order->shippingAddress->get()->countryID->get()));
	        $response->setValue('billingStates',  State::getStatesByCountry($order->billingAddress->get()->countryID->get()));
        }
        else
        {
            $order->user->get()->loadAddresses();
	        $response->setValue('shippingStates',  State::getStatesByCountry($order->user->get()->defaultShippingAddress->get()->userAddress->get()->countryID->get()));
	        $response->setValue('billingStates',  State::getStatesByCountry($order->user->get()->defaultBillingAddress->get()->userAddress->get()->countryID->get()));
        
	        $orderArray['BillingAddress'] = $order->user->get()->defaultBillingAddress->get()->userAddress->get()->toArray();
	        $orderArray['ShippingAddress'] = $order->user->get()->defaultShippingAddress->get()->userAddress->get()->toArray();
        }
       
	    $response->setValue('order', $orderArray);
	    $response->setValue('form', $this->createOrderForm($orderArray));
	    $response->setValue('formShippingAddress', $this->createUserAddressForm($orderArray['ShippingAddress']));
	    $response->setValue('formBillingAddress', $this->createUserAddressForm($orderArray['BillingAddress']));
        
		return $response;
	}
	
	/**
	 * @return RequestValidator
	 */
    public function createUserAddressFormValidator()
    {
        $validator = new RequestValidator("userAddress", $this->request);		            
			
		$validator->addCheck('countryID', new IsNotEmptyCheck($this->translate('_country_empty')));
		$validator->addCheck('city',      new IsNotEmptyCheck($this->translate('_city_empty')));
		$validator->addCheck('address1',  new IsNotEmptyCheck($this->translate('_address_empty')));
		$validator->addCheck('firstName', new IsNotEmptyCheck($this->translate('_first_name_is_empty')));
		$validator->addCheck('lastName',  new IsNotEmptyCheck($this->translate('_last_name_is_empty')));
        
        return $validator;
    }

    /**
     * @return Form
     */
	public function createUserAddressForm($addressArray = array())
	{
	    print_r($addressArray);
	    echo "<br /><br /><br />";
	    
		$form = new Form($this->createUserAddressFormValidator());	
	    if(!empty($addressArray))
	    {
	        $form->setData($addressArray);
	    }
	    
		return $form;
	}
	
	/**
	 * @return RequestValidator
	 */
    public function createOrderFormValidator()
    {
        $validator = new RequestValidator("CustomerOrder", $this->request);		            
			
		$validator->addCheck('status', new MinValueCheck($this->translate('_invalid_status'), 0));
		$validator->addCheck('status', new MaxValueCheck($this->translate('_invalid_status'), 4));	
        
        return $validator;
    }

    /**
     * @return Form
     */
	public function createOrderForm($orderArray)
	{
		$form = new Form($this->createOrderFormValidator());	
	    $form->setData($orderArray);
		
		return $form;
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
		$response->setValue("userID", $this->request->getValue('userID'));
		$response->setValue("totalCount", '0');	
		return $response;
	}
	
	public function switchCancelled()
	{
	    $order = CustomerOrder::getInstanceById((int)$this->request->getValue('id'), true);
	    $order->isCancelled->set(!$order->isCancelled->get());
	    $order->save();
	    
	    return new JSONResponse(array('status' => 'success', 'value' => $this->translate($order->isCancelled->get() ? '_cancelled' : '_applied')));
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
		        case 'setFinalized':
		            $order->isFinalized->set(1);
		            break;
		        case 'setUnfinalized':
		            $order->isFinalized->set(0);
		            break;
		        case 'delete':
		            $order->delete();
		            break;
		    }

			$order->save();
        }		
		
		return new JSONResponse($this->request->getValue('act'));	
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
		$displayedColumns = array_merge(array('CustomerOrder.viewOrder' => 'text'), $displayedColumns);
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
		$availableColumns['CustomerOrder.viewOrder'] = 'text'; 
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
    
    /**
     * @role update
     */
    public function update()
    {
        $order = CustomerOrder::getInstanceByID((int)$this->request->getValue('id'), true);
        return $this->save($order);
    }
    
    public function updateAddress()
    {
        $validator = $this->createUserAddressFormValidator();
        
        if($validator->isValid())
        {		
	        $address = UserAddress::getInstanceByID('UserAddress', (int)$this->request->getValue('ID'));
	        $address->address1->set($this->request->getValue('address1'));
	        $address->address2->set($this->request->getValue('address2'));
	        $address->city->set($this->request->getValue('city'));
	        $address->stateName->set($this->request->getValue('stateName'));
	        $address->state->set($this->request->getValue('stateID'));
	        $address->postalCode->set($this->request->getValue('postalCode'));
	        $address->countryID->set($this->request->getValue('countryID'));
	        $address->phone->set($this->request->getValue('phone'));
	        $address->companyName->set($this->request->getValue('companyName'));
	        $address->firstName->set($this->request->getValue('firstName'));
	        $address->lastName->set($this->request->getValue('lastName'));
	        
	        $address->save();
	        
	        return new JSONResponse(array('status' => 'success', 'address' => $address->toArray()));
        }
        else
        {
            return new JSONResponse(array('status' => 'failure', 'errors' => $validator->getErrorList()));
        }
    }
    
	private function save(CustomerOrder $order)
	{
   		$validator = self::createOrderFormValidator();
		if ($validator->isValid())
		{
		    $isCancelled = (int)$this->request->getValue('isCancelled') ? true : false;
		    $status = (int)$this->request->getValue('status');
		    
			$order->isCancelled->set($isCancelled);
			$order->status->set($status);

			$order->save();
			
			return new JSONResponse(array('status' => 'success'));
		}
		else
		{
		    return new JSONResponse(array('status' => 'failure', 'errors' => $validator->getErrorList()));
		}
	}
}
?>