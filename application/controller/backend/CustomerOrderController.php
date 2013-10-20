<?php




/**
 * @package application/controller/backend
 * @author Integry Systems
 * @role order
 */
class CustomerOrderController extends ActiveGridController
{
	const TYPE_ROOT = 0;

	const TYPE_ALL = 1;
	const TYPE_CURRENT = 2;
	const TYPE_NEW = 3;
	const TYPE_PROCESSING = 4;
	const TYPE_AWAITING = 5;
	const TYPE_SHIPPED = 6;
	const TYPE_RETURNED = 7;
	const TYPE_CARTS = 8;
	const TYPE_CANCELLED = 9;

	const TYPE_RECURRING = 10;
	const TYPE_RECURRING_ALL = 11;
	const TYPE_RECURRING_EXPIRED = 12;
	const TYPE_RECURRING_CANCELLED = 13;
	const TYPE_RECURRING_WITH_PARENT = 14; // isRecurring = 1 and parentID is not null

	private $extraDisplayedColumns = array();

	public function initialize()
	{
		parent::initialize();
		CustomerOrder::allowEmpty();
	}

	/**
	 * Action shows filters and datagrid.
	 */
	public function indexAction()
	{
		$orderGroups = array(
			array('ID' => self::TYPE_ALL, 'rootID' => self::TYPE_ROOT, 'name' => $this->translate('_all_orders')),
				array('ID' => self::TYPE_CURRENT, 'name' => $this->translate('_current_orders'), 'rootID' => self::TYPE_ALL),
					array('ID' => self::TYPE_NEW, 'name' => $this->translate('_new_orders'), 'rootID' => self::TYPE_CURRENT),
					array('ID' => self::TYPE_PROCESSING, 'name' => $this->translate('_processing_orders'), 'rootID' => self::TYPE_CURRENT),
					array('ID' => self::TYPE_AWAITING, 'name' => $this->translate('_awaiting_shipment_orders'), 'rootID' => self::TYPE_CURRENT));

		if (CustomerOrder::hasRecurringorderBy())
		{
			$orderGroups = array_merge($orderGroups, array(
				array('ID' => self::TYPE_RECURRING, 'rootID' => self::TYPE_ALL, 'name' => $this->translate('_recurring')),
				array('ID' => self::TYPE_RECURRING_ALL, 'rootID' => self::TYPE_RECURRING, 'name' => $this->translate('_recurring_all')),
				array('ID' => self::TYPE_RECURRING_EXPIRED, 'rootID' => self::TYPE_RECURRING, 'name' => $this->translate('_recurring_expired')),
				array('ID' => self::TYPE_RECURRING_CANCELLED, 'rootID' => self::TYPE_RECURRING, 'name' => $this->translate('_recurring_canceled')),
			));
		}

		$orderGroups = array_merge($orderGroups, array(
				array('ID' => self::TYPE_SHIPPED, 'name' => $this->translate('_shipped_orders'), 'rootID' => self::TYPE_ALL),
				array('ID' => self::TYPE_RETURNED, 'name' => $this->translate('_returned_orders'), 'rootID' => self::TYPE_ALL),
				array('ID' => self::TYPE_CANCELLED, 'name' => $this->translate('_cancelled_orders'), 'rootID' => self::TYPE_ALL),
			array('ID' => self::TYPE_CARTS, 'name' => $this->translate('_shopping_carts'), 'rootID' => self::TYPE_ROOT),
		));

		$this->set('orderGroups', $orderGroups);
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
		return array(
			'CustomerOrder.ID',
			'CustomerOrder.invoiceNumber',
			'User.fullName',
			'User.email',
			'CustomerOrder.dateCompleted',
			'CustomerOrder.totalAmount',
			'CustomerOrder.status',
			'User.ID'
		);
	}

	public function assignStatusesAction(ActionResponse $response)
	{

		$this->set('statuses', array(
										CustomerOrder::STATUS_NEW => $this->translate('_status_new'),
										CustomerOrder::STATUS_PROCESSING  => $this->translate('_status_processing'),
										CustomerOrder::STATUS_AWAITING  => $this->translate('_status_awaiting'),
										CustomerOrder::STATUS_SHIPPED  => $this->translate('_status_shipped'),
										CustomerOrder::STATUS_RETURNED  => $this->translate('_status_returned'),
						));
	}

