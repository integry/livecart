<?php

/**
 *
 * @package application.model.product
 */
class DiscountHandler
{
	private $discounts;

	public function __construct()
	{
		$this->discounts = array();
	}

	/**
	 * Returns discounts array.
	 * @return array
	 */
	public function getDiscounts()
	{
		return $this->discounts;
	}

	/**
	 * Proccess request data and calculates discounts
	 * $param array $data Request data
	 */
	public function proccessRequestData($data)
	{

		$j = 0;
		for ($i = 0; $i < $data['discountLayersIndex']; $i++)
		{
			if (isSet($data['amount_'.$i]))
			{
				$this->discounts[$j]['amount'] = $data['amount_'.$i];
				$this->discounts[$j]['discountValue'] = $data['discountValue_'.$i];
				$this->discounts[$j]['discountType'] = $data['discountType_'.$i];
				$j++;
			}
		}

		usort($this->discounts, "CompareResults");
	}

	/**
	 * Loads discounts from db by product
	 * @param ActiveRecord $product
	 */
	public function loadDataFromDb($product)
	{
		$discountSet = $product->getDiscountsSet();

		$j = 0;
		$this->discounts = array();
		foreach($discountSet->toArray()as $discount)
		{

			$this->discounts[$j]['amount'] = $discount['amount'];
			$this->discounts[$j]['discountValue'] = $discount['discountValue'];
			$this->discounts[$j]['discountType'] = $discount['discountType'];
			$j++;
		}
	}

	/**
	 * Validates discounts
	 * @return bool
	 */
	public function isValid()
	{

		$j = 0;
		foreach($this->discounts as $value)
		{
			if (!preg_match("/^([0-9]+)$/", $value['amount']))
			{
				$this->errorsList[$j] = "Items count must be whole numbers and more than 0. ";
			}
			else
			{
				for ($i = 0; $i < $j; $i++)
				{
					if ($value['amount'] == $this->discounts[$i]['amount'])
					{
						$this->errorsList[$j] = "This items count is the same as ".($i + 1).".";
					}
				}
			}

			if (!is_numeric($value['discountValue']) || $value['discountValue'] < 0)
			{
				@$this->errorsList[$j] .= "Discount must be whole numbers and more than 0. ";
			}
			else if ($value['discountType'] == 1 && ($value['discountValue'] < 0 || $value['discountValue'] >= 100))
			{
				@$this->errorsList[$j] .= "Discount percents must be from 0 to 100. ";
			}

			$j++;
		}

		if (empty($this->errorsList))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Saves state.
	 */
	public function saveState()
	{
		@session_start();
		unset($_SESSION['DiscountHandler']);
		$_SESSION['DiscountHandler']['values'] = $this->discounts;
		$_SESSION['DiscountHandler']['errors'] = !empty($this->errorsList) ? $this->errorsList: false;
	}

	/**
	 * Checks if discountHandler validation in previous form request has failed.
	 * @return bool
	 */
	public function validationFailed()
	{
		@session_start();
		if (!empty($_SESSION['DiscountHandler']['errors']))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Restores previuos state.
	 */
	public function restore()
	{
		@session_start();
		if (isSet($_SESSION['DiscountHandler']))
		{
			$this->discounts = $_SESSION['DiscountHandler']['values'];
			$this->errorsList = $_SESSION['DiscountHandler']['errors'];
		}
		unset($_SESSION['DiscountHandler']);
	}

	/**
	 * Create js script string for bodyOnload, to create input fields.
	 * $currencyName string
	 * @return string
	 */
	public function createJs($currencyName)
	{
		$js = '';
		$j = 0;
		foreach($this->discounts as $value)
		{

			$error = !empty($this->errorsList[$j]) ? ", \"".$this->errorsList[$j]."\"": '';
			$js .= "discount.addDiscount(\"".$currencyName."\", \"".$value['amount']."\", \"".$value['discountValue']."\", ".$value['discountType']." ".$error."); ";
			$j++;
		}
		return $js;
	}
}

function CompareResults($a, $b)
{
	if ($a['amount'] == $b['amount'])
	{
		return 0;
	}
	return ($a['amount'] < $b['amount']) ?  - 1: 1;
}


?>
