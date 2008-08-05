<?php

ClassLoader::import('application.model.product.Product');
ClassLoader::import('application.model.category.Category');
ClassLoader::import('application.model.system.MultilingualObject');
ClassLoader::import('application.model.product.ProductOptionChoice');

/**
 * Configurable product options
 *
 * @package application.model.product
 * @author Integry Systems <http://integry.com>
 */
class ProductOption extends MultilingualObject
{
	const TYPE_BOOL = 0;

	const TYPE_SELECT = 1;

	const TYPE_TEXT = 2;

	protected $choices = array();

	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("ProductOption");

		$schema->registerCircularReference('DefaultChoice', 'ProductOptionChoice');

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("productID", "Product", "ID", null, ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("categoryID", "Category", "ID", null, ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("defaultChoiceID", "ProductOptionChoice", "ID", "ProductOptionChoice", ARInteger::instance()));

		$schema->registerField(new ARField("name", ARArray::instance()));
		$schema->registerField(new ARField("description", ARArray::instance()));
		$schema->registerField(new ARField("type", ARInteger::instance(4)));
		$schema->registerField(new ARField("isRequired", ARBool::instance()));
		$schema->registerField(new ARField("isDisplayed", ARBool::instance()));
		$schema->registerField(new ARField("isDisplayedInList", ARBool::instance()));
		$schema->registerField(new ARField("isDisplayedInCart", ARBool::instance()));
		$schema->registerField(new ARField("position", ARInteger::instance(4)));
	}

	/**
	 * Creates a new option instance
	 *
	 * @param Category $category
	 *
	 * @return Product
	 */
	public static function getNewInstance(ActiveRecordModel $parent)
	{
		$option = parent::getNewInstance(__CLASS__);

		if ($parent instanceof Product)
		{
			$option->product->set($parent);
		}
		else if ($parent instanceof Category)
		{
			$option->category->set($parent);
		}
		else
		{
			throw new ApplicationException('ProductOption parent must be either Product or Category');
		}

		return $option;
	}

	/**
	 * Get ActiveRecord instance
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

	/*####################  Value retrieval and manipulation ####################*/

	public function isBool()
	{
		return $this->type->get() == self::TYPE_BOOL;
	}

	public function isText()
	{
		return $this->type->get() == self::TYPE_TEXT;
	}

	public function isSelect()
	{
		return $this->type->get() == self::TYPE_SELECT;
	}

	public function addChoice(ProductOptionChoice $choice)
	{
		$this->choices[$choice->getID()] = $choice;
	}

	public function getChoiceByID($id)
	{
		$s = $this->getRelatedRecordSet('ProductOptionChoice', new ARSelectFilter(new EqualsCond(new ARFieldHandle('ProductOptionChoice', 'ID'), $id)));
		if ($s->size())
		{
			return $s->get(0);
		}
	}

	public static function loadOptionsForProductSet(ARSet $products)
	{
		// load category options
		$f = new ARSelectFilter();

		$categories = $productIDs = array();
		foreach ($products as $product)
		{
			$categories[$product->category->get()->getID()] = $product->category->get();
			$productIDs[] = $product->getID();
		}

		foreach ($categories as $category)
		{
			$c = new EqualsOrLessCond(new ARFieldHandle('Category', 'lft'), $category->lft->get());
			$c->addAND(new EqualsOrMoreCond(new ARFieldHandle('Category', 'rgt'), $category->rgt->get()));

			if (!isset($categoryCond))
			{
				$categoryCond = $c;
			}
			else
			{
				$categoryCond->addOR($c);
			}
		}

		// product options
		$productCond = new INCond(new ARFieldHandle('ProductOption', 'productID'), $productIDs);
		if (!isset($categoryCond))
		{
			$categoryCond = $productCond;
		}
		else
		{
			$categoryCond->addOR($productCond);
		}

		$f->setCondition($categoryCond);

		// ordering
		$f->setOrder(new ARFieldHandle('ProductOption', 'productID'), 'DESC');
		$f->setOrder(new ARFieldHandle('Category', 'lft'), 'DESC');
		$f->setOrder(new ARFieldHandle('ProductOption', 'position'), 'DESC');

		$options = ProductOption::getRecordSet($f, array('DefaultChoice' => 'ProductOptionChoice', 'Category'));

		self::loadChoicesForRecordSet($options);

		// sort by products
		$sorted = array();
		foreach ($products as $product)
		{
			foreach ($options as $index => $option)
			{
				if ($option->product->get() && ($option->product->get()->getID() == $product->getID()))
				{
					$sorted[$product->getID()][] = $option;
					$options->remove($index);
				}

				if ($option->category->get())
				{
					$option->category->get()->load();
					if ($option->category->get()->isAncestorOf($product->category->get()))
					{
						$sorted[$product->getID()][] = $option;
					}
				}
			}
		}

		return $sorted;
	}

	public static function loadChoicesForRecordSet(ARSet $options)
	{
		$ids = $refs = array();

		// load select option choices
		foreach ($options as $option)
		{
			if ($option->isSelect())
			{
				$ids[] = $option->getID();
				$refs[$option->getID()] = $option;
			}
		}

		if ($ids)
		{
			$f = new ARSelectFilter(new INCond(new ARFieldHandle('ProductOptionChoice', 'optionID'), $ids));
			$f->setOrder(new ARFieldHandle('ProductOptionChoice', 'position'));

			foreach (ActiveRecordModel::getRecordSet('ProductOptionChoice', $f) as $choice)
			{
				$refs[$choice->option->get()->getID()]->addChoice($choice);
			}
		}
	}

	/*####################  Saving ####################*/

	protected function insert()
	{
	  	$this->setLastPosition();

		parent::insert();
	}

	public static function deleteByID($id)
	{
		return parent::deleteByID(__class__, $id);
	}

	/*####################  Data array transformation ####################*/

	public function toArray()
	{
		$array = parent::toArray();

	  	if ($this->choices)
	  	{
	  		$array['choices'] = array();

	  		foreach ($this->choices as $choice)
	  		{
	  			$array['choices'][] = $choice->toArray();
			}
		}

		$this->setArrayData($array);

	  	return $array;
	}

	/*####################  Get related objects ####################*/

	public function getChoiceSet()
	{
		$f = new ARSelectFilter();
		$f->setOrder(new ARFieldHandle('ProductOptionChoice', 'position'));

		return $this->getRelatedRecordSet('ProductOptionChoice', $f);
	}

}

?>