	public function infoAction()
	{
		$this->loadLanguageFile('backend/Shipment');
		$order = CustomerOrder::getInstanceById((int)$this->request->get('id'), true, array('User', 'Currency'));
		$order->getSpecification();
		$order->loadAll();
		$this->removeEmptyShipments($order);



		$this->assignStatuses($response);
		$this->set('countries', $this->application->getEnabledCountries());

		$orderArray = $order->toArray();
		if($order->isFinalized)
		{
			if($billingAddress = $order->billingAddress)
			{
				$billingAddress->load(true);
				$orderArray['BillingAddress'] = $billingAddress->toArray();
			}
			if($shippingAddress = $order->shippingAddress)
			{
				$shippingAddress->load(true);
				$orderArray['ShippingAddress'] = $shippingAddress->toArray();
			}

			if($order->billingAddress)
			{
				$billingStates = State::getStatesByCountry($order->billingAddress->countryID);
				$billingStates[''] = '';
				asort($billingStates);
				$this->set('billingStates',  $billingStates);
			}

			if($order->shippingAddress)
			{
				$shippingStates = State::getStatesByCountry($order->shippingAddress->countryID);
				$shippingStates[''] = '';
				asort($shippingStates);
				$this->set('shippingStates',  $shippingStates);
			}
		}
		elseif ($order->user)
		{
			$order->user->loadAddresses();

			if (!$order->shippingAddress && $order->user->defaultShippingAddress)
			{
				$shippingStates = State::getStatesByCountry($order->user->defaultShippingAddress->userAddress->countryID);
				$orderArray['ShippingAddress'] = $order->user->defaultShippingAddress->userAddress->toArray();
			}

			$shippingStates[''] = '';

			if (!$order->billingAddress && $order->user->defaultBillingAddress)
			{
				$billingStates = State::getStatesByCountry($order->user->defaultBillingAddress->userAddress->countryID);
				$orderArray['BillingAddress'] = $order->user->defaultBillingAddress->userAddress->toArray();
			}

			$billingStates[''] = '';

			$this->set('shippingStates',  $shippingStates);
			$this->set('billingStates',  $billingStates);
		}

		$this->set('order', $orderArray);
		$this->set('form', $this->createOrderForm($orderArray));

		$user = $order->user;

		$response->setStatusCode(200);

		if ($user)
		{
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

			$this->set('existingUserAddressOptions', $addressOptions);
			$this->set('existingUserAddresses', $addresses);
		}

		foreach (array('ShippingAddress', 'BillingAddress') as $type)
		{
			$this->set('form' . $type, $this->createUserAddressForm(isset($orderArray[$type]) ? $orderArray[$type] : array(), $response));
		}

		$shipableShipmentsCount = 0;
		$hideShipped = 0;

		$hasDownloadable = false;
		foreach($order->getShipments() as $shipment)
		{
			if($shipment->isShipped()) continue;
			if(!$shipment->isShippable() && count($shipment->getItems()) > 0) continue;

			if($shipment->status != Shipment::STATUS_SHIPPED && $shipment->isShippable())
			{
				$shipableShipmentsCount++;
			}

			$rate = unserialize($shipment->shippingServiceData);
			if((count($shipment->getItems()) == 0) || (!is_object($rate) && !$shipment->shippingService))
			{
				$hideShipped = 1;
				break;
			}
		}
		BackendToolbarItem::registerLastViewedorderBy($order);
//		$this->set('hideShipped', $shipableShipmentsCount > 0 ? $hideShipped : 1);
		$this->set('hideShipped', false);
		$this->set('type', $this->getOrderType($order));

		// custom fields
		$form = $this->createFieldsForm($order);
		$order->getSpecification()->setFormResponse($response, $form);
		$this->set('fieldsForm', $form);

		$this->appendCalendarForm($response);
		return $this->shipmentInfo($response);
	}

	private function createShipmentForm()
	{
		return new Form($this->createShipmentFormValidator());
	}

	private function createShipmentFormValidator()
	{
		$validator = $this->getValidator('shippingService', $this->request);
		return $validator;
	}

	protected function getDownloadCounts($itemIDs)
	{
		if (!$itemIDs)
		{
			return array();
		}
		$sql = 'SELECT orderedItemID, SUM(timesDownloaded) AS cnt FROM OrderedFile WHERE orderedItemID IN (' . implode(',', $itemIDs) . ') GROUP BY orderedItemID';
		$out = array();
		foreach (ActiveRecordModel::getDataBySQL($sql) as $item)
		{
			$out[$item['orderedItemID']] = $item['cnt'];
		}

		return $out;
	}

