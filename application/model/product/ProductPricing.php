<?php

ClassLoader::import('application.model.product.ProductPrice');
ClassLoader::import('application.model.Currency');

/**
 * Product pricing logic. Allows to modify product prices and calculates prices for other currencies
 * if a price is not defined for a particular currency. This class usually should not be used directly
 * as the Product class provides most of the methods necessary for pricing manipulation.
 *
 * @package application.model.product
 * @author Integry Systems <http://integry.com>
 */
class ProductPricing
{
	const CALCULATED = 'calculated';
	const DEFINED = 'defined';
	const BOTH = 'both';
	const LIST_PRICE = true;

	private $product;

	private $prices = array();

	private $removedPrices = array();

	private $listPrices = array();

	private $application;

	public function __construct(Product $product, $prices = null, LiveCart $application)
	{
		$this->product = $product;
		$this->application = $application;

		if (is_null($prices) && $product->getID())
		{
			$prices = $product->getRelatedRecordSet("ProductPrice", new ARSelectFilter());
		}

		if ($prices instanceof ARSet)
		{
			foreach ($prices as $price)
			{
				$this->setPrice($price);
			}
		}
		else if (is_array($prices))
		{
			foreach ($prices as $id => $price)
			{
				$this->prices[$id] = ProductPrice::getNewInstance($product, Currency::getInstanceById($id));
				$this->prices[$id]->price->set($price['price']);
				$this->prices[$id]->listPrice->set($price['listPrice']);
				$this->prices[$id]->serializedRules->set(serialize($price['serializedRules']));
				$this->prices[$id]->resetModifiedStatus();
			}
		}
	}

	/**
	 *	Used to change parent product after cloning
	 */
	public function setProduct(Product $product)
	{
		$this->product = $product;

		foreach ($this->prices as $k => $price)
		{
			$this->prices[$k]->product->set($product);
		}
	}

	public function setPrice(ProductPrice $price)
	{
		$this->prices[$price->currency->get()->getID()] = $price;
	}

	/**
	 * Get price
	 *
	 * @param Currency $currency
	 * @return ProductPrice
	 */
	public function getPrice(Currency $currency)
	{
		if (!$this->isPriceSet($currency))
		{
			return ProductPrice::getNewInstance($this->product, $currency);
		}

		return $this->prices[$currency->getID()];
	}

	/**
	 * Get price by currency code
	 *
	 * @param string $currencyCode
	 * @return ProductPrice
	 */
	public function getPriceByCurrencyCode($currencyCode)
	{
	  	return $this->getPrice(Currency::getInstanceByID($currencyCode));
	}

	public function removePrice(Currency $currency)
	{
		$this->removedPrices[$currency->getID()] = $currency;
		unset($this->prices[$currency->getID()]);
	}

	public function removePriceByCurrencyCode($currencyCode, $listPrice = false)
	{
		if (isset($this->prices[$currencyCode]))
		{
			if ($listPrice)
			{
				$this->prices[$currencyCode]->listPrice->setNull();
				return false;
			}

			$this->removedPrices[] = $this->prices[$currencyCode];
		}

		unset($this->prices[$currencyCode]);
	}

	public function isPriceSet(Currency $currency)
	{
		return isset($this->prices[$currency->getID()]);
	}

	public function getPrices()
	{
		return $this->prices;
	}

	public function save()
	{
		foreach ($this->prices as $price)
		{
			$price->save();
		}
		foreach ($this->removedPrices as $price)
		{
			$price->delete();
		}
		$this->removedPrices = array();
	}

