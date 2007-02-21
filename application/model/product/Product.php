<?php

ClassLoader::import("application.model.system.Language");
ClassLoader::import("application.model.system.MultilingualObject");

ClassLoader::import("application.model.specification.*");
ClassLoader::import("application.model.product.Manufacturer");
ClassLoader::import("application.model.product.ProductSpecification");

/**
 * Store product (item)
 *
 * @package application.model.product
 */
class Product extends MultilingualObject
{
	private static $multilingualFields = array("name", "shortDescription", "longDescription");

	private $specificationInstance = null;

	private $pricingHandlerInstance = null;
	
	/**
	 *  An array containing specification field values (specFieldID => value, specFieldID => value)	 
	 *
	 *	@var array
	 */
	private $specFieldData = array();

	/**
	 *  An array containing product prices (currencyID => price)	 
	 *
	 *	@var array
	 */
	private $priceData = array();
	
	const DO_NOT_RECALCULATE_PRICE = false;	
	
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("Product");

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("categoryID", "Category", "ID", null, ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("manufacturerID", "Manufacturer", "ID", null, ARInteger::instance()));
		
		$schema->registerField(new ARField("isEnabled", ARBool::instance()));
		$schema->registerField(new ARField("sku", ARVarchar::instance(20)));
		$schema->registerField(new ARField("name", ARArray::instance()));
		$schema->registerField(new ARField("shortDescription", ARArray::instance()));
		$schema->registerField(new ARField("longDescription", ARArray::instance()));
		$schema->registerField(new ARField("keywords", ARText::instance()));
		
		$schema->registerField(new ARField("dateCreated", ARDateTime::instance()));
		$schema->registerField(new ARField("dateUpdated", ARDateTime::instance()));

		$schema->registerField(new ARField("URL", ARVarchar::instance(256)));
		$schema->registerField(new ARField("handle", ARVarchar::instance(40)));
		$schema->registerField(new ARField("isBestSeller", ARBool::instance()));
		$schema->registerField(new ARField("type", ARInteger::instance(4)));

		$schema->registerField(new ArField("minimumQuantity", ARFloat::instance(8)));
		$schema->registerField(new ArField("shippingSurchargeAmount", ARFloat::instance(8)));
		$schema->registerField(new ArField("isSeparateShipment", ARBool::instance()));
		$schema->registerField(new ArField("isFreeShipping", ARBool::instance()));

		$schema->registerField(new ArField("shippingWeight", ARFloat::instance(8)));

