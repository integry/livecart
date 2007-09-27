<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.controller.backend.*");
ClassLoader::import("application.model.order.*");
ClassLoader::import("application.model.Currency");
ClassLoader::import("framework.request.validator.Form");
ClassLoader::import("framework.request.validator.RequestValidator");

/**
 * @package application.controller.backend
 * @role order
 */
class CustomerOrderController extends StoreManagementController
{
	const TYPE_ALL = 1;
	const TYPE_CURRENT = 2;
	const TYPE_NEW = 3;
	const TYPE_PROCESSING = 4;
	const TYPE_AWAITING = 5;
	const TYPE_SHIPPED = 6;
	const TYPE_RETURNED = 7;
	const TYPE_CARTS = 8;
	const TYPE_CANCELLED = 9;
								
	/**
	 * Action shows filters and datagrid.
	 * @return ActionResponse
	 */
	public function index()
	{
		$orderGroups = array(
		    array('ID' => self::TYPE_ALL, 'name' => $this->translate('_all_orders'), 'rootID' => 0),
		        array('ID' => self::TYPE_CURRENT, 'name' => $this->translate('_current_orders'), 'rootID' => 1),
		            array('ID' => self::TYPE_NEW, 'name' => $this->translate('_new_orders'), 'rootID' => 2),
		            array('ID' => self::TYPE_PROCESSING, 'name' => $this->translate('_processing_orders'), 'rootID' => 2),
		            array('ID' => self::TYPE_AWAITING, 'name' => $this->translate('_awaiting_shipment_orders'), 'rootID' => 2),
		        array('ID' => self::TYPE_SHIPPED, 'name' => $this->translate('_shipped_orders'), 'rootID' => 1),
		        array('ID' => self::TYPE_RETURNED, 'name' => $this->translate('_returned_orders'), 'rootID' => 1),
		        array('ID' => self::TYPE_CANCELLED, 'name' => $this->translate('_cancelled_orders'), 'rootID' => 1),
		    array('ID' => self::TYPE_CARTS, 'name' => $this->translate('_shopping_carts'), 'rootID' => 0),
		);

		return new ActionResponse('orderGroups', $orderGroups);
	}

	public function info()
	{
	    $order = CustomerOrder::getInstanceById((int)$this->request->get('id'), true, array('ShippingAddress' => 'UserAddress', 'BillingAddress' => 'UserAddress', 'State', 'User', 'Currency'));

	    $response = new ActionResponse();
	    $response->set('statuses', array(
                                        CustomerOrder::STATUS_NEW => $this->translate('_status_new'),
	                                    CustomerOrder::STATUS_PROCESSING  => $this->translate('_status_processing'),
	                                    CustomerOrder::STATUS_AWAITING  => $this->translate('_status_awaiting'),
	                                    CustomerOrder::STATUS_SHIPPED  => $this->translate('_status_shipped'),
	                                    CustomerOrder::STATUS_RETURNED  => $this->translate('_status_returned'),
				            ));

        $response->set('countries', $this->application->getEnabledCountries());

        $orderArray = $order->toArray();
        if($order->isFinalized->get())
        {
            if($billingAddress = $order->billingAddress->get())
            {
                $billingAddress->load(true);
                $orderArray['BillingAddress'] = $billingAddress->toArray();
            }
            if($shippingAddress = $order->shippingAddress->get())
            {
                $shippingAddress->load(true);
                $orderArray['ShippingAddress'] = $shippingAddress->toArray();
            }

            if($order->billingAddress->get())
            {
	            $billingStates = State::getStatesByCountry($order->billingAddress->get()->countryID->get());
	            $billingStates[''] = '';
	            asort($billingStates);
		        $response->set('billingStates',  $billingStates);
            }

            if($order->shippingAddress->get())
            {
	            $shippingStates = State::getStatesByCountry($order->shippingAddress->get()->countryID->get());
	            $shippingStates[''] = '';
	            asort($shippingStates);
		        $response->set('shippingStates',  $shippingStates);
            }
        }
        elseif ($order->user->get())
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

	    $response->set('order', $orderArray);
	    $response->set('form', $this->createOrderForm($orderArray));

        $user = $order->user->get();

        if (!$user)
        {
			return $response;
        }

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

	    $response->set('existingUserAddressOptions', $addressOptions);
	    $response->set('existingUserAddresses', $addresses);

	    if(isset($orderArray['ShippingAddress']))
	    {
	        $response->set('formShippingAddress', $this->createUserAddressForm($orderArray['ShippingAddress']));
	    }

	    if(isset($orderArray['BillingAddress']))
	    {
	        $response->set('formBillingAddress', $this->createUserAddressForm($orderArray['BillingAddress']));
	    }

	    $firstEmptyShipment = false;
        $response->set('hideShipped', 0);
	    foreach($order->getShipments() as $shipment)
	    {
            if(!$firstEmptyShipment && count($shipment->getItems()) == 0)
            {
                $firstEmptyShipment = true;
            }
            else if(!$shipment->isShipped())
	        {
	            $response->set('hideShipped', 1);
	            break;
	        }
	    }

		$response->set('type', $this->getOrderType($order));

		return $response;
	}

