<?php

/**
 * Defines a system currency. There can be multiple currencies active at the same time.
 * This allows to define product prices in different currencies or convert the prices
 * automatically using the currency rates. In addition to product prices, shipping rates,
 * taxes and other charges can also be converted to other currencies using the currency rates.
 *
 * @package application.model
 * @author Integry Systems <http://integry.com>
 */
class Currency extends ActiveRecordModel
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("Currency");

		$schema->registerField(new ARPrimaryKeyField("ID", ArChar::instance(3)));

		$schema->registerField(new ARField("rate", ArFloat::instance(16)));
		$schema->registerField(new ARField("lastUpdated", ArDateTime::instance()));
		$schema->registerField(new ARField("isDefault", ArBool::instance()));
		$schema->registerField(new ARField("isEnabled", ArBool::instance()));
		$schema->registerField(new ARField("position", ARInteger::instance()));
		$schema->registerField(new ARField("pricePrefix", ARText::instance(20)));
		$schema->registerField(new ARField("priceSuffix", ARText::instance(20)));
		$schema->registerField(new ARField("decimalSeparator", ARVarchar::instance(3)));
		$schema->registerField(new ARField("thousandSeparator", ARVarchar::instance(3)));
		$schema->registerField(new ARField("decimalCount", ARInteger::instance()));
	}

	/*####################  Static method implementations ####################*/

	public static function getInstanceById($id, $loadData = true)
	{
		return ActiveRecordModel::getInstanceById(__CLASS__, $id, $loadData);
	}

	public static function getNewInstance($id)
	{
		$inst = parent::getNewInstance(__class__);
		$inst->setID($id);
		return $inst;
	}

	/*####################  Instance retrieval ####################*/

	/**
	 *  Return Currency instance by ID and provide additional validation. If the currency doesn't exist
	 *  or is not valid, instance of the default currency is returned.
	 *
	 *  @return Currency
	 */
	public static function getValidInstanceById($id, $loadData = true)
	{
		try
		{
			$instance = ActiveRecordModel::getInstanceById(__CLASS__, $id, $loadData);
		}
		catch (ARNotFoundException $e)
		{
			$instance = null;
		}

		if (!$instance || !$instance->isEnabled->get())
		{
			$instance = self::getApplication()->getDefaultCurrency();
		}

		return $instance;
	}

	/*####################  Value retrieval and manipulation ####################*/

	public function setAsDefault($default = true)
	{
	  	$this->isDefault->set((bool)$default);
	}

	public function isDefault()
	{
	  	return $this->isDefault->get();
	}

	public function getFormattedPrice($price)
	{
		if (!$this->isLoaded())
		{
			$this->load();
		}

		return $this->pricePrefix->get() . number_format($price, $this->decimalCount->get(), $this->decimalSeparator->get(), $this->thousandSeparator->get()) . $this->priceSuffix->get();
	}

	public function convertAmountFromDefaultCurrency($amount)
	{
		if ($this->isDefault->get())
		{
			return $amount;
		}

		$rate = $this->rate->get();
		return $amount / (empty($rate) ? 1 : $rate);
	}

	public function convertAmountToDefaultCurrency($amount)
	{
		if ($this->isDefault->get())
		{
			return $amount;
		}

		$rate = $this->rate->get();
		return $amount * (empty($rate) ? 1 : $rate);
	}

	public function convertAmount(Currency $currency, $amount)
	{
		$amount = $currency->convertAmountToDefaultCurrency($amount);
		return $this->convertAmountFromDefaultCurrency($amount);
	}

	public function round($amount)
	{
		return round($amount, $this->decimalCount->get() ? $this->decimalCount->get() : 2);
	}

	/*####################  Data array transformation ####################*/

	public static function transformArray($array, ARSchema $schema)
	{
		$array = parent::transformArray($array, $schema);
		$array['name'] = self::getApplication()->getLocale()->info()->getCurrencyName($array['ID']);
		$array['format'] = $array['pricePrefix'] . '%d.' . $array['decimalCount'] . 'f' . $array['priceSuffix'];
		return $array;
	}

	/*####################  Saving ####################*/

	public static function deleteById($id)
	{
		// make sure the currency record exists
		$inst = ActiveRecord::getInstanceById('Currency', $id, true);

		// make sure it's not the default currency
		if (true != $inst->isDefault->get())
		{
			ActiveRecord::deleteByID('Currency', $id);
			return true;
		}
		else
		{
		  	return false;
		}
	}

	public function save($forceOperation = 0)
	{
		// do not allow 0 rates
		if (!$this->rate->get())
		{
			$this->rate->set(1);
		}

//		file_put_contents(ClassLoader::getRealPath('installdata.currency.test') . '.php', var_export($this->priceSuffix->get(), true));

		return parent::save($forceOperation);
	}

	protected function insert()
	{
	  	// check currency symbol
	  	if (!$this->pricePrefix->get() && !$this->priceSuffix->get())
	  	{
			$prefixes = include ClassLoader::getRealPath('installdata.currency.signs') . '.php';
			if (isset($prefixes[$this->getID()]))
			{
				$signs = $prefixes[$this->getID()];

				$this->pricePrefix->set($signs[0]);

				if (isset($signs[1]))
				{
					$this->priceSuffix->set($signs[1]);
				}
			}
		}

		// check if default currency exists
		$filter = new ARSelectFilter();
		$filter->setCondition(new EqualsCond(new ARFieldHandle('Currency', 'isDefault'), 1));

		$r = ActiveRecord::getRecordSet('Currency', $filter);
		$isDefault = ($r->getTotalRecordCount() == 0);

	  	// get max position
		$filter = new ARSelectFilter();
		$filter->setOrder(new ARFieldHandle('Currency', 'position'), 'DESC');
		$filter->setLimit(1);

		$r = ActiveRecord::getRecordSet('Currency', $filter);
		if ($r->getTotalRecordCount() > 0)
		{
			$max = $r->get(0);
			$position = $max->position->get() + 1;
		}
		else
		{
		  	$position = 0;
		}

		if ($isDefault)
		{
		  	$this->isDefault->set(true);
		  	$this->isEnabled->set(true);
		}

		$this->position->set($position);

		return parent::insert();
	}
}

?>