	private function shipmentInfo($response)
	{
		$order = CustomerOrder::getInstanceById($this->request->get('id'), true, true);
		$order->loadAll();
		$order->getCoupons();

		$products = ARSet::buildFromArray($order->getOrderedItems())->extractReferencedItemSet('product', 'ProductSet');
		$variations = $products->getVariationMatrix();

		$form = $this->createShipmentForm();
		$form->setData(array('orderID' => $order->getID()));
		$shipments = $order->getShipments();

		$zone = $order->getDeliveryZone();
		$taxZone = $order->getTaxZone();

		$statuses = array(
			Shipment::STATUS_NEW => $this->translate('_shipping_status_new'),
			Shipment::STATUS_PROCESSING => $this->translate('_shipping_status_pending'),
			Shipment::STATUS_AWAITING => $this->translate('_shipping_status_awaiting'),
			Shipment::STATUS_SHIPPED => $this->translate('_shipping_status_shipped'),
			Shipment::STATUS_RETURNED => $this->translate('_shipping_status_returned')
		);

		$subtotalAmount = 0;
		$shippingAmount = 0;
		$taxAmount = 0;
		$itemIDs = $shipmentsArray = array();

		$shipableShipmentsCount = 0;
		foreach($shipments as $shipment)
		{
			$subtotalAmount += $shipment->amount;
			$shippingAmount += $shipment->shippingAmount;
			$taxAmount += $shipment->taxAmount;

			$shipmentsArray[$shipment->getID()] = $shipment->toArray();

			$rate = unserialize($shipment->shippingServiceData);
			if(is_object($rate))
			{
				$rate->setApplication($this->application);
				$shipmentsArray[$shipment->getID()] = array_merge($shipmentsArray[$shipment->getID()], $rate->toArray());
				if (isset($shipmentsArray[$shipment->getID()]['serviceID']))
				{
					$shipmentsArray[$shipment->getID()]['ShippingService']['ID'] = $shipmentsArray[$shipment->getID()]['serviceID'];
				}
			}
			else if($shipment->shippingService)
			{
				$shipmentsArray[$shipment->getID()]['ShippingService']['name_lang'] = $shipmentsArray[$shipment->getID()]['ShippingService']['name'];
			}
			else
			{
				$shipmentsArray[$shipment->getID()]['ShippingService']['name_lang'] = $this->translate('_shipping_service_is_not_selected');
			}

			if($shipment->status != Shipment::STATUS_SHIPPED && $shipment->isShippable())
			{
				$shipableShipmentsCount++;
			}

			foreach ($shipment->getItems() as $item)
			{
				$itemIDs[] = $item->getID();
			}
		}

		$totalAmount = $subtotalAmount + $shippingAmount + $taxAmount;

		//
		$this->set('orderID', $this->request->get('id'));
		$this->set('order', $order->toArray());
		$this->set('shippingServiceIsNotSelected', $this->translate('_shipping_service_is_not_selected'));
		$this->set('shipments', $shipmentsArray);
		$this->set('subtotalAmount', $subtotalAmount);
		$this->set('shippingAmount', $shippingAmount);
		$this->set('variations', $variations);

		if ($downloadable = $order->getDownloadShipment(false))
		{

			$this->set('downloadableShipment', $downloadable->toArray());
		}

		$this->set('taxAmount', $taxAmount);
		$this->set('totalAmount', $totalAmount);
		$this->set('shipableShipmentsCount', $shipableShipmentsCount);
		$this->set('statuses', $statuses + array(-1 => $this->translate('_delete')));

		unset($statuses[3]);
		$this->set('statusesWithoutShipped', $statuses);
		$this->set('newShipmentForm', $form);
		$this->set('downloadCount', $this->getDownloadCounts($itemIDs));

		// load product options
		$this->set('allOptions', ProductOption::loadOptionsForProductSet($products));

	}


	public function updateDateAction()
	{
		$request = $this->getRequest();

		$validator = $this->getDateCompletedValidator($request);
		if($validator->isValid() == false)
		{
			$errors = $validator->getErrorList();
			return new JSONResponse(array('errors'=>$errors), 'validationError');
		}

		$order = CustomerOrder::getInstanceById($request->get('orderID'));
		$newDate =  $request->get('dateCompleted');
		if(strpos($newDate, ':') === false)
		{
			list($oldDate, $oldTime) = explode(' ',(string)$order->dateCompleted);
			$newDate .= ' '.$oldTime;
		}
		$order->dateCompleted->set($newDate);
		$order->save();
		$order->reload();
		return new JSONResponse(array('date'=>(string)$order->dateCompleted), 'saved');
	}

	private function appendCalendarForm($response)
	{
		$dateForm = new Form($this->getDateCompletedValidator($this->getRequest()));
		$order = $response->get('order');
		foreach(array('dateCompleted', 'dateCreated') as $key)
		{
			if(isset($order[$key]))
			{
				$dateForm->set($key, $order[$key]);
			}
		}
		$this->set('dateForm', $dateForm);
	}

	private function getDateCompletedValidator(\Phalcon\Http\Request $request)
	{
		$validator = $this->getValidator('dateCreatedValidator', $request);
		$validator->add('dateCompleted', new Validator\PresenceOf(array('message' => $this->translate('_err_date_cannot_be_empty'))));
		$validator->add('dateCompleted', new RegExpCheck($this->translate('_err_unknown_date_format'), '/^\d{4}\-\d{1,2}\-\d{1,2}$/'));

		return $validator;
	}



	private function getOrderType(CustomerOrder $order)
	{
		if (!$order->isFinalized)
		{
			return self::TYPE_CARTS;
		}
		else if ($order->isCancelled)
		{
			return self::TYPE_CANCELLED;
		}
		else
		{
			switch ($order->status)
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

	public function selectCustomerAction()
	{
		$userGroups = array();
		$userGroups[] = array('ID' => -2, 'name' => $this->translate('_all_users'), 'rootID' => 0);
		$userGroups[] = array('ID' => -1, 'name' => $this->translate('_default_user_group'), 'rootID' => -2);

		foreach(UserGroup::getRecordSet(new ARSelectFilter())->toArray() as $group)
		{
			$userGroups[] = array('ID' => $group['ID'], 'name' => $group['name'], 'rootID' => -2);
		}

		$this->set('userGroups', $userGroups);
	}

	public function ordersAction()
	{
		if (!$this->request->has('id'))
		{
			$this->request->set('id', 1);
		}


		$this->set("massForm", $this->getMassForm());
		$this->set("orderGroupID", $this->request->get('id'));

		if ($this->request->get('userOrderID'))
		{
			$order = CustomerOrder::getInstanceById($this->request->get('userOrderID'), true);
			$this->request->set('userID', $order->user->getID());
		}

		if ($this->request->get('userID'))
		{
			$this->set('userID', $this->request->get('userID'));
		}

		$this->setGridResponse($response);
		$this->set("filters", ((int)$this->request->get('userID') ? array('filter_User.ID' => $this->request->get('userID')) : false));

	}

	/**
	 * @role update
	 */
	public function switchCancelledAction()
	{
		$order = CustomerOrder::getInstanceById((int)$this->request->get('id'), true, true);

		$history = new OrderHistory($order, $this->user);
		if ($order->isCancelled)
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
				'isCanceled' => $order->isCancelled,
				'linkValue' => $this->translate($order->isCancelled ? '_accept_order' : '_cancel_order'),
				'value' => $this->translate($order->isCancelled ? '_canceled' : '_accepted')
			),
			'success',
			$this->translate($order->isCancelled ? '_order_is_canceled' : '_order_is_accepted')
		);
	}

