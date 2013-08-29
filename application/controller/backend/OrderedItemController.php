<?php

/**
 * ...
 *
 * @package application/controller/backend
 * @author Integry Systems
 *
 * @role order
 */
class OrderedItemController extends StoreManagementController
{
	public function initialize()
	{
		parent::initialize();
		CustomerOrder::allowEmpty();
	}

	public function createAction()
	{
		$request = $this->getRequest();
		$query = $request->get('query');

		if (strlen($query))
		{
			$products = $this->getProductsFromSearchQuery($query);
		}
		else
		{
			$products = new ARSet();
			$products->add(Product::getInstanceById((int)$this->request->get('productID'), true));
		}

		$saveResponse = array('errors'=>array(), 'items'=>array());

		$composite = new CompositeJSONResponse();


		$order = CustomerOrder::getInstanceByID((int)$this->request->get('orderID'), true);
		$order->loadAll();

		foreach ($products as $product)
		{
			if($product->isDownloadable())
			{
				$shipment = $order->getDownloadShipment();
			}
			else if ((int)$this->request->get('shipmentID'))
			{
				$shipment = Shipment::getInstanceById('Shipment', (int)$this->request->get('shipmentID'), true, array('Order' => 'CustomerOrder', 'ShippingService', 'ShippingAddress' => 'UserAddress', 'Currency'));
			}

			if (empty($shipment))
			{
				$shipment = $order->getShipments()->get(0);
			}

			if (!$shipment)
			{
				$shipment = Shipment::getNewInstance($order);
			}

			if (!$shipment->order)
			{
				$shipment->order->set($order);
			}

			$history = new OrderHistory($order, $this->user);

			$existingItem = false;
			foreach($shipment->getItems() as $item)
			{
				if($item->getProduct() === $product)
				{
					if (!$product->getOptions(true))
					{
						$existingItem = $item;
					}
					break;
				}
			}

			if($existingItem)
			{
				$item = $existingItem;
				if($product->isDownloadable())
				{
					return new JSONResponse(false, 'failure', $this->translate('_downloadable_item_already_exists_in_this_order'));
				}
				else
				{
					$item->count->set($item->count + 1);
				}
			}
			else
			{
				$currency = $shipment->getCurrency();
				$item = OrderedItem::getNewInstance($order, $product);
				$item->count->set(1);
				$item->price->set($currency->round($item->reduceBaseTaxes($product->getPrice($currency->getID()))));
				$order->addItem($item);
				$shipment->addItem($item);
				$shipment->save();
			}

			$resp = $this->save($item, $shipment, $existingItem ? true : false );

			if (array_key_exists('errors', $resp))
			{
				$saveResponse['errors'] = array_merge($saveResponse['errors'], $resp['errors']);
			}
			else if(array_key_exists('item', $resp))
			{
				$saveResponse['items'][] = $resp['item'];
			}
		} // for each product

		if (count($saveResponse['errors']) == 0)
		{
			unset($saveResponse['errors']);
		}

		if (isset($saveResponse['errors']))
		{
			$response =  new JSONResponse(array('errors' => $validator->getErrorList()), 'failure', $this->translate('_unable_to_update_items_quantity'));
		}
		else
		{
			$response = new JSONResponse($saveResponse, 'success', $this->translate('_item_has_been_successfuly_saved'));
		}
		$composite->addResponse('data', $response, $this, 'create');

		$ids = array();
		foreach($saveResponse['items'] as $item)
		{
			$ids[] = $item['ID'];
		}

		$composite->addAction('html', 'backend.orderedItem', 'items');
		$this->request->set('item_ids', implode(',',$ids));

		$history->saveLog();

		return $composite;
	}

	private function getProductsFromSearchQuery($query)
	{
				$request = $this->getRequest();
		$searchable  = SearchableModel::getInstanceByModelClass('Product',SearchableModel::BACKEND_SEARCH_MODEL);
		$searchable->setOption('BACKEND_QUICK_SEARCH', true);

		return ActiveRecordModel::getRecordSet('Product', $searchable->getSelectFilter($query));
	}

	public function updateAction()
	{

	}

