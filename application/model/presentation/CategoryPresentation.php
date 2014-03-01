<?php


/**
 * Store entity presentation configuration (products, categories)
 *
 * @package application/model/product
 * @author Integry Systems <http://integry.com>
 */
class CategoryPresentation extends ActiveRecordModel
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("categoryID", "Category", "ID", null, ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("productID", "Product", "ID", null, ARInteger::instance()));

		$schema->registerField(new ARField("isSubcategories", ARBool::instance()));
		$schema->registerField(new ARField("isVariationImages", ARBool::instance()));
		$schema->registerField(new ARField("isAllVariations", ARBool::instance()));
		$schema->registerField(new ARField("theme", ARVarchar::instance(20)));
		$schema->registerField(new ARField("listStyle", ARVarchar::instance(20)));

		return $schema;
	}

	public static function getInstance(ActiveRecordModel $parent)
	{
		$parentClass = get_class($parent);
		$set = $parent->getRelatedRecordSet(__CLASS__, new ARSelectFilter(), array($parentClass));
		if ($set->size())
		{
			return $set->get(0);
		}
		else
		{
			return self::getNewInstance($parent);
		}
	}

	public function getTheme()
	{
		return $this->theme;
	}

	public static function getNewInstance(ActiveRecordModel $parent)
	{
		$instance = new self();
		if ($parent instanceof Category)
		{
			$instance->category->set($parent);
		}
		else
		{
			$instance->product->set($parent);
		}

		return $instance;
	}

	public static function getThemeByCategory(Category $category)
	{
		$f = new ARSelectFilter(self::getCategoryCondition($category));
		self::setCategoryorderBy($category, $f);

		$set = ActiveRecordModel::getRecordSet(__CLASS__, $f, array('Category'));
		return self::getInheritedConfig($set);
	}

	public static function getThemeByProduct(Product $product, Category $category = null)
	{
		$category = $category ? $category : $product->getCategory();
		$c = eq(__CLASS__ . '.productID', $product->getID());
		$c->addOr(self::getCategoryCondition($category));
		$f = select($c);
		$f->orderBy(new ARExpressionHandle('CategoryPresentation.productID=' . $product->getID()), 'DESC');
		self::setCategoryorderBy($category, $f);

		// check if a theme is defined for this product particularly
		$set = ActiveRecordModel::getRecordSet(__CLASS__, $f, array('Category'));
		return self::getInheritedConfig($set);
	}

	private function getInheritedConfig(ARSet $set)
	{
		if ($set->size())
		{
			// category level configuration?
			$prod = $set->shift();

			// fill missing product level settings with category level settings
			foreach ($set as $cat)
			{
				foreach (array('theme', 'isAllVariations', 'isVariationImages', 'listStyle') as $field)
				{
					if (!$prod->$field)
					{
						$prod->$field->set($cat->$field);
					}
				}
			}

			return $prod;
		}
	}

	private static function getCategoryCondition(Category $category)
	{
		$own = new EqualsCond(new ARFieldHandle(__CLASS__, 'categoryID'), $category->getID());
		$parent = new EqualsOrLessCond('Category.lft', $category->lft);
		$parent->andWhere(new EqualsOrMoreCond('Category.rgt', $category->rgt));
		$parent->andWhere(new EqualsCond(new ARFieldHandle(__CLASS__, 'isSubcategories'), true));
		$own->addOR($parent);

		return $own;
	}

	private static function setCategoryorderBy(Category $category, ARSelectFilter $f)
	{
		$f->orderBy(new ARExpressionHandle('CategoryPresentation.categoryID=' . $category->getID()), 'DESC');
		$f->orderBy('Category.lft', 'DESC');
	}
}

?>