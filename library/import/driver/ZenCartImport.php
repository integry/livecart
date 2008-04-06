<?php

include_once dirname(__file__) . '/OsCommerceImport.php';

class ZenCartImport extends OsCommerceImport
{
	public function getName()
	{
		return 'Zen Cart';
	}

	public function isPathValid()
	{
		// no path provided - won't be able to import images
		if (!$this->path)
		{
			return true;
		}

		if (!parent::isPathValid())
		{
			return false;
		}

		return file_exists($this->path . '/admin/coupon_admin.php');
	}

	public function getNextProduct()
	{
		if (!$product = parent::getNextProduct())
		{
			return null;
		}

		$data = $product->rawData;

		// set Zen Cart specific fields
		foreach ($this->languages as $code)
		{
			if (!empty($data['shortDescr_' . $code]))
			{
				$product->setValueByLang('shortDescription', $code, $data['shortDescr_' . $code]);

				// long description the same as name - a common situation?
				if ($data['name_' . $code] == $data['descr_' . $code])
				{
					$product->setValueByLang('longDescription', $code, $data['shortDescr_' . $code]);
				}
			}

			if (!empty($data['keywords_' . $code]))
			{
				$product->keywords->set($data['keywords_' . $code]);
			}
		}

		return $product;
	}

	public function getNextCategory()
	{
		if (!$category = parent::getNextCategory())
		{
			return null;
		}

		$data = $category->rawData;

		// set Zen Cart specific fields
		foreach ($this->languages as $code)
		{
			$category->setValueByLang('description', $code, $data['descr_' . $code]);

			if (!empty($data['keywords_' . $code]))
			{
				$category->keywords->set($data['keywords_' . $code]);
			}
		}

		return $category;
	}

	protected function joinCategoryFields($id, $code)
	{
		return array('LEFT JOIN ' . $this->getTablePrefix() . 'categories_description AS category_' . $code . ' ON category_' . $code . '.categories_id=' . $this->getTablePrefix() . 'categories.categories_id AND category_' . $code . '.language_id=' . $id,
					 'category_' . $code . '.categories_name AS name_' . $code . ', category_' . $code . '.categories_description AS descr_' . $code . ($this->fieldExists('categories_description', 'categories_meta_keywords') ? ', category_' . $code . '.categories_meta_keywords AS keywords_' . $code : '')
					);
	}

	protected function joinProductFields($id, $code)
	{
		return array('LEFT JOIN ' . $this->getTablePrefix() . 'products_description AS product_' . $code . ' ON product_' . $code . '.products_id=' . $this->getTablePrefix() . 'products.products_id AND product_' . $code . '.language_id=' . $id,
					 'product_' . $code . '.products_name AS name_' . $code . ', ' . 'product_' . $code . '.products_description AS descr_' . $code .

					 ($this->fieldExists('products_description', 'products_short_description') ? ', product_' . $code . '.products_short_description AS shortDescr_' . $code : '') .
					 ($this->fieldExists('products_description', 'products_meta_keywords') ? ', product_' . $code . '.products_meta_keywords AS keywords_' . $code : '')
					 // . ', ' . 'product_' . $code . '.products_meta_keywords AS keywords_' . $code
					);
	}
}

?>
