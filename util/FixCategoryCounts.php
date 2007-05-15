<?php

include '../application/Initialize.php';

ClassLoader::import('application.model.category.Category');

// category product counts
$sql = 'UPDATE Category SET totalProductCount = (SELECT COUNT(*) FROM Product WHERE categoryID = Category.ID)';
ActiveRecord::executeUpdate($sql);

// subcategory counts
function updateProductCount(Category $category)
{
	$count = 0;
	foreach ($category->getSubCategorySet() as $sub)
	{
		updateProductCount($sub);
		$count += $sub->totalProductCount->get();	
	}
	
	$category->totalProductCount->set($category->totalProductCount->get() + $count);
	$category->save();
}

updateProductCount(Category::getInstanceByID(Category::ROOT_ID, Category::LOAD_DATA));

// active product count
$sql = 'UPDATE Category SET activeProductCount = totalProductCount, availableProductCount = totalProductCount';
ActiveRecord::executeUpdate($sql);

?>