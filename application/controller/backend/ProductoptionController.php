<?php

use product\ProductOption;
use product\ProductOptionChoice;
use product\Product;

require_once(dirname(__FILE__) . '/abstract/ActiveGridController.php');

/**
 * Configurable product options
 *
 * @package application/controller/backend
 * @author Integry Systems
 * @role option
 */
class ProductOptionController extends ActiveGridController
{
	/**
	 * Configuration data
	 *
	 * @see self::getProductOptionConfig
	 * @var array
	 */
	protected $productOptionConfig = array();

	protected function getClassName()
	{
		return 'product\ProductOption';
	}
	
	protected function getDefaultColumns()
	{
		return array('product\ProductOption.ID', 'product\ProductOption.name');
	}

	/**
	 * Specification field index page
	 *
	 */
	public function indexAction()
	{
		$this->set('defaultCurrencyCode', $this->application->getDefaultCurrencyCode());
	}
	
	public function editAction()
	{
	}

	/**
	 * Displays form for creating a new or editing existing one product group specification field
	 *
	 */
	public function getAction()
	{
		if ($id = $this->request->getParam('id'))
		{
			$option = ProductOption::getInstanceByID($id);
			$arr = $option->toArray();
		}
		else
		{
			$option = ProductOption::getNewInstance(Product::getInstanceByID($this->request->getParam('productID')));
			$arr = $option->toArray();
			$arr['type'] = ProductOption::TYPE_SELECT;
			$arr['displayType'] = ProductOption::DISPLAYTYPE_SELECTBOX;
			$arr['isRequired'] = true;
			$arr['isDisplayed'] = true;
		}

		echo json_encode($arr);
	}

	public function saveAction()
	{
		if ($this->request->getParam('ID'))
		{
			$productOption = ProductOption::getInstanceByID($this->request->getParam('ID'));
		}
		else
		{
			$productOption = ProductOption::getNewInstance(Product::getInstanceByID($this->request->getParam('productID')));
		}

		$productOption->loadRequestData($this->request);
		$productOption->save();
		
		$values = $this->request->getJson('choices');
		if (is_array($values))
		{
			$existingValues = array();
			foreach ($productOption->choices as $value)
			{
				$existingValues[$value->getID()] = $value;
			}
			
			foreach ($values as $key => &$value)
			{
				if (empty($value['name']))
				{
					continue;
				}

				if (empty($value['ID']))
				{
					$val = ProductOptionChoice::getNewInstance($productOption);
				}
				else
				{
					$val = isset($existingValues[$value['ID']]) ? $existingValues[$value['ID']] : ProductOptionChoice::getNewInstance($productOption);
					unset($existingValues[$value['ID']]);
				}
				
				$val->assign($value);
				$val->position = $key;
				$val->save();
			}
			
			// existing values not present in the posted data are deleted
			foreach ($existingValues as $deleted)
			{
				$deleted->delete();
			}
		}
		
		$productOption = ProductOption::getInstanceByID($productOption->getID());
		$arr = $productOption->toArray();
		
		echo json_encode($arr);
	}



	/**
	 * Creates a new or modifies an exisitng specification field (according to a passed parameters)
	 *
	 * @return JSONResponse Returns success status or failure status with array of erros
	 */
	private function save(ProductOption $productOption)
	{
			$productOption->loadRequestData($this->request);
			$productOption->save();


		$this->getProductOptionConfig();
		$errors = $this->validate($this->request->getValueArray(array('values', 'name', 'type', 'parentID', 'ID')), $this->productOptionConfig['languageCodes']);

		if(!$errors)
		{
			$productOption->loadRequestData($this->request);
			$productOption->save();

			// create a default choice for non-select options
			if (!$productOption->isSelect())
			{
				if (!$productOption->defaultChoice)
				{
					$defChoice = ProductOptionChoice::getNewInstance($productOption);
				}
				else
				{
					$defChoice = $productOption->defaultChoice;
					$defChoice->load();
				}

				$defChoice->loadRequestData($this->request);
				$defChoice->save();

				if (!$productOption->defaultChoice)
				{
					$productOption->defaultChoice->set($defChoice);
					$productOption->save();
				}
			}

			$parentID = (int)$this->request->get('parentID');
			$values = $this->request->get('values');

			// save specification field values in database
			$newIDs = array();
			if($productOption->isSelect() && is_array($values))
			{
				$position = 1;
				$countValues = count($values);
				$i = 0;

				$prices = $this->request->get('prices');

				foreach ($values as $key => $value)
				{
					$i++;

					// If last new is empty miss it
					if($countValues == $i && preg_match('/new/', $key) && empty($value[$this->productOptionConfig['languageCodes'][0]]))
					{
						continue;
					}

					if(preg_match('/^new/', $key))
					{
						$productOptionValues = ProductOptionChoice::getNewInstance($productOption);
					}
					else
					{
					   $productOptionValues = ProductOptionChoice::getInstanceByID((int)$key);
					}

					$productOptionValues->setLanguageField('name', $value, $this->productOptionConfig['languageCodes']);
					$productOptionValues->priceDiff->set($prices[$key]);
					$productOptionValues->position->set($position++);
					$productOptionValues->setColor($this->request->get(array('color', $key)));
					$productOptionValues->save();

	   				if(preg_match('/^new/', $key))
					{
						$newIDs[$productOptionValues->getID()] = $key;
					}
				}
			}

			return new JSONResponse(array('id' => $productOption->getID(), 'newIDs' => $newIDs), 'success');
		}
		else
		{
			return new JSONResponse(array('errors' => $this->translateArray($errors)));
		}
	}