		$schema->registerField(new ArField("stockCount", ARFloat::instance(8)));
		$schema->registerField(new ArField("reservedCount", ARFloat::instance(8)));
	}

	/**
	 * Inserts new product record to a database
	 *
	 */
	protected function insert()
	{
		ActiveRecordModel::beginTransaction();
		
		$this->dateCreated->set('NOW()');
		$this->dateUpdated->set('NOW()');
				
		try
		{
			parent::insert();

			// update category product count numbers
			$category = $this->category->get();
			$categoryPathNodes = $category->getPathNodeSet(Category::INCLUDE_ROOT_NODE);
			// Adding current category to a record set of path
			$categoryPathNodes->add($category);

			foreach ($categoryPathNodes as $categoryNode)
			{
				$categoryNode->totalProductCount->set('totalProductCount + 1');
				if ($this->isEnabled->get() == true)
				{
					$categoryNode->activeProductCount->set('activeProductCount + 1');
				}
				$categoryNode->save();
			}

			ActiveRecordModel::commit();
		}
		catch (Exception $e)
		{
			ActiveRecordModel::rollback();
			throw $e;
		}
	}

	/**
	 * Updates product record
	 *
	 */
	protected function update()
	{
		ActiveRecordModel::beginTransaction();
				
		$this->dateUpdated->set('NOW()');
				
		try
		{
			parent::update();

			// update category product count numbers
			if ($this->isEnabled->isModified())
			{
				if ($this->isEnabled->get() == true)
				{
					$productCountStr = 'activeProductCount + 1';
				}
				else
				{
					$productCountStr = 'activeProductCount - 1';
				}

				$category = $this->category->get();
				$categoryPathNodes = $category->getPathNodeSet(Category::INCLUDE_ROOT_NODE);
				$categoryPathNodes->add($category);

				foreach ($categoryPathNodes as $categoryNode)
				{
					$categoryNode->activeProductCount->set($productCountStr);
					$categoryNode->save();
				}
			}
			
			ActiveRecordModel::commit();
		}
		catch (Exception $e)
		{
			ActiveRecordModel::rollback();
			throw $e;
		}
	}

	public function save()
	{
		ActiveRecordModel::beginTransaction();
		
		if ($this->manufacturer->get())
		{
			$this->manufacturer->get()->save();		  
		}

		parent::save();
		$this->getSpecification()->save();
		$this->getPricingHandler()->save();

		// generate SKU automatically if not set
		if (!$this->sku->get())
		{
			ClassLoader::import('application.helper.check.IsUniqueSkuCheck');
			
			$sku = $this->getID();
			
			do
			{
				$check = new IsUniqueSkuCheck('', $this);
				$exists = $check->isValid('SKU' . $sku);
				if (!$exists)
				{
				  	$sku = '0' . $sku;
				}
			}
			while (!$exists);			
			
			$this->sku->set('SKU' . $sku);
			$this->save();
		}		

		ActiveRecordModel::commit();
	}

	protected function miscRecordDataHandler($miscRecordDataArray)
	{
		foreach ($miscRecordDataArray as $key => $value)
		{
			if (substr($key, 0, 6) == 'price_')
			{
			  	$this->priceData[substr($key, 6)] = $value;
			}
			if (substr($key, 0, 10) == 'specField_')
			{
			  	$key = substr($key, 10);
			  	$this->specFieldData[$key] = $value;
			}
			else if (substr($key, 0, 15) == 'specMultiValue_')
			{
			  	$key = substr($key, 15);
			  	list($fieldId, $valueId) = explode('_', $key);
			  	if (!is_array($this->specFieldData[$fieldId]))
			  	{
					$this->specFieldData[$fieldId] = array();		    
				}
				$this->specFieldData[$fieldId][$valueId] = $value;
			}
		}
	}

	public function getSpecificationFieldSet($loadReferencedRecords = false)
	{
	  	return $this->category->get()->getSpecificationFieldSet(true, $loadReferencedRecords);
	}

	public function loadRequestData(Request $request)
	{
	  	// basic data
		parent::loadRequestData($request);
	  	
		// set manufacturer
		if ($request->isValueSet('manufacturer'))
		{
			$this->manufacturer->set(Manufacturer::getInstanceByName($request->getValue('manufacturer')));	
		}
		
		// set prices
		$currencies = Store::getInstance()->getCurrencyArray();
		foreach ($currencies as $currency)
		{
			if ($request->isValueSet('price_' . $currency))
			{
			  	$this->setPrice($currency, $request->getValue('price_' . $currency));			  
			}
		}

		// set SpecField's
		$fields = $this->category->get()->getSpecificationFieldSet(Category::INCLUDE_PARENT);
		foreach ($fields as $field)
		{
			$fieldName = $field->getFormFieldName();
			
			if ($field->isSelector())
			{
				if (!$field->isMultiValue->get())
				{
				  	if ($request->isValueSet($fieldName))
				  	{
				  		$this->setAttributeValue($field, SpecFieldValue::getInstanceByID($request->getValue($fieldName), ActiveRecordModel::LOAD_DATA));				  	  
				  	}						  	
				}
				else
				{
					$values = $field->getValuesSet();
					foreach ($values as $value)
					{
					  	if ($request->isValueSet($value->getFormFieldName()))
					  	{
						  	if ($request->getValue($value->getFormFieldName()))
						  	{
								$this->setAttributeValue($field, $value);										    
							}
							else
							{
								$this->removeAttributeValue($field, $value);											  
							}	    						    
						}
					} 
				}					  	
			}
			else
			{
			  	if ($request->isValueSet($fieldName))
			  	{
					if ($field->isTextField())
					{
						$languages = Store::getInstance()->getLanguageArray(Store::INCLUDE_DEFAULT);
						foreach ($languages as $language)
						{
						  	if ($request->isValueSet($field->getFormFieldName($language)))
						  	{
								$this->setAttributeValueByLang($field, $language, $request->getValue($field->getFormFieldName($language)));				  						  							    
							}
						}				  
					}
					else
					{
						$this->setAttributeValue($field, $request->getValue($fieldName));				    					  
					}
				}
			}				
		}  	
	}

	public function toArray()
	{
	  	$array = parent::toArray();
	  	$array['attributes'] = $this->getSpecification()->toArray();
	  	$array['prices'] = $this->getPricingHandler()->toArray();
	  	return $array;
	}

	/**
	 * Removes a product from a database
	 *
	 * @todo Reduce category product count
	 * @param int $recordID
	 * @return bool
	 * @throws Exception
	 */
	public static function deleteByID($recordID)
	{
		ActiveRecordModel::beginTransaction();
		try
		{
			$product = Product::getInstanceByID($recordID, Product::LOAD_DATA);
			$category = $product->category->get();

			parent::deleteByID(__CLASS__, $recordID);
			ActiveRecordModel::commit();
			return true;
		}
		catch (Exception $e)
		{
			ActiveRecordModel::rollback();
			throw $e;
		}
	}

	public static function getInstanceByID($recordID, $loadRecordData = false, $loadReferencedRecords = false)
	{
		return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData, $loadReferencedRecords);
	}

	/**
	 * Creates a new product instance
	 *
	 * @param Category $category
	 */
	public static function getNewInstance(Category $category)
	{
		$product = parent::getNewInstance(__CLASS__);
		$product->category->set($category);

		return $product;
	}

	/**
	 * @todo implement
	 *
	 */
	public function getImageArray()
	{
	}

	/**
	 * @todo implement
	 *
	 */
	public function getImageSet()
	{
	}

	/**
	 * Sets specification attribute
	 *
	 * @param iSpecification $specification Specification item value
	 */
	public function setAttribute(iSpecification $specification)
	{
		$this->getSpecification()->setAttribute($specification);
	}

	/**
	 * Sets specification attribute value
	 *
	 * @param SpecField $field Specification field instance
	 * @param mixed $value Attribute value
	 */
	public function setAttributeValue(SpecField $field, $value)
	{
		if (!is_null($value))
		{
			$specification = $this->getSpecification()->getAttribute($field, $value);
			$specification->setValue($value);
			$this->setAttribute($specification);		  
		}
		else
		{
			$this->getSpecification()->removeAttribute($field);	
		}		
	}

	/**
	 * Sets specification String attribute value by language
	 *
	 * @param SpecField $field Specification field instance
	 * @param unknown $value Attribute value
	 */
	public function setAttributeValueByLang(SpecField $field, $langCode, $value)
	{
		$specification = $this->getSpecification()->getAttribute($field);
		$specification->setValueByLang($langCode, $value);
		$this->setAttribute($specification);
	}

	/**
	 * Removes persisted product specification property
	 *
	 *	@param SpecField $field SpecField instance
	 */
	public function removeAttribute(SpecField $field)
	{
		$this->getSpecification()->removeAttribute($field);	  	
	}	

	public function removeAttributeValue(SpecField $field, SpecFieldValue $value)
	{
		$this->getSpecification()->removeAttributeValue($field, $value);		
	}

	/**
	 * Gets a product specification instance
	 *
	 * @return ProductSpecification
	 */
	public function getSpecification()
	{
		if (!$this->specificationInstance)
		{
			$this->specificationInstance = new ProductSpecification($this, $this->specFieldData);
		}
		
		return $this->specificationInstance;
	}

	/**
	 * Gets a product pricing handler instance
	 *
	 * @return ProductSpecification
	 */
	public function getPricingHandler()
	{
		if (!$this->pricingHandlerInstance)
		{
			$this->pricingHandlerInstance = new ProductPricing($this, $this->priceData);
		}
		
		return $this->pricingHandlerInstance;
	}
	
	public function setPrice($currencyCode, $price)
	{	  	
	  	$instance = $this->getPricingHandler()->getPriceByCurrencyCode($currencyCode);
	  	$instance->price->set($price);
	  	
	  	if (strlen($price) == 0)
	  	{
		    $this->getPricingHandler()->removePriceByCurrencyCode($currencyCode);
		}
	}

	public function getPrice($currencyCode, $recalculate = true)
	{
	  	$instance = $this->getPricingHandler()->getPriceByCurrencyCode($currencyCode);
	  	if (!$instance->price->get() && $recalculate)
	  	{
	  		return $instance->reCalculatePrice();		    
		}
		else
		{
			return $instance->price->get();  
		}
	}

	/**
	 * Count products in category
	 *
	 * @param Category $category Category active record
	 * @return integer
	 */
    public static function countItems(Category $category)
    {
        return $category->getProductSet(new ARSelectFilter(), false)->getTotalRecordCount();
    }
}

?>