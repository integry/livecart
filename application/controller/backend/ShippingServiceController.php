<?php


/**
 * Application settings management
 *
 * @package application.controller.backend
 * @author Integry Systems
 * @role delivery
 */
class ShippingServiceController extends StoreManagementController
{
	public function indexAction()
	{
		if(($zoneID = (int)$this->request->gget('id')) <= 0)
		{
			$deliveryZoneArray = array('ID' => '');
			$shippingServices = ShippingService::getByDeliveryZone();
		}
		else
		{
			$deliveryZone = DeliveryZone::getInstanceByID($zoneID, true);
			$deliveryZoneArray = $deliveryZone->toArray();
			$shippingServices = $deliveryZone->getShippingServices();
		}

		$shippingServicesArray = array();
		foreach($shippingServices as $service)
		{
			$shippingServicesArray[$service->getID()] = $service->toArray();
			$shippingServicesArray[$service->getID()]['rangeTypeString'] = $this->translate($service->rangeType->get() == 0 ? '_weight_based_rates' : '_subtotal_based_rates');
			$shippingServicesArray[$service->getID()]['ratesCount'] = $service->getRates()->getTotalRecordCount();
		}

		$form = $this->createShippingServiceForm();
		$form->setData(array('rangeType' => 0));

		$response = new ActionResponse();
		$response->set('shippingServices', $shippingServicesArray);
		$response->set('newService', array('DeliveryZone' => $deliveryZoneArray));
		$response->set('newRate', array('ShippingService' => array('DeliveryZone' => $deliveryZoneArray, 'ID' => '')));
		$response->set('deliveryZone', $deliveryZoneArray);
		$response->set('defaultCurrencyCode', $this->application->getDefaultCurrency()->getID());
		$response->set('form', $form);
		return $response;
	}

	private function getSelectOptionsFromSet(ARSet $set)
	{
		$options = array();

		foreach ($set as $record)
		{
			$arr = $record->toArray();
			$options[$record->getID()] = $arr['name_lang'];
		}

		return $options;
	}

	/**
	 * @role update
	 */
	public function deleteAction()
	{
		$service = ShippingService::getInstanceByID((int)$this->request->gget('id'));
		$service->delete();

		return new JSONResponse(false, 'success');
	}

	public function editAction()
	{
		$shippingService = ShippingService::getInstanceByID($this->request->gget('id'), true);
		$spec = $shippingService->getSpecification();
		$form = $this->createShippingServiceForm();
		$form->setData($shippingService->toArray());
		$response = new ActionResponse();
		$spec->setFormResponse($response, $form);
		$response->set('form', $form);

		$response->set('service', $shippingService->toArray());
		$response->set('shippingRates', $shippingService->getRates()->toArray());
		$response->set('newRate', array('ShippingService' => $shippingService->toArray()));
		$response->set('defaultCurrencyCode', $this->application->getDefaultCurrency()->getID());
		$response->set('shippingClasses', $this->getSelectOptionsFromSet(ShippingClass::getAllClasses()));

		return $response;
	}

	/**
	 * @role update
	 */
	public function createAction()
	{
		if(($deliveryZoneId = (int)$this->request->gget('deliveryZoneID')) > 0)
		{
			$deliveryZone = DeliveryZone::getInstanceByID($deliveryZoneId, true);
		}
		else
		{
			$deliveryZone = null;
		}

		$shippingService = ShippingService::getNewInstance($deliveryZone, $this->request->gget('name'), $this->request->gget('rangeType'));

		return $this->save($shippingService);
	}

	/**
	 * @role update
	 */
	public function updateAction()
	{
		$shippingService = ShippingService::getInstanceByID((int)$this->request->gget('serviceID'), ShippingService::LOAD_DATA, ShippingService::LOAD_REFERENCES);
		return $this->save($shippingService);
	}

	/**
	 * @role update
	 */
	public function validateRatesAction()
	{
		$ratesData = $this->getRatesFromRequest();
		$errors = $this->validateRate('', $ratesData['']);
		return empty($errors)
			? new JSONResponse(array('validation' => 'success'))
			: new JSONResponse(array('validation' => 'failure', 'errors' => $errors));
	}

