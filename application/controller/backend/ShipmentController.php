<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.order.*");
ClassLoader::import("application.model.delivery.*");
ClassLoader::import("application.model.Currency");
ClassLoader::import("application.model.product.ProductSet");

/**
 * Controller for handling product based actions performed by store administrators
 *
 * @package application.controller.backend
 * @author Integry Systems
 * @role order
 */
class ShipmentController extends StoreManagementController
{
	public function index()
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
			$subtotalAmount += $shipment->amount->get();
			$shippingAmount += $shipment->shippingAmount->get();
			$taxAmount += $shipment->taxAmount->get();

			$shipmentsArray[$shipment->getID()] = $shipment->toArray();

			$rate = unserialize($shipment->shippingServiceData->get());
			if(is_object($rate))
			{
				$rate->setApplication($this->application);
				$shipmentsArray[$shipment->getID()] = array_merge($shipmentsArray[$shipment->getID()], $rate->toArray());
				if (isset($shipmentsArray[$shipment->getID()]['serviceID']))
				{
					$shipmentsArray[$shipment->getID()]['ShippingService']['ID'] = $shipmentsArray[$shipment->getID()]['serviceID'];
				}
			}
			else if($shipment->shippingService->get())
			{
				$shipmentsArray[$shipment->getID()]['ShippingService']['name_lang'] = $shipmentsArray[$shipment->getID()]['ShippingService']['name'];
			}
			else
			{
				$shipmentsArray[$shipment->getID()]['ShippingService']['name_lang'] = $this->translate('_shipping_service_is_not_selected');
			}

			if($shipment->status->get() != Shipment::STATUS_SHIPPED && $shipment->isShippable())
			{
				$shipableShipmentsCount++;
			}

