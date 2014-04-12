<?php

namespace delivery;

use \Currency;

//ClassLoader::import('library/shipping/ShippingRateSet');
//ClassLoader::import('library/shipping/ShippingRateResult');
//ClassLoader::import('application/model/delivery/ShippingService');

require_once(__ROOT__ . '/library/shipping/ShippingRateResult.php');
require_once(__ROOT__ . '/library/shipping/ShippingRateSet.php');

/**
 * Shipping cost calculation result for a particular Shipment. One Shipment can have several
 * ShipmentDeliveryRates - one for each available shipping service. Customer is able to choose between
 * the available rates. ShipmentDeliveryRate can be either a pre-defined rate or a real-time rate.
 *
 * @package application/model/delivery
 * @author Integry Systems <http://integry.com>
 */
class ShipmentDeliveryRate extends \ShippingRateResult implements \Serializable
{
	protected $amountWithTax;
	protected $amountWithoutTax;

	private $service;

	public static function getNewInstance(ShippingService $service, $cost)
	{
		$inst = new ShipmentDeliveryRate($service->getDI());
		$inst->setServiceId($service->getID());
		$inst->setCost((string)round($cost, 3), $inst->application->getDefaultCurrencyCode());
		$inst->setService($service);
		$inst->setServiceName($service->getValueByLang('name'));
		return $inst;
	}

	public static function getRealTimeRates(ShippingRateCalculator $handler, Shipment $shipment)
	{
		$rates = new ShippingRateSet();
		$handler->setWeight($shipment->getChargeableWeight());
		$order = $shipment->order;

		// TODO: fix issue when address has zip and country data, but are missing city, user and record id!
		//             (now workround - get address id, if $address has no id, load address by id)
		if ($order->isMultiAddress)
		{
			$address = $shipment->shippingAddress;
			$arr = $shipment->toArray();
		}
		else
		{
			$address = $order->shippingAddress;
			$arr = $order->toArray();
		}

		if (!$address->getID() && array_key_exists('shippingAddressID', $arr))
		{
			$address = UserAddress::getInstanceByID($arr['shippingAddressID'], true);
		}

		if (!$address)
		{
			return $rates;
		}

		$handler->setDestCountry($address->countryID);
		$handler->setDestState($address->state ? $address->state->code : $address->stateName);
		$handler->setDestZip($address->postalCode);
		$handler->setDestCity($address->city);
		$config = $shipment->getApplication()->getConfig();
		$handler->setSourceCountry($config->get('STORE_COUNTRY'));
		$handler->setSourceZip($config->get('STORE_ZIP'));
		$handler->setSourceState($config->get('STORE_STATE'));
		foreach ($handler->getAllRates() as $k => $rate)
		{
			$newRate = new ShipmentDeliveryRate();
			$newRate->setApplication($shipment->getApplication());
			$newRate->setCost($rate->getCostAmount(), $rate->getCostCurrency());
			$newRate->setServiceName($rate->getServiceName());
			$newRate->setClassName($rate->getClassName());
			$newRate->setProviderName($rate->getProviderName());
			$newRate->setServiceId($rate->getClassName() . '_' . $k);
			$rates->add($newRate);
		}

		return $rates;
	}

	public function getAmountByCurrency(Currency $currency)
	{
		$amountCurrency = Currency::getInstanceById($this->getCostCurrency());
		$amount = $currency->convertAmount($amountCurrency, $this->getCostAmount());

		return $amount;
	}

	public function setAmountByCurrency(Currency $currency, $amount)
	{
		$amountCurrency = Currency::getInstanceById($this->getCostCurrency());
		$this->setCost($amountCurrency->convertAmount($currency, $amount));
	}

	public function setAmountWithTax($amount)
	{
		$this->amountWithTax = (string)round($amount, 3);
	}

	public function setAmountWithoutTax($amount)
	{
		$this->amountWithoutTax = (string)round($amount, 3);
	}

	public function setService(ShippingService $service)
	{
		$this->service = $service;
	}

	public function getService()
	{
		return $this->service;
	}

	public function toArray($amount = null)
	{
		$array = parent::toArray();

		if (!is_null($amount))
		{
			$array['costAmount'] = $amount;
		}

		$amountCurrency = Currency::getInstanceById($array['costCurrency']);
		$currencies = $this->application->getCurrencySet();

		// get and format prices
		$prices = $formattedPrices = $taxPrices = $unformattedTaxPrices = array();

		foreach ($currencies as $id => $currency)
		{
			$prices[$id] = $currency->convertAmount($amountCurrency, $array['costAmount']);
			$formattedPrices[$id] = $currency->getFormattedPrice($prices[$id]);
			$unformattedTaxPrices[$id] = $currency->convertAmount($amountCurrency, $this->amountWithTax);
			$taxPrices[$id] = $currency->getFormattedPrice($unformattedTaxPrices[$id]);
			$withoutTaxPrices[$id] = $currency->convertAmount($amountCurrency, $this->amountWithoutTax);
			$formattedWithoutTaxPrices[$id] = $currency->getFormattedPrice($withoutTaxPrices[$id]);
		}

		$array['price'] = $prices;
		$array['priceWithTax'] = $unformattedTaxPrices;
		$array['formattedPrice'] = $formattedPrices;
		$array['taxPrice'] = $taxPrices;
		$array['priceWithoutTax'] = $withoutTaxPrices;
		$array['formattedPriceWithoutTax'] = $formattedWithoutTaxPrices;

		// shipping service name
		$id = $this->getServiceID();
		if (is_numeric($id))
		{
			$service = ShippingService::getInstanceById($id);
			
			if ($service)
			{
				$array['ShippingService'] = $service->toArray();
			}
			else
			{
				return array();
			}
		}
		else
		{
			$array['ShippingService'] = array('name_lang' => $this->getServiceName(), 'provider' => $this->getProviderName());
		}

		return $array;
	}

	public function serialize()
	{
		$vars = get_object_vars($this);
		unset($vars['application']);
		unset($vars['service']);

		return serialize($vars);
	}

	public function unserialize($serialized)
	{
		foreach (unserialize($serialized) as $key => $value)
		{
			$this->$key = $value;
		}
	}
}