	private function save(OrderedItem $item, Shipment $shipment, $existingItem = false)
	{
		$validator = $this->createOrderedItemValidator();
		if($validator->isValid())
		{
			if($count = (int)$this->request->get('count') && !(int)$this->request->get('downloadable'))
			{
				$item->count->set($count);
			}

			$shipment->loadItems();

			if(!$existingItem)
			{
				$shipment->addItem($item);
			}

			if($shipment->getShippingService())
			{
				$shipmentRates = $shipment->getDeliveryZone()->getShippingRates($shipment);
				$shipment->setAvailableRates($shipmentRates);
				$shipment->setRateId($shipment->getShippingService()->getID());
			}

			$shipment->recalculateAmounts();
			$shipment->save();

			return array(
				'item' => array(
					'ID' => $item->getID(),
					'Product' => $item->getProduct()->toArray(),
					'Shipment' => array(
						'ID' => $shipment->getID(),
						'amount' => (float)$shipment->amount,
						'shippingAmount' => (float)$shipment->shippingAmount,
						'taxAmount' => (float)$shipment->taxAmount,
						'total' => (float)$shipment->shippingAmount + (float)$shipment->amount + (float)$shipment->taxAmount,
						'prefix' => $shipment->getCurrency()->pricePrefix,
						'suffix' => $shipment->getCurrency()->priceSuffix,
						'Order' => $shipment->order->toFlatArray(),
					),
					'count' => $item->count,
					'price' => $item->price,
					'priceCurrencyID' => $item->getCurrency()->getID(),
					'isExisting' => $existingItem,
					'variations' => $item->getProduct()->getParent()->getVariationData($this->application),
				)
			);
		}
		else
		{
			return array(
				'errors' => $validator->getErrorList()
			);

			//return new JSONResponse(array('errors' => $validator->getErrorList()), 'failure', $this->translate('_unable_to_update_items_quantity'));
		}
	}

	/**
	 * Products popup
	 *
	 * @role update
	 */
	public function selectProductAction()
	{

		$response = new ActionResponse();

		$categoryList = Category::getRootNode()->getDirectChildNodes();
		$categoryList->unshift(Category::getRootNode());

		$response->set("categoryList", $categoryList->toArray($this->application->getDefaultLanguageCode()));

		$order = CustomerOrder::getInstanceById($this->request->get('id'), true, true);

		$response->set("order", $order->toFlatArray());
		$response->set("shipments", $this->getOrderShipments($order));

		return $response;
	}

	public function shipmentsAction()
	{
		$order = CustomerOrder::getInstanceById($this->request->get('id'), true, true);
		return new ActionResponse("shipments", $this->getOrderShipments($order));
	}

	private function getOrderShipments(CustomerOrder $order)
	{
		$order->loadItems();
		$shipments = $order->getShipments();
		$downloadable = $order->getDownloadShipment(false);

		if ($downloadable && count($shipments) == 1 && !count($downloadable->getItems()))
		{
			$downloadable = null;
		}

		$shipmentsArray = array();
		foreach($shipments as $key => $shipment)
		{
			// one shipment is reserved for downloadable items
			if ($shipment === $downloadable || $shipment->isShipped())
			{
				continue;
			}

			$shipmentsArray[$shipment->getID()] = $shipment->toArray();
			$rate = unserialize($shipment->shippingServiceData);

			if(is_object($rate))
			{
				$rate->setApplication($this->application);
				$shipmentsArray[$shipment->getID()] = array_merge($shipmentsArray[$shipment->getID()], $rate->toArray());
				$shipmentsArray[$shipment->getID()]['ShippingService']['ID'] = $shipmentsArray[$shipment->getID()]['serviceID'];
			}
			else
			{
				$shipmentsArray[$shipment->getID()]['ShippingService']['name_lang'] = $this->translate('_shipping_service_is_not_selected');
			}
		}

		return $shipmentsArray;
	}

	/**
	 * @return RequestValidator
	 */
	private function createOrderedItemValidator()
	{
		$validator = $this->getValidator('orderedItem', $this->request);
		$validator->addCheck('productID', new MinValueCheck('_err_invalid_product', 0));
		$validator->addCheck('orderID', new MinValueCheck('_err_invalid_customer_order', 0));
		$validator->addCheck('count', new MinValueCheck('_err_count_should_be_more_than_zero', 0));

		return $validator;
	}