	private function getOrderType(CustomerOrder $order)
	{
		if (!$order->isFinalized->get())
		{
			return self::TYPE_CARTS;
		}
		else if ($order->isCancelled->get())
		{
			return self::TYPE_CANCELLED;
		}
		else
		{
			switch ($order->status->get())
			{
				case CustomerOrder::STATUS_NEW: return self::TYPE_NEW;
				case CustomerOrder::STATUS_PROCESSING: return self::TYPE_PROCESSING;
				case CustomerOrder::STATUS_AWAITING: return self::TYPE_AWAITING;
				case CustomerOrder::STATUS_SHIPPED: return self::TYPE_SHIPPED;
				case CustomerOrder::STATUS_RETURNED: return self::TYPE_RETURNED;
				default: return 0;												
			}
		}
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

		return new ActionResponse('userGroups', $userGroups);
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
		$response->set("filters", ((int)$this->request->get('userID') ? array('User.ID' => $this->request->get('userID')) : false));
		if ($this->request->get('userID'))
		{
			$response->set('userID', $this->request->get('userID'));
		}
		$response->set("totalCount", '0');
		return $response;
	}

	/**
	 * @role update
	 */
	public function switchCancelled()
	{
	    $order = CustomerOrder::getInstanceById((int)$this->request->get('id'), true, true);
	    $history = new OrderHistory($order, $this->user);
	    $order->isCancelled->set(!$order->isCancelled->get());
	    $order->save();
	    $history->saveLog();

	    return new JSONResponse(array(
		        'isCanceled' => $order->isCancelled->get(),
		        'linkValue' => $this->translate($order->isCancelled->get() ? '_accept_order' : '_cancel_order'),
		        'value' => $this->translate($order->isCancelled->get() ? '_canceled' : '_accepted')
	        ),
	        'success',
	        $this->translate($order->isCancelled->get() ? '_order_is_canceled' : '_order_is_accepted')
        );
	}

