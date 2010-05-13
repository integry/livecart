<?php

ClassLoader::import('application.controller.backend.abstract.ActiveGridController');
ClassLoader::import('application.controller.backend.*');
ClassLoader::import('application.model.order.*');
ClassLoader::import('application.model.Currency');
ClassLoader::import('application.helper.massAction.MassActionInterface');

/**
 * @package application.controller.backend
 * @author Integry Systems
 * @role order
 */
class CustomerOrderController extends ActiveGridController
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

	public function init()
	{
		parent::init();
		CustomerOrder::allowEmpty();
	}

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

	protected function getClassName()
	{
		return 'CustomerOrder';
	}

	protected function getReferencedData()
	{
		return array('User', 'Currency', 'ShippingAddress' => 'UserAddress', 'BillingAddress', 'State');
	}

	protected function getDefaultColumns()
	{
		return array('CustomerOrder.ID', 'CustomerOrder.invoiceNumber', 'User.fullName', 'User.email', 'CustomerOrder.dateCompleted', 'CustomerOrder.totalAmount', 'CustomerOrder.status', 'User.ID');
	}

	public function info()
	{
		$order = CustomerOrder::getInstanceById((int)$this->request->get('id'), true, array('ShippingAddress' => 'UserAddress', 'BillingAddress' => 'UserAddress', 'State', 'User', 'Currency'));
		$order->getSpecification();
		$order->loadAddresses();

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

			if (!$order->shippingAddress->get() && $order->user->get()->defaultShippingAddress->get())
			{
				$shippingStates = State::getStatesByCountry($order->user->get()->defaultShippingAddress->get()->userAddress->get()->countryID->get());
				$orderArray['ShippingAddress'] = $order->user->get()->defaultShippingAddress->get()->userAddress->get()->toArray();
			}

			$shippingStates[''] = '';

			if (!$order->billingAddress->get() && $order->user->get()->defaultBillingAddress->get())
			{
				$billingStates = State::getStatesByCountry($order->user->get()->defaultBillingAddress->get()->userAddress->get()->countryID->get());
				$orderArray['BillingAddress'] = $order->user->get()->defaultBillingAddress->get()->userAddress->get()->toArray();
			}

			$billingStates[''] = '';

			$response->set('shippingStates',  $shippingStates);
			$response->set('billingStates',  $billingStates);
		}

		$response->set('order', $orderArray);
		$response->set('form', $this->createOrderForm($orderArray));

		$user = $order->user->get();

		$response->setStatusCode(200);

		if (!$user)
		{
			return $response;
		}

		$addressOptions = array('' => '');
		$addressOptions['optgroup_0'] = $this->translate('_billing_addresses');
		$addresses = array();
		foreach($user->getBillingAddressArray() as $address)
		{
			$addressOptions[$address['ID']] = $this->createAddressString($address);
			$addresses[$address['ID']] = $address;
		}

		$addressOptions['optgroup_1'] = $this->translate('_shipping_addresses');
		foreach($user->getShippingAddressArray() as $address)
		{
			$addressOptions[$address['ID']] = $this->createAddressString($address);
			$addresses[$address['ID']] = $address;
		}

		$response->set('existingUserAddressOptions', $addressOptions);
		$response->set('existingUserAddresses', $addresses);

		foreach (array('ShippingAddress', 'BillingAddress') as $type)
		{
			$response->set('form' . $type, $this->createUserAddressForm(isset($orderArray[$type]) ? $orderArray[$type] : array(), $response));
		}

		$shipableShipmentsCount = 0;
		$hideShipped = 0;

		$hasDownloadable = false;
		foreach($order->getShipments() as $shipment)
		{
			if($shipment->isShipped()) continue;
			if(!$shipment->isShippable() && count($shipment->getItems()) > 0) continue;

			if($shipment->status->get() != Shipment::STATUS_SHIPPED && $shipment->isShippable())
			{
				$shipableShipmentsCount++;
			}

			$rate = unserialize($shipment->shippingServiceData->get());
			if((count($shipment->getItems()) == 0) || (!is_object($rate) && !$shipment->shippingService->get()))
			{
				$hideShipped = 1;
				break;
			}
		}

