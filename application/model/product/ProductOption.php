<?php

namespace product;

use \category\Category;

/**
 * Configurable product options
 *
 * @package application/model/product
 * @author Integry Systems <http://integry.com>
 */
class ProductOption extends \system\MultilingualObject
{
	const TYPE_BOOL = 0;
	const TYPE_SELECT = 1;
	const TYPE_TEXT = 2;
	const TYPE_FILE = 3;

	const DISPLAYTYPE_SELECTBOX = 0;
	const DISPLAYTYPE_RADIO = 1;
	const DISPLAYTYPE_COLOR = 2;

	public $ID;
	public $name;
	public $description;
	public $selectMessage;
	public $type;
	public $displayType;
	public $isRequired;
	public $isDisplayed;
	public $isDisplayedInList;
	public $isDisplayedInCart;
	public $isPriceIncluded;
	public $position;
	public $maxFileSize;
	public $fileExtensions;
	
	public function initialize()
	{
		$this->belongsTo('productID', 'product\Product', 'ID', array('alias' => 'Product'));
		//$this->belongsTo('categoryID', 'category\Category', 'ID', array('alias' => 'Category'));
        $this->hasMany('ID', 'product\ProductOptionChoice', 'optionID', array('alias' => 'Choices'));
        //$this->hasOne('ID', 'product\ProductOptionChoice', 'defaultChoiceID', array('alias' => 'DefaultChoice'));
	}
	
	protected function _preSaveRelatedRecords()
	{
		return true;
	}

	protected function _postSaveRelatedRecords()
	{
		return true;
	}

	/**
	 * Creates a new option instance
	 *
	 * @param Category $category
	 *
	 * @return Product
	 */
	public static function getNewInstance(\ActiveRecordModel $parent)
	{
		$option = new self();

		if ($parent instanceof Product)
		{
			$option->productID = $parent->getID();
		}
		else if ($parent instanceof Category)
		{
			$option->category = $parent;
		}
		else
		{
			throw new ApplicationException('ProductOption parent must be either Product or Category');
		}

		return $option;
	}

	/*####################  Value retrieval and manipulation ####################*/

	public function isBool()
	{
		return $this->type == self::TYPE_BOOL;
	}

	public function isText()
	{
		return $this->type == self::TYPE_TEXT;
	}

	public function isSelect()
	{
		return $this->type == self::TYPE_SELECT;
	}

	public function isFile()
	{
		return $this->type == self::TYPE_FILE;
	}

	public function addChoice(ProductOptionChoice $choice)
	{
		$this->choices[$choice->getID()] = $choice;
	}

	public function getChoiceByID($id)
	{
		foreach ($this->choices as $choice)
		{
			if ($choice->getID() == $id)
			{
				return $choice;
			}
		}
	}

	public static function loadOptionsForProductSet(ARSet $products)
	{
		// load category options
		$f = new ARSelectFilter();

		$categories = $productIDs = array();
		foreach ($products as $product)
		{
			foreach ($product->getAllCategories() as $cat)
			{
				$categories[$cat->getID()] = $cat;
			}

			$productIDs[] = $product->getID();
			if ($product->parent)
			{
				$productIDs[] = $product->parent->getID();
			}
		}

		foreach ($categories as $category)
		{
			if($category->isLoaded() == false)
			{
				$category->load();
			}
			$c = new EqualsOrLessCond('Category.lft', $category->lft);
			$c->andWhere(new EqualsOrMoreCond('Category.rgt', $category->rgt));

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
		$productCond = new INCond('ProductOption.productID', $productIDs);
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
		$f->orderBy('ProductOption.productID', 'DESC');
		$f->orderBy('Category.lft', 'DESC');
		$f->orderBy('ProductOption.position', 'DESC');


		$options = ProductOption::getRecordSet($f, array('DefaultChoice' => 'ProductOptionChoice', 'Category'));

		self::loadChoicesForRecordSet($options);

		// sort by products
		$sorted = array();
		foreach ($products as $product)
		{
			foreach ($options as $index => $option)
			{
				if ($option->product && (($option->product->getID() == $product->getID()) || ($product->parent && ($option->product->getID() == $product->parent->getID()))))
				{
					$sorted[$product->getID()][] = $option;
				}

				if ($option->category)
				{
					$option->category->load();
					foreach ($product->getAllCategories() as $category)
					{
						if ($option->category->isAncestorOf($category))
						{
							$sorted[$product->getID()][] = $option;
							break;
						}
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
			$f = new ARSelectFilter(new INCond('ProductOptionChoice.optionID', $ids));
			$f->orderBy('ProductOptionChoice.position');

			foreach (ActiveRecordModel::getRecordSet('ProductOptionChoice', $f) as $choice)
			{
				$refs[$choice->option->getID()]->addChoice($choice);
			}
		}
	}

	public static function includeProductPrice(Product $product, &$options)
	{
		$prices = $product->getPricingHandler()->toArray();
		$prices = $prices['calculated'];
		foreach ($options as &$option)
		{
			if (!empty($option['choices']))
			{
				foreach ($option['choices'] as &$choice)
				{
					foreach ($prices as $currency => $price)
					{
						$instance = Currency::getInstanceByID($currency);
						$choice['formattedTotalPrice'][$currency] = $instance->getFormattedPrice($price + $instance->convertAmountFromDefaultCurrency($choice['priceDiff']));
					}
				}
			}
		}
	}

	public static function getFileExtensions($extensionString)
	{
		$s = trim(preg_replace('/[^ a-z0-9]/', '', strtolower($extensionString)));
		$extensions = array();
		foreach (explode(' ', $s) as $ext)
		{
			$extensions[] = trim($ext);
		}

		return $extensions;
	}

	/*####################  Saving ####################*/

	public function beforeCreate()
	{
		$this->setLastPosition();
	}

	/*####################  Data array transformation ####################*/

	public function toArray()
	{
		$array = parent::toArray();
		$array['choices'] = toArray($this->getChoices());
		return $array;
	}

	public function z__clone()
	{
		parent::__clone();

		$this->choices = null;
		$this->save();

		$defaultChoice = $this->originalRecord->defaultChoice;

		foreach ($this->originalRecord->getChoiceSet() as $choice)
		{
			$newChoice = clone $choice;
			$newChoice->option = $this;
			$newChoice->save();

			if ($defaultChoice && ($choice->getID() == $defaultChoice->getID()))
			{
				$this->defaultChoice = $newChoice;
			}
		}

		$this->save();
	}
}

?>
