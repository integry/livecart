<?php

ClassLoader::importNow("application/model/delivery/DeliveryZone");

/**
 * Application settings management
 *
 * @package application/controller/backend
 * @author Integry Systems
 * @role delivery
 */
class DeliveryZoneController extends StoreManagementController
{
	/**
	 * Main settings page
	 */
	public function indexAction()
	{
		$zones = array(
			0 => array('ID' => -1, 'name' => $this->translate('_default_zone')),
			1 => array('ID' => -2, 'name' => $this->translate('_delivery_zones')),
			2 => array('ID' => -3, 'name' => $this->translate('_tax_zones'))
		);

		foreach(DeliveryZone::getAll()->toArray() as $zone)
		{
			$zones[1]['items'][] = array('ID' => $zone['ID'], 'name' => $zone['name'], 'type'=>$zone['type']);
			$zones[2]['items'][] = array('ID' => $zone['ID'], 'name' => $zone['name'], 'type'=>$zone['type']);
		}

		$response = new ActionResponse();
		$response->set('zones', json_encode($zones));
		$response->set('countryGroups', json_encode($this->locale->info()->getCountryGroups()));
		$response->set('testAddress', new Form(new RequestValidator('testAddress', $this->request)));
		$response->set('countries', array_merge(array('' => ''), $this->application->getEnabledCountries()));
		$this->loadLanguageFile('backend/ShippingService');
		return $response;
	}

	public function countriesAndStatesAction()
	{
		if(($id = (int)$this->getId()) <= 0)
		{
			return;
		}

		$deliveryZone = DeliveryZone::getInstanceByID($id, true);
		$localeInfo = $this->locale->info();
		$allCountries = $countries = $localeInfo->getAllCountries();

		$stateCountry = $this->config->get('STORE_COUNTRY');
		if (!isset($allCountries[$stateCountry]))
		{
			$stateCountry = 'US';
		}

		$selectedCountries = array();
		foreach($deliveryZone->getCountries()->toArray() as $country)
		{
			$selectedCountries[$country['countryCode']] = $allCountries[$country['countryCode']];
			unset($countries[$country['countryCode']]);
		}

		$alternativeLanguagesCodes = array();
		foreach ($this->application->getLanguageArray() as $lang)
		{
			$alternativeLanguagesCodes[$lang] = $this->locale->info()->getOriginalLanguageName($lang);
		}

		$form = $this->createCountriesAndStatesForm($deliveryZone);
		$form->setData($deliveryZone->toArray());
		$form->set('stateListCountry', $stateCountry);

		$states = $this->getStates($deliveryZone, $stateCountry);

		$response = new ActionResponse();
		$response->set('form', $form);
		$response->set('zoneID', $id);
		$response->set('states', $states['all']);
		$response->set('allCountries', $allCountries);
		$response->set('countries', $countries);
		$response->set('countryGroups', $this->locale->info()->getCountryGroups());
		$response->set('selectedCountries', $selectedCountries);
		$response->set('selectedStates', $states['selected']);
		$response->set('zipMasks', $deliveryZone->getZipMasks()->toArray());
		$response->set('cityMasks', $deliveryZone->getCityMasks()->toArray());
		$response->set('addressMasks', $deliveryZone->getAddressMasks()->toArray());
		$response->set('defaultLanguageCode', $this->application->getDefaultLanguageCode());
		$response->set('alternativeLanguagesCodes', $alternativeLanguagesCodes);
		$this->assignAllTypes($response);
		return $response;
	}

	public function loadStatesAction()
	{
		$zone = DeliveryZone::getInstanceByID($this->getId(), true);
		$states = $this->getStates($zone, $this->request->gget('country'));

		return new JSONResponse($states['all']);
	}

	private function getStates(DeliveryZone $zone, $country)
	{
		$localeInfo = $this->locale->info();
		$allCountries = $countries = $localeInfo->getAllCountries();

		$allStates = array();
		foreach(State::getStatesByCountry($country) as $stateID => $state)
		{
			$allStates[$stateID] = $localeInfo->getCountryName($country) . ": " . $state;
		}

		$selectedStates = array();
		foreach($zone->getStates()->toArray() as $state)
		{
			$selectedStates[$state['State']['ID']] = $localeInfo->getCountryName($state['State']['countryID']) . ": " . $state['State']['name'];
			unset($allStates[$state['State']['ID']]);
		}

		return array('all' => $allStates, 'selected' => $selectedStates);
	}

	/**
	 * @role update
	 */
	public function saveStatesAction()
	{
		$zone = DeliveryZone::getInstanceByID((int)$this->getId());
		DeliveryZoneState::removeByZone($zone);

		foreach((array)$this->request->gget('active') as $activeStateID)
		{
			$state = State::getInstanceByID((int)$activeStateID);
			$deliveryZoneState = DeliveryZoneState::getNewInstance($zone, $state);
			$deliveryZoneState->save();
		}

		return new JSONResponse(false, 'success');
	}

	/**
	 * @role update
	 */
	public function saveCountriesAction()
	{
		$zone = DeliveryZone::getInstanceByID((int)$this->getId());
		DeliveryZoneCountry::removeByZone($zone);

		foreach((array)$this->request->gget('active') as $countryCode)
		{
			$deliveryZoneState = DeliveryZoneCountry::getNewInstance($zone, $countryCode);
			$deliveryZoneState->save();
		}

		return new JSONResponse(false, 'success', $this->translate('_countries_list_was_successfully_updated'));
	}