//		$response->set('hideShipped', $shipableShipmentsCount > 0 ? $hideShipped : 1);
		$response->set('hideShipped', false);
		$response->set('type', $this->getOrderType($order));

		// custom fields
		$form = $this->createFieldsForm($order);
		$order->getSpecification()->setFormResponse($response, $form);
		$response->set('fieldsForm', $form);

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
		if (!$this->request->isValueSet('id'))
		{
			$this->request->set('id', 1);
		}

		$response = new ActionResponse();
		$response->set("massForm", $this->getMassForm());
		$response->set("orderGroupID", $this->request->get('id'));

		if ($this->request->get('userOrderID'))
		{
			$order = CustomerOrder::getInstanceById($this->request->get('userOrderID'), true);
			$this->request->set('userID', $order->user->get()->getID());
		}

		if ($this->request->get('userID'))
		{
			$response->set('userID', $this->request->get('userID'));
		}

		$this->setGridResponse($response);
		$response->set("filters", ((int)$this->request->get('userID') ? array('filter_User.ID' => $this->request->get('userID')) : false));

		return $response;
	}

	/**
	 * @role update
	 */
	public function switchCancelled()
	{
		$order = CustomerOrder::getInstanceById((int)$this->request->get('id'), true, true);

		$history = new OrderHistory($order, $this->user);
		if ($order->isCancelled->get())
		{
			$order->restore();
		}
		else
		{
			$order->cancel();
		}

		$history->saveLog();

		$this->sendCancelNotifyEmail($order);

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
	 * @role update
	 */
	public function finalize()
	{
		$order = CustomerOrder::getInstanceById((int)$this->request->get('id'), true, true);
		$order->loadAll();

		$order->finalize();

		$url = $this->router->createUrl(array('controller' => 'backend.customerOrder', 'action' => 'index')) . '#order_' . $order->getID();
		return new RedirectResponse($url);
	}

	public function sendCancelNotifyEmail(CustomerOrder $order)
	{
		if ($order->isCancelled->get() && $this->config->get('EMAIL_ORDER_CANCELLATION'))
		{
			$order->user->get()->load();
			$email = new Email($this->application);
			$email->setUser($order->user->get());
			$email->setTemplate('order.cancel');
			$email->set('order', $order->toArray(array('payments' => true)));
			$email->send();
		}
	}

	public function sendStatusNotifyEmail(CustomerOrder $order)
	{
		if ($this->config->get('EMAIL_STATUS_UPDATE'))
		{
			$this->loadLanguageFile('Frontend');
			$this->application->loadLanguageFiles();
			$order->user->get()->load();
			$email = new Email($this->application);
			$email->setUser($order->user->get());
			$email->setTemplate('order.status');
			$email->set('order', $order->toArray(array('payments' => true)));
			$email->set('shipments', $order->getShipments()->toArray());
			$email->send();
		}
	}

	/**
	 * @role mass
	 */
	public function processMass()
	{
		ClassLoader::import('application.helper.massAction.OrderMassActionProcessor');
		ClassLoader::import('application.model.feed.ShipmentFeed');

		$filter = new ARSelectFilter();
		$grid = new ActiveGrid($this->application, $filter, 'CustomerOrder');
		$typeCond = $this->getTypeCondition($this->request->get('id'));
		$this->applyFullNameFilter($typeCond);
		$this->applyStateFilter($typeCond);
		$filter->mergeCondition($typeCond);

		if ('printLabels' == $this->request->get('act'))
		{
			$GLOBALS['filter'] = $filter;
			return new InternalRedirectResponse('backend.customerOrder', 'printLabels');
		}
		else
		{
			$mass = new OrderMassActionProcessor($grid, array('controller' => $this));
			$mass->setCompletionMessage($this->translate('_mass_action_succeed'));
			return $mass->process(CustomerOrder::LOAD_REFERENCES);
		}
	}

	public function printLabels()
	{
		$filter = $GLOBALS['filter'];

		// HAVING User.fullName >> causes problems
		$filter->setHavingCondition(eq(new ARExpressionHandle('1'), 1));

		return new ActionResponse('feed', new ShipmentFeed($filter, array('User')));
	}

	public function isMassCancelled()
	{
		ClassLoader::import('application.helper.massAction.OrderMassActionProcessor');

		return new JSONResponse(array('isCancelled' => OrderMassActionProcessor::isCancelled($this->request->get('pid'))));
	}

	public function changeColumns()
	{
		parent::changeColumns();

		return new ActionRedirectResponse('backend.customerOrder', 'orders', array('id' => $this->request->get('id')));
	}

	public function exportDetailed()
	{
		@set_time_limit(0);

		$this->loadLanguageFile('backend/Product');
		$this->loadLanguageFile('backend/User');

		// init file download
		header('Content-Disposition: attachment; filename="orderDetails.csv"');
		$out = fopen('php://output', 'w');

		// header row
		$columns = array_intersect_key($this->getAvailableColumns(), array_flip($this->getDefaultColumns()));
		$columns = array_merge(array('CustomerOrder.ID' => 'numeric'), $columns);
		$detailColumns = array(array('Product', 'sku'), array('Product', 'name_lang'), array('Product', 'Manufacturer', 'name'), array('OrderedItem', 'count'), array('OrderedItem', 'price'), array('OrderedItem', 'priceCurrencyID'), array('OrderedItem', 'itemSubtotal'), array('ShippingAddress', 'phone'), array('ShippingAddress', 'companyName'), array('ShippingAddress', 'address1'), array('ShippingAddress', 'address2'), array('ShippingAddress', 'city'), array('ShippingAddress', 'stateName'), array('ShippingAddress', 'postalCode'), array('ShippingAddress', 'countryName'));
		unset($columns['hiddenType']);

		foreach ($columns as $column => $type)
		{
			$header[] = $this->translate($column);
		}

		foreach ($detailColumns as $column)
		{
			$cnt = count($column);
			$field = $column[$cnt - 2] . '.' . $column[$cnt - 1];
			if (substr($field, -5) == '_lang')
			{
				$field = substr($field, 0, -5);
			}
			$header[] = $this->translate($field);
		}

		fputcsv($out, $header);

		// find ID column index
		$index = -1;
		foreach ($columns as $col => $type)
		{
			$index++;
			if ('CustomerOrder.ID' == $col)
			{
				break;
			}
		}

		// collect order ID's
		$ids = array();
		foreach ($this->lists(true, array('CustomerOrder.ID' => 'numeric')) as $row)
		{
			$ids[] = $row[0];
		}

		// fetch detailed data
		$f = new ARSelectFilter(new INCond(new ARFieldHandle('OrderedItem', 'customerOrderID'), $ids));
		$data = array();
		foreach (ActiveRecordModel::getRecordSetArray('OrderedItem', $f, array('Product', 'CustomerOrder', 'Manufacturer', 'ShippingAddress' => 'UserAddress')) as $row)
		{
			$data[$row['customerOrderID']][] = $row;
		}

		// columns
		$index = 0;
		foreach ($this->lists(true, $columns) as $row)
		{
			foreach ((array)$data[$row[$index]] as $item)
			{
				$itemData = $row;
				$item['OrderedItem'] =& $item;
				foreach ($detailColumns as $column)
				{
					$value = $this->getColumnValue($item, $column[0], $column[1]);
					if (is_array($value))
					{
						if (isset($column[2]) && isset($value[$column[2]]))
						{
							$value = $value[$column[2]];
						}
						else
						{
							$value = '';
						}
					}

					$itemData[] = $value;
				}

				fputcsv($out, $itemData);
			}
		}

		exit;
	}

	protected function getSelectFilter()
	{
		$filter = parent::getSelectFilter();

		$id = $this->request->get('id');
		if (!is_numeric($id))
		{
			list ($foo, $id) = explode('_', $this->request->get('id'));
		}
		$cond = $this->getTypeCondition($id);

		$this->applyFullNameFilter($cond);
		$this->applyStateFilter($cond);

		if($this->request->get('sort_col') == 'User.fullName')
		{
			$this->request->remove('sort_col');

			$direction = ($this->request->get('sort_dir') == 'DESC') ? ARSelectFilter::ORDER_DESC : ARSelectFilter::ORDER_ASC;

			$filter->setOrder(new ARFieldHandle("User", "lastName"), $direction);
			$filter->setOrder(new ARFieldHandle("User", "firstName"), $direction);
		}

		if ($this->request->get('userID'))
		{
			$cond->addAND(new EqualsCond(new ARFieldHandle('CustomerOrder', 'userID'), $this->request->get('userID')));
		}

		$filter->setCondition($cond);

		return $filter;
	}

	protected function setDefaultSortOrder(ARSelectFilter $filter)
	{
		$filter->setOrder(new ARFieldHandle($this->getClassName(), 'dateCompleted'), 'DESC');
		$filter->setOrder(new ARFieldHandle($this->getClassName(), 'ID'), 'DESC');
	}

	public function processDataArray($orders, $displayedColumns)
	{
		$orders = parent::processDataArray($orders, $displayedColumns);

		foreach ($orders as &$order)
		{
			foreach ($order as $field => &$value)
			{
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
						$value .= ' ' . $order['Currency']['ID'];
					}
				}

				if('dateCompleted' == $field && !$value)
				{
					$value = '-';
				}
			}
		}

		return $orders;
	}

	protected function getCSVFileName()
	{
		return 'orders.csv';
	}

	protected function getColumnValue($record, $class, $field)
	{
		if ('stateName' == $field)
		{
			if (isset($record['ShippingAddress']['State']['name']))
			{
				return $record['ShippingAddress']['State']['name'];
			}
			else
			{
				return $record['ShippingAddress']['stateName'];
			}
		}
		else
		{
			return parent::getColumnValue($record, $class, $field);
		}
	}

	private function applyFullNameFilter(Condition $cond)
	{
		$filters = $this->request->get('filters');
		if (!is_array($filters))
		{
			$filters = (array)json_decode($filters);
		}

		if (isset($filters['User.fullName']))
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

	private function applyStateFilter(Condition $cond)
	{
		$filters = $this->request->get('filters');
		if (!is_array($filters))
		{
			$filters = (array)json_decode($filters);
		}

		if (isset($filters['ShippingAddress.stateName']))
		{
			$value = $filters['ShippingAddress.stateName'];

			$c = new LikeCond(new ARFieldHandle('UserAddress', "stateName"), '%' . $value . '%');
			$c->addOR(new LikeCond(new ARFieldHandle('State', "name"), '%' . $value . '%'));

			$cond->addAND($c);
			unset($filters['ShippingAddress.stateName']);
			$this->request->set('filters', $filters);
		}
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
				$cond2 = new EqualsCond(new ARFieldHandle('CustomerOrder', "status"), CustomerOrder::STATUS_PROCESSING);

				// @todo fix NEW status = NULL bug
				$cond2->addOR(new IsNullCond(new ARFieldHandle('CustomerOrder', "status")));

				$cond2->addOR(new EqualsCond(new ARFieldHandle('CustomerOrder', "status"), CustomerOrder::STATUS_AWAITING));
				$cond2->addOR(new EqualsCond(new ARFieldHandle('CustomerOrder', "status"), CustomerOrder::STATUS_NEW));
				$cond->addAND($cond2);
				break;
			case self::TYPE_NEW:
				$cond = new EqualsCond(new ARFieldHandle('CustomerOrder', "isFinalized"), 1);
				$cond2 = new EqualsCond(new ARFieldHandle('CustomerOrder', "status"), CustomerOrder::STATUS_NEW);

				// @todo fix NEW status = NULL bug
				$cond2->addOR(new IsNullCond(new ARFieldHandle('CustomerOrder', "status")));
				$cond->addAND($cond2);
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
	public function saveFields()
	{
		$order = CustomerOrder::getInstanceByID($this->request->get('id'), true);
		$order->loadAll();
		if ($this->createFieldsFormValidator($order)->isValid())
		{
			$order->loadRequestData($this->request);
			$order->save(true);

			$response = new ActionResponse('order', $order->toArray());
			$form = $this->createFieldsForm($order);
			$order->getSpecification()->setFormResponse($response, $form);
			$response->set('fieldsForm', $form);
			return $response;
		}
	}

	/**
	 * @role update
	 */
	public function update()
	{
		$order = CustomerOrder::getInstanceByID((int)$this->request->get('ID'), true);
		$order->loadAll();
		$history = new OrderHistory($order, $this->user);

		$oldStatus = $order->status->get();

		$status = (int)$this->request->get('status');
		$order->status->set($status);
		//$isCancelled = (int)$this->request->get('isCancelled') ? true : false;
		//$order->isCancelled->set($isCancelled);

		$order->updateShipmentStatuses();
		$response = $this->save($order);
		$history->saveLog();

		$this->sendStatusNotifyEmail($order);

		return $response;
	}

	/**
	 * @role update
	 */
	public function setMultiAddress()
	{
		$order = CustomerOrder::getInstanceByID((int)$this->request->get('id'), true);
		$order->isMultiAddress->set($this->request->get('status'));
		$order->save(true);
	}

	/**
	 * @role create
	 */
	public function create()
	{
		ActiveRecord::beginTransaction();
		$user = User::getInstanceByID((int)$this->request->get('customerID'), true, true);
		$user->loadAddresses();

		$order = CustomerOrder::getNewInstance($user);
		$status = CustomerOrder::STATUS_NEW;
		$order->status->set($status);
		$order->isFinalized->set(0);
		$order->capturedAmount->set(0);
		$order->totalAmount->set(0);
		$order->dateCompleted->set(new ARSerializableDateTime());
		$order->currency->set($this->application->getDefaultCurrency());

		foreach (array('billingAddress' => 'defaultBillingAddress', 'shippingAddress' => 'defaultShippingAddress') as $orderField => $userField)
		{
			if($user->$userField->get())
			{
				$user->$userField->get()->userAddress->get()->load();
				$address = clone $user->$userField->get()->userAddress->get();
				$address->save();
				$order->$orderField->set($address);
			}
		}

		$response = $this->save($order);

		ActiveRecord::commit();
		return $response;
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
			$address->loadRequestData($this->request);
			$address->save();
			$history->saveLog();

			if (!$this->request->get('ID'))
			{
				if (!$order->billingAddress->get())
				{
					$order->billingAddress->set($address);
				}

				if (!$order->shippingAddress->get())
				{
					$order->shippingAddress->set($address);
				}

				$order->save();
			}

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

		$order->updateStatusFromShipments();
		$order->save(true);

		return new RawResponse();
	}

	public function printInvoice()
	{
		$this->application->setTheme('');
		$order = CustomerOrder::getInstanceById($this->request->get('id'), CustomerOrder::LOAD_DATA, CustomerOrder::LOAD_REFERENCES);
		$order->loadAll();

		if ($order->user->get())
		{
			$order->user->get()->getSpecification();
		}

		$this->setLayout('frontend');
		$this->loadLanguageFile('Frontend');
		$this->loadLanguageFile('User');

		return new ActionResponse('order', $order->toArray(array('payments' => true)));
	}

	private function save(CustomerOrder $order)
	{
   		$validator = self::createOrderFormValidator();
		if ($validator->isValid())
		{
			$existingRecord = $order->isExistingRecord();
			$order->save(true);

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

	public function getAvailableColumns()
	{
		// get available columns
		$availableColumns = parent::getAvailableColumns();

		unset($availableColumns['CustomerOrder.shipping']);
		unset($availableColumns['CustomerOrder.isFinalized']);

		return $availableColumns;
	}

	protected function getCustomColumns()
	{
		$availableColumns['User.email'] = 'text';
		$availableColumns['User.ID'] = 'text';
		$availableColumns['User.fullName'] = 'text';

		$availableColumns['CustomerOrder.status'] = 'text';

		// Shipping address
		$availableColumns['ShippingAddress.firstName'] = 'text';
		$availableColumns['ShippingAddress.lastName'] = 'text';
		$availableColumns['ShippingAddress.countryID'] = 'text';
		$availableColumns['ShippingAddress.stateName'] = 'text';
		$availableColumns['ShippingAddress.city'] = 'text';
		$availableColumns['ShippingAddress.address1'] = 'text';
		$availableColumns['ShippingAddress.postalCode'] = 'text';
		$availableColumns['ShippingAddress.phone'] = 'text';

		// User
		$availableColumns['User.firstName'] = 'text';
		$availableColumns['User.lastName'] = 'text';
		$availableColumns['User.companyName'] = 'text';

		return $availableColumns;
	}

	protected function getMassForm()
	{
		$validator = $this->getValidator("OrdersMassFormValidator", $this->request);

		return new Form($validator);
	}

	/**
	 * @return RequestValidator
	 */
	public function createUserAddressFormValidator()
	{
		$validator = $this->getValidator("userAddress", $this->request);

		$validator->addCheck('countryID', new IsNotEmptyCheck($this->translate('_country_empty')));
		$validator->addCheck('city',	  new IsNotEmptyCheck($this->translate('_city_empty')));
		$validator->addCheck('address1',  new IsNotEmptyCheck($this->translate('_address_empty')));
		$validator->addCheck('firstName', new IsNotEmptyCheck($this->translate('_first_name_is_empty')));
		$validator->addCheck('lastName',  new IsNotEmptyCheck($this->translate('_last_name_is_empty')));

		UserAddress::getNewInstance()->getSpecification()->setValidation($validator);

		return $validator;
	}

	/**
	 * @return Form
	 */
	public function createUserAddressForm($addressArray = array(), ActionResponse $response)
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

		$address = !empty($addressArray['ID']) ? ActiveRecordModel::getInstanceByID('UserAddress', $addressArray['ID'], true) : UserAddress::getNewInstance();
		$address->getSpecification()->setFormResponse($response, $form);

		return $form;
	}

	/**
	 * @return RequestValidator
	 */
	private function createOrderFormValidator()
	{
		$validator = $this->getValidator("CustomerOrder", $this->request);

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

	/**
	 * @return RequestValidator
	 */
	private function createFieldsFormValidator(CustomerOrder $order)
	{
		$validator = $this->getValidator("CustomerOrderFields", $this->request);
		$order->getSpecification()->setValidation($validator);
		return $validator;
	}

	/**
	 * @return Form
	 */
	private function createFieldsForm(CustomerOrder $order)
	{
		return new Form($this->createFieldsFormValidator($order));
	}
}
?>