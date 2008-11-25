<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.tax.TaxRate");
ClassLoader::import("application.model.delivery.DeliveryZone");

/**
 * Application settings management
 *
 * @package application.controller.backend
 * @author	Integry Systems
 * @role delivery
 */
class TaxRateController extends StoreManagementController
{
	public function index()
	{
		if(($zoneID = (int)$this->request->get('id')) <= 0)
		{
			$deliveryZone = null;
			$deliveryZoneArray = array('ID' => '');
			$taxRatesArray = TaxRate::getRecordSetByDeliveryZone($deliveryZone)->toArray();
		}
		else
		{
			$deliveryZone = DeliveryZone::getInstanceByID($zoneID, true);
			$deliveryZoneArray = $deliveryZone->toArray();
			$taxRatesArray = $deliveryZone->getTaxRates()->toArray();
		}


		$form = $this->createTaxRateForm();
		$enabledTaxes = array();
		foreach(Tax::getTaxes($deliveryZone)->toArray() as $tax)
		{
			$enabledTaxes[$tax['ID']] = $tax['name'];
		}


		$response = new ActionResponse();
		$response->set('enabledTaxes', $enabledTaxes);
		$response->set('taxRates', $taxRatesArray);
		$response->set('newTaxRate', array('ID' => '', 'DeliveryZone' => $deliveryZoneArray));
		$response->set('deliveryZone', $deliveryZoneArray);
		$response->set('form', $form);
		return $response;
	}

	/**
	 * @role update
	 */
	public function delete()
	{
		$taxRate = TaxRate::getInstanceByID((int)$this->request->get('id'), true, array('Tax'));
		$tax = $taxRate->tax->get();
		$taxRate->delete();

		return new JSONResponse(array('tax' => $tax->toArray()), 'success');
	}

	public function edit()
	{
		$rate = TaxRate::getInstanceByID((int)$this->request->get('id'), true, array('Tax'));

		$form = $this->createTaxRateForm();
		$form->setData($rate->toArray());

		$response = new ActionResponse();
		$response->set('taxRate', $rate->toArray());
		$response->set('form', $form);

		return $response;
	}

	/**
	 * @role update
	 */
	public function create()
	{
		if(($deliveryZoneId = (int)$this->request->get('deliveryZoneID')) > 0)
		{
			$deliveryZone = DeliveryZone::getInstanceByID($deliveryZoneId, true);
		}
		else
		{
			$deliveryZone = null;
		}

		$taxRate = TaxRate::getNewInstance($deliveryZone, Tax::getInstanceByID((int)$this->request->get('taxID'), true), (float)$this->request->get('rate'));

		return $this->save($taxRate);
	}

	/**
	 * @role update
	 */
	public function update()
	{
		return $this->save(TaxRate::getInstanceByID((int)$this->request->get('taxRateID'), true));
	}

	/**
	 * @role update
	 */
	public function save(TaxRate $taxRate)
	{
		$validator = $this->createTaxRateFormValidator();
		if($validator->isValid())
		{
			$taxRate->loadRequestData($this->request);
			$taxRate->save();

			return new JSONResponse(array('rate' => $taxRate->toArray()), 'success', $this->translate('_tax_rate_has_been_successfully_saved'));
		}
		else
		{
			return new JSONResponse(array('errors' => $validator->getErrorList()), 'failure', $this->translate('_could_not_save_tax_rate'));
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
		$validator = new RequestValidator('shippingService', $this->request);

		$validator->addCheck("taxID", new IsNotEmptyCheck($this->translate("_error_tax_should_not_be_empty")));
		$validator->addCheck("rate", new IsNotEmptyCheck($this->translate("_error_rate_should_not_be_empty")));
		$validator->addCheck("rate", new IsNumericCheck($this->translate("_error_rate_should_be_numeric_value")));
		$validator->addCheck("rate", new MinValueCheck($this->translate("_error_rate_should_be_greater_than_zero_and_less_than_hundred"), 0));
		$validator->addCheck("rate", new MaxValueCheck($this->translate("_error_rate_should_be_greater_than_zero_and_less_than_hundred"), 100));

		return $validator;
	}

}
?>