	/**
	 * Delete option from database
	 *
	 * @return JSONResponse
	 */
	public function deleteAction()
	{
		if($id = $this->request->get("id", null, false))
		{
			ProductOption::deleteById($id);
			return new JSONResponse(false, 'success');
		}
		else
		{
			return new JSONResponse(false, 'failure', $this->translate('_could_not_remove_option'));
		}
	}

	/**
	 * Delete option from database
	 *
	 * @return JSONResponse
	 */
	public function deleteChoiceAction()
	{
		if($id = $this->request->get("id", null, false))
		{
			ProductOptionChoice::deleteById($id);
			return new JSONResponse(false, 'success');
		}
		else
		{
			return new JSONResponse(false, 'failure', $this->translate('_could_not_remove_option_choice'));
		}
	}

	/**
	 * Sort options
	 *
	 * @return JSONResponse
	 */
	public function sortAction()
	{
		$target = $this->request->get('target');

		foreach($this->request->get($target, null, array()) as $position => $key)
		{
			if(!empty($key))
			{
				$productOption = ProductOption::getInstanceByID((int)$key);
				$productOption->writeAttribute('position', (int)$position);
				$productOption->save();
			}
		}

		return new JSONResponse(false, 'success');
	}

	/**
	 * Sort product option choices
	 *
	 * @return JSONResponse
	 */
	public function sortChoiceAction()
	{
		$target = $this->request->get('target');

		foreach($this->request->get($target, null, array()) as $position => $key)
		{
			if(!empty($key))
			{
				$productOption = ProductOptionChoice::getInstanceByID((int)$key);
				$productOption->writeAttribute('position', (int)$position);
				$productOption->save();
			}
		}

		return new JSONResponse(false, 'success');
	}

	/**
	 * Create and return configurational data. If configurational data is already created just return the array
	 *
	 * @see self::$productOptionConfig
	 * @return array
	 */
	private function getProductOptionConfig()
	{
		if(!empty($this->productOptionConfig)) return $this->productOptionConfig;

		$languages[$this->application->getDefaultLanguageCode()] =  $this->locale->info()->getOriginalLanguageName($this->application->getDefaultLanguageCode());
		foreach ($this->application->getLanguageList()->toArray() as $lang)
		{
			if($lang['isDefault'] != 1)
			{
				$languages[$lang['ID']] = $this->locale->info()->getOriginalLanguageName($lang['ID']);
			}
		}

		$this->productOptionConfig = array(
			'languages' => $languages,
			'languageCodes' => array_keys($languages),
			'messages' => array
			(
				'removeFieldQuestion' => $this->translate('_ProductOption_remove_question')
			),

			'selectorValueTypes' => array(ProductOption::TYPE_SELECT),
			'doNotTranslateTheseValueTypes' => array(2),
			'countNewValues' => 0
		);

		return $this->productOptionConfig;
	}

	/**
	 * Validates specification field form
	 *
	 * @param array $values List of values to validate.
	 * @param array $config
	 * @return array List of all errors
	 */
	private function validate($values = array(), $languageCodes)
	{
		$errors = array();

		if(empty($values['name']))
		{
			$errors['name'] = '_error_name_empty';
		}

		if((ProductOption::TYPE_SELECT == $values['type']) && isset($values['values']) && is_array($values['values']))
		{
			$countValues = count($values['values']);
			$i = 0;
			foreach ($values['values'] as $key => $v)
			{
				$i++;
				if($countValues == $i && preg_match('/new/', $key) && empty($v[$languageCodes[0]]))
				{
					continue;
				}
				else if(empty($v[$languageCodes[0]]))
				{
					$errors["values[$key][{$languageCodes[0]}]"] = '_error_value_empty';
				}
			}
		}

		return $errors;
	}
}

?>
