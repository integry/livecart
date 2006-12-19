<?php

ClassLoader::import("library.*");
ClassLoader::import("application.controller.backend.abstract.StoreManagementController");

/**
 *
 * @package application.controller.backend
 */
class CurrencyController extends StoreManagementController
{

	private $defaultId;
	private $currData;
	
	/**
	 * List all system currencies
	 * @return ActionResponse
	 */
	public function index()
	{
		$filter = new ArSelectFilter();
		$filter->setOrder(new ArFieldHandle('Currency', 'position'), 'ASC');
//		$filter->setOrder(new ArFieldHandle("Currency", "isDefault"), ArSelectFilter::ORDER_DESC);

		$currSet = ActiveRecord::getRecordSet("Currency", $filter, true);

		$curr = $currSet->toArray();
		foreach($curr as $key => $value)
		{
			$curr[$key]['name'] = $this->locale->info()->getCurrencyName($value["ID"]);
		}		

		$response = new ActionResponse();
		$response->setValue("currencies", $curr);

		return $response;
	}

	/**
	 * Displays form for adding new currency
	 * @return ActionRedirectResponse
	 */
	public function addForm()
	{
		$currencies = $this->locale->info()->getAllCurrencies();  	
		
		foreach ($currencies as $key => $currency)
		{
		  	$currencies[$key] = $key . ' - ' . $currency;
		}
		
		$response = new ActionResponse();
		$response->setValue('currencies', $currencies);
		return $response;
	}

	public function add()
	{
		$id = $this->request->getValue('id');
		
	  	// check if the currency hasn't been added already
		$filter = new ArSelectFilter();
		$filter->setCondition(new EqualsCond(new ArFieldHandle('Currency', 'ID'), $id));
		$r = ActiveRecord::getRecordSet('Currency', $filter);
		if ($r->getTotalRecordCount() > 0)
		{
			return new RawResponse(0);  	
		}
	
	  	// check if default currency exists
		$filter = new ArSelectFilter();
		$filter->setCondition(new EqualsCond(new ArFieldHandle('Currency', 'isDefault'), 1));
		
		$r = ActiveRecord::getRecordSet('Currency', $filter);
		$isDefault = ($r->getTotalRecordCount() == 0);

	  	// get max position
		$filter = new ArSelectFilter();
		$filter->setOrder(new ArFieldHandle('Currency', 'position'), 'DESC');
		$filter->setLimit(1);
		
		$r = ActiveRecord::getRecordSet('Currency', $filter);
		$max = $r->get(0);
		
		$position = $max->position->get() + 1;		
		  
		// create new record
		$newCurrency = ActiveRecord::getNewInstance('Currency');
	  	$newCurrency->setId($id);
		
		$newCurrency->position->set($position);
		$newCurrency->isDefault->set($isDefault);
				
		$newCurrency->save(ActiveRecord::PERFORM_INSERT);	  	
		
		$arr = $newCurrency->toArray();
		$arr['name'] = $this->locale->info()->getCurrencyName($id);
		
		$response = new ActionResponse();
		$response->setValue('item', $arr);
		$response->setHeader('Content-type', 'application/xml');
		return $response;
	}

	/**
	 * Sets default currency.
	 * @return ActionRedirectResponse
	 */
	public function setDefault()
	{
		try 
		{
			$r = ActiveRecord::getInstanceByID('Currency', $this->request->getValue('id'), true);
		}
		catch (ArNotFoundException $e)
		{
			return new RawResponse(0);  	
		}
			
		ActiveRecord::beginTransaction();

		$update = new ArUpdateFilter();
		$update->addModifier('isDefault', 0);
		ActiveRecord::updateRecordSet('Currency', $update);

		$r->setAsDefault(true);
		$r->save();

		ActiveRecord::commit();

		return new ActionRedirectResponse('backend.currency', 'index');
	}

	/**
	 * Save currency order
	 * @return RawResponse
	 */
	public function saveOrder()
	{
	  	$order = $this->request->getValue('currencyList');
		foreach ($order as $key => $value)
		{
			$update = new ARUpdateFilter();
			$update->setCondition(new EqualsCond(new ARFieldHandle('Currency', 'ID'), $value));
			$update->addModifier('position', $key);
			ActiveRecord::updateRecordSet('Currency', $update);  	
		}

		$resp = new RawResponse();
	  	$resp->setContent($this->request->getValue('draggedId'));
		return $resp;		  	
	}