	/**
	 * @role update
	 */
	public function finalizeAction()
	{
		$order = CustomerOrder::getInstanceById((int)$this->request->get('id'), true, true);
		$order->loadAll();

		$order->finalize();

		$url = $this->url->get('backend.customerOrder/index') . '#order_' . $order->getID();
		return new RedirectResponse($url);
	}

	public function sendCancelNotifyEmailAction(CustomerOrder $order)
	{
		if ($order->isCancelled && $this->config->get('EMAIL_ORDER_CANCELLATION'))
		{
			$order->user->load();
			$email = new Email($this->application);
			$email->setUser($order->user);
			$email->setTemplate('order.cancel');
			$email->set('order', $order->toArray(array('payments' => true)));
			$email->send();
		}
	}

	public function sendStatusNotifyEmailAction(CustomerOrder $order)
	{
		$status = $order->status;
		$enabledStatuses = $this->config->get('EMAIL_STATUS_UPDATE_STATUSES');
		$m = array(
			'EMAIL_STATUS_UPDATE_NEW'=>CustomerOrder::STATUS_NEW,
			'EMAIL_STATUS_UPDATE_PROCESSING'=>CustomerOrder::STATUS_PROCESSING,
			'EMAIL_STATUS_UPDATE_AWAITING_SHIPMENT'=>CustomerOrder::STATUS_AWAITING,
			'EMAIL_STATUS_UPDATE_SHIPPED'=> CustomerOrder::STATUS_SHIPPED
		);

		$sendEmail = false;
		foreach($m as $configKey => $constValue)
		{
			if($status == $constValue && array_key_exists($configKey, $enabledStatuses))
			{
				$sendEmail = true;
			}
		}

		if ($this->config->get('EMAIL_STATUS_UPDATE') || $sendEmail)
		{
			$this->loadLanguageFile('Frontend');
			$this->application->loadLanguageFiles();
			$order->user->load();
			$email = new Email($this->application);
			$email->setUser($order->user);
			$email->setTemplate('order.status');
			$email->set('order', $order->toArray(array('payments' => true)));
			$email->set('shipments', $order->getShipments()->toArray());
			$email->send();
		}
	}

