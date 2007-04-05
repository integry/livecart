<?php

echo "<pre>";
include("../Initialize.php");
include('../../../prex/PrexCategory.php');
ClassLoader::import("application.model.category.*");
ClassLoader::import("application.model.product.*");

// download all data
//$prex = new PrexCategory(1); exit;

include cachedir . 'prex_dict.php';

$categoryIDs = array();
$i = new DirectoryIterator(cachedir . '/cat');
foreach ($i as $key => $value)
{
  	if (!$value->isDot())
  	{
	    $categoryIDs[] = (int)(string)$value;
	}
}

//ActiveRecordModel::beginTransaction();
$products = array();

$byType = array();

$categoryNames = array();

foreach ($categoryIDs as $ccid => $id)
{
	$prex = new PrexCategory($id);
	foreach ($prex->getProducts() as $product)
	{
        echo '<h1>' . $product->getID() . '</h1>';
                
        $prices = $product->getPrices();
        
        if ($prices)
        {
            $avg = array_sum($prices) / count($prices);             
        }
        else
        {
            $avg = rand($avg, $avg * 1.2);    
        }
        
        $usd = $avg / 2.5;
        
        $prod = Product::getInstanceBySku($product->getID(), Product::LOAD_DATA);
        
        if (!$prod)
        {
            continue;
        }
        
        $prod->setPrice('USD', $usd);
        $prod->save();
        
        //print_r($product->getPrices());
        //exit;
    }

}

//ActiveRecordModel::commit();
//ActiveRecordModel::rollback();

exit;

?>