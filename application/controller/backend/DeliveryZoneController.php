<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.delivery.DeliveryZone");
ClassLoader::import("application.model.delivery.State");
ClassLoader::import("framework.request.validator.RequestValidator");
ClassLoader::import("framework.request.validator.Form");


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
		$zones = array();
		$zones[] = array('ID' => -1, 'name' => $this->translate('_default_zone'));
		foreach(DeliveryZone::getAll()->toArray() as $zone)
		{
			$zones[] = array('ID' => $zone['ID'], 'name' => $zone['name']);
		}

		$response = new ActionResponse();
		$response->set('zones', json_encode($zones));
		$response->set('countryGroups', json_encode($this->locale->info()->getCountryGroups()));
		return $response;
	}

	public function countriesAndStates()
	{
		if(($id = (int)$this->request->get('id')) <= 0)
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

		return $response;
	}

	public function loadStates()
	{
		$zone = DeliveryZone::getInstanceByID($this->request->get('id'), true);
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
		$zone = DeliveryZone::getInstanceByID((int)$this->request->get('id'));
		DeliveryZoneState::removeByZone($zone);

		foreach($this->request->get('active') as $activeStateID)
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
		$zone = DeliveryZone::getInstanceByID((int)$this->request->get('id'));
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
		$zone = DeliveryZone::getInstanceByID((int)$this->request->get('id'));
		$zone->name->set($this->request->get('name'));

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
			if($id = (int)$this->request->get('id'))
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
		DeliveryZone::getInstanceByID((int)$this->request->get('id'))->delete();

		return new JSONResponse(false, 'success');
	}

	/**
	 * @role update
	 */
	public function deleteCityMask()
	{
		DeliveryZoneCityMask::getInstanceByID((int)$this->request->get('id'))->delete();

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
			if($id = (int)$this->request->get('id'))
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
		DeliveryZoneZipMask::getInstanceByID((int)$this->request->get('id'))->delete();

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
			if($id = (int)$this->request->get('id'))
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

	private function createCountriesAndStatesForm(DeliveryZone $zone)
	{
		return new Form($this->createCountriesAndStatesFormValidator($zone));
	}

	private function createCountriesAndStatesFormValidator(DeliveryZone $zone)
	{
		$validator = new RequestValidator('countriesAndStates', $this->request);

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

}

?>