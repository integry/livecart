<?php

ClassLoader::import("application.model.system.Language");
ClassLoader::import("application.model.system.MultilingualObject");
ClassLoader::import("application.model.product.SpecificationItem");

/**
 * Store product (item)
 *
 * @package application.model.product
 */
class Product extends MultilingualObject
{
	private static $multilingualFields = array("name", "shortDescription", "longDescription");

	/**
	 *  An array containing specification field values (specFieldID => value, specFieldID => value)	 
	 *
	 *	@var array
	 */
	protected $specFieldData = array();

	/**
	 *  Spec field ID's which values have been modified and must be saved (updated)
	 *
	 *	@var array
	 */
	protected $modifiedSpecFieldValues = array();

	/**
	 *  Spec field ID's which values have been newly added and must be inserted in database
	 *
	 *	@var array
	 */
	protected $addedSpecFieldValues = array();

	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("Product");

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("categoryID", "Category", "ID", null, ARInteger::instance()));

		$schema->registerField(new ARField("name", ARArray::instance()));
		$schema->registerField(new ARField("shortDescription", ARArray::instance()));
		$schema->registerField(new ARField("longDescription", ARArray::instance()));

		$schema->registerField(new ARField("sku", ARVarchar::instance(20)));

		$schema->registerField(new ARField("dateCreated", ARDateTime::instance()));
		$schema->registerField(new ARField("dateUpdated", ARDateTime::instance()));

		$schema->registerField(new ARField("isEnabled", ARBool::instance()));
		$schema->registerField(new ARField("URL", ARVarchar::instance(256)));
		$schema->registerField(new ARField("isBestSeller", ARBool::instance()));
		$schema->registerField(new ARField("type", ARInteger::instance(4)));

		$schema->registerField(new ArField("minimumQuantity", ARInteger::instance()));
		$schema->registerField(new ArField("shippingSurchargeAmount", ARFloat::instance(8)));
		$schema->registerField(new ArField("isSeparateShipment", ARBool::instance()));
		$schema->registerField(new ArField("isFreeShipping", ARBool::instance()));

		$schema->registerField(new ArField("shippingWidth", ARFloat::instance(8)));
		$schema->registerField(new ArField("shippingHeight", ARFloat::instance(8)));
		$schema->registerField(new ArField("shippingLength", ARFloat::instance(8)));
		$schema->registerField(new ArField("shippingWeight", ARFloat::instance(8)));
		$schema->registerField(new ArField("unitsType", ARInteger::instance()));
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

			// save specification field values
			$this->saveSpecFields();

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
			
			// save specification field values
			$this->saveSpecFields();
			
			ActiveRecordModel::commit();
		}
		catch (Exception $e)
		{
			ActiveRecordModel::rollback();
			throw $e;
		}
	}

	/**
	 *	Saves specification field values
	 *	Note: transaction has to be started already
	 */
	protected function saveSpecFields()
	{
		$fields = $this->category->get()->getSpecificationFieldSet(Category::INCLUDE_PARENT);
		
		$tables = array();

		// map each field to its value table
		foreach ($fields as $field)
		{
			if (!isset($this->specFieldData[$field->getID()]))
			{
			  	continue;
			}
			
			$tables[$field->getValueTableName()][] = $field->getID();
		}		
		
		// get instances for all field values
		$instances = array();
		foreach ($tables as $table => $ids)
		{
			if (count($ids) > 0)
			{
				$cond = new EqualsCond(new ARFieldHandle($table, 'productID'), $this->getID());
				$cond->addAND(new INCond(new ARFieldHandle($table, 'specFieldID'), $ids));
				$filter = new ARSelectFilter();
				$filter->setCondition($cond);
				$set = ActiveRecordModel::getRecordSet($table, $filter);
				
				foreach ($set as $instance)
				{
				  	$instances[$instance->specField->getID()] = $instance();
				}
				
				// create missing instances
				foreach ($ids as $id)
				{
					if (!isset($instances[$id]))
					{
					  	$instances[$id] = call_user_func(array($table, 'getNewInstance'), $this, $field, $this->specFieldData[$id]);
					}  	
					
					$instances[$id]->value->set($this->specFieldData[$id]);
				}
			}
		}		
				
		try
		{
			ActiveRecordModel::beginTransaction();
			
			foreach ($instances as $instance)
			{
			  	$instance->save();
			}
		}
		catch (Exception $e)
		{
			ActiveRecordModel::rollback();
			throw $e;
		}

		ActiveRecordModel::commit();

	}

	protected function miscRecordDataHandler($miscRecordDataArray)
	{
		foreach ($miscRecordDataArray as $key => $value)
		{
			if (substr($key, 0, 10) == 'specField_')
			{
			  	$key = substr($key, 10);
			  	$this->specFieldData[$key] = $value;
			}
		}
	}

	public function getSpecFieldValue($id)
	{
	  	if (isset($this->specFieldData[$id]))
	  	{
		    return $this->specFieldData[$id];
		}
	}

	public function setSpecFieldValue($id, $value)
	{
	  	if (isset($this->specFieldData[$id]) && !isset($this->addedSpecFieldValues[$id]))
	  	{
			$this->modifiedSpecFieldValues[$id] = true;
		}
		else
		{
			$this->addedSpecFieldValues[$id] = true;		  
		}

	    $this->specFieldData[$id] = $value;
	}

	public function toArray()
	{
	  	$array = parent::toArray();
	  	$array['specFieldData'] = $this->specFieldData;
	}

	/**
	 * Removes a product from a database
	 *
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
	 * Gets a product specification instance
	 *
	 * @return ProductSpecification
	 */
	public function getSpecification()
	{
		$specification = new ProductSpecification($this);
		return $specification;
	}

}

?>