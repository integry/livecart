<?php

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
	 * Main currencies action.
	 * @return ActionResponse
	 */
	public function index()
	{
		$filter = new ArSelectFilter();
		$filter->setOrder(new ArFieldHandle("Currency", "isDefault"), ArSelectFilter::ORDER_DESC);

		$currSet = ActiveRecord::getRecordSet("Currency", $filter, true);

		///	$locale = Locale::getCurrentLocale();

		$curr = $currSet->toArray();
		foreach($curr as $key => $value)
		{
			$curr[$key]['currName'] = $this->locale->getCurrency($value["ID"]);
		}

		$response = new ActionResponse();
		$response->setValue("curr", $curr);
		$response->setValue("action", "index");
		$response->setValue("tabPanelFile", "tabpanel.index.tpl");

		return $response;
	}

	/**
	 * Sets default currency.
	 * @return ActionRedirectResponse
	 */
	public function setDefault()
	{
		Currency::setDefault($this->request->getValue("id"));
		return new ActionRedirectResponse($this->request->getControllerName(), 
                                      "index", 
                                      array("id" => $this->request->getValue("id")));
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