	/**
	 * @role mass
	 */
    public function processMass()
    {
		$filter = new ARSelectFilter();

		$filters = (array)json_decode($this->request->get('filters'));
		$this->request->set('filters', $filters);
        $grid = new ActiveGrid($this->application, $filter, 'CustomerOrder');
        $filter->setLimit(0);

		$filter->mergeCondition($this->getTypeCondition($this->request->get('id')));

		$orders = CustomerOrder::getRecordSet($filter, CustomerOrder::LOAD_REFERENCES);

        $act = $this->request->get('act');
		$field = array_pop(explode('_', $act, 2));

        foreach ($orders as $order)
		{
	       $history = new OrderHistory($order, $this->user);
	    
		    switch($act)
		    {
		        case 'setNew':
		            $order->status->set(CustomerOrder::STATUS_NEW);
		            break;
		        case 'setProcessing':
		            $order->status->set(CustomerOrder::STATUS_PROCESSING);
		            break;
		        case 'setAwaitingShipment':
		            $order->status->set(CustomerOrder::STATUS_AWAITING);
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
		        case 'setCancel':
		            $order->isCancelled->set(true);
		            break;
		        case 'delete':
		            $order->delete();
		            break;
		    }

		    if($act != 'delete')
		    {
			    $order->save();
			    $history->saveLog();
		    }
        }

		return new JSONResponse(array('act' => $this->request->get('act')), 'success', $this->translate('_mass_action_succeed'));
    }

	public function changeColumns()
	{
		$columns = array_keys($this->request->get('col', array()));
		$this->setSessionData('columns', $columns);
		return new ActionRedirectResponse('backend.customerOrder', 'orders', array('id' => $this->request->get('id')));
	}