	/**
	 * Sets if currency is enabled
	 * @return ActionResponse
	 */
	public function setEnabled()
	{
		$id = $this->request->getValue('id');		
		$curr = ActiveRecord::getInstanceById('Currency', $id, true);
		$curr->isEnabled->set((int)(bool)$this->request->getValue("status"));
		$curr->save();
		
		$response = new ActionResponse();
		$item = $curr->toArray();
		$item['name'] = $this->locale->info()->getCurrencyName($item['ID']);
		$response->setValue('item', $item);

		return $response;		
	}

	/**
	 * Remove a currency
	 * @return RawResponse
	 */
	public function delete()
	{  	
		$id = $this->request->getValue('id');
		
		try
	  	{
			// make sure the currency record exists
			$inst = ActiveRecord::getInstanceById('Currency', $id, true);
			
			$success = $id;
			
			// make sure it's not the default currency
			if (true == $inst->isDefault->get())			
			{
				$success = false;
			}
			
			// remove it
			if ($success)
			{
				ActiveRecord::deleteByID('Currency', $id);
			}

		}
		catch (Exception $exc)
		{			  	
		  	$success = false;
		}
		  
		$resp = new RawResponse();
	  	$resp->setContent($success);
		return $resp;
	}

	/**
	 * Adjust currency rates
	 * @return ActionResponse
	 */
	public function rates()
	{
		// get currency list and names
		$filter = new ArSelectFilter();
		$filter->setCondition(new NotEqualsCond(new ArFieldHandle("Currency", "isDefault"), 1));
		$filter->setOrder(new ArFieldHandle("Currency", "isEnabled"), 'DESC');
		$filter->setOrder(new ArFieldHandle("Currency", "position"), 'ASC');
		$currencies = ActiveRecord::getRecordSet('Currency', $filter)->toArray();

		foreach ($currencies as &$currency)
		{
		  	$currency['name'] = $this->locale->info()->getCurrencyName($currency['ID']);
		}

		// build form
		$form = $this->buildForm();

		$response = new ActionResponse();
		$response->setValue('currencies', $currencies);
		$response->setValue('rateForm', $form);
//		$response->setValue('defaultCurrency', Store::getInstance()->getDefaultCurrency());		
		return $response;
	}

	/**
	 * Change currency options
	 * @return ActionResponse
	 */
	public function options()
	{
		

		$response = new ActionResponse();

		return $response;
	}

	/**
	 * Builds a currency form validator
	 *
	 * @return RequestValidator
	 */
	private function buildValidator()
	{
		ClassLoader::import("framework.request.validator.RequestValidator");

		$validator = new RequestValidator("rate", $this->request);
		$validator->addCheck("rate", new IsNotEmptyCheck($this->translate("Please enter the rate")));
		return $validator;
	}

	/**
	 * Builds a currency form instance
	 *
	 * @return Form
	 */
	private function buildForm()
	{
		ClassLoader::import("framework.request.validator.Form");
		return new Form($this->buildValidator());		
	}

	/**
	 * Saves currencies rates.
	 * @return ActionRedirectResponse
	 */
	public function saveRates()
	{
		$this->formatCurrencyData();
		$form = $this->createRatesForm();
		$form->setData($this->request->toArray());

		if ($form->isValid())
		{
			foreach($this->currData as $key => $value)
			{
				if ($value != $form->getField($key)->getValue())
				{
					$curr = ActiveRecord::getInstanceById("Currency", $key);

					if ($form->getField($key)->getValue() == "")
					{
						$curr->rate->setNull();
					}
					else
					{
						$curr->rate->set($form->getField($key)->getValue());
					}
					$curr->save();
				}
			}
		}
		else
		{
			$form->saveState();
		}
		return new ActionRedirectResponse($this->request->getControllerName(), "ratesForm");
	}

	private function createRatesForm()
	{
		ClassLoader::import("library.formhandler.*");
		ClassLoader::import("library.formhandler.check.numeric.*");
		ClassLoader::import("library.formhandler.filter.*");

		$form = new Form("ratesForm");
		foreach($this->currData as $key => $value)
		{
			$field = new TextLineField($key, $this->locale->getCurrency($key));
			$field->addCheck(new IsNumericCheck("Value must be numeric!", true));
			$field->addFilter(new NumericFilter());
			$form->addField($field);
		}
		$form->addField(new SubmitField("submit", "Save"));
		return $form;
	}
}

?>