			foreach ($shipment->getItems() as $item)
			{
				$itemIDs[] = $item->getID();
			}
		}

		$totalAmount = $subtotalAmount + $shippingAmount + $taxAmount;

		$response = new ActionResponse();
		$response->set('orderID', $this->request->get('id'));
		$response->set('order', $order->toArray());
		$response->set('shippingServiceIsNotSelected', $this->translate('_shipping_service_is_not_selected'));
		$response->set('shipments', $shipmentsArray);
		$response->set('subtotalAmount', $subtotalAmount);
		$response->set('shippingAmount', $shippingAmount);
		$response->set('variations', $variations);

		if ($downloadable = $order->getDownloadShipment(false))
		{

			$response->set('downloadableShipment', $downloadable->toArray());
		}

		$response->set('taxAmount', $taxAmount);
		$response->set('totalAmount', $totalAmount);
		$response->set('shipableShipmentsCount', $shipableShipmentsCount);
		$response->set('statuses', $statuses + array(-1 => $this->translate('_delete')));

		unset($statuses[3]);
		$response->set('statusesWithoutShipped', $statuses);
		$response->set('newShipmentForm', $form);
		$response->set('downloadCount', $this->getDownloadCounts($itemIDs));

		// load product options
		$response->set('allOptions', ProductOption::loadOptionsForProductSet($products));

		return $response;
	}

	public function changeService()
	{
		$shipment = Shipment::getInstanceByID('Shipment', (int)$this->request->get('id'), true, array('Order' => 'CustomerOrder', 'ShippingAddress' => 'UserAddress'));
		$shipment->loadItems();
		$order = $shipment->order->get();
		$shipment->order->get()->loadAll();
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

		/*
		$shippingService = ShippingService::getInstanceByID($this->request->get('serviceID'), ShippingService::LOAD_DATA);

		$shipment->setAvailableRates($shipment->order->get()->getDeliveryZone()->getShippingRates($shipment));
		$shipment->setRateId($shippingService->getID());

		$shipment->shippingService->set($shippingService);
		$shipment->recalculateAmounts();

		$shipment->save();
		*/

		$history->saveLog();

		$shipmentArray = $shipment->toArray();
		$shipmentArray['ShippingService']['ID'] = $this->request->get('serviceID');

		return new JSONResponse(array(
				'shipment' => array(
					   'ID' => $shipment->getID(),
					   'amount' => $shipment->amount->get(),
					   'shippingAmount' => (float)$shipment->shippingAmount->get(),
					   'taxAmount' => $shipment->taxAmount->get(),
					   'total' => $shipment->shippingAmount->get() + $shipment->amount->get() + (float)$shipment->taxAmount->get(),
					   'prefix' => $shipment->getCurrency()->pricePrefix->get(),
					   'suffix' => $shipment->getCurrency()->priceSuffix->get(),
					   'ShippingService' => $shipmentArray['ShippingService']
				   )
			),
			'success'
		);
	}

	public function changeStatus()
	{
		$status = (int)$this->request->get('status');

		$shipment = Shipment::getInstanceByID('Shipment', (int)$this->request->get('id'), true, array('Order' => 'CustomerOrder', 'ShippingAddress' => 'UserAddress'));
		$shipment->loadItems();

		$zone = $shipment->getDeliveryZone();
		$shipmentRates = $zone->getShippingRates($shipment);
		$shipment->setAvailableRates($shipmentRates);

		$history = new OrderHistory($shipment->order->get(), $this->user);

		$shipment->status->set($status);
		$shipment->save();

		$history->saveLog();

		if ($this->config->get('EMAIL_STATUS_UPDATE'))
		{
			$user = $shipment->order->get()->user->get();
			$user->load();

			$email = new Email($this->application);
			$email->setUser($user);
			$email->setTemplate('order.status');
			$email->set('order', $shipment->order->get()->toArray(array('payments' => true)));
			$email->set('shipments', array($shipment->toArray()));
			$email->send();
		}

		return new JSONResponse(false, 'success');
	}

	public function getAvailableServices()
	{
		$this->loadLanguageFile('Checkout');

		if($shipmentID = (int)$this->request->get('id'))
		{
			$shipment = Shipment::getInstanceByID('Shipment', $shipmentID, true, array('Order' => 'CustomerOrder'));
			$shipment->loadItems();

			if ($shipment->shippingAddress->get())
			{
				$shipment->shippingAddress->get()->load();
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
					'amount' => $shipment->amount->get(),
					'shippingAmount' => (float)$rateArray['costAmount'],
					'taxAmount' => $shipment->taxAmount->get(),
					'total' => (float)$shipment->taxAmount->get() + (float)$shipment->amount->get() + (float)$rateArray['costAmount'],
					'prefix' => $shipment->getCurrency()->pricePrefix->get(),
					'suffix' => $shipment->getCurrency()->priceSuffix->get()
				);
			}

			return new JSONResponse(array( 'services' => $shippingRatesArray));
		}
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

	/**
	 * @role update
	 */
	public function create()
	{
		$order = CustomerOrder::getInstanceByID((int)$this->request->get('orderID'), true, array('BillingAddress', 'ShippingAddress'));

		$shipment = Shipment::getNewInstance($order);
		$history = new OrderHistory($order, $this->user);
		$response = $this->save($shipment);
		$history->saveLog();

		return $response;
	}

	public function editAddress()
	{
		$this->loadLanguageFile('backend/CustomerOrder');

		ClassLoader::import('application.controller.backend.CustomerOrderController');
		$shipment = Shipment::getInstanceByID('Shipment', $this->request->get('id'), true, array('CustomerOrder', 'User'));

		if (!$shipment->shippingAddress->get())
		{
			$shipment->shippingAddress->set(UserAddress::getNewInstance());
			$shipment->shippingAddress->get()->save();
		}

		$shipment->shippingAddress->get()->load();
		$address = $shipment->shippingAddress->get()->toArray();

		$response = new ActionResponse();
		$controller = new CustomerOrderController($this->application);
		$response->set('form', $controller->createUserAddressForm($address, $response));

		$response->set('countries', $this->application->getEnabledCountries());
		$response->set('states', State::getStatesByCountry($address['countryID']));
		$response->set('shipmentID', $shipment->getID());

		$addressOptions = array('' => '');
		foreach($shipment->order->get()->user->get()->getShippingAddressArray() as $address)
		{
			$addressOptions[$address['ID']] = $address['UserAddress']['compact'];
			$addresses[$address['ID']] = $address;
		}
		$response->set('existingUserAddressOptions', $addressOptions);
		$response->set('existingUserAddresses', $addresses);

		return $response;
	}

	public function saveAddress()
	{
		ClassLoader::import('application.controller.backend.CustomerOrderController');
		$shipment = Shipment::getInstanceByID('Shipment', $this->request->get('id'), true, array('CustomerOrder', 'User'));
		$address = $shipment->shippingAddress->get();

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
			return new JSONResponse($shipment->shippingAddress->get()->toArray(), 'success', $this->translate('_shipment_address_changed'));
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
	public function update()
	{
		$order = CustomerOrder::getInstanceByID((int)$this->request->get('ID'));
		return $this->save($order);
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
				$shipment->status->set($shipment->order->get()->status->get());
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
						'amount' => $shipment->amount->get(),
						'shippingAmount' => $shipment->shippingAmount->get(),
						'ShippingService' => array('ID' => ($shipment->shippingService->get() ? $shipment->shippingService->get()->getID() : 0) ),
						'taxAmount' => $shipment->taxAmount->get(),
						'total' => $shipment->shippingAmount->get() + $shipment->amount->get() + (float)$shipment->taxAmount->get(),
						'prefix' => $shipment->getCurrency()->pricePrefix->get(),
						'status' => $shipment->status->get(),
						'suffix' => $shipment->getCurrency()->priceSuffix->get()
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

	public function edit()
	{
		$group = ProductFileGroup::getInstanceByID((int)$this->request->get('id'), true);

		return new JSONResponse($group->toArray());
	}

	/**
	 * @role update
	 */
	public function delete()
	{
		$shipment = Shipment::getInstanceByID('Shipment', (int)$this->request->get('id'), true, array('Order' => 'CustomerOrder'));
		$shipment->order->get()->loadAll();

		$history = new OrderHistory($shipment->order->get(), $this->user);

		$shipment->delete();

		$shipment->order->get()->updateStatusFromShipments();
		$shipment->order->get()->save();

		$history->saveLog();

		return new JSONResponse(array('deleted' => true), 'success');
	}

	protected function getDownloadCounts($itemIDs)
	{
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
