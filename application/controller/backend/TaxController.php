<?php


/**
 *
 * @package application/controller/backend
 * @author	Integry Systems
 * @role taxes
 */
class TaxController extends StoreManagementController
{
	/**
	 * List all system currencies
	 * @return ActionResponse
	 */
	public function indexAction()
	{
		$response = new ActionResponse();

		$taxesForms = array();
		$taxes = array();
		foreach(Tax::getAllTaxes() as $tax)
		{
			$taxes[] = $tax->toArray();
			$taxesForms[] = $this->createTaxForm($tax);
		}

		$response->set("taxesForms", $taxesForms);
		$response->set("taxes", $taxes);

		$newTax = Tax::getNewInstance('');
		$response->set("newTaxForm", $this->createTaxForm($newTax));
		$response->set("newTax", $newTax->toArray());

		return $this->appendTaxRates($response);
	}

	public function editAction()
	{
		$tax = Tax::getInstanceByID((int)$this->request->gget('id'), true);
		$form = $this->createTaxForm($tax);
		$form->setData($tax->toArray());
		$response = new ActionResponse();
		$response->set('tax', $tax->toArray());
		$response->set('taxForm', $form);
		return $this->appendTaxRates($response, $tax->getID());
	}

	/**
	 * @role remove
	 */
	public function deleteAction()
	{
		$service = Tax::getInstanceByID((int)$this->request->gget('id'));
		$service->delete();

		return new JSONResponse(false, 'success');
	}

	/**
	 * @role update
	 */
	public function updateAction()
	{
		$request = $this->getRequest();
		$tax = Tax::getInstanceByID((int)$request->gget('id'));
		return $this->saveTax($tax);
	}

	/**
	 * @role create
	 */
	public function createAction()
	{
		$tax = Tax::getNewInstance($this->request->gget('name'));
		$tax->position->set(1000);

		return $this->saveTax($tax);
	}

	private function saveTax(Tax $tax)
	{
		$validator = $this->createTaxFormValidator($tax);
		if($validator->isValid())
		{
			$tax->setValueArrayByLang(array('name'), $this->application->getDefaultLanguageCode(), $this->application->getLanguageArray(true, false), $this->request);
			$tax->save();
			$this->saveTaxRates($tax);
			return new JSONResponse(array('tax' => $tax->toArray()), 'success');
		}
		else
		{
			return new JSONResponse(array('errors' => $validator->getErrorList()), 'failure', $this->translate('_could_not_save_tax_entry'));
		}
	}


	/**
	 * @return Form
	 */
	private function createTaxForm(Tax $tax)
	{
		$form = new Form($this->createTaxFormValidator($tax));

		$form->setData($tax->toArray());

		return $form;
	}

	/**
	 * @return RequestValidator
	 */
	public function createTaxFormValidatorAction(Tax $tax)
	{
		$validator = $this->getValidator("taxForm_" . $tax->isExistingRecord() ? $tax->getID() : '', $this->request);
		$validator->addCheck("name", new IsNotEmptyCheck($this->translate("_error_the_name_should_not_be_empty")));

		$zones = DeliveryZone::getAll();
		$zones->add(DeliveryZone::getDefaultZoneInstance());
		$classes = TaxClass::getAllClasses();
		$this->loadLanguageFile('backend/TaxRate'); // tax rate error messages
		foreach($zones as $zone)
		{
			$this->appendTaxRateFieldValidator($validator, $zone);
			foreach($classes as $class)
			{
				$this->appendTaxRateFieldValidator($validator, $zone, $class);
			}
		}
		return $validator;
	}

	/**
	 * @role update
	 */
	public function sortAction()
	{
		foreach($this->request->gget($this->request->gget('target'), array()) as $position => $key)
		{
		   $tax = Tax::getInstanceByID((int)$key);
		   $tax->position->set((int)$position);
		   $tax->save();
		}

		return new JSONResponse(false, 'success');
	}

	private function appendTaxRates($response, $taxID = null)
	{
		$this->loadLanguageFile('backend/DeliveryZone'); // 'default zone'

		$zones = array();
		//default
		$taxRates = TaxRate::getRecordSetByDeliveryZone(null);
		$zones[] = array(
			'ID' => -1,
			'name' => $this->translate('_default_zone'),
			'taxRates' => $taxRates ? $taxRates->toArray() : null
		);

		// custom
		foreach(DeliveryZone::getTaxZones() as $deliveryZone)
		{
			$zone = $deliveryZone->toArray();
			$taxRates = $deliveryZone->getTaxRates();
			$zones[] = array(
				'ID' => $zone['ID'],
				'name' => $zone['name'],
				'taxRates' => $taxRates ? $taxRates->toArray() : null
			);
		}

		// reorder tax rates in structure $zone['taxRates'][<delivery zone id>][<tax class id>] = ..
		//                                (for default delivery zone and tax class id use -1)
		foreach($zones as &$zone)
		{
			$filtered = array();
			if(is_array($zone['taxRates']))
			{
				foreach($zone['taxRates'] as $taxRate)
				{
					if($taxRate['Tax']['ID'] == $taxID)
					{
						$filtered[$zone['ID']][array_key_exists('taxClassID',$taxRate) ? $taxRate['taxClassID'] : -1] = $taxRate;
					}
				}
			}
			$zone['taxRates'] = $filtered;
			//pp($zone['taxRates']);
		}
		$classes = TaxClass::getAllClasses()->toArray();

		$response->set('zones', $zones);
		$response->set('classes', $classes);
		return $response;
	}

	private function saveTaxRates(Tax $tax)
	{
		$zones = DeliveryZone::getAll();
		$zones->add(DeliveryZone::getDefaultZoneInstance());
		$classes = TaxClass::getAllClasses();
		ActiveRecord::beginTransaction();
		foreach($zones as $zone)
		{
			// delete all zone tax rates
			$taxRates = $zone->getTaxRates();
			foreach ($taxRates as $rate)
			{
				if($rate->taxID->get()->getID() == $tax->getID())
				{
					$rate->delete();
				}
			}
			$this->saveRate($zone, $tax);
			foreach($classes as $class)
			{
				$this->saveRate($zone, $tax, $class);
			}
		}
		ActiveRecord::commit();
	}

	private function saveRate(DeliveryZone $zone, Tax $tax, TaxClass $class = null)
	{
		$value = $this->request->gget($this->getFieldName($zone, $class));
		if (!is_null($value) && ($value !== ''))
		{
			$taxRate = TaxRate::getNewInstance($zone, $tax, $value);
			$taxRate->taxClass->set($class);
			$taxRate->save();
			return $taxRate;
		}
	}

	private function appendTaxRateFieldValidator(LiveCartValidator $validator, DeliveryZone $zone, TaxClass $class = null)
	{
		$field = $this->getFieldName($zone, $class);
		$validator->addCheck($field, new IsNumericCheck($this->translate("_error_rate_should_be_numeric_value")));
		$validator->addCheck($field, new MinValueCheck($this->translate("_error_rate_should_be_greater_than_zero"), 0));
		$validator->addFilter($field, new NumericFilter());
	}

	private function getFieldName(DeliveryZone $zone = null, TaxClass $class = null)
	{
		$classID = $class ? $class->getID() : '-1';
		$zoneID = $zone ? $zone->getID() : '-1';
		if($classID == 0)
		{
			$classID = -1;
		}
		if($zoneID == 0)
		{
			$zoneID = -1;
		}
		return 'taxRate_' . $zoneID . '_' . $classID;
	}

}

?>