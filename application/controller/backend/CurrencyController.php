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
		$filter = new ARSelectFilter();
		$filter->setCondition(new EqualsCond(new ARFieldHandle('Currency', 'ID'), $id));
		$r = ActiveRecord::getRecordSet('Currency', $filter);
		if ($r->getTotalRecordCount() > 0)
		{
			return new RawResponse(0);  	
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
		catch (ARNotFoundException $e)
		{
			return new RawResponse(0);  	
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
		$currencies = $this->getCurrencies();
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
//		$this->setConfigValue('another_test', 'a whole different value here');
//		$this->config->save();
//		echo $this->config->getValue('another_test');
		

		ClassLoader::import("framework.request.validator.Form");
		$form = new Form($this->buildOptionsValidator());
		$form->setValue('updateCb', 'on');
		
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
		  	$currency['feed'] = $settings[$id]['feed'];
		  	$currency['enabled'] = $settings[$id]['enabled'];
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
		$this->setConfigValue('currencyAutoUpdate', $val->getValue('updateCb'));
		
		// frequency
		$this->setConfigValue('currencyUpdateFrequency', $val->getValue('frequency'));
				  	
		// individual currency settings
		$setting = $this->config->getValue('currencyUpdate');
		if (!is_array($setting))
		{
		  	$setting = array();
		}
		$currencies = $this->getCurrencySet();
		foreach ($currencies as $currency)
		{
			$setting[$currency->getID()] = array('enabled' => $val->getValue('curr_' . $currency->getID()) == 'on',
												 'feed' => $val->getValue('feed_' . $currency->getID())
												);  	
		}
		$this->setConfigValue('currencyUpdate', $setting);
		
		$this->config->save();
		
		return new JSONResponse(true);
	}

	private function getCurrencySet()
	{
		// get currency list and names
		$filter = new ARSelectFilter();
		$filter->setCondition(new NotEqualsCond(new ARFieldHandle("Currency", "isDefault"), 1));
		$filter->setOrder(new ARFieldHandle("Currency", "isEnabled"), 'DESC');
		$filter->setOrder(new ARFieldHandle("Currency", "position"), 'ASC');
		return ActiveRecord::getRecordSet('Currency', $filter);
	}

	private function getCurrencies()
	{
		$currencies = $this->getCurrencySet()->toArray();

		foreach ($currencies as &$currency)
		{
		  	$currency['name'] = $this->locale->info()->getCurrencyName($currency['ID']);
		}
		
		return $currencies;			  	
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
}

?>