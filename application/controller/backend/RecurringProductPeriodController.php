<?php



// why depends from??:

/**
 *
 * @package application/controller/backend
 * @author Integry Systems
 * @role product
 */
class RecurringProductPeriodController extends StoreManagementController
{
	public function indexAction()
	{
		$this->loadLanguageFile('backend/Product');
		$productID = (int)$this->request->get('id');
		$product = Product::getInstanceByID($productID, ActiveRecord::LOAD_DATA);
		$rppa = RecurringProductPeriod::getRecordSetByProduct($product)->toArray();

		$this->set('recurringProductPeriods', $rppa);
		$this->set('product', $product->toArray());

		$newRpp = RecurringProductPeriod::getNewInstance($product);
		$this->set('newRecurringProductPeriod', $newRpp->toArray());
		$this->set('newForm', $this->createForm($newRpp->toArray()));
		$this->set('currencies', $this->application->getCurrencyArray(true));
		$this->set('periodTypes', array_map(array($this,'translate'), RecurringProductPeriod::getAllPeriodTypes(RecurringProductPeriod::PERIOD_TYPE_NAME_PLURAL)));

	}

	public function editAction()
	{
		$this->loadLanguageFile('backend/Product');
		$rpp = RecurringProductPeriod::getInstanceByID((int)$this->request->get('id'), ActiveRecord::LOAD_DATA);
		$rpp = $rpp->toArray();

		$form = $this->createForm($rpp);

		$this->set('recurringProductPeriod', $rpp);
		$this->set('form', $form);
		$this->set('periodTypes', array_map(array($this,'translate'), RecurringProductPeriod::getAllPeriodTypes(RecurringProductPeriod::PERIOD_TYPE_NAME_PLURAL)));
		$this->set('currencies', $this->application->getCurrencyArray(true));

	}


	/**
	 * @role update
	 */
	public function updateAction()
	{
		$request = $this->getRequest();
		$rpp = RecurringProductPeriod::getInstanceByID($request->get('id'), true);

		return $this->save($rpp);
	}

	/**
	 * @role create
	 */
	public function createAction()
	{
		$request = $this->getRequest();
		$rpp = RecurringProductPeriod::getNewInstance(
			Product::getInstanceByID((int)$request->get('productID'), ActiveRecord::LOAD_DATA)
		);
		$rpp->position->set(1000);

		return $this->save($rpp);
	}

	public function deleteAction()
	{
		$request = $this->getRequest();
		$rpp = RecurringProductPeriod::getInstanceByID($request->get('id'));
		$rpp->delete();
		return new JSONResponse(null, 'success');
	}

	private function save(RecurringProductPeriod $rpp)
	{
		$request = $this->getRequest();
		$validator = $this->createFormValidator($rpp->toArray());
		if($validator->isValid())
		{
			$rpp->loadRequestData($this->request);
			// null value is not set by loadRequestData()..
			$rebillCount = $this->request->get('rebillCount');
			$rebillCount=floor($rebillCount);
			$rpp->rebillCount->set(is_numeric($rebillCount) && $rebillCount <= 0 ? $rebillCount : NULL);
			$rpp->save();
			$product = $rpp->product;
			$currencies = array();
			foreach ($this->application->getCurrencyArray(true) as $currency)
			{
				if (array_key_exists($currency, $currencies) == false)
				{
					$currencies[$currency] = Currency::getInstanceByID($currency);
				}
				foreach(array(
					ProductPrice::TYPE_SETUP_PRICE => $request->get('ProductPrice_setup_price_'.$currency),
					ProductPrice::TYPE_PERIOD_PRICE => $request->get('ProductPrice_period_price_'.$currency)
					) as $type=>$value)
				{
					$price = ProductPrice::getInstance($product, $currencies[$currency], $rpp, $type);
					if (strlen($value) == 0 && $price->isExistingRecord())
					{
						$price->delete();
					}
					else
					{
						$price->price->set($value);
						$price->save();
					}
				}
			}
			return new JSONResponse(array('rpp' => $rpp->toArray()), 'success');
		}
		else
		{
			return new JSONResponse(array('errors' => $validator->getErrorList()), 'failure', $this->translate('_could_not_save_recurring_product_period_entry'));
		}
	}

	/**
	 * @return Form
	 */
	private function createForm($rpp)
	{
		$form = new Form($this->createFormValidator($rpp));
		$form->setData($rpp);
		return $form;
	}

	/**
	 * @return \Phalcon\Validation
	 */
	public function createFormValidatorAction($rpp)
	{
		$validator = $this->getValidator(
			'RecurringProductPeriodForm_'.( $rpp['ID'] ? $rpp['ID'] : ''), $this->request);
		$validator->add('name', new Validator\PresenceOf(array('message' => $this->translate('_error_the_name_should_not_be_empty'))));
		$validator->add('periodLength', new Validator\PresenceOf(array('message' => $this->translate('_error_period_length_should_not_be_empty'))));
		// $validator->add('rebillCount', new Validator\PresenceOf(array('message' => $this->translate('_error_rebill_count_should_not_be_empty'))));
		$validator->add('periodLength', new IsNumericCheck($this->translate('_error_period_length_expected_positive_numeric')));
		// $validator->add('rebillCount', new IsNumericCheck($this->translate('_error_rebill_count_expected_positive_numeric')));
		$validator->add('periodLength', new MinValueCheck($this->translate('_error_period_length_expected_positive_numeric'), 1));
		// $validator->add('rebillCount', new MinValueCheck($this->translate('_error_rebill_count_expected_positive_numeric'), 1));
		$validator->addFilter('periodLength', new NumericFilter());
		// $validator->addFilter('rebillCount', new NumericFilter());

		ProductController::addPricesValidator($validator, 'ProductPrice_period_');

		// setup price is not required.
		$validator->addFilter('ProductPrice_setup_price', new NumericFilter());
		foreach ($this->getApplication()->getCurrencyArray() as $currency)
		{
			$validator->addFilter('ProductPrice_setup_price_' . $currency, new NumericFilter());
		}

		return $validator;
	}
}

?>