<?php


/**
 * Controller for handling product based actions performed by store administrators
 *
 * @package application/controller/backend
 * @author Integry Systems
 * @role order
 */
class ShipmentController extends StoreManagementController
{
	public function initialize()
	{
		parent::initialize();
		CustomerOrder::allowEmpty();
	}

	public function changeServiceAction()
	{
		$shipment = Shipment::getInstanceByID('Shipment', (int)$this->request->get('id'), true, array('Order' => 'CustomerOrder', 'ShippingAddress' => 'UserAddress'));
		$shipment->loadItems();
		$order = $shipment->order;
		$shipment->order->loadAll();
		$zone = $shipment->getDeliveryZone();
		$shipmentRates = $zone->getShippingRates($shipment);

		$shipment->setAvailableRates($shipmentRates);

		$history = new OrderHistory($order, $this->user);

		$selectedRate = null;
		foreach($shipment->getAvailableRates() as $rate)
		{
			if($rate->getServiceID() == $this->request->get('serviceID'))
			{
				$selectedRate = $rate;
				break;
			}
		}

		$shipment->setRateId($this->request->get('serviceID'));

		$shipment->recalculateAmounts();
		$order->save();
		$shipment->save(ActiveRecord::PERFORM_UPDATE);

		$history->saveLog();

		$shipmentArray = $shipment->toArray();
		$shipmentArray['ShippingService']['ID'] = $this->request->get('serviceID');

		return new JSONResponse(array(
				'shipment' => array(
					   'ID' => $shipment->getID(),
					   'amount' => $shipment->amount,
					   'shippingAmount' => (float)$shipment->shippingAmount,
					   'taxAmount' => $shipment->taxAmount,
					   'total' => $shipment->shippingAmount + $shipment->amount + (float)$shipment->taxAmount,
					   'prefix' => $shipment->getCurrency()->pricePrefix,
					   'suffix' => $shipment->getCurrency()->priceSuffix,
					   'ShippingService' => $shipmentArray['ShippingService'],
					   'Order' => $shipment->order->toFlatArray(),
				   )
			),
			'success'
		);
	}

	public function changeStatusAction()
	{
		$status = (int)$this->request->get('status');

		$shipment = Shipment::getInstanceByID('Shipment', (int)$this->request->get('id'), true, array('Order' => 'CustomerOrder', 'ShippingAddress' => 'UserAddress'));
		$shipment->loadItems();

		$zone = $shipment->getDeliveryZone();
		$shipmentRates = $zone->getShippingRates($shipment);
		$shipment->setAvailableRates($shipmentRates);

		$history = new OrderHistory($shipment->order, $this->user);

		$shipment->status->set($status);
		$shipment->save();

		$history->saveLog();

		$status = $shipment->status;
		$enabledStatuses = $this->config->get('EMAIL_STATUS_UPDATE_STATUSES');
		$m = array(
			'EMAIL_STATUS_UPDATE_NEW'=>Shipment::STATUS_NEW,
			'EMAIL_STATUS_UPDATE_PROCESSING'=>Shipment::STATUS_PROCESSING,
			'EMAIL_STATUS_UPDATE_AWAITING_SHIPMENT'=>Shipment::STATUS_AWAITING,
			'EMAIL_STATUS_UPDATE_SHIPPED'=> Shipment::STATUS_SHIPPED
		);
		$sendEmail = false;
		foreach($m as $configKey => $constValue)
		{
			if($status == $constValue && array_key_exists($configKey, $enabledStatuses))
			{
				$sendEmail = true;
			}
		}

		if ($sendEmail || $this->config->get('EMAIL_STATUS_UPDATE'))
		{
			$user = $shipment->order->user;
			$user->load();

			$email = new Email($this->application);
			$email->setUser($user);
			$email->setTemplate('order.status');
			$email->set('order', $shipment->order->toArray(array('payments' => true)));
			$email->set('shipments', array($shipment->toArray()));
			$email->send();
		}

		return new JSONResponse(false, 'success');
	}

	public function getAvailableServicesAction()
	{
		$this->loadLanguageFile('Checkout');

		if($shipmentID = (int)$this->request->get('id'))
		{
			$shipment = Shipment::getInstanceByID('Shipment', $shipmentID, true, array('Order' => 'CustomerOrder'));
			$shipment->loadItems();

			if ($shipment->shippingAddress)
			{
				$shipment->shippingAddress->load();
			}

			$zone = $shipment->getDeliveryZone();

			$shipmentRates = $zone->getShippingRates($shipment);
			$shipment->setAvailableRates($shipmentRates);

			$shippingRatesArray = array();
			foreach($shipment->getAvailableRates() as $rate)
			{
				$rateArray = $rate->toArray();
				$shippingRatesArray[$rateArray['serviceID']] = $rateArray;
				$shippingRatesArray[$rateArray['serviceID']]['shipment'] = array(
					'ID' => $shipment->getID(),
					'amount' => $shipment->amount,
					'shippingAmount' => (float)$rateArray['costAmount'],
					'taxAmount' => $shipment->taxAmount,
					'total' => (float)$shipment->taxAmount + (float)$shipment->amount + (float)$rateArray['costAmount'],
					'prefix' => $shipment->getCurrency()->pricePrefix,
					'suffix' => $shipment->getCurrency()->priceSuffix
				);
			}

			return new JSONResponse(array( 'services' => $shippingRatesArray));
		}
	}

	private function createShipmentFormValidator()
	{
		$validator = $this->getValidator('shippingService', $this->request);

		return $validator;
	}

	/**
	 * @role update
	 */
	public function createAction()
	{
		$order = CustomerOrder::getInstanceByID((int)$this->request->get('orderID'), true, array('BillingAddress', 'ShippingAddress'));

		$shipment = Shipment::getNewInstance($order);
		$history = new OrderHistory($order, $this->user);
		$response = $this->save($shipment);
		$history->saveLog();

	}