	/**
	 * @role mass
	 */
	public function processMassAction()
	{

		$filter = new ARSelectFilter();
		$grid = new ActiveGrid($this->application, $filter, 'CustomerOrder', $this->getHavingClauseColumnTypes());
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

	public function getHavingClauseColumnTypesAction()
	{
		return array_merge($this->getAvailableColumns(), $this->getAdvancedSearchFields());
	}

	public function printLabelsAction()
	{

		if (isset($GLOBALS['filter']) && ($filter = $GLOBALS['filter']))
		{
			// HAVING User.fullName >> causes problems
			$filter->setHavingCondition(eq(new ARExpressionHandle('1'), 1));
		}
		else
		{
			$filter = select(eq('CustomerOrder.ID', $this->request->get('id')));
		}

		return new ActionResponse('feed', new ShipmentFeed($filter, array('User')));
	}

	public function isMassCancelledAction()
	{

		return new JSONResponse(array('isCancelled' => OrderMassActionProcessor::isCancelled($this->request->get('pid'))));
	}

	public function changeColumnsAction()
	{
		parent::changeColumns();

		return new ActionRedirectResponse('backend.customerOrder', 'orders', array('id' => $this->request->get('id')));
	}

	public function exportDetailedAction()
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

		$filters = $this->request->get('filters');
		$displayedColumns = $this->getDisplayedColumns();
		$columns = array_merge(array_keys(is_array($filters) ? $filters : array()), array_keys(is_array($displayedColumns) ? $displayedColumns : array()));

		if(in_array('ProductCount',$columns))
		{
			$filter->addField('(SELECT sum(count) AS ProductCount FROM OrderedItem AS oi WHERE oi.customerOrderID = CustomerOrder.ID)', '', 'ProductCount');
		}
		if(in_array('UniqueProductCount', $columns))
		{
			$filter->addField('(SELECT count(*) AS UniqueProductCount FROM OrderedItem AS oi WHERE oi.customerOrderID = CustomerOrder.ID)', '', 'UniqueProductCount');
		}

		if(in_array('HasUsedCoupon', $columns))
		{
			$filter->addField('(
				SELECT
					IF(COUNT(*),1,0) AS HasUsedCoupon
				FROM
					OrderCoupon AS oc
				WHERE
					oc.orderID = CustomerOrder.ID
			)', '', 'HasUsedCoupon');
		}

		if(in_array('UsedCouponCount', $columns))
		{
			$filter->addField('(SELECT count(*) AS UsedCoupon FROM OrderCoupon AS oc WHERE oc.orderID = CustomerOrder.ID)', '', 'UsedCouponCount');
		}

		if(in_array('ProductSKU', $columns) && !empty($filters['ProductSKU']))
		{
			$filter->addField('(SELECT 0x'.bin2hex($filters['ProductSKU']).' AS ProductSKU FROM OrderedItem AS oi INNER JOIN Product pr on pr.ID=oi.productID AND pr.sku LIKE 0x'.bin2hex('%'.$filters['ProductSKU'].'%').' WHERE oi.customerOrderID = CustomerOrder.ID Limit 1)', '', 'ProductSKU');
		}

		if(in_array('ProductName', $columns) && !empty($filters['ProductName']))
		{
			$filter->addField('(SELECT 0x'.bin2hex($filters['ProductName']).' AS ProductSKU FROM OrderedItem AS oi INNER JOIN Product pr on pr.ID=oi.productID AND pr.name LIKE 0x'.bin2hex('%'.$filters['ProductName'].'%').' WHERE oi.customerOrderID = CustomerOrder.ID Limit 1)', '', 'ProductName');
		}

		if(in_array('Manufacturer', $columns) && !empty($filters['Manufacturer']))
		{
			$filter->addField('(
				SELECT
					0x'.bin2hex($filters['Manufacturer']).' AS Manufacturer
				FROM
					OrderedItem AS oi
					INNER JOIN Product pr on pr.ID=oi.productID
					INNER JOIN Manufacturer mf on mf.ID=pr.manufacturerID AND mf.name LIKE 0x'.bin2hex('%'.$filters['Manufacturer'].'%').'
				WHERE
					oi.customerOrderID = CustomerOrder.ID Limit 1
			)', '', 'Manufacturer');
		}
		if(in_array('UsedCouponCode', $columns) && !empty($filters['UsedCouponCode']))
		{
			$filter->addField('(
				SELECT
					0x'.bin2hex($filters['UsedCouponCode']).' AS UsedCoupon
				FROM
					OrderCoupon AS oc
				WHERE
					oc.couponCode LIKE 0x'.bin2hex('%'.$filters['UsedCouponCode'].'%').'
					AND oc.orderID = CustomerOrder.ID
				)', '', 'UsedCouponCode');
		}

		if(in_array('ProductOption', $columns) && !empty($filters['ProductOption']))
		{
			$filter->addField('(
			SELECT
				0x'.bin2hex($filters['ProductOption']).' AS ProductOption
			FROM
				OrderedItemOption
				LEFT JOIN OrderedItem ON OrderedItem.ID=OrderedItemOption.orderedItemID
				LEFT JOIN ProductOptionChoice ON OrderedItemOption.choiceID=ProductOptionChoice.ID
			WHERE
				OrderedItem.customerOrderID=CustomerOrder.ID
				AND (
					(OrderedItemOption.optionText LIKE 0x'.bin2hex('%'.$filters['ProductOption'].'%').')
						OR
					(ProductOptionChoice.name LIKE 0x'.bin2hex('%'.$filters['ProductOption'].'%').' )
				)
			)', '', 'ProductOption');
		}

		if(in_array('OrderMessageText', $columns) && !empty($filters['OrderMessageText']))
		{
			$filter->addField('(
				SELECT
					0x'.bin2hex($filters['OrderMessageText']).' AS OrderMessageText
				FROM
					OrderNote AS o
				WHERE
					o.text LIKE 0x'.bin2hex('%'.$filters['OrderMessageText'].'%').'
					AND o.orderID = CustomerOrder.ID
				)', '', 'OrderMessageText');
		}

		if(in_array('HasUnreadCustomerMessage', $columns))
		{
			$filter->addField('(
				SELECT
					IF(COUNT(*),1,0) AS HasUnreadCustomerMessage
				FROM
					OrderNote AS o
				WHERE
					o.isRead=0 AND
					o.orderID = CustomerOrder.ID
			)', '', 'HasUnreadCustomerMessage');
		}

		if(in_array('HasUnrespondedCustomerMessage', $columns))
		{
			$filter->addField('(
				SELECT
					o.isAdmin AS HasUnrespondedCustomerMessage
				FROM
					OrderNote AS o
				WHERE
					o.orderID = CustomerOrder.ID
				ORDER BY time DESC
				LIMIT 1
			)', '', 'HasUnrespondedCustomerMessage');
		}

		if($this->request->get('sort_col') == 'User.fullName')
		{
			$this->request->remove('sort_col');
			$direction = ($this->request->get('sort_dir') == 'DESC') ? ARSelectFilter::ORDER_DESC : ARSelectFilter::ORDER_ASC;
			$filter->orderBy(new ARFieldHandle("User", "lastName"), $direction);
			$filter->orderBy(new ARFieldHandle("User", "firstName"), $direction);
		}

		if ($this->request->get('userID'))
		{
			$cond->addAND(new EqualsCond(new ARFieldHandle('CustomerOrder', 'userID'), $this->request->get('userID')));
		}

		$filter->setCondition($cond);

		return $filter;
	}