	/**
	 * @role update
	 */
	public function sortAction()
	{
		foreach($this->request->gget($this->request->gget('target'), array()) as $position => $key)
		{
		   $shippingService = ShippingService::getInstanceByID((int)$key);
		   $shippingService->position->set((int)$position);
		   $shippingService->save();
		}

		return new JSONResponse(false, 'success');
	}

	private function isNotValid($name, $rates = array())
	{
		$errors = array();

		if($name == '')
		{
			$errors['name'] = $this->translate('_error_name_should_not_be_empty');
		}

		foreach($rates as $id => $rate)
		{
			if(!empty($id))
			{
				$errors = array_merge($errors, $this->validateRate($id, $rate));
			}
		}

		return empty($errors) ? false : $errors;
	}

	private function getRatesFromRequest()
	{
		$rates = array();

		foreach($this->request->toArray() as $variable => $value)
		{
			$matches = array();
			
			
			if(preg_match('/^rate_([^_]*)_(perKgCharge|subtotalPercentCharge|perItemCharge|perItemChargeClass|flatCharge|weightRangeEnd|weightRangeStart|subtotalRangeEnd|subtotalRangeStart)$/', $variable, $matches))
			{
				$id = $matches[1];
				$name = $matches[2];

				$rates[$id][$name] = $value;
			}
		}
		//pp($rates['']);
		
		
		$rangeType = $this->request->gget('rangeType');

		// unset rate without id (or it will mess up sorting)
		if(isset($rates['']))
		{
			unset($rates['']); 
		}
		// unset empty rates (last column)
		$unsetKeys = array();
		foreach($rates as $key=>$rate)
		{
			if(!count(array_filter($rate)))
			{
				$unsetKeys[] = $key; // should not unset in forech()
			}
		}
		while($key = array_shift($unsetKeys))
		{
			unset($rates[$key]);
		}

		$previousEnd = 0;
		if ($rangeType == ShippingService::SUBTOTAL_BASED)
		{
			uasort($rates, array($this, 'sortRangesBySubtotalRangeEnd'));
			foreach($rates as &$rate)
			{
				$rate = array_merge($rate, array(
					'subtotalRangeStart' => array_key_exists('subtotalRangeStart', $rate) && is_numeric($rate['subtotalRangeStart']) ? $rate['subtotalRangeStart'] : $previousEnd,
					'weightRangeStart' => 0,
					'weightRangeEnd' => 0,
					'perKgCharge' => 0)
				);
				$previousEnd = $rate['subtotalRangeEnd'] + 0.01; // smallest Currency amount
			}
		}
		else if ($rangeType == ShippingService::WEIGHT_BASED)
		{
			uasort($rates, array($this, 'sortRangesByWeightRangeEnd'));
			foreach($rates as &$rate)
			{
				$rate = array_merge($rate, array(
					'weightRangeStart' => array_key_exists('weightRangeStart', $rate) && is_numeric($rate['weightRangeStart']) ? $rate['weightRangeStart'] : $previousEnd,
					'subtotalRangeStart' => 0,
					'subtotalRangeEnd' => 0,
					'subtotalPercentCharge' => 0)
				);
				$previousEnd = $rate['weightRangeEnd'] + 0.001; // smallest weight amount
			}
		}
		return $rates;
	}

	private function sortRangesByWeightRangeEnd($a, $b)
	{
		return $a['weightRangeEnd'] - $b['weightRangeEnd'];
	}

	private function sortRangesBySubtotalRangeEnd($a, $b)
	{
		return $a['subtotalRangeEnd'] - $b['subtotalRangeEnd'];
	}