	public function editAddressAction()
	{
		$this->loadLanguageFile('backend/CustomerOrder');

				$shipment = Shipment::getInstanceByID('Shipment', $this->request->get('id'), true, array('CustomerOrder', 'User'));

		if (!$shipment->shippingAddress)
		{
			$shipment->shippingAddress->set(UserAddress::getNewInstance());
			$shipment->shippingAddress->save();
		}

		$shipment->shippingAddress->load();
		$address = $shipment->shippingAddress->toArray();


		$controller = new CustomerOrderController($this->application);
		$this->set('form', $controller->createUserAddressForm($address, $response));

		$this->set('countries', $this->application->getEnabledCountries());
		$this->set('states', State::getStatesByCountry($address['countryID']));
		$this->set('shipmentID', $shipment->getID());

		$addressOptions = array('' => '');
		$addresses = array();
		foreach(array_merge($shipment->order->user->getShippingAddressArray(), $shipment->order->user->getBillingAddressArray()) as $address)
		{
			$addressOptions[$address['ID']] = $address['UserAddress']['compact'];
			$addresses[$address['ID']] = $address;
		}
		$this->set('existingUserAddressOptions', $addressOptions);
		$this->set('existingUserAddresses', $addresses);

	}

	public function saveAddressAction()
	{
		$this->loadLanguageFile('backend/Shipment');

				$shipment = Shipment::getInstanceByID('Shipment', $this->request->get('id'), true, array('CustomerOrder', 'User'));
		$address = $shipment->shippingAddress;

		if (!$address)
		{
			$address = UserAddress::getNewInstance();
			$address->save();
			$shipment->shippingAddress->set($address);
			$shipment->save();
		}
		else
		{
			$address->load();
		}

		$controller = new CustomerOrderController($this->application);
		$validator = $controller->createUserAddressFormValidator();

		if ($validator->isValid())
		{
			$address->loadRequestData($this->request);
			$address->save();
			return new JSONResponse($shipment->shippingAddress->toArray(), 'success', $this->translate('_shipment_address_changed'));
		}
		else
		{
			return new JSONResponse(
				array(
					'errors' => $validator->getErrorList()
				),
				'failure'
			);
		}
	}

	/**
	 * @role update
	 */
	public function updateAction()
	{
		$order = CustomerOrder::getInstanceByID((int)$this->request->get('ID'));
		return $this->save($order);
	}

	/**
	 * @role update
	 */
	public function updateShippingAmountAction()
	{
		$shipment = Shipment::getInstanceByID('Shipment', $this->request->get('id'), true, array('CustomerOrder', 'User'));
		$order = $shipment->order;

		$order->loadAll();

		$shipment->shippingAmount->set($shipment->reduceTaxesFromShippingAmount($this->request->get('amount')));
		$shipment->recalculateAmounts(true);
		$shipment->save();

		$order->totalAmount->set($order->getTotal(true));
		$order->save();

		$array = $shipment->toArray();
		$array['total'] = $order->getTotal();

		unset($array['items']);
		unset($array['taxes']);

		$array['Order'] = $order->toFlatArray();
		return new JSONResponse(array('Shipment' => $array));
	}

	private function save(Shipment $shipment)
	{
		$validator = $this->createShipmentFormValidator();
		if ($validator->isValid())
		{
			if($shippingServiceID = $this->request->get('shippingServiceID'))
			{
				$shippingService = ShippingService::getInstanceByID($shippingServiceID);

				$shipment->shippingService->set($shippingService);
				$shipment->setAvailableRates($shipment->getDeliveryZone()->getShippingRates($shipment));
				$shipment->setRateId($shippingService->getID());
			}

			if($this->request->get('noStatus'))
			{
				$shipment->status->set($shipment->order->status);
			}
			else if($this->request->get('shippingServiceID') || ((int)$this->request->get('status') < 3))
			{
				$shipment->status->set((int)$this->request->get('status'));
			}

			$shipment->save();

			return new JSONResponse(
				array(
					'shipment' => array(
						'ID' => $shipment->getID(),
						'amount' => $shipment->amount,
						'shippingAmount' => $shipment->shippingAmount,
						'ShippingService' => array('ID' => ($shipment->shippingService ? $shipment->shippingService->getID() : 0) ),
						'taxAmount' => $shipment->taxAmount,
						'total' => $shipment->shippingAmount + $shipment->amount + (float)$shipment->taxAmount,
						'prefix' => $shipment->getCurrency()->pricePrefix,
						'status' => $shipment->status,
						'suffix' => $shipment->getCurrency()->priceSuffix
					)
				),
				'success',
				($this->request->get('noStatus') ? false : $this->translate('_new_shipment_has_been_successfully_created'))
			);
		}
		else
		{
			return new JSONResponse(
				array(
					'errors' => $validator->getErrorList()
				),
				'failure',
				$this->translate('_error_creating_new_shipment')
			);
		}
	}

	public function editAction()
	{
		$group = ProductFileGroup::getInstanceByID((int)$this->request->get('id'), true);

		return new JSONResponse($group->toArray());
	}

	/**
	 * @role update
	 */
	public function deleteAction()
	{
		$shipment = Shipment::getInstanceByID('Shipment', (int)$this->request->get('id'), true, array('Order' => 'CustomerOrder'));
		$shipment->order->loadAll();

		$history = new OrderHistory($shipment->order, $this->user);

		$shipment->delete();

		$shipment->order->updateStatusFromShipments();
		$shipment->order->save();

		$history->saveLog();

		return new JSONResponse(array('deleted' => true), 'success');
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
}

?>