	/**
	 * Delete filter from database
	 *
	 * @role update
	 *
	 * @return JSONResponse
	 */
	public function deleteAction()
	{
		if($id = $this->request->get("id", null, false);)
		{
			$item = OrderedItem::getInstanceByID('OrderedItem', (int)$id, true, array('Shipment', 'Order' => 'CustomerOrder', 'ShippingService', 'Currency', 'ShippingAddress' => 'UserAddress', 'Product'));
			$shipment = $item->shipment;
			$order = $shipment->order;
			$order->loadItems();

			$history = new OrderHistory($order, $this->user);

			$shipment->loadItems();
			$shipment->removeItem($item);
			$order->removeItem($item);

			if($shipment->getShippingService())
			{
				$shipmentRates = $shipment->getDeliveryZone()->getShippingRates($shipment);
				$shipment->setAvailableRates($shipmentRates);
				$shipment->setRateId($shipment->getShippingService()->getID());
			}

			$shipment->recalculateAmounts();
			$shipment->save();

			$history->saveLog();

			return new JSONResponse(array(
					'item' => array(
						'ID'			  => $item->getID(),
						'Shipment'		=> array(
												'ID' => $shipment->getID(),
												'amount' => $shipment->amount,
												'shippingAmount' => $shipment->shippingAmount,
												'taxAmount' => $shipment->taxAmount,
												'total' =>((float)$shipment->shippingAmount + (float)$shipment->amount + (float)$shipment->taxAmount),
												'prefix' => $shipment->getCurrency()->pricePrefix,
												'suffix' => $shipment->getCurrency()->priceSuffix,
												'Order' => $shipment->order->toFlatArray()
											 ),
						'count'		   => $item->count,
						'price'		   => $item->price,
						'priceCurrencyID' => $item->getCurrency()->getID(),
						'downloadable' => $item->getProduct()->isDownloadable()
					)
				),
				'success'
			);
		}
		else
		{
			return new JSONResponse(false, 'failure', '_error_removing_item_from_shipment');
		}
	}

	/**
	 * @role update
	 */
	public function changeShipmentAction()
	{
		if(($id = (int)$this->request->get("id", null, false)) && ($fromID = (int)$this->request->get("from", null, false)) && ($toID = (int)$this->request->get("to", null, false)))
		{
			$item = OrderedItem::getInstanceByID('OrderedItem', $id, true, array('Product'));

			$oldShipment = Shipment::getInstanceByID('Shipment', $fromID, true, array('Order' => 'CustomerOrder', 'ShippingAddress' => 'UserAddress', 'Currency'));
			$newShipment = Shipment::getInstanceByID('Shipment', $toID, true, array('Order' => 'CustomerOrder', 'ShippingAddress' => 'UserAddress', 'Currency'));

			$history = new OrderHistory($oldShipment->order, $this->user);

			$zone = $oldShipment->getDeliveryZone();

			if($oldShipment !== $newShipment)
			{
				$oldShipment->loadItems();
				$oldShipment->removeItem($item);

				$newShipment->loadItems();
				$newShipment->addItem($item);


				if($oldShipment->getShippingService())
				{
					$shipmentRates = $zone->getShippingRates($oldShipment);
					$oldShipment->setAvailableRates($shipmentRates);

					$oldShipment->setRateId($oldShipment->getShippingService()->getID());
					$oldShipment->save();
				}

				if($newShipment->getShippingService())
				{
					$shipmentRates = $zone->getShippingRates($newShipment);
					$newShipment->setAvailableRates($shipmentRates);

					$newShipment->setRateId($newShipment->getShippingService()->getID());
					$newShipment->save();
				}

				$item->save();

				if($newShipment->getSelectedRate() || !$newShipment->getShippingService() || !is_int($newShipment->getShippingService()->getID()))
				{
					$item->save();

					$oldShipment->recalculateAmounts();
					$newShipment->recalculateAmounts();

					$oldShipment->save();
					$newShipment->save();

					$history->saveLog();

					return new JSONResponse(
						array(
							'oldShipment' => array(
								'ID' => $oldShipment->getID(),
								'amount' => $oldShipment->amount,
								'shippingAmount' => $oldShipment->shippingAmount,
								'taxAmount' => $oldShipment->taxAmount,
								'total' =>((float)$oldShipment->shippingAmount + (float)$oldShipment->amount + (float)$oldShipment->taxAmount),
								'prefix' => $oldShipment->getCurrency()->pricePrefix,
								'suffix' => $oldShipment->getCurrency()->priceSuffix
							),
							'newShipment' => array(
								'ID' => $newShipment->getID(),
								'amount' =>  $newShipment->amount,
								'shippingAmount' =>  $newShipment->shippingAmount,
								'taxAmount' => $newShipment->taxAmount,
								'total' => ((float)$newShipment->shippingAmount + (float)$newShipment->amount + (float)$newShipment->taxAmount),
								'prefix' => $newShipment->getCurrency()->pricePrefix,
								'suffix' => $newShipment->getCurrency()->priceSuffix,
								'Order' => $newShipment->order->toFlatArray()
							)
						),
						'success',
						$this->translate('_ordered_item_was_successfully_moved')
					);
				}
				else
				{
					return new JSONResponse(
						array(
							'oldShipment' => array('ID' => $fromID),
							'newShipment' => array('ID' => $toID)
						),
						'failure',
						$this->translate('_this_shipping_service_has_no_available_rates')
					);
				}
			}
		}
		else
		{
			return new JSONResponse(array('status' => 'failure'));
		}
	}

