<?php

ClassLoader::import("application.model.system.Language");
ClassLoader::import("application.model.system.MultilingualObject");

ClassLoader::import("application.model.specification.*");
ClassLoader::import("application.model.product.*");

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

	const DO_NOT_RECALCULATE_PRICE = false;
	
	/**
	 * Related products
	 * @return ARSet
	 */
	private $relationships = null;
	
	/**
	 * Removed relationships
	 * @return ARSet
	 */
	private $removedRelationships = null;

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
		$schema->registerField(new ArField("isBackOrderable", ARBool::instance()));

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

    		$update = new ARUpdateFilter();
    		$update->addModifier('dateUpdated', new ARExpressionHandle('NOW()'));
    		$update->addModifier('dateCreated', new ARExpressionHandle('NOW()'));
    		$update->setCondition(new EqualsCond(new ARFieldHandle(__CLASS__, 'ID'), $this->getID()));
    		ActiveRecordModel::updateRecordSet(__CLASS__, $update);
            
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

    		$update = new ARUpdateFilter();
    		$update->addModifier('dateUpdated', new ARExpressionHandle('NOW()'));
    		$update->setCondition(new EqualsCond(new ARFieldHandle(__CLASS__, 'ID'), $this->getID()));
    		ActiveRecordModel::updateRecordSet(__CLASS__, $update);

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

		$this->saveRelationships();
		ActiveRecordModel::commit();
	}
	
    public function saveRelationships()
    {
        if(is_null($this->relationships)) return;
        
        foreach($this->getRelationships() as $relationship)
        {
            $relationship->save();
        }
          
        foreach($this->getRemovedRelationships() as $relationship)
        {
            $relationship->delete();
        }
    }

	public function getSpecificationFieldSet($loadReferencedRecords = false)
	{
	  	return $this->category->get()->getSpecificationFieldSet(Category::INCLUDE_PARENT, $loadReferencedRecords);
	}

	public function loadSpecification($specificationData = null)
	{
	  	if (!$specificationData)
	  	{

		$cond = '
		LEFT JOIN 	
			SpecField ON specFieldID = SpecField.ID 
		LEFT JOIN 	
			SpecFieldGroup ON SpecField.specFieldGroupID = SpecFieldGroup.ID 
		WHERE 
			productID = ' . $this->getID() . '';

	    $query = '
		SELECT SpecificationDateValue.*, NULL AS valueID, NULL AS specFieldValuePosition, SpecFieldGroup.position AS SpecFieldGroupPosition, SpecField.* as valueID FROM SpecificationDateValue ' . $cond . '
	    UNION
		SELECT SpecificationStringValue.*, NULL, NULL, SpecFieldGroup.position, SpecField.* as valueID FROM SpecificationStringValue ' . $cond . '
	    UNION
		SELECT SpecificationNumericValue.*, NULL, NULL, SpecFieldGroup.position, SpecField.* as valueID FROM SpecificationNumericValue ' . $cond . '
	    UNION
		SELECT SpecificationItem.productID, SpecificationItem.specFieldID, SpecFieldValue.value, SpecFieldValue.ID, SpecFieldValue.position, SpecFieldGroup.position, SpecField.*
				 FROM SpecificationItem
				 	LEFT JOIN SpecFieldValue ON SpecificationItem.specFieldValueID =  SpecFieldValue.ID
				 ' . str_replace('ON specFieldID', 'ON SpecificationItem.specFieldID', $cond) . 
                 ' ORDER BY productID, SpecFieldGroupPosition, position, specFieldValuePosition';
                 
			$specificationData = self::getDataBySQL($query);
		}

		$this->specificationInstance = new ProductSpecification($this, $specificationData);
	}

	public function loadPricing($pricingData = null)
	{
	  	if (!$pricingData)
	  	{
			$pricingData = $this->getRelatedRecordSet("ProductPrice", new ARSelectFilter());
	  	}

  	  	$this->pricingHandlerInstance = new ProductPricing($this, $pricingData);		
	}

	public function loadRequestData(Request $request)
	{
	  	if(!$this->isExistingRecord()) $this->save();

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
					if ($request->isValueSet($fieldName) && !in_array($request->getValue($fieldName), array('other', '')))
				  	{
				  		$this->setAttributeValue($field, SpecFieldValue::getInstanceByID((int)$request->getValue($fieldName), ActiveRecordModel::LOAD_DATA));
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
		$array = array_merge($array, $this->getPricesFields());
	  	return $array;
	}

	public function getPricesFields()
	{
		$fields = array();
		$prices = $this->getPricingHandler()->toArray();
	  	foreach($prices['calculated'] as $code => $value)
	  	{
	  	    $fields["price_$code"] = $value;
	  	}

	  	return $fields;
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

	/**
	 * Get product active record
	 *
	 * @param mixed $recordID
	 * @param bool $loadRecordData
	 * @param bool $loadReferencedRecords
	 *
	 * @return Product
	 */
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
	 * Get products record set
	 *
	 * @param ARSelectFilter $filter
	 * @param bool $loadReferencedRecords
	 *
	 * @return ARSet
	 */
	public static function getRecordSet(ARSelectFilter $filter, $loadReferencedRecords = false)
	{
		return parent::getRecordSet(__CLASS__, $filter, $loadReferencedRecords);
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
			$this->specificationInstance = new ProductSpecification($this);
		}

		return $this->specificationInstance;
	}

	/**
	 * Gets a product pricing handler instance
	 *
	 * @return ProductPricing
	 */
	public function getPricingHandler()
	{
		if (!$this->pricingHandlerInstance)
		{
			$this->pricingHandlerInstance = new ProductPricing($this);
		}

		return $this->pricingHandlerInstance;
	}

	public function setPrice($currencyCode, $price)
	{
	  	$instance = $this->getPricingHandler()->getPriceByCurrencyCode($currencyCode);

	  	if (strlen($price) == 0)
	  	{
	  		$this->getPricingHandler()->removePriceByCurrencyCode($currencyCode);
		}
		else
		{
		    $instance->price->set($price);
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

/*
    public function getPricesArray()
    {
        if(empty($this->priceData))
        {
            $this->priceData = array();
            foreach(ProductPrice::getProductPricesSet($this)->toArray() as $price)
            {
                $this->priceData[$price['Currency']] = $price['price'];
            }
        }
        return $this->priceData;
    }
*/
    
    private function loadRelationships()
    {       
        $this->relationships = RelatedProduct::getRelationships($this);
    }
    
    /**
     * @return ARSet
     */
    public function getRelationships()
    {
        if(is_null($this->relationships))
        {
            $this->loadRelationships();
        }

        return $this->relationships;
    }
    
    /**
     * @return ARSet
     */
    public function getRelatedProducts()
    {
        $relatedProducts = new ARSet();
        
        foreach($this->getRelationships() as $relationship)
        {
            $relatedProducts->add($relationship->relatedProduct->get());
        }
        return $relatedProducts;
    }
    
    public function addRelatedProduct(Product $product)
    {        
        $relationship = RelatedProduct::getNewInstance($this, $product);
        $this->getRelationships()->add($relationship);
        $this->getRemovedRelationships()->removeRecord($relationship);
    }
    
    public function removeFromRelatedProducts(Product $product)
    {
        $relationship = RelatedProduct::getInstance($this, $product);
        
        $this->relationships->removeRecord($relationship);
        
        $this->getRemovedRelationships()->add($relationship);
    }
    
    public function markAsNotLoaded()
    {
        parent::markAsNotLoaded();
        $this->relationships = null;
    }
    
    private function getRemovedRelationships()
    {
        if(is_null($this->removedRelationships)) $this->removedRelationships = new ARSet();
        
        return $this->removedRelationships;
    }
    
    public function isRelatedTo(Product $product)
    {
        return RelatedProduct::hasRelationship($product, $this);
    }
}

?>