	private function save(ShippingService $shippingService)
	{
		$ratesData = $this->getRatesFromRequest();
		$rates = array();
		if(!($errors = $this->isNotValid($this->request->gget('name'), $ratesData)))
		{
			$shippingService->loadRequestData($this->request);
			$shippingService->setValueArrayByLang(array('name'), $this->application->getDefaultLanguageCode(), $this->application->getLanguageArray(true, false), $this->request);
			$shippingService->isFinal->set($this->request->gget('isFinal'));
			$shippingService->setValueArrayByLang(array('description'), $this->application->getDefaultLanguageCode(), $this->application->getLanguageArray(true, false), $this->request);
			$shippingService->deliveryTimeMinDays->set($this->request->gget('deliveryTimeMinDays'));
			$shippingService->deliveryTimeMaxDays->set($this->request->gget('deliveryTimeMaxDays'));
			$shippingService->save();
			$shippingService->deleteShippingRates();
			$shippingServiceArray = $shippingService->toArray();
			$shippingServiceArray['newRates'] = array();
			
			foreach($ratesData as $id => $data)
			{
				if (!$id)
				{
					continue;
				}
				if($shippingService->rangeType->get() == ShippingService::WEIGHT_BASED)
				{
					$rangeStart = $data['weightRangeStart'];
					$rangeEnd = $data['weightRangeEnd'];
				}
				else if($shippingService->rangeType->get() == ShippingService::SUBTOTAL_BASED)
				{
					$rangeStart = $data['subtotalRangeStart'];
					$rangeEnd = $data['subtotalRangeEnd'];
				}
				$rate = ShippingRate::getNewInstance($shippingService, $rangeStart, $rangeEnd);
				foreach($data as $var => $value)
				{
					$rate->$var->set($value);
				}
				$rate->save();
				$shippingServiceArray['newRates'][$id] = $rate->getID();
			}
			return new JSONResponse(array('service' => $shippingServiceArray), 'success');
		}
		else
		{
			return new JSONResponse(array('errors' => $errors), 'failure', $this->translate('_could_note_save_shipping_service'));
		}
	}

	private function createShippingServiceForm()
	{
		return new Form($this->createShippingServiceFormValidator());
	}

	private function createShippingServiceFormValidator()
	{
		$validator = $this->getValidator('shippingService', $this->request);

		return $validator;
	}

	private function validateRate($id, $rate)
	{
	   $errors = array();
	   if($this->request->gget('rangeType') == ShippingService::WEIGHT_BASED)
	   {
		   if(!is_numeric($rate['weightRangeStart'])) $errors["rate_" . $id . "_weightRangeStart"] = $this->translate('_error_range_start_should_be_a_float_value');
		   if(!is_numeric($rate['weightRangeEnd'])) $errors["rate_" . $id . "_weightRangeEnd"] = $this->translate('_error_range_end_should_be_a_float_value');
		   if(!empty($rate['perKgCharge']) && !is_numeric($rate['perKgCharge'])) $errors["rate_" . $id . "_perKgCharge"] = $this->translate('_error_per_kg_charge_should_be_a_float_Value');

		   if(empty($errors) && $rate['weightRangeStart'] > $rate['weightRangeEnd']) $errors["rate_" . $id . "_weightRangeStart"] = $this->translate('_error_range_start_should_be_less_end');
	   }
	   else
	   {
		   if(!is_numeric($rate['subtotalRangeStart'])) $errors["rate_" . $id . "_subtotalRangeStart"] = $this->translate('_error_range_start_should_be_a_float_value');
		   if(!is_numeric($rate['subtotalRangeEnd'])) $errors["rate_" . $id . "_subtotalRangeEnd"] = $this->translate('_error_range_end_should_be_a_float_value');
		   if(!empty($rate['subtotalPercentCharge']) && !is_numeric($rate['subtotalPercentCharge'])) $errors["rate_" . $id . "_subtotalPercentCharge"] = $this->translate('_error_subtotal_percent_charge_should_be_a_float_value');

		   if(empty($errors) && $rate['subtotalRangeStart'] > $rate['subtotalRangeEnd']) $errors["rate_" . $id . "_weightRangeStart"] = $this->translate('_error_range_start_should_be_less_end');
	   }

	   if(!empty($rate['flatCharge']) && !is_numeric($rate['flatCharge'])) $errors["rate_" . $id . "_flatCharge"] = $this->translate('_error_flat_charge_should_be_a_float_value');
	   if(!empty($rate['perItemCharge']) && !is_numeric($rate['perItemCharge'])) $errors["rate_" . $id . "_perItemCharge"] = $this->translate('_error_per_item_charge_should_be_a_float_value');

	   return $errors;
	}

}
?>