	public function changeCountAction()
	{
		if(($id = (int)$this->request->get("id", null, false)) )
		{
			$count = (int)$this->request->get("count");
			$price = (float)$this->request->get("price");
			$item = OrderedItem::getInstanceByID('OrderedItem', $id, true, array('Shipment', 'Order' => 'CustomerOrder', 'ShippingService', 'Currency', 'ShippingAddress' => 'UserAddress', 'Product', 'Category'));
			$item->customerOrder->loadAll();
			$history = new OrderHistory($item->customerOrder, $this->user);

			$item->count->set($count);

			if ($item->price != $price)
			{
				$item->price->set($item->getCurrency()->round($item->reduceBaseTaxes($price)));
			}

			$shipment = $item->shipment;
			$shipment->loadItems();

			if($shipment->getShippingService())
			{
				$shipmentRates = $shipment->getDeliveryZone()->getShippingRates($shipment);
				$shipment->setAvailableRates($shipmentRates);
				$shipment->setRateId($shipment->getShippingService()->getID());
			}

			$shipment->recalculateAmounts();

			$item->customerOrder->save(true);
			$item->save();
			$shipment->save();

			$history->saveLog();

			return new JSONResponse(array(
				'ID' => $item->getID(),
				'Shipment' => array(
					'ID' => $shipment->getID(),
					'Order' => $item->customerOrder->toFlatArray(),
					'isDeleted' => $item->isDeleted(),
					'amount' => $shipment->amount,
					'downloadable' => !$shipment->isShippable(),
					'shippingAmount' => $shipment->shippingAmount,
					'total' =>((float)$shipment->shippingAmount + (float)$shipment->amount + (float)$shipment->taxAmount),
					'taxAmount' => $shipment->taxAmount,
					'prefix' => $shipment->getCurrency()->pricePrefix,
					'suffix' => $shipment->getCurrency()->priceSuffix
				 )),
				 'success'
			 );
		}
		else
		{
			return new JSONResponse(false, 'failure', $this->translate('_error_updating_item_quantity'));
		}
	}

	public function optionFormAction()
	{
		$item = ActiveRecordModel::getInstanceById('OrderedItem', $this->request->get('id'), true, true);
		$item->customerOrder->loadAll();

		$c = new OrderController($this->application);

		$response = $c->optionForm($item->customerOrder, '');
		$response->set('currency', $item->customerOrder->currency->getID());
		return $response;
	}

