<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::importNow("application.model.delivery.DeliveryZone");
ClassLoader::import("application.model.delivery.State");

/**
 * Application settings management
 *
 * @package application.controller.backend
 * @author Integry Systems
 * @role delivery
 */
class DeliveryZoneController extends StoreManagementController
{
	/**
	 * Main settings page
	 */
	public function index()
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

		return $response;
	}

	public function countriesAndStates()
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

	public function loadStates()
	{
		$zone = DeliveryZone::getInstanceByID($this->getId(), true);
		$states = $this->getStates($zone, $this->request->get('country'));

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
	public function saveStates()
	{
		$zone = DeliveryZone::getInstanceByID((int)$this->getId());
		DeliveryZoneState::removeByZone($zone);

		foreach((array)$this->request->get('active') as $activeStateID)
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
	public function saveCountries()
	{
		$zone = DeliveryZone::getInstanceByID((int)$this->getId());
		DeliveryZoneCountry::removeByZone($zone);

		foreach((array)$this->request->get('active') as $countryCode)
		{
			$deliveryZoneState = DeliveryZoneCountry::getNewInstance($zone, $countryCode);
			$deliveryZoneState->save();
		}

		return new JSONResponse(false, 'success', $this->translate('_countries_list_was_successfully_updated'));
	}

	/**
	 * @role create
	 */
	public function create()
	{
		$zone = DeliveryZone::getNewInstance();
		$zone->name->set($this->translate('_new_delivery_zone'));
		$zone->save();

		return new JSONResponse(array('zone' => $zone->toArray()), 'success', $this->translate('_new_delivery_zone_was_successfully_created'));
	}

	/**
	 * @role update
	 */
	public function save()
	{
		$zone = DeliveryZone::getInstanceByID((int)$this->getId());
		$zone->name->set($this->request->get('name'));
		$zone->type->set((int)$this->request->get('type'));

		$zone->isEnabled->set((int)$this->request->get('isEnabled'));
		$zone->isFreeShipping->set((int)$this->request->get('isFreeShipping'));
		$zone->isRealTimeDisabled->set((int)$this->request->get('isRealTimeDisabled'));

		$zone->save();

		return new JSONResponse(false, 'success');
	}

	/**
	 * @role update
	 */
	public function saveCityMask()
	{
		if(($errors = $this->isValidMask()) === true)
		{
			$maskValue = $this->request->get('mask');
			if($id = (int)$this->getId())
			{
				$mask = DeliveryZoneCityMask::getInstanceByID($id);
				$mask->mask->set($maskValue);
			}
			else
			{
				$zone = DeliveryZone::getInstanceByID((int)$this->request->get('zoneID'));
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
	public function delete()
	{
		DeliveryZone::getInstanceByID((int)$this->getId())->delete();

		return new JSONResponse(false, 'success');
	}

	/**
	 * @role update
	 */
	public function deleteCityMask()
	{
		DeliveryZoneCityMask::getInstanceByID((int)$this->getId())->delete();

		return new JSONResponse(false, 'success');
	}

	/**
	 * @role update
	 */
	public function saveZipMask()
	{
   		if(($errors = $this->isValidMask()) === true)
		{
			$maskValue = $this->request->get('mask');
			if($id = (int)$this->getId())
			{
				$mask = DeliveryZoneZipMask::getInstanceByID($id);
				$mask->mask->set($maskValue);
			}
			else
			{
				$zone = DeliveryZone::getInstanceByID((int)$this->request->get('zoneID'));
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
	public function deleteZipMask()
	{
		DeliveryZoneZipMask::getInstanceByID((int)$this->getId())->delete();

		return new JSONResponse(false, 'success');
	}

	/**
	 * @role update
	 */
	public function saveAddressMask()
	{
   		if(($errors = $this->isValidMask()) === true)
		{
			$maskValue = $this->request->get('mask');
			if($id = (int)$this->getId())
			{
				$mask = DeliveryZoneAddressMask::getInstanceByID($id);
				$mask->mask->set($maskValue);
			}
			else
			{
				$zone = DeliveryZone::getInstanceByID((int)$this->request->get('zoneID'));
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
	public function deleteAddressMask()
	{
		DeliveryZoneAddressMask::getInstanceByID((int)$this->request->get('id'))->delete();

		return new JSONResponse(false, 'success');
	}

	public function testAddress()
	{
		$address = UserAddress::getNewInstance();
		$address->loadRequestData($this->request);
		$zone = DeliveryZone::getZoneByAddress($address, DeliveryZone::SHIPPING_RATES);

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
		if($this->request->get('mask'))
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
			DeliveryZone::BOTH_RATES => $this->translate('_both_zones'),
			DeliveryZone::SHIPPING_RATES  => $this->translate('_delivery_zones'),
			DeliveryZone::TAX_RATES => $this->translate('_tax_zones')
		));
	}

	private function getId()
	{
		$request = $this->getRequest();
		$id = $request->get('id');
		if(strpos($id, '_') !== false)
		{
			$chunks = explode('_', $id);
			return $chunks[1];
		}
		return $id;
	}
}

?>
