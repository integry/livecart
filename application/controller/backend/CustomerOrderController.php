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
		$response->set('orderGroups', $orderGroups);
		return $response;
	    
	}
	
	public function info()
	{
	    $order = CustomerOrder::getInstanceById((int)$this->request->get('id'), true, true);
	    
	    $response = new ActionResponse();
	    $response->set('statuses', array(
	                                    CustomerOrder::STATUS_BACKORDERED  => $this->translate('_status_backordered'),
	                                    CustomerOrder::STATUS_AWAITING_SHIPMENT  => $this->translate('_status_awaiting_shipment'),
	                                    CustomerOrder::STATUS_SHIPPED  => $this->translate('_status_shipped'),
	                                    CustomerOrder::STATUS_RETURNED  => $this->translate('_status_returned'),
	                                    CustomerOrder::STATUS_NEW => $this->translate('_status_new'),
				            ));
				            
        $response->set('countries', $this->store->getEnabledCountries());
        
        $orderArray = $order->toArray();
        if($order->isFinalized->get())
        {
            $shippingStates = State::getStatesByCountry($order->shippingAddress->get()->countryID->get());
            $billingStates = State::getStatesByCountry($order->billingAddress->get()->countryID->get());
           
            $billingStates[''] = '';
            $shippingStates[''] = '';
                        
            asort($shippingStates);
            asort($billingStates);
            
	        $response->set('shippingStates',  $shippingStates);
	        $response->set('billingStates',  $billingStates);
        }
        else
        {
            $order->user->get()->loadAddresses();
            
            $shippingStates = State::getStatesByCountry($order->user->get()->defaultShippingAddress->get()->userAddress->get()->countryID->get());
            $shippingStates[''] = '';
            
            $billingStates = State::getStatesByCountry($order->user->get()->defaultBillingAddress->get()->userAddress->get()->countryID->get());
            $billingStates[''] = '';
            
	        $response->set('shippingStates',  $shippingStates);
	        $response->set('billingStates',  $billingStates);
        
	        $orderArray['BillingAddress'] = $order->user->get()->defaultBillingAddress->get()->userAddress->get()->toArray();
	        $orderArray['ShippingAddress'] = $order->user->get()->defaultShippingAddress->get()->userAddress->get()->toArray();
        }
       
        $user = $order->user->get();
        $addressOptions = array('' => '');
        $addressOptions['optgroup_0'] = $this->translate('_shipping_addresses');
        $addresses = array();
        foreach($user->getBillingAddressArray() as $address)
        {
            $addressOptions[$address['ID']] = $this->createAddressString($address);
            $addresses[$address['ID']] = $address;
        }
        
        $addressOptions['optgroup_1'] = $this->translate('_billing_addresses');
        foreach($user->getShippingAddressArray() as $address)
        {
            $addressOptions[$address['ID']] = $this->createAddressString($address);
            $addresses[$address['ID']] = $address;
        }
        
	    $response->set('order', $orderArray);
	    $response->set('form', $this->createOrderForm($orderArray));
	    $response->set('existingUserAddressOptions', $addressOptions);
	    $response->set('existingUserAddresses', $addresses);
	    $response->set('formShippingAddress', $this->createUserAddressForm($orderArray['ShippingAddress']));
	    $response->set('formBillingAddress', $this->createUserAddressForm($orderArray['BillingAddress']));
        
		return $response;
	}
	
	public function selectCustomer()
	{
		$userGroups = array();
		$userGroups[] = array('ID' => -2, 'name' => $this->translate('_all_users'), 'rootID' => 0);
		$userGroups[] = array('ID' => -1, 'name' => $this->translate('_default_user_group'), 'rootID' => -2);
		
		foreach(UserGroup::getRecordSet(new ARSelectFilter())->toArray() as $group) 
		{
		    $userGroups[] = array('ID' => $group['ID'], 'name' => $group['name'], 'rootID' => -2);
		}
		    
		$response = new ActionResponse();
		$response->set('userGroups', $userGroups);
		
		return $response;
	}
	
	private function createAddressString($addressArray)
	{
          $addressString = '';
          
          if(!empty($addressArray['UserAddress']['fullName']))
          {
              $addressString .= $addressArray['UserAddress']['fullName'] . ', ';
          }
          
          if(!empty($addressArray['UserAddress']['countryName']))
          {
              $addressString .= $addressArray['UserAddress']['countryName'] . ', ';
          }
      
          if(!empty($addressArray['UserAddress']['stateName']))
          {
              $addressString .= $addressArray['UserAddress']['stateName'] . ', ';
          }
      
          if(!empty($addressArray['State']['code']))
          {
              $addressString .= $addressArray['State']['code'] . ', ';
          }
      
          if(!empty($addressArray['UserAddress']['city']))
          {
              $addressString .= $addressArray['UserAddress']['city'] . ', ';
          }
          
          if(strlen($addressString) > 2)
          {
              $addressString = substr($addressString, 0, -2);
          }
          
          return $addressString;
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
		$form = new Form($this->createUserAddressFormValidator());	
		
		if(!empty($addressArray))
	    {
			if(isset($addressArray['State']['ID']))
			{
			    $addressArray['stateID'] = $addressArray['State']['ID'];
			}
			
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
        $response->set("massForm", $this->getMassForm());
        $response->set("orderGroupID", $this->request->get('id'));
        $response->set("displayedColumns", $displayedColumns);
        $response->set("availableColumns", $availableColumns);
		$response->set("offset", $this->request->get('offset'));
		$response->set("userID", $this->request->get('userID'));
		$response->set("totalCount", '0');	
		return $response;
	}
	
	public function switchCancelled()
	{
	    $order = CustomerOrder::getInstanceById((int)$this->request->get('id'), true, true);
	    $order->isCancelled->set(!$order->isCancelled->get());
	    $order->save();
	    
	    return new JSONResponse(array(
	        'status' => 'success', 
	        'isCanceled' => $order->isCancelled->get(),
	        'linkValue' => $this->translate($order->isCancelled->get() ? '_accept_order' : '_cancel_order'),
	        'value' => $this->translate($order->isCancelled->get() ? '_canceled' : '_accepted')
        ));
	}
	
	/**
	 * @role mass
	 */
    public function processMass()
    {        
		$filter = new ARSelectFilter();
		
		$filters = (array)json_decode($this->request->get('filters'));
		$this->request->set('filters', $filters);
        $grid = new ActiveGrid($this->request, $filter, 'CustomerOrder');
        $filter->setLimit(0);
        					
		$orders = CustomerOrder::getRecordSet($filter);
		
        $act = $this->request->get('act');
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
		
		return new JSONResponse($this->request->get('act'));	
    } 
    
	public function changeColumns()
	{		
		$columns = array_keys($this->request->get('col', array()));
		$this->setSessionData('columns', $columns);
		return new ActionRedirectResponse('backend.customerOrder', 'orders', array('id' => $this->request->get('group')));
	}

	public function lists()
	{
	    $filter = new ARSelectFilter();
	    switch($id = $this->request->get('id'))
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
	    
	    if($this->request->get('sort_col') == 'CustomerOrder.ID2')
	    {
	        $this->request->set('sort_col', 'CustomerOrder.ID');
	    }
	    
	    if($this->request->get('sort_col') == 'User.fullName')
	    {
	        $this->request->set('sort_col', 'User.lastName');
	    }
	    
	    if($filters = $this->request->get('filters'))
	    {
	        if(isset($filters['CustomerOrder.ID2']))
	        {
	            $filters['CustomerOrder.ID'] = $filters['CustomerOrder.ID2'];
	            unset($filters['CustomerOrder.ID2']);
	            $this->request->set('filters', $filters);
	        }
	    }
	    
	    if($filters = $this->request->get('filters'))
	    {
	        if(isset($filters['User.fullName']))
	        {
	            $filters['User.lastName'] = $filters['User.fullName'];
	            unset($filters['User.fullName']);
	            $this->request->set('filters', $filters);
	        }
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
		
		$displayedColumns = array_merge(array('User.email' => 'text'), $displayedColumns);
		$displayedColumns = array_merge(array('User.ID' => 'number'), $displayedColumns); // user id must go after user email here
		$displayedColumns = array_merge(array('User.fullName' => 'text'), $displayedColumns);
		$displayedColumns = array_merge(array('CustomerOrder.ID2' => 'numeric'), $displayedColumns);
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
		$availableColumns['CustomerOrder.ID2'] = 'numeric'; 
		$availableColumns['User.fullName'] = 'text'; 

		foreach (ActiveRecordModel::getSchemaInstance('CustomerOrder')->getFieldList() as $field)
		{
			$type = ActiveGrid::getFieldType($field);
			
			if (!$type)
			{
			    continue;
			}		
            
			$availableColumns['CustomerOrder.' . $field->getName()] = $type;            			
        }

        unset($availableColumns['CustomerOrder.shipping']);

        // @todo - does why enabling this column raise an error?
        unset($availableColumns['CustomerOrder.dateCompleted']);
                                        
		$availableColumns['CustomerOrder.status'] = 'text'; 
		
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
		
		unset($availableColumns['CustomerOrder.isFinalized']);

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
        $order = CustomerOrder::getInstanceByID((int)$this->request->get('ID'), true);
	    $status = (int)$this->request->get('status');
		$order->status->set($status);
	    $isCancelled = (int)$this->request->get('isCancelled') ? true : false;
		$order->isCancelled->set($isCancelled);
		
        return $this->save($order);
    }
    
    /**
     * @role update
     */
    public function create()
    {
        $user = User::getInstanceByID((int)$this->request->get('customerID'), true, true);
        $order = CustomerOrder::getNewInstance($user);
	    $status = CustomerOrder::STATUS_NEW;
		$order->status->set($status);
		$order->isFinalized->set(1);
		$order->capturedAmount->set(0);
		$order->totalAmount->set(0);
		$order->dateCompleted->set(new ARSerializableDateTime());
		$order->currency->set($this->store->getDefaultCurrency());
		
		if($user->defaultShippingAddress->get() && $user->defaultBillingAddress->get())
		{
		    $user->defaultBillingAddress->get()->load(array('UserAddress'));
		    $user->defaultShippingAddress->get()->load(array('UserAddress'));
		    
		    $billingAddress = clone $user->defaultBillingAddress->get()->userAddress->get();
		    $shippingAddress = clone $user->defaultShippingAddress->get()->userAddress->get();
		    
		    $billingAddress->save();
		    $shippingAddress->save();
		    
			$order->billingAddress->set($billingAddress);
			$order->shippingAddress->set($shippingAddress);
		
		    return $this->save($order);
		}
		else
		{
		    return new JSONResponse(array(
				    'status' => 'failure', 
				    'errors' => array(
					    'noaddress' => $this->translate('_err_user_has_no_billing_or_shipping_address')
				    )
		    ));
		}
    }
    
	private function save(CustomerOrder $order)
	{
   		$validator = self::createOrderFormValidator();
		if ($validator->isValid())
		{
			$order->save();
			
			return new JSONResponse(array('status' => 'success'));
		}
		else
		{
		    return new JSONResponse(array('status' => 'failure', 'errors' => $validator->getErrorList()));
		}
	}
    
    public function updateAddress()
    {
        $validator = $this->createUserAddressFormValidator();
        
        if($validator->isValid())
        {		
	        $address = UserAddress::getInstanceByID('UserAddress', (int)$this->request->get('ID'));
	        $address->address1->set($this->request->get('address1'));
	        $address->address2->set($this->request->get('address2'));
	        $address->city->set($this->request->get('city'));
	        $address->stateName->set($this->request->get('stateName'));
	        
	        if($this->request->get('stateID'))
	        {
	            $address->state->set(State::getInstanceByID((int)$this->request->get('stateID')));
	        }
	        
	        $address->postalCode->set($this->request->get('postalCode'));
	        $address->countryID->set($this->request->get('countryID'));
	        $address->phone->set($this->request->get('phone'));
	        $address->companyName->set($this->request->get('companyName'));
	        $address->firstName->set($this->request->get('firstName'));
	        $address->lastName->set($this->request->get('lastName'));
	        
	        $address->save();
	        
	        return new JSONResponse(array('status' => 'success', 'address' => $address->toArray()));
        }
        else
        {
            return new JSONResponse(array('status' => 'failure', 'errors' => $validator->getErrorList()));
        }
    }

    /**
     * @role update
     */
	public function removeEmptyShipments()
	{
	    $order = CustomerOrder::getInstanceById((int)$this->request->get('id'), true, true);
	    
	    foreach($order->getShipments() as $shipment)
	    {
	        if(count($shipment->getItems()) == 0)
	        {
                $shipment->delete();
	        }
	    }
	    
	    return new JSONResponse(array('status' => 'success'));
	}
}
?>