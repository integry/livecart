<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.product.ProductOption");
ClassLoader::import("application.model.product.ProductOptionChoice");
ClassLoader::import("library.*");

/**
 * Configurable product options
 *
 * @package application.controller.backend
 * @author Integry Systems
 * @role category
 */
class ProductOptionController extends StoreManagementController
{
	/**
	 * Configuration data
	 *
	 * @see self::getProductOptionConfig
	 * @var array
	 */
	protected $productOptionConfig = array();

	/**
	 * Specification field index page
	 *
	 * @return ActionResponse
	 */
	public function index()
	{
		$response = new ActionResponse();

		$parentId = $this->request->get('id');
		$product = Product::getInstanceByID($parentId);

		$defaultProductOptionValues = array
		(
			'ID' => $parentId . '_new',
			'name' => array(),
			'values' => array(),
			'rootId' => 'productOption_item_new_' . $parentId . '_form',
			'type' => ProductOption::TYPE_BOOL,
			'parentID' => $parentId
		);

		$response->set('parentID', $parentId);
		$response->set('configuration', $this->getProductOptionConfig());
		$response->set('productOptionsList', $defaultProductOptionValues);
		$response->set('defaultLangCode', $this->application->getDefaultLanguageCode());
		$response->set('defaultCurrencyCode', $this->application->getDefaultCurrencyCode());
		$response->set('options', $product->getOptions()->toArray());

		return $response;
	}

	/**
	 * Displays form for creating a new or editing existing one product group specification field
	 *
	 * @return ActionResponse
	 */
	public function item()
	{
		$response = new ActionResponse();
		$option = ProductOption::getInstanceByID($this->request->get('id'), true);
		$productOptionList = $option->toArray();

		foreach($option->getChoiceSet()->toArray() as $value)
		{
		   $productOptionList['values'][$value['ID']] = $value;
		}

		$productOptionList['parentID'] = (!empty($productOptionList['Category']['ID'])) ?
											$productOptionList['Category']['ID'] :
											$productOptionList['Product']['ID'];

		return new JSONResponse($productOptionList);
	}

	/**
	 * @role update
	 */
	public function update()
	{
		try
		{
			$productOption = ProductOption::getInstanceByID((int)$this->request->get('ID'));
		}
		catch (ARNotFoundException $e)
		{
			return new JSONResponse(array(
					'errors' => array('ID' => $this->translate('_error_record_id_is_not_valid')),
					'ID' => (int)$this->request->get('ID')
				)
			);
		}

		return $this->save($productOption);
	}

	/**
	 * @role update
	 */
	public function create()
	{
		$productOption = ProductOption::getNewInstance(Product::getInstanceByID($this->request->get('parentID', false)));

		return $this->save($productOption);
	}

	/**
	 * Creates a new or modifies an exisitng specification field (according to a passed parameters)
	 *
	 * @return JSONResponse Returns success status or failure status with array of erros
	 */
	private function save(ProductOption $productOption)
	{
		$this->getProductOptionConfig();
		$errors = $this->validate($this->request->getValueArray(array('values', 'name', 'type', 'parentID', 'ID')), $this->productOptionConfig['languageCodes']);

		if(!$errors)
		{
			$productOption->loadRequestData($this->request);
			$productOption->save();

			$parentID = (int)$this->request->get('parentID');
			$values = $this->request->get('values');

			// save specification field values in database
			$newIDs = array();
			//var_dump($productOption->isSelect(), $values);
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
	 * Delete specification field from database
	 *
	 * @role update
	 * @return JSONResponse
	 */
	public function delete()
	{
		if($id = $this->request->get("id", false))
		{
			ProductOption::deleteById($id);
			return new JSONResponse(false, 'success');
		}
		else
		{
			return new JSONResponse(false, 'failure', $this->translate('_could_not_remove_attribute'));
		}
	}

	/**
	 * Sort specification fields
	 *
	 * @role update
	 * @return JSONResponse
	 */
	public function sort()
	{
		$target = $this->request->get('target');

		foreach($this->request->get($target, array()) as $position => $key)
		{
			if(!empty($key))
			{
				$productOption = ProductOption::getInstanceByID((int)$key);
				$productOption->setFieldValue('position', (int)$position);
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
				'deleteField' => $this->translate('_delete_field'),
				'removeFieldQuestion' => $this->translate('_remove_field_question')
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