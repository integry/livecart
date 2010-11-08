<?php
ClassLoader::import('application.model.product.Product');
ClassLoader::import('application.model.product.ProductPrice');
ClassLoader::import('application.model.product.RecurringProductPeriod');
ClassLoader::import('application.controller.backend.abstract.StoreManagementController');


ClassLoader::import('application.controller.backend.ProductController'); // for price validator

// why depends from??:
ClassLoader::import('application.model.delivery.ShippingClass');
ClassLoader::import('application.model.tax.TaxClass');
ClassLoader::import('application.model.category.CategoryImage');

/**
 *
 * @package application.controller.backend
 * @author Integry Systems
 * @role product
 */
class RecurringProductPeriodController extends StoreManagementController
{
	public function index()
	{
		$this->loadLanguageFile('backend/Product');
		$productID = (int)$this->request->get('id');
		$product = Product::getInstanceByID($productID, ActiveRecord::LOAD_DATA);
		$rppa = RecurringProductPeriod::getAssignedToArray($product);
		$response = new ActionResponse();
		$response->set('recurringProductPeriods', $rppa);
		$response->set("product", $product->toArray());

		$newRpp = RecurringProductPeriod::getNewInstance($product);
		$response->set("newRecurringProductPeriod", $newRpp->toArray());
		$response->set("newForm", $this->createForm($newRpp->toArray()));
		$response->set("currencies", $this->application->getCurrencyArray(true));

		$this->assignPeriodTypes($response);
		return $response;
	}

	public function edit()
	{
		$this->loadLanguageFile('backend/Product');
		$rpp = RecurringProductPeriod::getInstanceByID((int)$this->request->get('id'), ActiveRecord::LOAD_DATA);
		$rpp = $rpp->toArray();

		$form = $this->createForm($rpp);
		$response = new ActionResponse();
		$response->set('recurringProductPeriod', $rpp);
		$response->set('form', $form);
		$this->assignPeriodTypes($response);
		$response->set("currencies", $this->application->getCurrencyArray(true));

		return $response;
	}


	/**
	 * @role update
	 */
	public function update()
	{
		$request = $this->getRequest();
		$rpp = RecurringProductPeriod::getInstanceByID($request->get('id'), true);

		return $this->save($rpp);
	}

	/**
	 * @role create
	 */
	public function create()
	{
		$request = $this->getRequest();
		$rpp = RecurringProductPeriod::getNewInstance(
			Product::getInstanceByID((int)$request->get('productID'), ActiveRecord::LOAD_DATA)
		);
		$rpp->position->set(1000);

		return $this->save($rpp);
	}

	public function delete()
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
			$rpp->save();
			$product = $rpp->product->get();
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
	 * @return RequestValidator
	 */
	public function createFormValidator($rpp)
	{
		$validator = $this->getValidator(
			"RecurringProductPeriodForm_".( $rpp['ID'] ? $rpp['ID'] : ''), $this->request);
		$validator->addCheck("name", new IsNotEmptyCheck($this->translate("_error_the_name_should_not_be_empty")));

		ProductController::addPricesValidator($validator, 'ProductPrice_period_');
		ProductController::addPricesValidator($validator, 'ProductPrice_setup_');

		return $validator;
	}

	private function assignPeriodTypes($response)
	{
		$response->set('periodTypes', array(
			RecurringProductPeriod::TYPE_PERIOD_DAY => $this->translate('_type_period_day'),
			RecurringProductPeriod::TYPE_PERIOD_WEEK =>$this->translate('_type_period_week'),
			RecurringProductPeriod::TYPE_PERIOD_MONTH =>$this->translate('_type_period_month'),
			RecurringProductPeriod::TYPE_PERIOD_YEAR =>$this->translate('_type_period_year')
		));
	}
}

?>