	/**
	 * @role create
	 */
	public function createAction()
	{
		$zone = DeliveryZone::getNewInstance();
		$zone->name->set($this->translate('_new_delivery_zone'));
		$zone->save();

		return new JSONResponse(array('zone' => $zone->toArray()), 'success', $this->translate('_new_delivery_zone_was_successfully_created'));
	}

	/**
	 * @role update
	 */
	public function saveAction()
	{
		$zone = DeliveryZone::getInstanceByID((int)$this->getId());
		$zone->name->set($this->request->gget('name'));
		$zone->type->set((int)$this->request->gget('type'));

		$zone->isEnabled->set((int)$this->request->gget('isEnabled'));
		$zone->isFreeShipping->set((int)$this->request->gget('isFreeShipping'));
		$zone->isRealTimeDisabled->set((int)$this->request->gget('isRealTimeDisabled'));

		$zone->save();

		return new JSONResponse(false, 'success');
	}

	/**
	 * @role update
	 */
	public function saveCityMaskAction()
	{
		if(($errors = $this->isValidMask()) === true)
		{
			$maskValue = $this->request->gget('mask');
			if($id = (int)$this->getId())
			{
				$mask = DeliveryZoneCityMask::getInstanceByID($id);
				$mask->mask->set($maskValue);
			}
			else
			{
				$zone = DeliveryZone::getInstanceByID((int)$this->request->gget('zoneID'));
				$mask = DeliveryZoneCityMask::getNewInstance($zone, $maskValue);
			}

			$mask->save();

			return new JSONResponse(array('ID' => $mask->getID()), 'success');
		}
		else
		{
			return new JSONResponse(array('errors' => $errors), 'failure', $this->translate('_could_not_save_mask'));
		}
	}

	/**
	 * @role remove
	 */
	public function deleteAction()
	{
		DeliveryZone::getInstanceByID((int)$this->getId())->delete();

		return new JSONResponse(false, 'success');
	}

	/**
	 * @role update
	 */
	public function deleteCityMaskAction()
	{
		DeliveryZoneCityMask::getInstanceByID((int)$this->getId())->delete();

		return new JSONResponse(false, 'success');
	}

	/**
	 * @role update
	 */
	public function saveZipMaskAction()
	{
   		if(($errors = $this->isValidMask()) === true)
		{
			$maskValue = $this->request->gget('mask');
			if($id = (int)$this->getId())
			{
				$mask = DeliveryZoneZipMask::getInstanceByID($id);
				$mask->mask->set($maskValue);
			}
			else
			{
				$zone = DeliveryZone::getInstanceByID((int)$this->request->gget('zoneID'));
				$mask = DeliveryZoneZipMask::getNewInstance($zone, $maskValue);
			}

			$mask->save();

			return new JSONResponse(array('ID' => $mask->getID()), 'success');
		}
		else
		{
			return new JSONResponse(false, 'failure', $this->translate('_could_not_save_mask'));
		}
	}

	/**
	 * @role update
	 */
	public function deleteZipMaskAction()
	{
		DeliveryZoneZipMask::getInstanceByID((int)$this->getId())->delete();

		return new JSONResponse(false, 'success');
	}

	/**
	 * @role update
	 */
	public function saveAddressMaskAction()
	{
   		if(($errors = $this->isValidMask()) === true)
		{
			$maskValue = $this->request->gget('mask');
			if($id = (int)$this->getId())
			{
				$mask = DeliveryZoneAddressMask::getInstanceByID($id);
				$mask->mask->set($maskValue);
			}
			else
			{
				$zone = DeliveryZone::getInstanceByID((int)$this->request->gget('zoneID'));
				$mask = DeliveryZoneAddressMask::getNewInstance($zone, $maskValue);
			}

			$mask->save();

			return new JSONResponse(array('ID' => $mask->getID()), 'success');
		}
		else
		{
			return new JSONResponse(array('errors' => $errors), 'failure', $this->translate('_could_not_save_mask'));
		}
	}

	/**
	 * @role update
	 */
	public function deleteAddressMaskAction()
	{
		DeliveryZoneAddressMask::getInstanceByID((int)$this->request->gget('id'))->delete();

		return new JSONResponse(false, 'success');
	}

	public function testAddressAction()
	{
		$address = UserAddress::getNewInstance();
		$address->loadRequestData($this->request);
		$zone = DeliveryZone::getZoneByAddress($address, $this->request->gget('type'));

		return new JSONResponse($zone->toArray());
	}

	private function createCountriesAndStatesForm(DeliveryZone $zone)
	{
		return new Form($this->createCountriesAndStatesFormValidator($zone));
	}

	private function createCountriesAndStatesFormValidator(DeliveryZone $zone)
	{
		$validator = $this->getValidator('countriesAndStates', $this->request);

		return $validator;
	}

	private function isValidMask() {
		if($this->request->gget('mask'))
		{
			return true;
		}
		else
		{
			return array('mask' => $this->translate('_error_mask_is_empty'));
		}
	}

	private function assignAllTypes(Response $response)
	{
		$response->set('allTypes', array(
			DeliveryZone::BOTH_RATES => $this->translate('_tax_and_shipping_rates'),
			DeliveryZone::SHIPPING_RATES  => $this->translate('_shipping_rates'),
			DeliveryZone::TAX_RATES => $this->translate('_tax_rates')
		));
	}

	private function getId()
	{
		$request = $this->getRequest();
		$id = $request->gget('id');
		if(strpos($id, '_') !== false)
		{
			$chunks = explode('_', $id);
			return $chunks[1];
		}
		return $id;
	}
}

?>