	public function lists()
	{
	    $filter = new ARSelectFilter();

	    if($this->request->get('sort_col') == 'CustomerOrder.ID2')
	    {
	        $this->request->set('sort_col', 'CustomerOrder.ID');
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

		list ($foo, $id) = explode('_', $this->request->get('id'));
		$cond = $this->getTypeCondition($id);

	    if($filters = $this->request->get('filters'))
	    {
	        if(isset($filters['User.fullName']))
	        {
	            $nameParts = explode(' ', $filters['User.fullName']);
	            unset($filters['User.fullName']);
	            $this->request->set('filters', $filters);

	            if(count($nameParts) == 1)
	            {
	                $nameParts[1] = $nameParts[0];
	            }

	            $firstNameCond = new LikeCond(new ARFieldHandle('User', "firstName"), '%' . $nameParts[0] . '%');
	            $firstNameCond->addOR(new LikeCond(new ARFieldHandle('User', "lastName"), '%' . $nameParts[1] . '%'));

                $lastNameCond = new LikeCond(new ARFieldHandle('User', "firstName"), '%' . $nameParts[0] . '%');
                $lastNameCond->addOR(new LikeCond(new ARFieldHandle('User', "lastName"), '%' . $nameParts[1] . '%'));

                $cond->addAND($firstNameCond);
                $cond->addAND($lastNameCond);
	         }
	    }

        if($this->request->get('sort_col') == 'User.fullName')
        {
            $this->request->remove('sort_col');

            $direction = ($this->request->get('sort_dir') == 'DESC') ? ARSelectFilter::ORDER_DESC : ARSelectFilter::ORDER_ASC;

            $filter->setOrder(new ARFieldHandle("User", "lastName"), $direction);
            $filter->setOrder(new ARFieldHandle("User", "firstName"), $direction);
        }

	    $filter->setCondition($cond);

	    new ActiveGrid($this->application, $filter);
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
				            $value = $this->translate('_status_processing');
				            break;
				        case 2:
				            $value = $this->translate('_STATUS_AWAITING');
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

	private function getTypeCondition($type)
	{
	    switch($type)
	    {
	        case self::TYPE_ALL:
	            $cond = new EqualsCond(new ARFieldHandle('CustomerOrder', "isFinalized"), 1);
	            break;
	        case self::TYPE_CURRENT:
	            $cond = new EqualsCond(new ARFieldHandle('CustomerOrder', "isFinalized"), 1);
	            $cond2 = new NotEqualsCond(new ARFieldHandle('CustomerOrder', "status"), CustomerOrder::STATUS_SHIPPED);
	            $cond2->addOR(new NotEqualsCond(new ARFieldHandle('CustomerOrder', "status"), CustomerOrder::STATUS_RETURNED));
	            $cond2->addOR(new EqualsCond(new ARFieldHandle('CustomerOrder', "status"), CustomerOrder::STATUS_NEW));
	            $cond2->addAND($cond);
	            break;
	        case self::TYPE_NEW:
	            $cond = new EqualsCond(new ARFieldHandle('CustomerOrder', "status"), CustomerOrder::STATUS_NEW);
	            $cond->addAND(new EqualsCond(new ARFieldHandle('CustomerOrder', "isFinalized"), 1));
	            break;
	        case self::TYPE_PROCESSING:
	            $cond = new EqualsCond(new ARFieldHandle('CustomerOrder', "status"), CustomerOrder::STATUS_PROCESSING);
	            $cond->addAND(new EqualsCond(new ARFieldHandle('CustomerOrder', "isFinalized"), 1));
	            break;
	        case self::TYPE_AWAITING:
	            $cond = new EqualsCond(new ARFieldHandle('CustomerOrder', "status"), CustomerOrder::STATUS_AWAITING);
	            $cond->addAND(new EqualsCond(new ARFieldHandle('CustomerOrder', "isFinalized"), 1));
	            break;
	        case self::TYPE_SHIPPED:
	            $cond = new EqualsCond(new ARFieldHandle('CustomerOrder', "status"), CustomerOrder::STATUS_SHIPPED);
	            $cond->addAND(new EqualsCond(new ARFieldHandle('CustomerOrder', "isFinalized"), 1));
	            break;
	        case self::TYPE_RETURNED:
	            $cond = new EqualsCond(new ARFieldHandle('CustomerOrder', "status"), CustomerOrder::STATUS_RETURNED);
	            $cond->addAND(new EqualsCond(new ARFieldHandle('CustomerOrder', "isFinalized"), 1));
	            break;
	        case self::TYPE_CANCELLED:
	            $cond = new EqualsCond(new ARFieldHandle('CustomerOrder', "isCancelled"), true);
	            $cond->addAND(new EqualsCond(new ARFieldHandle('CustomerOrder', "isFinalized"), 1));
	            break;
	        case self::TYPE_CARTS:
	            $cond = new EqualsCond(new ARFieldHandle('CustomerOrder', "isFinalized"), 0);
	            break;
	        default:
	            return;
	    }		
	
	    $filters = $this->request->get('filters');
		if (!in_array($type, array(self::TYPE_CANCELLED, self::TYPE_ALL, self::TYPE_SHIPPED, self::TYPE_RETURNED)))
	    {
            $cond->addAND(new EqualsCond(new ARFieldHandle('CustomerOrder', "isCancelled"), 0));
        }
	
		return $cond;
	}

    /**
     * @role update
     */
    public function update()
    {
        $order = CustomerOrder::getInstanceByID((int)$this->request->get('ID'), true);
	    $history = new OrderHistory($order, $this->user);

	    $oldStatus = $order->status->get();
	    
        $status = (int)$this->request->get('status');
		$order->status->set($status);
	    $isCancelled = (int)$this->request->get('isCancelled') ? true : false;
		$order->isCancelled->set($isCancelled);
	    
        $response = $this->save($order);
        $history->saveLog();

        return $response;
    }

    /**
     * @role create
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
		$order->currency->set($this->application->getDefaultCurrency());

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
		    return new JSONResponse(array('noaddress' => true), 'failure', $this->translate('_err_user_has_no_billing_or_shipping_address'));
		}
    }

    /**
     * @role update
     */
    public function updateAddress()
    {
        $validator = $this->createUserAddressFormValidator();

        if($validator->isValid())
        {
            $order = CustomerOrder::getInstanceByID((int)$this->request->get('orderID'), true, array('ShippingAddress' => 'UserAddress', 'BillingAddress' => 'UserAddress', 'State'));
            $address = UserAddress::getInstanceByID('UserAddress', (int)$this->request->get('ID'), true, array('State'));

            $history = new OrderHistory($order, $this->user);

	        $address->address1->set($this->request->get('address1'));
	        $address->address2->set($this->request->get('address2'));
	        $address->city->set($this->request->get('city'));


	        if($this->request->get('stateID'))
	        {
	            $address->state->set(State::getInstanceByID((int)$this->request->get('stateID'), true));
	            $address->stateName->set(null);
	        }
	        else
	        {
	            $address->stateName->set($this->request->get('stateName'));
                $address->state->set(null);
                echo get_class($address->state->get());
	        }

	        $address->postalCode->set($this->request->get('postalCode'));
	        $address->countryID->set($this->request->get('countryID'));
	        $address->phone->set($this->request->get('phone'));
	        $address->companyName->set($this->request->get('companyName'));
	        $address->firstName->set($this->request->get('firstName'));
	        $address->lastName->set($this->request->get('lastName'));

	        $address->save();
	        $history->saveLog();

	        return new JSONResponse(array('address' => $address->toArray()), 'success', $this->translate('_order_address_was_successfully_updated'));
        }
        else
        {
            return new JSONResponse(array('errors' => $validator->getErrorList()), 'failure', $this->translate('_error_updating_order_address'));
        }
    }

	public function removeEmptyShipments()
	{
	    $order = CustomerOrder::getInstanceById((int)$this->request->get('id'), true, true);
	    $order->loadAll();

	    foreach($order->getShipments() as $shipment)
	    {
	        if(count($shipment->getItems()) == 0)
	        {
	            $shipment->delete();
	        }
	    }

        return new RawResponse();
	}

    public function printInvoice()
    {
        $order = CustomerOrder::getInstanceById($this->request->get('id'), CustomerOrder::LOAD_DATA, CustomerOrder::LOAD_REFERENCES);
        $order->loadAll();

        $this->setLayout('frontend');
        $this->loadLanguageFile('User');

        return new ActionResponse('order', $order->toArray(array('payments' => true)));
    }

	private function save(CustomerOrder $order)
	{
   		$validator = self::createOrderFormValidator();
		if ($validator->isValid())
		{
		    $existingRecord = $order->isExistingRecord();
			$order->save();

			return new JSONResponse(
			   array('order' => array( 'ID' => $order->getID())),
			   'success',
			   $this->translate($existingRecord ? '_order_status_has_been_successfully_changed' : '_new_order_has_been_successfully_created')
		    );
		}
		else
		{
		    return new JSONResponse(array('errors' => $validator->getErrorList()), 'failure', $this->translate('_error_updating_order_status'));
		}
	}

	private function createAddressString($addressArray)
	{
		if(!empty($addressArray['UserAddress']['fullName']))
		{
			$address[] = $addressArray['UserAddress']['fullName'];
		}

		if(!empty($addressArray['UserAddress']['countryName']))
		{
			$address[] = $addressArray['UserAddress']['countryName'];
		}

		if(!empty($addressArray['UserAddress']['stateName']))
		{
			$address[] = $addressArray['UserAddress']['stateName'];
		}

		if(!empty($addressArray['State']['code']))
		{
			$address[] = $addressArray['State']['code'];
		}

		if(!empty($addressArray['UserAddress']['city']))
		{
			$address[] = $addressArray['UserAddress']['city'];
		}

		return implode(', ', array_filter($address, array($this, 'filterAddress')));
	}

	private function filterAddress($item)
	{
		return trim($item);
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
	 * @return RequestValidator
	 */
    private function createUserAddressFormValidator()
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
	private function createUserAddressForm($addressArray = array())
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
    private function createOrderFormValidator()
    {
        $validator = new RequestValidator("CustomerOrder", $this->request);

		$validator->addCheck('status', new MinValueCheck($this->translate('_invalid_status'), 0));
		$validator->addCheck('status', new MaxValueCheck($this->translate('_invalid_status'), 4));

        return $validator;
    }

    /**
     * @return Form
     */
	private function createOrderForm($orderArray)
	{
		$form = new Form($this->createOrderFormValidator());
	    $form->setData($orderArray);

		return $form;
	}
}
?>
