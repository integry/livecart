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
		$filter->setOrder(new ArFieldHandle('Currency', 'position'), 'DESC');
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
		$r = ActiveRecord::getInstanceByID('Currency', $this->request->getValue('id'), true);
		
		if (!$r->isExistingRecord())
		{
		  	
			return new RawResponse(0);
		}		
		
		echo $this->request->getValue('id');
		
		exit;
		
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
	 * Currencies rates action.
	 * @return ActionResponse
	 */
	public function ratesForm()
	{
		$this->formatCurrencyData();
		$form = $this->createRatesForm();
		$form->setAction(Router::getInstance()->createUrl(array("controller" => $this->request->getControllerName(), "action" => "saveRates")));
		if ($form->validationFailed())
		{
			$form->restore();
		}
		else
		{
			$form->setData($this->currData);
		}

		//response
		$response = new ActionResponse();
		$response->setValue("action", "ratesForm");
		$response->setValue("tabPanelFile", "tabpanel.ratesForm.tpl");

		$response->setValue("defaultId", $this->defaultId);
		$response->setValue("defaultName", $this->locale->getCurrency($this->defaultId));
		$response->setValue("form", @$form->render());

		return $response;
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

	private function formatCurrencyData()
	{
		unset($this->currData);
		$currSet = ActiveRecord::getRecordSet("Currency", new ArSelectFilter(), true);

		foreach($currSet->toArray()as $value)
		{
			if ($value['isDefault'] == 1)
			{
				$this->defaultId = $value['ID'];
			}
			else
			{
				$this->currData[$value['ID']] = $value['rate'];
			}
		}
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
