<?php

ClassLoader::import("application/controller/backend/abstract/StoreManagementController");
ClassLoader::import("application/model/tax/Tax");
ClassLoader::import("application/model/tax/TaxRate");
ClassLoader::import("application/model/delivery/DeliveryZone");

/**
 * Application settings management
 *
 * @package application/controller/backend
 * @author	Integry Systems
 * @role delivery
 */
class TaxRateController extends StoreManagementController
{
	public function indexAction()
	{
		if(($zoneID = (int)$this->request->get('id')) <= 0)
		{
			$deliveryZone = null;
			$deliveryZoneArray = array('ID' => '-1');
			$taxRates = TaxRate::getRecordSetByDeliveryZone($deliveryZone);
		}
		else
		{
			$deliveryZone = DeliveryZone::getInstanceByID($zoneID, true);
			$deliveryZoneArray = $deliveryZone->toArray();
			$taxRates = $deliveryZone->getTaxRates();
		}

		$taxes = Tax::getAllTaxes()->toArray();
		$classes = TaxClass::getAllClasses()->toArray();

		$form = $this->createTaxRateForm();
		foreach($taxRates as $tax)
		{
			$form->set($this->getFieldName($tax->tax->get(), $tax->taxClass->get()), $tax->rate->get());
		}


		$this->set('deliveryZone', $deliveryZoneArray);
		$this->set('form', $form);
		$this->set('taxes', $taxes);
		$this->set('classes', $classes);
	}

	/**
	 * @role update
	 */
	public function saveAction()
	{
		$taxes = Tax::getAllTaxes();
		$classes = TaxClass::getAllClasses();

		if(($zoneID = (int)$this->request->get('id')) <= 0)
		{
			$taxRates = TaxRate::getRecordSetByDeliveryZone(null);
			$deliveryZone = DeliveryZone::getDefaultZoneInstance();
		}
		else
		{
			$deliveryZone = DeliveryZone::getInstanceByID($zoneID, true);
			$taxRates = $deliveryZone->getTaxRates();
		}

		ActiveRecord::beginTransaction();

		// delete all rates
		foreach ($taxRates as $rate)
		{
			$rate->delete();
		}

		foreach ($taxes as $tax)
		{
			$this->saveRate($deliveryZone, $tax, null);

			foreach ($classes as $class)
			{
				$this->saveRate($deliveryZone, $tax, $class);
			}
		}

		ActiveRecord::commit();

		return new JSONResponse(false, 'success', $this->translate('_tax_rates_have_been_successfully_saved'));
	}

	private function saveRate(DeliveryZone $zone, Tax $tax, TaxClass $class = null)
	{
		$value = $this->request->get($this->getFieldName($tax, null, $class));
		if (!is_null($value) && ($value !== ''))
		{
			$taxRate = TaxRate::getNewInstance($zone, $tax, $value);
			$taxRate->taxClass->set($class);
			$taxRate->save();
			return $taxRate;
		}
	}

	/**
	 * @return Form
	 */
	private function createTaxRateForm()
	{
		return new Form($this->createTaxRateFormValidator());
	}

	/**
	 * @return RequestValidator
	 */
	private function createTaxRateFormValidator()
	{
		$validator = $this->getValidator('shippingService', $this->request);

		$taxes = Tax::getAllTaxes();
		$classes = TaxClass::getAllClasses();
		foreach ($taxes as $tax)
		{
			$this->setFieldValidation($validator, $tax, null);
			foreach ($classes as $class)
			{
				$this->setFieldValidation($validator, $tax, $class);
			}
		}

		return $validator;
	}

	private function setFieldValidation(RequestValidator $validator, Tax $tax, TaxClass $class = null)
	{
		$field = $this->getFieldName($tax, $class);
		$validator->addCheck($field, new IsNumericCheck($this->translate("_error_rate_should_be_numeric_value")));
		$validator->addCheck($field, new MinValueCheck($this->translate("_error_rate_should_be_greater_than_zero"), 0));
		$validator->addFilter($field, new NumericFilter());
	}

	private function getFieldName(Tax $tax, TaxClass $class = null)
	{
		$classID = $class ? $class->getID() : '';
		return 'tax_' . $tax->getID() . '_' . $classID;
	}
}

?>