	protected function setDefaultSortorderBy(ARSelectFilter $filter)
	{
		$filter->orderBy(new ARFieldHandle($this->getClassName(), 'dateCompleted'), 'DESC');
		$filter->orderBy(new ARFieldHandle($this->getClassName(), 'ID'), 'DESC');
	}

	public function processDataArrayAction($orders, $displayedColumns)
	{
		$orders = parent::processDataArray($orders, $displayedColumns);
		$ids = array();

		foreach ($orders as &$order)
		{
			$ids[$order['ID']] =& $order;

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

		if ($orders && (isset($displayedColumns['CustomerOrder.taxAmount']) || 1))
		{
			$sql = 'SELECT SUM(ShipmentTax.amount) AS taxAmount, Shipment.orderID AS orderID FROM ShipmentTax LEFT JOIN Shipment ON ShipmentTax.shipmentID=Shipment.ID WHERE Shipment.orderID IN(' . implode(', ', array_keys($ids)) .') GROUP BY Shipment.orderID';
			foreach (ActiveRecord::getDataBySQL($sql) as $row)
			{
				$ids[$row['orderID']]['taxAmount'] = round($row['taxAmount'], 2);
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

			case self::TYPE_RECURRING:
				$cond = new AndChainCondition(array(
					new EqualsCond(new ARFieldHandle('CustomerOrder', 'isFinalized'), 1),
					new EqualsCond(new ARFieldHandle('CustomerOrder', 'isRecurring'), 1),
						new OrChainCondition(array(
							new MoreThanCond(new ARFieldHandle('CustomerOrder', 'rebillsLeft'), 0),
							new IsNullCond(new ARFieldHandle('CustomerOrder', 'isRecurring'))
						))
					)
				);

				break;

			case self::TYPE_RECURRING_ALL:
				$cond = new EqualsCond(new ARFieldHandle('CustomerOrder', 'isRecurring'), 1);
				$cond->addAND(new EqualsCond(new ARFieldHandle('CustomerOrder', 'isFinalized'), 1));
				$cond->addAND(new IsNullCond(new ARFieldHandle('CustomerOrder', 'parentID')));
				break;

			case self::TYPE_RECURRING_EXPIRED:
				$cond = new EqualsCond(new ARFieldHandle('CustomerOrder', 'isRecurring'), 1);
				$cond->addAnd(new EqualsOrLessCond(new ARFieldHandle('CustomerOrder', 'rebillsLeft'), 0));
				$cond->addAND(new EqualsCond(new ARFieldHandle('CustomerOrder', 'isFinalized'), 1));
				$cond->addAND(new IsNullCond(new ARFieldHandle('CustomerOrder', 'parentID')));
				break;

			case self::TYPE_RECURRING_CANCELLED:
				$cond = new EqualsCond(new ARFieldHandle('CustomerOrder', 'isRecurring'), 1);
				$cond->addAND(new EqualsCond(new ARFieldHandle('CustomerOrder', 'isCancelled'), 1));
				$cond->addAND(new EqualsCond(new ARFieldHandle('CustomerOrder', 'isFinalized'), 1));
				$cond->addAND(new IsNullCond(new ARFieldHandle('CustomerOrder', 'parentID')));
				break;

			case self::TYPE_RECURRING_WITH_PARENT:
				$parentID = $this->getRequest()->get('parentID');
				if (!$parentID)
				{
					$parentID = -1;
				}
				$cond = new EqualsCond(new ARFieldHandle('CustomerOrder', 'isRecurring'), 1);
				$cond->addAND(new EqualsCond(new ARFieldHandle('CustomerOrder', 'parentID'), $parentID));
				break;

			default:
				return;
		}

		$filters = $this->request->get('filters');
		if (!in_array($type, array(
			self::TYPE_CANCELLED,
			self::TYPE_ALL,
			self::TYPE_SHIPPED,
			self::TYPE_RETURNED,
			self::TYPE_RECURRING_CANCELLED)))
		{
			$cond->addAND(new EqualsCond(new ARFieldHandle('CustomerOrder', "isCancelled"), 0));
		}
// $f  = new ARSelectFilter();
// $f->setCondition($cond);
// pp($f->createString());
		return $cond;
	}

	/**
	 * @role update
	 */
	public function saveFieldsAction()
	{
		$order = CustomerOrder::getInstanceByID($this->request->get('id'), true);
		$order->loadAll();
		if ($this->createFieldsFormValidator($order)->isValid())
		{
			$order->loadRequestData($this->request);
			$order->save(true);

			$this->set('order', $order->toArray());
			$form = $this->createFieldsForm($order);
			$order->getSpecification()->setFormResponse($response, $form);
			$this->set('fieldsForm', $form);
		}
	}

	/**
	 * @role update
	 */
	public function updateAction()
	{
		$order = CustomerOrder::getInstanceByID((int)$this->request->get('ID'), true);
		$order->loadAll();
		$history = new OrderHistory($order, $this->user);

		$oldStatus = $order->status;

		$status = (int)$this->request->get('status');
		$order->status->set($status);
		//$isCancelled = (int)$this->request->get('isCancelled') ? true : false;
		//$order->isCancelled->set($isCancelled);

		$order->updateShipmentStatuses();
		$response = $this->save($order);
		$history->saveLog();

		$this->sendStatusNotifyEmail($order);

	}

	/**
	 * @role update
	 */
	public function setMultiAddressAction()
	{
		$order = CustomerOrder::getInstanceByID((int)$this->request->get('id'), true);
		$order->isMultiAddress->set($this->request->get('status'));
		$order->save(true);
	}

	/**
	 * @role create
	 */
	public function createAction()
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
			if($user->$userField)
			{
				$user->$userField->userAddress->load();
				$address = clone $user->$userField->userAddress;
				$address->save();
				$order->$orderField->set($address);
			}
		}

		$response = $this->save($order);

		ActiveRecord::commit();
	}