	public function variationFormAction()
	{
		$this->loadLanguageFile('Frontend');
		$this->loadLanguageFile('Product');
		$this->loadLanguageFile('backend/Shipment');
		$item = ActiveRecordModel::getInstanceById('OrderedItem', $this->request->get('id'), true, true);
		$item->customerOrder->loadAll();

		$c = new OrderController($this->application);

		$response = $c->variationForm($item->customerOrder, '');
		$response->set('currency', $item->customerOrder->currency->getID());
		$response->set('variations', $item->getProduct()->getVariationData($this->application));
		return $response;
	}

	public function saveOptionsAction()
	{
		$item = ActiveRecordModel::getInstanceByID('OrderedItem', $this->request->get('id'), OrderedItem::LOAD_DATA, OrderedItem::LOAD_REFERENCES);
		$item->customerOrder->loadAll();
		foreach ($item->getProduct()->getOptions(true) as $option)
		{
			OrderController::modifyItemOption($item, $option, $this->request, OrderController::getFormFieldName($item, $option));
		}

		$item->save();
		$item->price->set($item->getPrice(true));
		$item->shipment->save();
		$item->customerOrder->getTotal(true);
		$item->customerOrder->save();

		return $this->getItemResponse($item);
	}

	public function saveVariationsAction()
	{
		$item = ActiveRecordModel::getInstanceByID('OrderedItem', $this->request->get('id'), OrderedItem::LOAD_DATA, OrderedItem::LOAD_REFERENCES);
		$item->customerOrder->loadAll();

		$variations = $item->getProduct()->getVariationData($this->application);

		$c = new OrderController($this->application);
		if (!$c->buildVariationsValidator($item, $variations)->isValid())
		{
			return new RawResponse();
		}

		$product = $c->getVariationFromRequest($variations);
		$item->product->set($product);
		$item->setCustomPrice($product->getItemPrice($item));

		$item->save();
		$item->shipment->save();
		$item->customerOrder->save();

		return $this->getItemResponse($item);
	}

	public function itemsAction()
	{
		$request = $this->getRequest();
		$ids = explode(',', $request->get('item_ids'));
		$items = array();
		$this->application->getLocale()->translationManager()->loadFile('backend/Shipment');
		$set = new ProductSet();
		foreach($ids as $id)
		{
			$item = ActiveRecordModel::getInstanceByID('OrderedItem', $id, OrderedItem::LOAD_DATA);
			$item->customerOrder->load();
			$item->customerOrder->loadItems();
			if ($image = $item->getProduct()->defaultImage)
			{
				$image->load();
			}
			$items[] = $item->toArray();
			$set->add($item->getProduct()->getParent());
		}
		$response = new ActionResponse('items', $items);

		// load product options and variations
		//  pp(array_keys(ProductOption::loadOptionsForProductSet($set)));
		$response->set('allOptions', ProductOption::loadOptionsForProductSet($set));
		$response->set('variations', $set->getVariationData($this->application));

		return $response;
	}

	public function itemAction()
	{
		$item = ActiveRecordModel::getInstanceByID('OrderedItem', $this->request->get('id'), OrderedItem::LOAD_DATA);
		return $this->getItemResponse($item);
	}

	public function downloadOptionFileAction()
	{
				$f = select(eq('OrderedItem.ID', $this->request->get('id')),
					eq('ProductOptionChoice.optionID', $this->request->get('option')));

		$set = ActiveRecordModel::getRecordSet('OrderedItemOption', $f, array('CustomerOrder', 'OrderedItem', 'ProductOptionChoice'));
		if ($set->size())
		{
			return new ObjectFileResponse($set->get(0)->getFile());
		}
	}

	private function getItemResponse(OrderedItem $item)
	{
		$item->customerOrder->load();
		$item->customerOrder->loadItems();

		if ($image = $item->getProduct()->defaultImage)
		{
			$image->load();
		}

		$this->application->getLocale()->translationManager()->loadFile('backend/Shipment');

		$response = new ActionResponse('item', $item->toArray());

		// load product options and variations
		$response->set('allOptions', ProductOption::loadOptionsForProductSet($item->getProduct()->getParent()->initSet()));
		$response->set('variations', $item->getProduct()->getParent()->initSet()->getVariationData($this->application));

		return $response;
	}
}

?>