	/**
	 * Convert current product prices to array
	 *
	 * @param string $part Which part of array prices you want to get ('defined', 'calculated' or 'both')
	 */
	public function toArray($part = null, $listPrice = false)
	{
		if (!in_array($part, array('defined', 'calculated', 'both')))
		{
			$part = 'both';
		}

		$field = $listPrice ? 'listPrice' : 'price';

		$ruleController = $this->application->getBusinessRuleController();

		$defined = array();
		foreach ($this->prices as $inst)
		{
			$defPrice = $inst->$field->get();
			$curr = $inst->currency->get()->getID();
			if ('price' == $field)
			{
				$defined[$curr] = $inst->getPriceByGroup($ruleController->getContext()->getUserGroupID());
				if ($defined[$curr] != $defPrice)
				{
					$this->setListPrice($curr, $defPrice);
				}
			}
			else
			{
				$defined[$curr] = $defPrice;
			}
		}

		if ($listPrice)
		{
			$defined = array_merge($defined, $this->listPrices);
		}

		$baseCurrency = $this->application->getDefaultCurrencyCode();
		$basePrice = isset($defined[$baseCurrency]) ? $defined[$baseCurrency] : 0;

		$formattedPrice = $calculated = array();

		$parent = $this->product->parent->get();
		$setting = $this->product->getChildSetting('price');

		foreach ($this->application->getCurrencySet() as $id => $currency)
		{
			if (!empty($defined[$id]))
		  	{
		  		$calculated[$id] = $defined[$id];
			}
			else
			{
				$calculated[$id] = ProductPrice::calculatePrice($this->product, $currency, $basePrice);
			}

			if (!$calculated[$id] && $listPrice)
			{
				continue;
			}

			if ($parent && (($setting != Product::CHILD_OVERRIDE) || !$calculated[$id]))
			{
				$parentPrice = $parent->getPrice($id);
				$calculated[$id] += $parentPrice * (($setting != Product::CHILD_ADD) ? 1 : -1);
			}

			if (!$listPrice)
			{
				$calculated[$id] = $this->application->getDisplayTaxPrice($calculated[$id], $this->product);
				$discountedPrice = $ruleController->getProductPrice($this->product, $calculated[$id], $currency->getID());
				if ($discountedPrice != $calculated[$id])
				{
					$this->setListPrice($currency->getID(), $calculated[$id]);
					$calculated[$id] = $discountedPrice;
				}
			}

			if ((float)$calculated[$id] || !$listPrice)
			{
				$formattedPrice[$id] = $currency->getFormattedPrice($calculated[$id]);
			}
		}

		$return = array('defined' => $defined, 'calculated' => $calculated, 'formattedPrice' => $formattedPrice);
		return ('both' == $part) ? $return : $return[$part];
	}

	private function setListPrice($currencyID, $price)
	{
		$this->listPrices[$currencyID] = $price;
	}

	public function getDiscountPrices(User $user, $currency)
	{
		if (!$currency instanceof Currency)
		{
			$currency = Currency::getInstanceByID($currency);
		}

		$price = $this->getPrice($currency);
		if (!$price->getPrice())
		{
			$price = $this->getPriceByCurrencyCode($this->application->getDefaultCurrencyCode());
		}

		$prices = array();
		foreach ($price->getUserPrices($user) as $quant => $pr)
		{
			$pr = $currency->convertAmount($price->currency->get(), $pr);
			$prices[$quant] = array(
								'price' => $pr,
								'formattedPrice' => $currency->getFormattedPrice($pr),
								'from' => $quant
							);
		}

		foreach ($prices as $quant => &$price)
		{
			if (isset($previousPrice))
			{
				$previousPrice['to'] = $quant - 1;
			}

			$previousPrice =& $price;
		}

		return $prices;
	}

	public function __clone()
	{
		foreach ($this->prices as $k => $price)
		{
			$this->prices[$k] = clone $price;
		}
	}

	public function __destruct()
	{
		foreach ($this->prices as $k => $price)
		{
			$this->prices[$k]->__destruct();
		}
		unset($this->prices);

		foreach ($this->removedPrices as $k => $price)
		{
			$this->removedPrices[$k]->__destruct();
		}
		unset($this->removedPrices);
	}
}

?>