	/**
	 * @role update
	 */
	public function updateAddressAction()
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
				if (!$order->billingAddress)
				{
					$order->billingAddress->set($address);
				}

				if (!$order->shippingAddress)
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

	protected function removeEmptyShipments(CustomerOrder $order)
	{
		foreach($order->getShipments() as $shipment)
		{
			if(count($shipment->getItems()) == 0)
			{
				$shipment->delete();
			}
		}
	}

	public function recalculateDiscountsAction()
	{
		$order = CustomerOrder::getInstanceById((int)$this->request->get('id'), true, true);
		$order->deleteRelatedRecordSet('OrderDiscount');
		$order->loadAll();

		$order->isFinalized->set(false);
		$order->finalize(array('allowRefinalize' => true));

		$order->isFinalized->set(true);
		$order->save();

		$redirectUrl = $this->url->get('backend.customerOrder/index') . '#order_' . $order->getID() . '__';
		return new RedirectResponse($redirectUrl);
	}

	public function printInvoiceAction()
	{
		$this->application->setTheme('');
		$order = CustomerOrder::getInstanceById($this->request->get('id'), CustomerOrder::LOAD_DATA, CustomerOrder::LOAD_REFERENCES);
		$order->loadAll();

		if ($order->user)
		{
			$order->user->getSpecification();
		}

		$this->setLayout('frontend');
		$this->loadLanguageFile('Frontend');
		$this->loadLanguageFile('User');

		$this->set('order', $order->toArray(array('payments' => true)));
	}

