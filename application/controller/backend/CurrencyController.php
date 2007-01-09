<?php

ClassLoader::import("library.*");
ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.Currency");

/**
 *
 * @package application.controller.backend
 * @author Rinalds Uzkalns <rinalds@integry.net>
 *
 */
class CurrencyController extends StoreManagementController
{

	/**
	 * List all system currencies
	 * @return ActionResponse
	 */
	public function index()
	{
		$filter = new ARSelectFilter();
		$filter->setOrder(new ARFieldHandle('Currency', 'position'), 'ASC');

		$curr = ActiveRecord::getRecordSet("Currency", $filter, true)->toArray();

		$response = new ActionResponse();
		$response->setValue("currencies", json_encode($curr));

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
		
		// remove already added currencies from list
		$addedCurrencies = $this->getCurrencySet();
		foreach ($addedCurrencies as $currency)
		{
			unset($currencies[$currency->getID()]);  
		}

		$response = new ActionResponse();
		$response->setValue('currencies', $currencies);
		return $response;
	}

	public function add()
	{
		try
		{
			$newCurrency = ActiveRecord::getNewInstance('Currency');
		  	$newCurrency->setId($this->request->getValue('id'));
			$newCurrency->save(ActiveRecord::PERFORM_INSERT);	  	
	
			return new JSONResponse($newCurrency->toArray());
		}
		catch (Exception $exc)
		{
			return new JSONResponse(0);		  
		}
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
		catch (ARNotFoundException $e)
		{
			return new ActionRedirectResponse('backend.currency', 'index');  
		}
			
		ActiveRecord::beginTransaction();

		$update = new ARUpdateFilter();
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
				
		return new JSONResponse($curr->toArray());
	}

	/**
	 * Remove a currency
	 * @return RawResponse
	 */
	public function delete()
	{  	
		try
	  	{
			$success = Currency::deleteById($this->request->getValue('id'));
		}
		catch (Exception $exc)
		{			  	
		  	$success = false;
		}
		  
		return new RawResponse($success);
	}

	/**
	 * Currency rates form
	 * @return ActionResponse
	 */
	public function rates()
	{
		$currencies = $this->getCurrencySet()->toArray();
		$form = $this->buildForm($currencies);

		foreach ($currencies as $currency)
		{
			$form->setValue('rate_' . $currency['ID'], $currency['rate']);
		}

		$response = new ActionResponse();
		$response->setValue('currencies', $currencies);
		$response->setValue('saved', $this->request->getValue('saved'));
		$response->setValue('rateForm', $form);
		$response->setValue('defaultCurrency', Store::getInstance()->getDefaultCurrency()->getID());		
		return $response;
	}

	/**
	 * Change currency options
	 * @return ActionResponse
	 */
	public function options()
	{
		ClassLoader::import("framework.request.validator.Form");
		$form = new Form($this->buildOptionsValidator());
		$form->setValue('updateCb', $this->config->getValue('currencyAutoUpdate'));
		$form->setValue('frequency', $this->config->getValue('currencyUpdateFrequency'));
				
		// get all feeds
		$dir = new DirectoryIterator(ClassLoader::getRealPath('application.helper.currency'));
		foreach ($dir as $file) {
			$p = pathinfo($file->getFilename());
			if ($p['extension'] == 'php')
			{
				include_once($file->getPathName());		  
				$className = basename($file->getFilename(), '.php');
				$classInfo = new ReflectionClass($className);
				if (!$classInfo->isAbstract())
				{
					$feeds[$className] = call_user_func(array($className, 'getName'));
				}
			}			
		}		
						
		// get currency settings		
		$currencies = $this->getCurrencySet()->toArray();
		
		$settings = $this->config->getValue('currencyFeeds');

		foreach ($currencies as $id => &$currency)
		{
			if (isset($settings[$currency['ID']]))
			{
			  	$form->setValue('curr_' . $currency['ID'], $settings[$currency['ID']]['enabled']);
			  	$form->setValue('feed_' . $currency['ID'], $settings[$currency['ID']]['feed']);
			}
		}

		$frequency = array();
		foreach (array(15, 60, 240, 1440) as $mins)
		{
			$frequency[$mins] = $this->translate('_freq_' . $mins);
		}
				
		$response = new ActionResponse();
		$response->setValue('form', $form);
		$response->setValue('currencies', $currencies);
		$response->setValue('frequency', $frequency);
		$response->setValue('feeds', $feeds);
		return $response;
	}

	public function saveOptions()
	{
		$val = $this->buildOptionsValidator();
		
		// main update setting
		$this->setConfigValue('currencyAutoUpdate', $this->request->getValue('updateCb'));
		
		// frequency
		$this->setConfigValue('currencyUpdateFrequency', $this->request->getValue('frequency'));
				  	
		// individual currency settings
		$setting = $this->config->getValue('currencyFeeds');
		if (!is_array($setting))
		{
		  	$setting = array();
		}
		$currencies = $this->getCurrencySet();
		foreach ($currencies as $currency)
		{
			$setting[$currency->getID()] = array('enabled' => $this->request->getValue('curr_' . $currency->getID()),
												 'feed' => $this->request->getValue('feed_' . $currency->getID())
												);  	
		}
		$this->setConfigValue('currencyFeeds', $setting);
		
		$this->config->save();
		
		return new JSONResponse(1);
	}
	
	/**
	 * Saves currency rates.
	 * @return JSONResponse
	 */
	public function saveRates()
	{		
		$currencies = $this->getCurrencySet();
		
		// save rates
		if($this->buildValidator($currencies->toArray())->isValid())
		{ 
			foreach($currencies as &$currency)
			{
				$currency->rate->set($this->request->getValue('rate_' . $currency->getID()));
				$currency->save();
			}
		}

		// read back from DB
		$currencies = $this->getCurrencySet();
		$values = array();
				
		foreach($currencies as &$currency)
		{
			$values[$currency->getID()] = $currency->rate->get();
		}

		return new JSONResponse($values);		
	}	

	private function getCurrencySet()
	{
		$filter = new ARSelectFilter();
		$filter->setCondition(new NotEqualsCond(new ARFieldHandle("Currency", "isDefault"), 1));
		$filter->setOrder(new ARFieldHandle("Currency", "isEnabled"), 'DESC');
		$filter->setOrder(new ARFieldHandle("Currency", "position"), 'ASC');
		return ActiveRecord::getRecordSet('Currency', $filter);
	}

	private function buildOptionsValidator()
	{
		ClassLoader::import("framework.request.validator.RequestValidator");
		return new RequestValidator("currencySettings", $this->request);	
	}

	/**
	 * Builds a currency form validator
	 *
	 * @return RequestValidator
	 */
	private function buildValidator($currencies)
	{
		ClassLoader::import("framework.request.validator.RequestValidator");

		$validator = new RequestValidator("rate", $this->request);
		foreach ($currencies as $currency)
		{
			$validator->addCheck('rate_' . $currency['ID'], new IsNotEmptyCheck($this->translate('_err_empty')));		  
			$validator->addCheck('rate_' . $currency['ID'], new IsNumericCheck($this->translate('_err_numeric')));		  			
			$validator->addCheck('rate_' . $currency['ID'], new MinValueCheck($this->translate('_err_negative'), 0));
			$validator->addFilter('rate_' . $currency['ID'], new NumericFilter());	
		}

		return $validator;
	}

	/**
	 * Builds a currency form instance
	 *
	 * @return Form
	 */
	private function buildForm($currencies)
	{
		ClassLoader::import("framework.request.validator.Form");
		return new Form($this->buildValidator($currencies));		
	}
}

?>