	private function save(CustomerOrder $order)
	{
		$validator = self::createOrderFormValidator();
		if ($validator->isValid())
		{
			$existingRecord = $order->isExistingRecord();
			$order->save(true);
			BackendToolbarItem::registerLastViewedorderBy($order);

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

	public function getAvailableColumnsAction()
	{
		// get available columns
		$availableColumns = parent::getAvailableColumns();
		unset($availableColumns['CustomerOrder.shipping']);
		unset($availableColumns['CustomerOrder.isFinalized']);
		unset($availableColumns['CustomerOrder.checkoutStep']);

		$availableColumns['HasUsedCoupon'] = array('type' => 'bool', 'name' => $this->translate('HasUsedCoupon'));
		$availableColumns['UsedCouponCount'] = array('type' => 'numeric', 'name' => $this->translate('UsedCouponCount'));
		$availableColumns['ProductCount'] = array('type' => 'numeric', 'name' => $this->translate('ProductCount'));
		$availableColumns['UniqueProductCount'] = array('type' => 'numeric', 'name' => $this->translate('UniqueProductCount'));
		$availableColumns['HasUnreadCustomerMessage'] = array('type' => 'bool', 'name' => $this->translate('HasUnreadCustomerMessage'));
		$availableColumns['HasUnrespondedCustomerMessage'] = array('type' => 'bool', 'name' => $this->translate('HasUnrespondedCustomerMessage'));
		return $availableColumns;
	}

	public function getAdvancedSearchFieldsAction()
	{
		return $this->translateFieldArray(
			array
			(
				'ProductSKU' => array('name'=>'', 'type'=>'text'),
				'ProductName'=> array('name'=>'', 'type'=>'text'),
				'Manufacturer'=> array('name'=>'', 'type'=>'text'),
				'UsedCouponCode'=> array('name'=>'', 'type'=>'text'),
				'OrderMessageText'=> array('name'=>'', 'type'=>'text'),
				'ProductOption' => array('name'=>'', 'type'=>'text')
			)
		);
	}

	protected function getCustomColumns()
	{

		$availableColumns = array();

		$availableColumns['User.email'] = 'text';
		$availableColumns['User.ID'] = 'text';
		$availableColumns['User.fullName'] = 'text';

		$availableColumns['CustomerOrder.status'] = 'text';
		$availableColumns['CustomerOrder.taxAmount'] = 'number';

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
	 * @return \Phalcon\Validation
	 */
	public function createUserAddressFormValidatorAction()
	{
		$validator = $this->getValidator("userAddress", $this->request);

		$validator->add('countryID', new Validator\PresenceOf(array('message' => $this->translate('_country_empty'))));
		$validator->add('city',	  new Validator\PresenceOf(array('message' => $this->translate('_city_empty'))));
		$validator->add('address1',  new Validator\PresenceOf(array('message' => $this->translate('_address_empty'))));
		$validator->add('firstName', new Validator\PresenceOf(array('message' => $this->translate('_first_name_is_empty'))));
		$validator->add('lastName',  new Validator\PresenceOf(array('message' => $this->translate('_last_name_is_empty'))));

		UserAddress::getNewInstance()->getSpecification()->setValidation($validator);

		return $validator;
	}

	/**
	 * @return Form
	 */
	public function createUserAddressFormAction($addressArray = array(), ActionResponse $response)
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

		$address = !empty($addressArray['ID']) ? UserAddress::getInstanceByID($addressArray['ID'], true) : UserAddress::getNewInstance();
		$address->getSpecification()->setFormResponse($response, $form);

		return $form;
	}

	/**
	 * @return \Phalcon\Validation
	 */
	private function createOrderFormValidator()
	{
		$validator = $this->getValidator("CustomerOrder", $this->request);

		$validator->add('status', new MinValueCheck($this->translate('_invalid_status'), 0));
		$validator->add('status', new MaxValueCheck($this->translate('_invalid_status'), 4));

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
	 * @return \Phalcon\Validation
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

	public function addCouponAction()
	{
				$response = $this->getRequest();
		$code = $this->request->get('coupon');

		$msg = '_coupon_not_found';
		$error = true;
		if (strlen($code))
		{
			$order = CustomerOrder::getInstanceByID($response->get('id'), CustomerOrder::LOAD_DATA);
			$condition = DiscountCondition::getInstanceByCoupon($code);

			if ($condition)
			{
				if (!$order->hasCoupon($code))
				{
					$coupon = OrderCoupon::getNewInstance($order, $code);
					$coupon->save();
					$order->getCoupons(true);

					if ($order->hasCoupon($code))
					{
						$msg = '_coupon_added';
						$this->recalculateDiscounts();
						$error = false;
					}
					else
					{
						$msg = '_cant_add_coupon';
					}
				}
				else
				{
					$msg = '_coupon_already_added';
				}
			}
			$order->getCoupons(true);
		}

		$this->loadLanguageFile('Order');

		return new JSONResponse(null,
			$error ? 'failure' : 'success', $this->makeText($msg, array($code)));
	}

	public function invoicesAction()
	{
		$this->invoiceRequestPreFilter();

		$this->set('parentID', $this->getRequest()->get('parentID'));
		$this->setGridResponse($response);
	}

	public function listInvoicesAction()
	{
		$this->invoiceRequestPreFilter();
		return $this->lists();
	}

	private function invoiceRequestPreFilter() // or putting it simple - witchcraft
	{
		$request = $this->getRequest();
		$parentID = $request->get('id'); // for invoice tab active grid parentID is passed as id
		$parentID = str_replace('recurringOrdersWithParent_', '',$parentID); // and when listing (reload, advanced search) id can has some prefix, removing.
		$request->set('id', self::TYPE_RECURRING_WITH_PARENT); // but for orders active grid id is used as type, setting type then, because reusing as much as possible from orders grid.
		$request->set('parentID', $parentID); // and also parent id.
	}

	public function cancelSubscriptionAction()
	{
		try {
			$request = $this->getRequest();
			$id = $request->get('id');
			$order = CustomerOrder::getInstanceById($id);
			if ($this->canCancelRecurring($order))
			{
				$status = $order->cancelRecurring(); // in payment processor.
				if ($status == true)
				{
					return $this->stopRebills($order); // chain next action without user confirmation.
				}
				else
				{
					return new JSONResponse(array(
						'confirm' => $this->translate('_cancel_subscription_payment_processor_error'),
						'code' => 0),
						'confirmation_required'
					);
				}
			}
		} catch(Exception $e) { }

		return new JSONResponse(null, 'failure', $this->translate('_cannot_cancel_subscription'));
	}

	public function stopRebillsAction($order=null)
	{
		if ($order === null)
		{
			$request = $this->getRequest();
			$id = $request->get('id');
			$order = CustomerOrder::getInstanceById($id);
		}

		if ($this->canCancelRecurring($order))
		{
			$order->cancelFurtherRebills(); // localy in store database.
			return new JSONResponse(array(
				'recurringStatus' => $this->translate('_recurring_status_expired'),
				'rebillCount' => 0),
				'success'
			);
		}

		return new JSONResponse(null, 'failure', $this->translate('_cannot_cancel_subscription'));
	}

	private function canCancelRecurring(CustomerOrder $order)
	{
		return $order->isCancelled == false && $order->isRecurring == true && $order->rebillsLeft > 0;
	}

	private function getAlwaysDisplayedColumns()
	{
		return in_array($this->getRequest()->getActionName(), array('listInvoices', 'invoices'))
			? array('CustomerOrder.isPaid'=>'bool')
			: array();
	}

	protected function getDisplayedColumns($params = null, $customColumns = array())
	{
		return parent::getDisplayedColumns($params, array_merge($customColumns, $this->getAlwaysDisplayedColumns()));
	}
}
?>
